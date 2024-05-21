<?php

namespace App\Modules\User\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleApi;
use App\Models\GlobalModel;
use App\Models\LdapModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    private  $globalModel, $userModel, $ldapModel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->globalModel = new GlobalModel();
        $this->userModel = new UserModel();
        $this->ldapModel = new LdapModel();
    }

    public function index()
    {
        $user = $this->userModel->getUserByEmail(session()->get('email_u'));
        $data = [
            'title' => 'Profil Akun',
            'content' => 'user/v_index',
            'a_menu' => 'profil',
            'user' => (array)$user,
            'validation' => \Config\Services::validation(),
        ];

        return view('layout/v_wrapper', $data);
    }

    public function save()
    {

        $id = session()->get('id');
        $email = session()->get('email_u');
        $pass = $this->request->getVar('password');
        $ldapPass = ldapPass($pass);      // untuk pass_ldap
        $get_user = $this->userModel->getUserDetailByEmail($email);
        $uid = emailToUid($email);
        $ou = $get_user->ou_name;
        $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $get_user->base_dn;
        // d($get_user);
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
            'recoveryemail' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} required',
                ],
            ],
            'password' => [
                'rules' => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Sandi min 8 karakter',
                ],
            ],
            'confpassword' => [
                'rules' => 'matches[password]',
                'errors' => [
                    'matches' => 'Sandi harus sama'
                ]
            ]
        ];

        // prepare database data
        $db_data = [
            'firstname'     => $this->request->getVar('firstname'),
            'lastname'     => $this->request->getVar('lastname'),
            'dispname'     => $this->request->getVar('firstname') . ' ' . $this->request->getVar('lastname'),
            'recoveryemail'     => $this->request->getVar('recoveryemail'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => "self",
            'info' => "Ubah password",
        ];

        // prepare ldap data
        $ld_data = [
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
        ];

        if (empty($pass)) { // jika tidak diisi password
            unset($rules['password']);
            unset($rules['confpassword']);
            unset($db_data['info']);
            $prefix_message = '';
        } else {
            $db_data = array_merge($db_data, [ // tambahkan password
                'password' => password_hash($pass, PASSWORD_DEFAULT),
                'pass_ldap' => $ldapPass,
            ]);
            $ld_data = array_merge($ld_data, ["userpassword" => $db_data["pass_ldap"]]);
            $g_data = array_merge($g_data, ["password" => $pass]);
            $prefix_message = ' dan ubah password';
        }

        if (!$this->validate($rules)) { // validasi dulu
            // dd(\Config\Services::validation());
            return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
        }

        // jika sync dengan google di-enable
        if (Config('MyConfig')->google['sync']) {
            // Proses insert ke google
            $gApi = new GoogleApi();
            $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google
            // dd($queryGoogle);
            if ($queryGoogle['httpcode'] === 400 && isset($queryGoogle['body']['error']['message'])) { // jika ada pesan error dari google
                $this->globalModel->insertLog($email, 'Google Error - ' . $queryGoogle['body']['error']['message']);
                session()->setFlashdata('error', 'Google Error - ' . $queryGoogle['body']['error']['message']);
                return redirect()->back()->withInput();
            }
            if (!$queryGoogle) {
                $this->globalModel->insertLog($email, 'Google Error - gagal ubah data' . $prefix_message);
                session()->setFlashdata('error', 'Google Error - gagal ubah data' . $prefix_message);
                return redirect()->back()->withInput();
            }
        }

        $queryldap = $this->ldapModel->modifyUser($dn, $ld_data);
        if (!$queryldap) { // cek proses di ldap jika error
            $this->globalModel->insertLog($email, 'Ldap Error - gagal ubah data' . $prefix_message);
            session()->setFlashdata('error', 'Ldap Error - gagal ubah data' . $prefix_message);
            return redirect()->back()->withInput();
        }

        $querydb = $this->userModel->update($id, $db_data);
        if (!$querydb) { // cek jika proses database error
            $this->globalModel->insertLog($email, 'Database Error - gagal ubah data' . $prefix_message);
            session()->setFlashdata('error', 'Database Error - gagal ubah data' . $prefix_message);
            return redirect()->back()->withInput();
        }

        // Jika semua proses berhasil
        $this->globalModel->insertLog($email, 'Sukses - ubah data' . $prefix_message);
        session()->setFlashdata('msg', 'Sukses ubah data' . $prefix_message);
        return redirect()->to(base_url('user'));
    }
}
