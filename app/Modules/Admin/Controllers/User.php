<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleApi;
use App\Models\CategoryModel;
use App\Models\DomainModel;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\UserModel;
use App\Models\LdapModel;
use Config\Ldap;

class User extends BaseController
{
    private  $ldapConf, $userModel, $ldapModel, $ldap, $domainModel, $categoryModel, $groupModel, $globalModel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->domainModel = new DomainModel();
        $this->categoryModel = new CategoryModel();
        $this->groupModel = new GroupModel();
        $this->globalModel = new GlobalModel();
        $this->ldapConf = new Ldap();
        $this->ldap = $this->ldapConf->stmikelrahma; // pilih koneksi config LDAP
        $this->ldapModel = new LdapModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Pengguna',
            'content' => 'admin/v_users',
            'a_menu' => 'userm',
        ];

        return view('layout/v_wrapper', $data);
    }

    public function users_fetch()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $rowperpage = $this->request->getPost('length');
        $columnIndex = $this->request->getPost("order[0][column]");
        $columnName = $this->request->getPost("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getPost("order[0][dir]");
        $searchValue = $this->request->getPost("search[value]");
        $response = [];

        $builder = $this->userModel->getUsers();
        $builder->select('*');
        $totalRecords = $builder->countAllResults(false);

        ## Total number of record with filtering
        if ($searchValue) {
            $builder->like('a.email', $searchValue, 'both', null, true);
            $builder->orLike('a.dispname', $searchValue, 'both', null, true);
        }
        $totalRecordsWithFilter = $builder->countAllResults(false);

        ## Fetch records
        $builder->orderBy($columnName, $columnSortOrder);
        $builder->limit($rowperpage, $start);
        $records = $builder->get()->getResult();
        $data = [];

        foreach ($records as $r) {
            $action = "<a type=\"button\" class=\"badge badge-info btn-sm \" href=\"/admin/user_edit/" . $r->id . "\"><i class=\"fa fa-edit\"></i> </a> <a type=\"button\" class=\"badge badge-danger btn-sm btn-delete\"  onclick=\"hapus('$r->email','$r->id')\"  ><i class=\"fa fa-trash-alt\"></i> </a>";

            $data[] = [
                'id' => $r->id,
                'action' => $action,
                'email' => $r->email,
                'dispname' => $r->dispname,
                'created_at' => $r->created_at,
                'created_by' => $r->created_by,
                'updated_at' => $r->updated_at,
                'updated_by' => $r->updated_by,
                'info' => $r->info,
                'active' => $r->active,
            ];
        }

        $response = [
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalRecordsWithFilter,
            'aaData' => $data,
        ];
        echo  json_encode($response);
    }

    public function user_add()
    {
        $ou = $this->request->getPost('ou_option');
        (isset($ou)) ? $default_ou = $ou : $default_ou = 'staff'; // default select ou atau category

        $getOu = $this->categoryModel->getCategoryAll()->orderBy('id')->get()->getResultArray();
        $getDomain = $this->domainModel->getDomainJoin()->where('c.name', $default_ou)->get()->getResultArray(); // dapatkan domain sesuai degan default_ou
        $getGroups = $this->groupModel->getGroupByOu($default_ou);

        $data = [
            'title' => 'Tambah Pengguna',
            'content' => 'admin/v_useradd',
            'a_menu' => 'user_add',
            'getDomain' => $getDomain,
            // 'units' => $units,
            'groups' => $getGroups,
            'getOu' => $getOu,
            'ou_selected' => $default_ou,
            'validation' => \Config\Services::validation(),
        ];
        // d($data);

        return view('layout/v_wrapper', $data);
    }

    public function user_created()
    {
        // d($_POST);
        $pass = $this->request->getVar('password');
        $uid = $this->request->getPost('email');
        $domain = $this->request->getPost('domain_hidden');
        $email = $uid . '@' . $domain;
        $nip = $this->request->getPost('nip');
        $nidn = $this->request->getPost('nidn');
        $groups = $this->request->getPost('group');
        $ou = $this->request->getPost('ou');

        $rules = [
            'firstname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} harus diisi',
                ],
            ],
            'lastname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} harus diisi',
                ],
            ],
            'password' => [
                'rules' => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Sandi min 8 karakter',
                ],
            ],
        ];

        if ($nip) {
            $rules = array_merge(
                $rules,
                [
                    'nip' => [
                        'rules' => 'alpha_numeric',
                        'errors' => [
                            'alpha_numeric' => '{field} harus angka atau huruf',
                        ],
                    ]
                ]
            );
        }

        if ($nidn) {
            $rules = array_merge(
                $rules,
                [
                    'nidn' => [
                        'rules' => 'numeric',
                        'errors' => [
                            'numeric' => '{field} harus angka',
                        ],
                    ]
                ]
            );
        }

        // validasi dulu
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
        }

        $ou_detail = $this->categoryModel->getCategoryAll()
            ->join('group_with_cat b', 'b.c_id=a.id', 'left')
            ->join('group c', 'c.g_id=b.g_id', 'left')
            ->where('a.name', $ou)
            ->get()
            ->getFirstRow();
        // dd($ou_detail);
        $base_dn = $ou_detail->base_dn;
        $base_group_dn = $ou_detail->base_group_dn;
        $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $base_dn;
        $gid = $ou_detail->id;
        $ou_id = $gid;

        // dapatkan id terakhir pada table User untuk dijadikan uidNumber ldap
        $id_last = $this->userModel->getNextId()->last_value;
        $uidNumber = $id_last + 1;

        // cari domain id
        $domain_id = $this->domainModel->getDomByName($domain)->id;

        $db_data = [
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'dispname' => $this->request->getPost('firstname') . ' ' . $this->request->getPost('lastname'),
            'domain' => $domain_id,
            'email' => $email,
            'ou' => $ou_id,
            'nip' => $nip ?? '',
            'nidn' => $nidn ?? '',
            'active' => 0, // default active = 0 (tidak aktif)
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'pass_ldap' => ldapPass($pass),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('email'),
        ];

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
        $gdata = [
            'name' => [
                'givenName' => $db_data['firstname'], // = firstname
                'familyName' => $db_data['lastname'],  // = lastname
            ],
            'password' => $pass,
            'primaryEmail' => $email,
            'orgUnitPath' => '/' . $ou,
        ];
        // dd($db_data, $ld_data, $gdata);

        // cek di ldap apakah email exist 
        if ($this->ldapModel->getMatchUser($base_dn, $email)['count'] == 1) {
            session()->setFlashdata('error', 'LDAP error - akun ' . $email . ' sudah ada!');
            return redirect()->to(base_url('admin/user_add'));
        }

        // Proses tambah user di LDAP
        $ld_add_user = $this->ldapModel->createUser($dn, $ld_data);
        if (!$ld_add_user) { // cek apakah proses tambah user ke ldap sukses
            session()->setFlashdata('error', 'LDAP error - Gagal menambahkan <br> ' . $email);
            $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal menambahkan  ' . $email); // Catat log ke database
            return redirect()->to('admin/user_add');
        }

        $get_exist_user_in_group = $this->ldapModel->getUserMemberOf($base_dn, $email);
        if (($get_exist_user_in_group)) { // cek jika user ada di sebuah group, kemudian hapus
            foreach ($get_exist_user_in_group as $ge) {
                $group_dn = 'cn=' . $ge . ',' . $base_group_dn;
                // $dn = 'uid=ajeng,ou=staff,dc=stmikelrahma,dc=ac,dc=id';
                $del_user_from_group = $this->ldapModel->delUserFromGroup($group_dn, $dn);  //hapus user dari semua grup dulu
            }
        }

        // proses tambah user ke group ldap
        foreach ($groups as $g) { // looping array groups
            $get_group = $this->groupModel->getGrpById($g); // cari group name berdasarkan id group
            $group_name = $get_group['g_name']; // cari group name berdasarkan id group
            $base_group_dn = $get_group['base_group_dn'];
            $group_dn = 'cn=' . $group_name . ',' . $base_group_dn;
            $addUserToGroup = $this->ldapModel->addUserToGroup($group_dn, $dn);  // tambahkan user ke group ldap
            if (!$addUserToGroup) {
                session()->setFlashdata('error', 'LDAP error - Gagal menambahkan <br>' . $email . ' ke group: ' . $group_name);
                $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal menambahkan ' . $email . ' ke group: ' . $group_name); // Catat log ke database
                return redirect()->to('admin/user_add');
            }
        }

        // Proses insert ke database
        $db_add_user = $this->userModel->saveUser($db_data);
        if (!$db_add_user) {
            session()->setFlashdata('error', 'Database error - Gagal menambahkan <br>' . $email);
            $this->globalModel->insertLog(session()->get('email'), 'Database error - Gagal menambahkan ' . $email); // Catat log ke database
            return redirect()->to('admin/user_add');
        }

        $id_user = $this->userModel->getUserByEmail($email)->id;
        $this->groupModel->deleteAllGroupInUser($id_user); //hapus dulu semua grup yang dimiliki user
        foreach ($groups as $g) {
            $dataInsert = [
                'group_id' => $g,
                'user_id' => $id_user,
            ];
            $db_add_user_to_group = $this->groupModel->insertUserToGroup($dataInsert);
            if (!$db_add_user_to_group) {
                $this->globalModel->insertLog(session()->get('email'), 'Database error - Gagal menambahkan' . $id_user . ' ke group ' . $g); // Catat log ke database
                session()->setFlashdata('error', 'Database error - Gagal menambahkan' . $id_user . ' ke group ' . $g);
            }

            $this->globalModel->insertLog(session()->get('email'), 'Database - Sukses menambahkan ' . $id_user . ' ke group ' . $g); // Catat log ke database
        }

        // jika sync dengan google di-enable
        if (Config('MyConfig')->google['sync']) {
            // Proses insert ke google
            $gApi = new GoogleApi();
            if ($gApi->getUser($email)) { // Jika user sudah ada di google, diset unsuspend
                $gdata = array_merge($gdata, ["suspended" => false]);
                $gApi->patchUser($email, $gdata);
            } else { // jika belum ada, create user di google
                $gApi->insertUser($gdata);
            }
        }

        session()->setFlashdata('msg', 'Sukses menambahkan <br>Email: ' . $email . '<br>Password: ' . $pass);
        $this->globalModel->insertLog(session()->get('email'), 'Sukses menambahkan Email: ' . $email);  // Catat log ke database
        return redirect()->to('admin/user_add');
    }

    public function user_delete()
    {
        $id_user = $this->request->getPost('id');
        $email = $this->request->getPost('email');

        if (empty($id_user) || empty($email)) {
            return redirect()->back();
        }

        $getUser = $this->userModel->getUserByEmail($email);
        $domain_id = $getUser->domain;
        $get_category = $this->categoryModel->getCategoryAll()
            ->select('*')
            ->where('a.id', $getUser->ou)
            ->get()->getFirstRow();
        $ou_name = $get_category->name;
        $base_dn = $get_category->base_dn;
        $dn_user = 'uid=' . emailToUid($email) . ',ou=' . $ou_name . ',' . $base_dn;

        // Proses ke LDAP
        $get_groups = $this->groupModel->getGroups()
            ->select('*')
            ->join('user_group b', 'b.group_id=a.g_id', 'left')
            ->where('b.user_id', $id_user)
            ->get()->getResult();

        if ($get_groups) {
            foreach ($get_groups as $g) {
                $group_dn = 'cn=' . $g->g_name . ',' . $g->base_group_dn;
                $delUserFromGroup = $this->ldapModel->delUserFromGroup($group_dn, $dn_user);  //hapus user dari semua grup dulu
            }
        }
        $cek_ldap_user = $this->ldapModel->getMatchUser($base_dn, $email);
        if (isset($cek_ldap_user["count"]) && $cek_ldap_user["count"] === 1) { // cek jika user ada
            $this->ldapModel->deleteUser($dn_user); // hapus user di ldap
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
        $data_del = $this->userModel->getUser($id_user);
        // var_dump($ou_name, $base_dn, $dn_user, $data_del);
        // die();
        $data_del = array_merge($data_del, [
            'deleted_by' => session()->get('email'),
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        $this->groupModel->deleteAllGroupInUser($id_user);   //hapus dulu semua grup yang dimiliki user
        $del_user = $this->userModel->insertDelUser($data_del);
        if ($del_user) {
            // Catat log ke database
            $this->globalModel->insertLog(session()->get('email'), 'Sukses - Hapus user ' . $email);
            return json_encode(['msg' => 'Email: <strong>' . $email . '</strong> Terhapus']);
        } else {
            // Catat log ke database
            $this->globalModel->insertLog(session()->get('email'), 'Gagal - hapus user ' . $email);
            return json_encode(['msg' => 'Email: <strong>' . $email . '</strong> Gagal dihapus!']);
        }
    }

    public function user_edit($id = null)
    {
        // ekseskusi jika ada post data
        if ($this->request->getPost()) {
            $id_decrypt = $this->request->getPost('id') ? dekrip($this->request->getPost('id')) : '';
            if (empty($id_decrypt)) return redirect()->back();
            $pass = $this->request->getVar('password');
            $nip = $this->request->getPost('nip');
            $nidn = $this->request->getPost('nidn');
            $rules = [
                'firstname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => '{field} required',
                    ],
                ],
                'lastname' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => '{field} required',
                    ],
                ],
                // 'recoveryemail' => [
                //     'rules' => 'required',
                //     'errors' => [
                //         'required' => '{field} required',
                //     ],
                // ],
            ];

            if ($nip) {
                $rules = array_merge($rules, [
                    'nip' => [
                        'rules' => 'required',
                        'errors' => [
                            'required' => '{field} required',
                        ]
                    ]
                ]);
            }
            if ($nidn) {
                $rules = array_merge($rules, [
                    'nidn' => [
                        'rules' => 'required',
                        'errors' => [
                            'required' => '{field} required',
                        ]
                    ]
                ]);
            }
            if ($pass) {
                $rules = array_merge($rules, [
                    'password' => [
                        'rules' => 'min_length[8]',
                        'errors' => [
                            'min_length' => 'Sandi min 8 karakter',
                        ],
                    ],
                ]);
            }
            if (!$this->validate($rules)) {   // validasi dulu jika error redirect back
                return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
            }

            $active = $this->request->getPost('active') === 'on' ? 1 : 0;
            $forbid = $this->request->getPost('forbid') === 'on' ? true : false;
            $groups = $this->request->getPost('group') ?? $this->request->getPost('group');
            $ou_opt_id = $this->request->getPost('ou_option') ? dekrip($this->request->getPost('ou_option')) : "";
            $ou_opt_det = $this->categoryModel->getCtgById($ou_opt_id);
            $new_ou_name = $ou_opt_det->name;
            $new_ou_base_dn = $ou_opt_det->base_dn;
            $new_parrent = "ou=$new_ou_name,$new_ou_base_dn";

            $getUser = $this->userModel->getUserDetailById($id_decrypt);
            $email = $getUser->u_email;
            $uid = emailToUid($email);
            $new_rdn = "uid=$uid";
            $firstname = $this->request->getPost('firstname');
            $lastname = $this->request->getPost('lastname');
            $dispname = $this->request->getPost('firstname') . ' ' . $this->request->getPost('lastname');
            $recoveryemail = $this->request->getPost('recoveryemail') ?? $this->request->getPost('recoveryemail');
            $old_base_dn = $getUser->base_dn;
            $old_base_group_dn = $getUser->base_group_dn;
            $old_ou_name = $getUser->ou_name;
            $old_dn = 'uid=' . $uid . ',ou=' . $old_ou_name . ',' . $old_base_dn;
            // dd($new_rdn, $ou_opt_det, $getUser, $old_dn, $old_ou_name, $old_base_group_dn, $new_ou_name, $new_ou_base_dn, $new_parrent);

            // prepare data user dan ldap
            $db_data = [
                'firstname'     => $firstname,
                'lastname'     => $lastname,
                'dispname'     => $dispname,
                'password' => password_hash($pass, PASSWORD_DEFAULT),
                'pass_ldap' => ldapPass($pass),
                'nip' => $nip ?? '',
                'nidn' => $nidn ?? '',
                'recoveryemail' => $recoveryemail,
                'active' => $active,
                'forbid' => $forbid,
                'ou' => $ou_opt_id,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => session()->get('username'),
                'info' => session()->get('username') . ' updated',
            ];

            $ld_data = [
                'userpassword' => $db_data['pass_ldap'],
                'displayname' =>  $db_data['dispname'],
                'cn' =>  $db_data['dispname'],
                'givenname' =>  $db_data['firstname'],
                'sn' =>  $db_data['lastname'],
            ];

            $g_data = [
                'name' => [
                    'givenName' => $db_data['firstname'], // = firstname
                    'familyName' => $db_data['lastname'],  // = lastname
                ],
                'password' => $pass,
                'suspended' => false,
                'orgUnitPath' => '/' . $new_ou_name,
            ];

            // jika password tidak diisi
            if (empty($pass)) {
                unset($db_data['password']);
                unset($db_data['pass_ldap']);
                unset($ld_data['userpassword']);
                unset($g_data['password']);
            }

            // jika sync dengan google di-enable
            if (Config('MyConfig')->google['sync']) {
                // Proses insert ke google
                $gApi = new GoogleApi();
                $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google
                if ($queryGoogle['httpcode'] === 400 && isset($queryGoogle['body']['error']['message'])) { // jika ada pesan error dari google
                    $this->globalModel->insertLog($email, 'Google Error - ' . $queryGoogle['body']['error']['message']);
                    session()->setFlashdata('error', 'Google Error - ' . $queryGoogle['body']['error']['message']);
                    return redirect()->back()->withInput();
                }
                if (!$queryGoogle) {
                    session()->setFlashdata('error', 'Google error - Gagal ubah password ' . $email);
                    $this->globalModel->insertLog($email, 'Google error - Gagal ubah password ' . $email);
                    return redirect()->back()->withInput();
                }
            }

            // proses di database 
            $db_update_user = $this->userModel->update($id_decrypt, $db_data);
            if (!$db_update_user) { // jika gagal update user 
                session()->setFlashdata('error', 'Database error - Gagal update user ' . $id_decrypt);
                $this->globalModel->insertLog(session()->get('email'), 'Database error - Gagal update user ' . $id_decrypt);
                return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
            }
            $this->groupModel->deleteAllGroupInUser($id_decrypt);   //hapus dulu semua grup yang dimiliki user
            foreach ($groups as $g) {
                $dataInsert = [
                    'group_id' => $g,
                    'user_id' => $id_decrypt,
                ];

                // dd($dataInsert);
                $db_user_to_group = $this->groupModel->insertUserToGroup($dataInsert);
                if (!$db_user_to_group) { // jika gagal insert user ke group
                    session()->setFlashdata('error', 'Database error - Gagal tambah user ' . $id_decrypt . ' ke group ' . $g);
                    $this->globalModel->insertLog(session()->get('email'), 'Database error - Gagal tambah user ' . $id_decrypt . ' ke group ' . $g);
                    return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
                }
                // Jika berhasil, catat log ke database
                $this->globalModel->insertLog(session()->get('email'), 'Database sukses - tambah user ' . $id_decrypt . ' ke group ' . $g);
            }


            // proses di LDAP
            $get_exist_user_in_group = $this->ldapModel->getUserMemberOf($old_base_dn, $email);
            if (($get_exist_user_in_group)) { // cek jika user ada di sebuah group, kemudian hapus
                foreach ($get_exist_user_in_group as $ge) {
                    $group_dn = 'cn=' . $ge . ',' . $old_base_group_dn;   // misal format = 'uid=ajeng,ou=staff,dc=stmikelrahma,dc=ac,dc=id';
                    $this->ldapModel->delUserFromGroup($group_dn, $old_dn);  //hapus user dari semua grup dulu
                }
            }

            if ($old_ou_name !== $new_ou_name) { // jika ou lama TIDAK sama dengan ou baru maka ubah OU di ldap
                $ld_ren_ou = $this->ldapModel->updateOu($old_dn, $new_rdn, $new_parrent, true);
                if (!$ld_ren_ou) { // jika gagal ganti OU di LDAP
                    session()->setFlashdata('error', "LDAP Error - Gagal ganti $old_dn ke OU $new_parrent");
                    $this->globalModel->insertLog(session()->get('email'), "LDAP Error - Gagal ganti $old_dn ke OU $new_parrent");
                    return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
                }

                $new_dn = "$new_rdn,$new_parrent";
                foreach ($groups as $g) {
                    $get_group_by_id = $this->groupModel->getGrpById($g);
                    $g_name = $get_group_by_id['g_name'];
                    $base_group_dn = $get_group_by_id['base_group_dn'];
                    $group_dn = 'cn=' . $g_name . ',' . $base_group_dn;  // menjadi format cn=xxx,ou=groups,dc=xxx,dc=yyy,dc=zzz
                    $addUserToGroup = $this->ldapModel->addUserToGroup($group_dn, $new_dn);  //tambahkan user ke group
                    if (!$addUserToGroup) {
                        session()->setFlashdata('error', 'LDAP error - Gagal tambah ' . $new_dn . ' => ' . $group_dn);
                        $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal tambah ' . $new_dn . ' - ' . $group_dn);
                        return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
                    }
                }
            } else { // Jika tidak ada perubahan OU
                $ldap_query = $this->ldapModel->modifyUser($old_dn, $ld_data);
                if (!$ldap_query) { // jika gagal update user di LDAP
                    session()->setFlashdata('error', 'LDAP error - Gagal update ' . $id_decrypt . ' - ' . $old_dn);
                    $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal update ' . $id_decrypt . ' - ' . $old_dn);
                    return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
                }

                foreach ($groups as $g) {
                    // dd($this->groupModel->getGrpById($g));
                    $get_group_by_id = $this->groupModel->getGrpById($g);
                    $g_name = $get_group_by_id['g_name'];
                    $base_group_dn = $get_group_by_id['base_group_dn'];
                    $group_dn = 'cn=' . $g_name . ',' . $base_group_dn;  // menjadi format cn=xxx,ou=groups,dc=xxx,dc=yyy,dc=zzz
                    $addUserToGroup = $this->ldapModel->addUserToGroup($group_dn, $old_dn);  //tambahkan user ke group
                    if (!$addUserToGroup) {
                        session()->setFlashdata('error', 'LDAP error - Gagal tambah ' . $old_dn . ' => ' . $group_dn);
                        $this->globalModel->insertLog(session()->get('email'), 'LDAP error - Gagal tambah ' . $old_dn . ' - ' . $group_dn);
                        return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
                    }
                }
            }

            // Jika berhasil update semua
            session()->setFlashdata('msg', 'Sukses - update ' . $id_decrypt . ' - ' . $email);
            $this->globalModel->insertLog(session()->get('email'), 'Sukses - update ' . $id_decrypt . ' - ' . $email);
            return redirect()->back();
        }

        // Jika id kosong lempar balik
        if (empty($id) || empty($this->userModel->getUser($id)))  return redirect()->back();

        // Tampilkan halaman editor
        $user = (array) $this->userModel->getUserDetailById($id);
        $domain = $user['dom_name'];
        // $userCategory = $this->categoryModel->getCategoryAll()->orderBy('id')->get()->getResultArray();
        $groups = $this->groupModel->getGroupByOu($user['ou_name']);
        $ou_all = $this->categoryModel->getCategoryAll()->orderBy('id')->get()->getResult();

        $data = [
            'title' => 'Edit Pengguna',
            'content' => 'admin/v_edituser',
            'a_menu' => 'editu',
            'user' => $user,
            // 'units' => $domain === config('ldap')->stmikelrahma['domainstaff'] ? $this->globalModel->getUnitKerja() : '',
            // 'groups' => $this->groupModel->getGroupFilter($domain, ''),
            'groups' => $groups,
            'ou_all' => $ou_all,
            // 'usercategory' => $userCategory,
            'validation' => \Config\Services::validation(),
        ];
        // d($data);
        return view('layout/v_wrapper', $data);
    }

    public function user_deleted()
    {
        $data = [
            'title' => 'Riwayat Akun Terhapus',
            'content' => 'admin/v_userh',
            'a_menu' => 'userh',
        ];

        return view('layout/v_wrapper', $data);
    }

    public function fetch_user_deleted()
    {
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $rowperpage = $this->request->getPost('length');
        $columnIndex = $this->request->getPost("order[0][column]");
        $columnName = $this->request->getVar("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getVar("order[0][dir]");
        $searchValue = $this->request->getVar("search[value]");
        $draw = $this->request->getPost('draw');
        $response = [];

        $builder = $this->userModel->getUsersDeleted();
        $builder->select('*');

        ## Total number of record 
        $totalRecords = $builder->countAllResults(false);

        ## Total number of record with filtering
        if ($searchValue) {
            $builder->like('a.email', $searchValue, 'both', null, true);
            $builder->orLike('a.dispname', $searchValue, 'both', null, true);
        }
        $totalRecordsWithFilter = $builder->countAllResults(false);

        ## Fetch records
        $builder->orderBy($columnName, $columnSortOrder);
        $builder->limit($rowperpage, $start);
        $records = $builder->get()->getResult();
        // var_dump($records);
        // die();
        $data = [];
        $nomer = 1;
        foreach ($records as $r) {
            $data[] = [
                'id' => $r->id,
                'email' => $r->email,
                'firstname' => $r->firstname,
                'lastname' => $r->lastname,
                'info' => $r->info,
                'deleted_at' => $r->deleted_at,
                'deleted_by' => $r->deleted_by,
                'recoveryemail' => $r->recoveryemail,
            ];
        }

        $response = [
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalRecordsWithFilter,
            'aaData' => $data,
        ];
        echo  json_encode($response);
    }
}
