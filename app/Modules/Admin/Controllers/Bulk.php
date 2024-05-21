<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleApi;
use App\Libraries\Uuid;
use App\Models\CategoryModel;
use App\Models\DomainModel;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\LdapModel;
use App\Models\UserModel;
use Config\Ldap;


class Bulk extends BaseController
{
    private  $globalModel, $uuid, $groupModel, $categoryModel, $ldapConf, $userModel, $ldapModel, $domainModel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->globalModel = new GlobalModel();
        $this->uuid = new Uuid();
        $this->ldapConf = new Ldap();
        $this->ldapModel = new LdapModel();
        $this->groupModel = new GroupModel();
        $this->domainModel = new DomainModel();
        $this->userModel = new UserModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index($arg = null)
    {
        $data = [
            'title' => 'Bulk',
            'content' => 'admin/v_bulk',
            'a_menu' => 'bulk',
            'sub_menu' => $arg ? $arg : '',
            'validation' => \Config\Services::validation(),
        ];
        return view('layout/v_wrapper', $data);
    }

    public function create()
    {
        $rules = [
            'import_create' => [
                'rules' => 'uploaded[import_create]|max_size[import_create,10240]|ext_in[import_create,csv,xls,xlsx,ods]',
                'errors' => [
                    'uploaded' => 'File harus diunggah',
                    'max_size' => 'Ukuran maksimal 10MB',
                    'ext_in' => 'Hanya file csv,xls,xlsx,ods',
                ],
            ],
        ];
        // Validation
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('validation', \Config\Services::validation());

        $path = WRITEPATH . 'uploads/';
        $file = $this->request->getFile('import_create');
        if (!$file->isValid() && $file->hasMoved()) { //jika file tidak valid
            session()->setFlashdata('error', 'File error');
            return redirect()->back();
        }

        $arr_name = explode('.', $file->getName());
        $ext_file = end($arr_name);
        $newName = $arr_name[0] . '_' . date('Ymdhis') . '.' . $ext_file; // format nama baru, nama_file_tahunbulanharijammenitdetik.ext
        $file->move($path, $newName);
        if ($ext_file === 'csv') {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } elseif ($ext_file === 'xls') {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }

        $spreadsheet = $reader->load($path . $newName);
        $data = $spreadsheet->getActiveSheet()->toArray();
        $col_name = $data[0];

        // proses olah data yang diperoleh dari sheet
        foreach ($data as $k => $v) {
            if ($k === 0) continue;  // lewati karena isinya header field
            $c = $k - 1;

            for ($i = 0; $i < count($v); $i++) { // array dirangkai biar sesuai field
                $j[$col_name[$i]] = $v[$i];
            }
            $data_new[] = $j; // masukkan array yang sudah tertata ke array kosong
        }

        // Mulai proses semua data untuk diimport
        $total = 0;
        foreach ($data_new as $k => $v) {

            if (empty($v['firstname'])) { // jika firstname kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' firstname kosong!');
                return redirect()->back();
            }
            if (empty($v['lastname'])) { // jika lastname kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' lastname kosong!');
                return redirect()->back();
            }
            if (empty($v['email'])) { // jika email kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' email kosong!');
                return redirect()->back();
            }
            if (empty($v['password'])) { // jika password kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' password kosong!');
                return redirect()->back();
            }
            if (empty($v['ou'])) { // jika password kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' ou kosong!');
                return redirect()->back();
            }
            $email = $v['email'];
            $domain = emailToDomain($email);
            $uid = emailToUid($email);
            $ou = $v['ou'];
            $get_ou = $this->categoryModel->getCatByName($ou);
            $ou_id = $get_ou->id;
            $base_dn = $get_ou->base_dn;
            $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $base_dn;
            $gid = $get_ou->id;
            $id_last = $this->userModel->getNextId()->last_value; // dapatkan id terakhir pada table User untuk dijadikan uidNumber ldap
            $uidNumber = $id_last + 1;
            $domain_id = $this->domainModel->getDomain($domain)->get()->getFirstRow()->id;

            // prepare db data
            $db_data = [
                'firstname' => $v['firstname'],
                'lastname' => $v['lastname'],
                'email' => $v['email'],
                'password' => password_hash($v['password'], PASSWORD_DEFAULT),
                'pass_ldap' => ldapPass($v['password']),
                'dispname' => $v['firstname'] . ' ' . $v['lastname'],
                'domain' => $domain_id,
                'active' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => session()->get('email'),
                'info' => 'bulk create',
                'role' => 2,
                'ou' => $ou_id,
            ];

            // ldap data
            $ld_data = [
                'objectClass' => [
                    0 => 'inetOrgPerson',
                    1 => 'posixAccount',
                ],
                'userPassword' => $db_data['pass_ldap'],
                'displayname' =>  $db_data['dispname'],
                'cn' =>  $db_data['dispname'],
                'givenName' =>  $db_data['firstname'],
                'sn' =>  $db_data['lastname'],
                'gidNumber' =>  $gid,
                'uidNumber' =>  $uidNumber,
                'uid' =>  $uid,
                'mail' =>  $email,
                'homeDirectory' => '/home/' . $uid,
            ];

            // prepare google data untuk insert
            $g_data = [
                'name' => [
                    'givenName' => $db_data['firstname'], // = firstname
                    'familyName' => $db_data['lastname'],  // = lastname
                ],
                'password' => $v['password'],
                'primaryEmail' => $email,
                'orgUnitPath' => '/' . $ou,
            ];
            // dd($db_data, $ld_data, $dn, $g_data);

            // cek di ldap apakah email exist 
            if ($this->ldapModel->getMatchUser($base_dn, $email)['count'] == 1) {
                session()->setFlashdata('error', 'LDAP error - akun ' . $email . ' sudah ada!');
                return redirect()->back();
            }

            // Proses tambah user di LDAP
            $ld_add_user = $this->ldapModel->createUser($dn, $ld_data);
            if (!$ld_add_user) { // cek apakah proses tambah user ke ldap sukses
                session()->setFlashdata('error', 'LDAP error - Gagal menambahkan <br> ' . $email);
                $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal menambahkan  ' . $email); // Catat log ke database
                return redirect()->back();
            }

            // Proses insert ke database
            $check_user_in_db = $this->userModel->getUserByEmail($email); // check jika user sdh ada di database
            if ($check_user_in_db) {
                session()->setFlashdata('error', 'Database error - sudah ada <br>' . $email);
                $this->globalModel->insertLog(session()->get('email'), 'Database error - sudah ada ' . $email); // Catat log ke database
                return redirect()->back();
            }
            $db_add_user = $this->userModel->saveUser($db_data);
            if (!$db_add_user) {
                session()->setFlashdata('error', 'Database error - Gagal menambahkan <br>' . $email);
                $this->globalModel->insertLog(session()->get('email'), 'Database error - Gagal menambahkan ' . $email); // Catat log ke database
                return redirect()->back();
            }

            // jika sync dengan google di-enable
            if (Config('MyConfig')->google['sync']) {
                // Proses insert ke google
                $gApi = new GoogleApi();
                if ($gApi->getUser($email)) { // Jika user sudah ada di google, diset unsuspend
                    $g_data = array_merge($g_data, ["suspended" => false]);
                    $gApi->patchUser($email, $g_data);
                } else { // jika belum ada, create user di google
                    $gApi->insertUser($g_data);
                }
            }

            $this->globalModel->insertLog(session()->get('email'), 'Bulk create - sukses tambah ' . $email);  // Catat log ke database
            $total++;
        }

        unlink($path . $newName); // hapus file yang diunggah
        session()->setFlashdata('msg', 'Sukses tambah ' . $total . ' akun via impor!');
        return redirect()->to(base_url('admin/users'));
    }

    public function delete()
    {
        $rules = [
            'import_delete' => [
                'rules' => 'uploaded[import_delete]|max_size[import_delete,10240]|ext_in[import_delete,csv,xls,xlsx,ods]',
                'errors' => [
                    'uploaded' => 'File harus diunggah',
                    'max_size' => 'Ukuran maksimal 10MB',
                    'ext_in' => 'Hanya file csv,xls,xlsx,ods',
                ],
            ],
        ];

        // Validation
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('validation', \Config\Services::validation());

        $path = WRITEPATH . 'uploads/';
        $file = $this->request->getFile('import_delete');
        if (!$file->isValid() && $file->hasMoved()) { //jika file tidak valid
            session()->setFlashdata('error', 'File error');
            return redirect()->back();
        }

        $arr_name = explode('.', $file->getName());
        $ext_file = end($arr_name);
        $newName = $arr_name[0] . '_' . date('Ymdhis') . '.' . $ext_file; // format nama baru, nama_file_tahunbulanharijammenitdetik.ext
        $file->move($path, $newName);
        if ($ext_file === 'csv') {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } elseif ($ext_file === 'xls') {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }

        $spreadsheet = $reader->load($path . $newName);
        $data = $spreadsheet->getActiveSheet()->toArray();
        $col_name = $data[0];

        // proses olah data yang diperoleh dari sheet
        foreach ($data as $k => $v) {
            if ($k === 0) continue;  // lewati karena isinya header field
            $c = $k - 1;

            for ($i = 0; $i < count($v); $i++) { // array dirangkai biar sesuai field
                $j[$col_name[$i]] = $v[$i];
            }
            $data_new[] = $j; // masukkan array yang sudah tertata ke array kosong
        }

        // Mulai proses semua data 
        $total = 0;
        foreach ($data_new as $k => $v) {
            if (empty($v['email'])) { // jika email kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' email kosong!');
                return redirect()->back();
            }
            $email = $v['email'];
            $domain = emailToDomain($email);
            $uid = emailToUid($email);
            $getUser = $this->userModel->getUserByEmail($email);
            if (empty($getUser)) { // jika user tidak ada
                session()->setFlashdata('error', 'Gagal data ' . $email . ' tidak ditemukan!');
                return redirect()->back();
            }
            $user_id = $getUser->id;
            $ou_id = $getUser->ou;
            $get_ou = $this->categoryModel->getCtgById($ou_id);
            $ou_name = $get_ou->name;
            $base_dn = $get_ou->base_dn;
            $dn = 'uid=' . $uid . ',ou=' . $ou_name . ',' . $base_dn;

            // Proses ke LDAP
            $get_groups = $this->groupModel->getGrpByIdUser($user_id);
            if ($get_groups) {
                foreach ($get_groups as $g) {
                    $group_dn = 'cn=' . $g->g_name . ',' . $g->base_group_dn;
                    $delUserFromGroup = $this->ldapModel->delUserFromGroup($group_dn, $dn);  //hapus user dari semua grup dulu
                }
            }
            $cek_ldap_user = $this->ldapModel->getMatchUser($base_dn, $email);
            if (isset($cek_ldap_user["count"]) && $cek_ldap_user["count"] === 1) { // cek jika user ada
                $del_user_ldap = $this->ldapModel->deleteUser($dn); // hapus user di ldap
                if (!$del_user_ldap) {
                    session()->setFlashdata('error', 'Ldap error - Gagal hapus ' . $dn);
                    return redirect()->back();
                }
            }

            //  jika google sync enable
            if (Config('MyConfig')->google['sync']) {
                // Proses suspend user di google
                $gApi = new GoogleApi();
                if ($gApi->getUser($email)) { // Jika user sudah ada di google, diset unsuspend
                    $gApi->patchUser($email, ["suspended" => true]);
                }
            }

            //  prepare data untuk delete di database
            $data_del = $this->userModel->getUser($user_id);
            if (empty($data_del)) {
                session()->setFlashdata('error', 'Database error - Gagal ' . $user_id . ' tidak ditemukan');
                return redirect()->back();
            }
            $data_del = array_merge($data_del, [
                'deleted_by' => session()->get('email'),
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

            $this->groupModel->deleteAllGroupInUser($user_id);   //hapus dulu semua grup yang dimiliki user
            $del_user = $this->userModel->insertDelUser($data_del);
            if (!$del_user) {
                session()->setFlashdata('error', 'Database error - Gagal hapus ' . $user_id);
                return redirect()->back();
            }

            $this->globalModel->insertLog(session()->get('email'), 'Bulk delete - sukses hapus ' . $email);  // Catat log ke database
            $total++;
        }

        unlink($path . $newName); // hapus file yang diunggah
        session()->setFlashdata('msg', 'Sukses hapus ' . $total . ' akun via impor!');
        return redirect()->to(base_url('admin/users'));
    }

    public function reset()
    {
        $rules = [
            'import_reset' => [
                'rules' => 'uploaded[import_reset]|max_size[import_reset,10240]|ext_in[import_reset,csv,xls,xlsx,ods]',
                'errors' => [
                    'uploaded' => 'File harus diunggah',
                    'max_size' => 'Ukuran maksimal 10MB',
                    'ext_in' => 'Hanya file csv,xls,xlsx,ods',
                ],
            ],
        ];

        // Validation
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('validation', \Config\Services::validation());

        $path = WRITEPATH . 'uploads/';
        $file = $this->request->getFile('import_reset');
        if (!$file->isValid() && $file->hasMoved()) { //jika file tidak valid
            session()->setFlashdata('error', 'File error');
            return redirect()->back();
        }

        $arr_name = explode('.', $file->getName());
        $ext_file = end($arr_name);
        $newName = $arr_name[0] . '_' . date('Ymdhis') . '.' . $ext_file; // format nama baru, nama_file_tahunbulanharijammenitdetik.ext
        $file->move($path, $newName);
        if ($ext_file === 'csv') {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } elseif ($ext_file === 'xls') {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }

        $spreadsheet = $reader->load($path . $newName);
        $data = $spreadsheet->getActiveSheet()->toArray();
        $col_name = $data[0];

        // proses olah data yang diperoleh dari sheet
        foreach ($data as $k => $v) {
            if ($k === 0) continue;  // lewati karena isinya header field
            $c = $k - 1;

            for ($i = 0; $i < count($v); $i++) { // array dirangkai biar sesuai field
                $j[$col_name[$i]] = $v[$i];
            }
            $data_new[] = $j; // masukkan array yang sudah tertata ke array kosong
        }

        // Mulai proses semua data 
        $total = 0;
        foreach ($data_new as $k => $v) {
            if (empty($v['email'])) { // jika email kosong
                session()->setFlashdata('error', 'Gagal data ke ' . ($k + 1) . ' email kosong!');
                return redirect()->back();
            }

            empty($v['password']) ? $password = random_string('alnum', 8) : $password = $v['password'];  // jika password kosong

            $email = $v['email'];
            $domain = emailToDomain($email);
            $uid = emailToUid($email);
            $getUser = $this->userModel->getUserByEmail($email);
            if (empty($getUser)) { // jika user tidak ada
                session()->setFlashdata('error', 'Gagal data ' . $email . ' tidak ditemukan!');
                return redirect()->back();
            }
            $user_id = $getUser->id;
            $get_category = $this->domainModel->getDomainJoin()
                ->where('a.name', $domain)
                ->get()->getFirstRow();
            $ou = $get_category->ctg_name;
            $base_dn = $get_category->base_dn;
            $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $base_dn;

            // prepare db data
            $db_data = [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'pass_ldap' => ldapPass($password),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => session()->get('email'),
                'info' => 'Bulk reset: ' . $password,
            ];

            // ldap data
            $ld_data = [
                'userPassword' => $db_data['pass_ldap'],
            ];

            // google data
            $g_data = [
                'password' => $password,
            ];

            // jika sync dengan google di-enable
            if (Config('MyConfig')->google['sync']) {
                // Proses insert ke google
                $gApi = new GoogleApi();
                $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google
                if ($queryGoogle['httpcode'] === 400 && isset($queryGoogle['body']['error']['message'])) { // jika ada pesan error dari google
                    $this->globalModel->insertLog($email, 'Google Error - ' . $queryGoogle['body']['error']['message']);
                    session()->setFlashdata('error', 'Google Error - ' . $queryGoogle['body']['error']['message']);
                    return redirect()->back();
                }
                if (!$queryGoogle) {
                    session()->setFlashdata('error', 'Google error - Gagal ubah password ' . $email);
                    $this->globalModel->insertLog($email, 'Google error - Gagal ubah password ' . $email);
                    return redirect()->back();
                }
            }

            // proses di database
            $db_update_user = $this->userModel->update($user_id, $db_data);
            if (!$db_update_user) { // jika gagal update user 
                session()->setFlashdata('error', 'Database error - Gagal update user ' . $email);
                $this->globalModel->insertLog(session()->get('email'), 'Database error - Gagal update user ' . $email);
                return redirect()->back();
            }

            // proses di ldap
            $ldap_query = $this->ldapModel->modifyUser($dn, $ld_data);
            if (!$ldap_query) { // jika gagal update user di LDAP
                session()->setFlashdata('error', 'LDAP error - Gagal update ' . $email . ' - ' . $dn);
                $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal update ' . $email . ' - ' . $dn);
                return redirect()->back();
            }

            $this->globalModel->insertLog(session()->get('email'), 'Bulk reset - Sukses ubah password email' . $email);  // Catat log ke database
            $total++;
        }

        unlink($path . $newName); // hapus file yang diunggah
        session()->setFlashdata('msg', 'Sukses ubah ' . $total . ' akun via impor!');
        return redirect()->to(base_url('admin/users'));
    }

    public function file($param)
    {
        // dd(scandir(ROOTPATH . 'file/template'));
        $file = ROOTPATH . 'file/template/' . $param;
        // d($file);
        // dd(!file_exists($file));
        if (!file_exists($file)) {
            return view('errors/html/error_404.php');
        }
        return $this->response->download($file, null);
    }
}
