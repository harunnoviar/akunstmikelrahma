<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleApi;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\LdapModel;
use App\Models\UserModel;
use Config\MyConfig;
use Google\Client as Google_Client;
use Google_Service_Oauth2;

class Register extends BaseController
{

    protected $globalModel, $groupModel, $ldapModel, $userModel;
    protected $groupEduroam = "inet-eduroam";

    public function __construct()
    {
        $this->globalModel = new GlobalModel();
        $this->groupModel = new GroupModel();
        $this->ldapModel = new LdapModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return $this->gauth();
    }

    public function regstaff()
    {
        // validasi login google
        if (!session()->get('email_u') && !session()->get('gauthLogin')) {
            return $this->gauth();
        }

        $group_pilih = $this->request->getPost('group');
        $email = session()->get('email_u');
        $domain_email = explode('@', $email)[1];
        $domain = session()->get('domain_u');
        if (!isset($domain) || $domain_email != $domain) return $this->gauth(); // jika domain dan ou_id tidak sesuai, redirect

        $groupGet = $this->groupModel->getGrpDetByDomain($domain);
        foreach ($groupGet as $g) {
            if ($g['g_name'] !== $this->groupEduroam) { //buang group eduroam dari array
                $groups[] = (object)['g_id' => $g['g_id'], 'g_name' => $g['g_name']];
            }
        }
        // $units = $this->globalModel->getUnitKerja();
        $data = [
            'title' => 'Aktivasi ' . config('MyConfig')->siteName,
            'groups' => (object) $groups,
            'group_pilih' => $group_pilih,
            // 'units' => $units,
            'validation' => \Config\Services::validation(),
        ];
        return view('auth/v_regstaff', $data);
    }

    public function regstaffsave()
    {
        // validasi login google
        if (!session()->get('email_u') && !session()->get('gauthLogin')) {
            return $this->gauth();
        }

        $g_id = $this->request->getPost('group');
        if (empty($g_id)) {
            return redirect()->back();
        }

        $g_id =  dekrip($g_id);
        $email = session()->get('email_u');
        $domain_email = emailToDomain($email);
        $domain = session()->get('domain_u');

        // validasi email session yang tersimpan setelah login google, jika tidak valid, lempar ke aktivasi ulang
        if (!isset($domain) || $domain_email != $domain) { // jika domain tidak sesuai, redirect
            session()->setFlashdata('error', 'Session login gmail tidak valid/habis, silakan ulangi kembali proses aktivasi!');
            return redirect()->to(base_url('auth'));
        }

        $user = $this->userModel->getUserDetailByEmail($email);
        $user_id = $user->u_id;
        $getGroup = (array) $this->groupModel->getGrpWithOuById($g_id);
        // d($user, $user_id, $g_id);
        $g_eduroam = $this->groupModel->getGrpByName($this->groupEduroam);   // cari group eduroam
        $groups_all = [  // jadikan format data group array
            [
                'user_id' => $user_id,
                'group_id' => $getGroup['g_id'],
                'g_name' => $getGroup['g_name'],
                'base_group_dn' => $getGroup['base_group_dn']
            ],
            [
                'user_id' => $user_id,
                'group_id' => $g_eduroam['g_id'],
                'g_name' => $g_eduroam['g_name'],
                'base_group_dn' => $g_eduroam['base_group_dn']
            ]
        ];

        $uid = emailToUid($email);
        $ou = $getGroup['ou_name'];
        $base_dn = $getGroup['base_dn'];
        $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $base_dn;
        $pass = $this->request->getVar('password');

        $rules = [
            'firstname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nama Depan harus diisi',
                ],
            ],
            'lastname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nama Belakang harus diisi',
                ],
            ],
            'recoveryemail' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email Pemulihan harus diisi',
                    'valid_email' => 'Email Pemulihan tidak valid',
                ],
            ],
            'nip' => [
                'rules' => 'required|alpha_numeric',
                'errors' => [
                    'required' => 'NIP harus diisi',
                    'alpha_numeric' => 'NIP harus angka atau huruf',
                ],
            ],
            'nidn' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'NIDN harus diisi',
                    'numeric' => 'NIDN harus angka',
                ],
            ],
            // 'unit' => [
            //     'rules' => 'required',
            //     'errors' => [
            //         'required' => 'Satker harus dipilih',
            //     ],
            // ],
            'password' => [
                'rules' => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Sandi min 8 karakter',
                ],
            ],
            'confpassword' => 'matches[password]',
        ];

        // jika group id 2 (tendik) rule nidn dibuang
        if ($g_id === '2') unset($rules['nidn']);

        // jika rule tidak valid kembalikan ke page sebelumnya dan membawa  id group pilih
        if (!$this->validate($rules)) {
            session()->setFlashdata('group_pilih', $g_id);
            return redirect()->back()->withInput('group', 'dosen')->with('validation', \Config\Services::validation());
        }

        // prepare data untuk database
        $db_data = [
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'dispname' => $this->request->getPost('firstname') . ' ' . $this->request->getPost('lastname'),
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'pass_ldap' => ldapPass($pass),
            // 'category' => $category,
            'recoveryemail' => $this->request->getPost('recoveryemail'),
            'active' => 1, // set active menjadi 1
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => "selfuser",
            'info' => "Aktivasi akun",
            'nip' => $this->request->getPost('nip'),
            'nidn' => $this->request->getPost('nidn'),
            'ou' => $getGroup['ou_id'],
            // 'unit' => dekrip($unit),
        ];

        // prepare data ldap
        $ld_data = [
            'userpassword' => $db_data['pass_ldap'],
            'displayname' =>  $db_data['dispname'],
            'cn' =>  $db_data['dispname'],
            'givenname' =>  $db_data['firstname'],
            'sn' =>  $db_data['lastname'],
        ];

        // prepare google data
        $g_data = [
            'name' => [
                'givenName' => $db_data['firstname'], // = firstname
                'familyName' => $db_data['lastname'],  // = lastname
            ],
            'password' => $pass,
            'suspended' => false,
            'orgUnitPath' => '/' . $ou,
        ];

        // cek di ldap apakah email exist 
        if ($this->ldapModel->getMatchUser($base_dn, $email)['count'] != 1) {
            session()->setFlashdata('error', 'LDAP error - akun ' . $email . ' tidak ada!');
            return redirect()->to(base_url('auth'));
        }

        // proses di database dulu
        $db_update_user = $this->userModel->update($user_id, $db_data);
        if (!$db_update_user) { // jika gagal update user 
            session()->setFlashdata('error', 'Database error - Gagal update user ' . $email);
            $this->globalModel->insertLog($email, 'Database error - Gagal update user ' . $email);
            return redirect()->to(base_url('auth'));
        }
        $this->groupModel->deleteAllGroupInUser($user_id);   //hapus dulu semua grup yang dimiliki user

        foreach ($groups_all as $g) { // masukkan user ke semua group di database
            $db_user_to_group = $this->groupModel->insertUserToGroup([
                'user_id' => $g['user_id'],
                'group_id' => $g['group_id']
            ]);
            if (!$db_user_to_group) { // jika gagal insert user ke group
                session()->setFlashdata('error', 'Database error - Gagal update group ' . $email);
                $this->globalModel->insertLog($email, 'Database error - Gagal update group ' . $email);
                return redirect()->to(base_url('auth'));
            }
        }

        // proses di LDAP
        $ldap_query = $this->ldapModel->modifyUser($dn, $ld_data);
        if (!$ldap_query) { // jika gagal update user di LDAP
            session()->setFlashdata('error', 'LDAP error - Gagal update ' . $email);
            $this->globalModel->insertLog($email, 'LDAP error - Gagal update ' . $email);
            return redirect()->to(base_url('auth'));
        }

        $get_exist_user_in_group = $this->ldapModel->getUserMemberOfDetail($base_dn, $email);
        if (($get_exist_user_in_group)) { // cek jika user ada di sebuah group, kemudian hapus
            foreach ($get_exist_user_in_group as $ge) {
                $this->ldapModel->delUserFromGroup($ge, $dn);  //hapus user dari semua grup dulu
            }
        }

        foreach ($groups_all as $g) {
            $group_dn = 'cn=' . $g['g_name'] . ',' . $g['base_group_dn'];
            $addUserToGroup = $this->ldapModel->addUserToGroup($group_dn, $dn);  //tambahkan user ke group
            if (!$addUserToGroup) {
                session()->setFlashdata('error', 'LDAP error - Gagal ubah group ' . $email);
                $this->globalModel->insertLog(session()->get('email_u'), 'LDAP error - Gagal ubah group ' . $email);
                return redirect()->to(base_url('auth'));
            }
        }

        // jika sync dengan google di-enable
        if (Config('MyConfig')->google['sync']) {
            // Proses insert ke google
            $gApi = new GoogleApi();
            $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google
            if (!$queryGoogle) {
                session()->setFlashdata('error', 'Google error - Gagal ubah profil ' . $email);
                $this->globalModel->insertLog($email, 'Google error - Gagal ubah profil ' . $email);
            }
        }

        // Jika semua proses sudah berhasil
        $this->globalModel->insertLog(session()->get('email_u'), 'Sukses - aktivasi ' . $email);
        // session()->destroy();
        session()->setFlashdata('msg', 'Sukses - aktivasi ' . $email);
        session()->remove(['gauthLogin', 'isLogin', 'gtoken', 'email_u', 'name_u', 'picture_u', 'id_u', 'firstname_u', 'lastname_u', 'email_u', 'domain_u', 'ou_id']);
        return redirect()->to(base_url('auth'));
    }

    public function regstudent()
    {
        // validasi login google
        if (!session()->get('email_u') && !session()->get('gauthLogin')) {
            return $this->gauth();
        }

        // $group_pilih = 'mhs';
        $email = session()->get('email_u');
        $domain_email = emailToDomain($email);
        $domain = session()->get('domain_u');
        if (!isset($domain) || $domain_email != $domain) return $this->gauth(); // jika domain dan ou_id tidak sesuai, redirect

        $groupGet = $this->groupModel->getGrpDetByDomain($domain);
        foreach ($groupGet as $g) {
            if ($g['g_name'] !== $this->groupEduroam) { //buang group eduroam dari array
                $groups[] = (object)['g_id' => $g['g_id'], 'g_name' => $g['g_name']];
            }
        }

        $data = [
            'title' => 'Aktivasi ' . config('MyConfig')->siteName,
            'groups' => (object) $groups,
            // 'group_pilih' => $group_pilih,
            'validation' => \Config\Services::validation(),
        ];
        // d($data, session()->get());

        return view('auth/v_regstudent', $data);
    }

    public function regstudentsave()
    {
        // validasi login google
        if (!session()->get('email_u') && !session()->get('gauthLogin')) {
            return $this->gauth();
        }

        $g_id = $this->request->getPost('group');
        if (empty($g_id)) {
            return redirect()->back();
        }

        $g_id =  dekrip($g_id);
        $email = session()->get('email_u');
        $domain_email = emailToDomain($email);
        $domain = session()->get('domain_u');

        // validasi email session yang tersimpan setelah login google, jika tidak valid, lempar ke aktivasi ulang
        if (!isset($domain) || $domain_email != $domain) { // jika domain tidak sesuai, redirect
            session()->setFlashdata('error', 'Session login gmail tidak valid/habis, silakan ulangi kembali proses aktivasi!');
            return redirect()->to(base_url('auth'));
        }

        $user = $this->userModel->getUserDetailByEmail($email);
        $user_id = $user->u_id;
        $getGroup = (array) $this->groupModel->getGrpWithOuById($g_id);
        $g_eduroam = $this->groupModel->getGrpByName($this->groupEduroam);   // cari group eduroam
        $groups_all = [  // jadikan format data group array
            [
                'user_id' => $user_id,
                'group_id' => $getGroup['g_id'],
                'g_name' => $getGroup['g_name'],
                'base_group_dn' => $getGroup['base_group_dn']
            ],
            [
                'user_id' => $user_id,
                'group_id' => $g_eduroam['g_id'],
                'g_name' => $g_eduroam['g_name'],
                'base_group_dn' => $g_eduroam['base_group_dn']
            ]
        ];

        $uid = emailToUid($email);
        $ou = $getGroup['ou_name'];
        $base_dn = $getGroup['base_dn'];
        $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $base_dn;
        $pass = $this->request->getVar('password');
        // dd($getGroup, $g_eduroam, $groups_all, $dn, $pass);

        $rules = [
            'firstname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nama Depan harus diisi',
                ],
            ],
            'lastname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nama Belakang harus diisi',
                ],
            ],
            'recoveryemail' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email Pemulihan harus diisi',
                    'valid_email' => 'Email Pemulihan tidak valid',
                ],
            ],
            'password' => [
                'rules' => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Sandi min 8 karakter',
                ],
            ],
            'confpassword' => 'matches[password]',
        ];

        // jika rule tidak valid kembalikan ke page sebelumnya 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
        }

        // prepare database data
        $db_data = [
            'firstname' => $this->request->getPost('firstname'),
            'lastname' => $this->request->getPost('lastname'),
            'dispname' => $this->request->getPost('firstname') . ' ' . $this->request->getPost('lastname'),
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'pass_ldap' => ldapPass($pass),
            // 'category' => $category,
            'recoveryemail' => $this->request->getPost('recoveryemail'),
            'active' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => "selfuser",
        ];

        // prepare data ldap
        $ld_data = [
            'userpassword' => $db_data['pass_ldap'],
            'displayname' =>  $db_data['dispname'],
            'cn' =>  $db_data['dispname'],
            'givenname' =>  $db_data['firstname'],
            'sn' =>  $db_data['lastname'],
        ];

        // prepare google data
        $g_data = [
            'name' => [
                'givenName' => $db_data['firstname'], // = firstname
                'familyName' => $db_data['lastname'],  // = lastname
            ],
            "password" => $pass,
            'suspended' => false,
            'orgUnitPath' => '/' . $ou,
        ];
        // dd($db_data, $ld_data, $g_data);

        // cek di ldap apakah email exist 
        if ($this->ldapModel->getMatchUser($base_dn, $email)['count'] != 1) {
            session()->setFlashdata('error', 'LDAP error - akun ' . $email . ' tidak ada!');
            return redirect()->to(base_url('auth'));
        }

        // proses di database dulu
        $db_update_user = $this->userModel->update($user_id, $db_data);
        if (!$db_update_user) { // jika gagal update user 
            session()->setFlashdata('error', 'Database error - Gagal update user ' . $email);
            $this->globalModel->insertLog($email, 'Database error - Gagal update user ' . $email);
            return redirect()->to(base_url('auth'));
        }
        $this->groupModel->deleteAllGroupInUser($user_id);   //hapus dulu semua grup yang dimiliki user

        foreach ($groups_all as $g) { // masukkan user ke semua group di database
            $db_user_to_group = $this->groupModel->insertUserToGroup([
                'user_id' => $g['user_id'],
                'group_id' => $g['group_id']
            ]);
            if (!$db_user_to_group) { // jika gagal insert user ke group
                session()->setFlashdata('error', 'Database error - Gagal update group ' . $email);
                $this->globalModel->insertLog($email, 'Database error - Gagal update group ' . $email);
                return redirect()->to(base_url('auth'));
            }
        }

        // proses di LDAP
        $ldap_query = $this->ldapModel->modifyUser($dn, $ld_data);
        if (!$ldap_query) { // jika gagal update user di LDAP
            session()->setFlashdata('error', 'LDAP error - Gagal update ' . $email);
            $this->globalModel->insertLog($email, 'LDAP error - Gagal update ' . $email);
            return redirect()->to(base_url('auth'));
        }

        $get_exist_user_in_group = $this->ldapModel->getUserMemberOfDetail($base_dn, $email);
        if (($get_exist_user_in_group)) { // cek jika user ada di sebuah group, kemudian hapus
            foreach ($get_exist_user_in_group as $ge) {
                $this->ldapModel->delUserFromGroup($ge, $dn);  //hapus user dari semua grup dulu
            }
        }

        foreach ($groups_all as $g) {
            $group_dn = 'cn=' . $g['g_name'] . ',' . $g['base_group_dn'];
            $addUserToGroup = $this->ldapModel->addUserToGroup($group_dn, $dn);  //tambahkan user ke group
            if (!$addUserToGroup) {
                session()->setFlashdata('error', 'LDAP error - Gagal ubah group ' . $email);
                $this->globalModel->insertLog(session()->get('email_u'), 'LDAP error - Gagal ubah group ' . $email);
                return redirect()->to(base_url('auth'));
            }
        }

        // jika sync dengan google di-enable
        if (Config('MyConfig')->google['sync']) {
            // Proses insert ke google
            $gApi = new GoogleApi();
            $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google
            if (!$queryGoogle) {
                session()->setFlashdata('error', 'Google error - Gagal ubah profil ' . $email);
                $this->globalModel->insertLog($email, 'Google error - Gagal ubah profil ' . $email);
            }
        }

        // Jika semua proses sudah berhasil
        $this->globalModel->insertLog(session()->get('email_u'), 'Sukses - aktivasi ' . $email);
        // session()->destroy();
        session()->setFlashdata('msg', 'Sukses - aktivasi ' . $email);
        session()->remove(['gauthLogin', 'isLogin', 'gtoken', 'email_u', 'name_u', 'picture_u', 'id_u', 'firstname_u', 'lastname_u', 'email_u', 'domain_u', 'ou_id']);
        return redirect()->to(base_url('auth'));
    }

    public function gauth()
    {
        $gclient =  new \Google_Client();
        $userModel = new UserModel();
        $myConfig = new MyConfig;
        $redirectUrl = $myConfig->google['redirect_url'];
        $gclient->setApprovalPrompt('force');
        $gclient->setPrompt('select_account');
        $gclient->setClientId($myConfig->google['client_id']);
        $gclient->setClientSecret($myConfig->google['client_secret']);
        $gclient->setRedirectUri($myConfig->google['redirect_url']);
        $gclient->addScope('email');
        $gclient->addScope('profile');
        $google_oauthv2 = new Google_Service_Oauth2($gclient);

        if (!$this->request->getGet()) {
            // die('ora login');
            $authUrl = $gclient->createAuthUrl();
            return redirect()->to($authUrl);
        }

        $gCode = $this->request->getGet('code');
        if (!isset($gCode)) { // jika tidak ada google code
            die('error google code');
        }

        $gclient->fetchAccessTokenWithAuthCode($gCode);
        $gToken = $gclient->getAccessToken();
        if (empty($gToken)) {
            return redirect()->to(base_url('auth'));
        }
        $gpuserprofile = $google_oauthv2->userinfo->get();  // data user profile di google
        $user = $userModel->getUserDetailByEmail($gpuserprofile['email']);
        // cek akun ada atau tidak
        if (empty($user)) {
            session()->setFlashdata('error', 'Email ' . $gpuserprofile['email'] . ' tidak ditemukan, silakan cek kembali!');
            return redirect()->to(base_url('auth'));
        }
        // cek akun forbidden atau tidak
        if ($user->forbid === 't') {
            session()->setFlashdata('error', 'Email ' . $gpuserprofile['email'] . ' tidak diijinkan melakukan registrasi!');
            return redirect()->to(base_url('auth'));
        }
        // Cek apakah user sudah aktivasi
        if ($user->active === '1') {
            // dd(session()->get());
            session()->setFlashdata('msg', 'Akun ' . $gpuserprofile['email'] . ' sudah diaktivasi, silakan masuk.');
            return redirect()->to(base_url('auth'));
        }

        $user_id = $user->u_id;
        $firstname = $user->firstname;
        $lastname = $user->lastname;
        $domain = $user->dom_name;
        // $ou_id = $user->ou; // ambil category/ou id

        session()->set([
            'gauthLogin' => true,
            'isLogin' => true,
            'gtoken' => $gToken,
            'email_u' => $gpuserprofile['email'],
            'name_u' => $gpuserprofile['name'],
            'picture_u' => $gpuserprofile['picture'],
            'id_u' => $user_id,
            'firstname_u' => $firstname,
            'lastname_u' => $lastname,
            'domain_u' => $domain,
            // 'ou_id' => $ou_id,
        ]);
        session()->setFlashdata('aktivasi', '1');

        if (isset($domain) && $domain != 'mhs.stmikelrahma.ac.id') {
            return redirect()->to(base_url('regstaff'));
        } else {
            return redirect()->to(base_url('regstudent'));
        }
    }
}
