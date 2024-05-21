<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleApi;
use App\Libraries\Uuid;
use App\Models\CategoryModel;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\LdapModel;
use App\Models\UserAppModel;
use App\Models\UserModel;
use Config\Ldap;

class Dashboard extends BaseController
{
    private $userAppModel, $globalModel, $uuid, $groupModel, $categoryModel, $ldapConf, $userModel, $ldapModel, $ldap;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userAppModel = new UserAppModel();
        $this->globalModel = new GlobalModel();
        $this->categoryModel = new CategoryModel();
        $this->groupModel = new GroupModel();
        $this->uuid = new Uuid();
        $this->ldapConf = new Ldap();
        $this->ldap = $this->ldapConf->stmikelrahma; // pilih koneksi config LDAP
        $this->userModel = new UserModel();
        $this->ldapModel = new LdapModel();
    }

    public function index()
    {
        $user = $this->userAppModel->where('email', session()->get('email'))->first();
        $data = [
            'title' => 'Profil Admin',
            'content' => 'admin/v_index',
            'a_menu' => 'profil',
            'user' => $user,
            'validation' => \Config\Services::validation(),
        ];
        return view('layout/v_wrapper', $data);
    }

    public function save()
    {
        $id = session()->get('id');
        $email = session()->get('email');
        $pass = $this->request->getVar('password');

        if ($pass) {
            // validasi dulu
            if (!$this->validate([
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
            ])) {
                return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
            }

            $data = [
                'firstname'     => $this->request->getVar('firstname'),
                'lastname'     => $this->request->getVar('lastname'),
                'password' => password_hash($pass, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => "selfuser",

            ];
            $querydb = $this->userAppModel->update($id, $data);

            // Catat log ke database
            $this->globalModel->insertLog($email, 'Ubah password');
            session()->setFlashdata('msg', 'Berhasil ubah profil dan sandi');
            return redirect()->to(base_url('admin'));
        } else {
            // validasi dulu
            if (!$this->validate([
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
                ]
            ])) {
                return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
            }

            $data = [
                'firstname'     => $this->request->getVar('firstname'),
                'lastname'     => $this->request->getVar('lastname'),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => "selfuser",
            ];
            $querydb = $this->userAppModel->update($id, $data);

            // Catat log ke database
            $this->globalModel->insertLog($email, 'Ubah Profil ' . $data['firstname'] . ' ' . $data['lastname']);
            session()->setFlashdata('msg', 'Berhasil ubah profil');
            return redirect()->to(base_url('admin'));
        }
    }

    public function test()
    {
        $email = 'harunnoviar@uny.ac.id';
        $message = 'cek satu dua tiga';
        $send_mail = send_email($email, $message);
        dd($send_mail);
    }
}
