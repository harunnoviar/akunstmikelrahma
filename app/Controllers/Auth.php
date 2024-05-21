<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GlobalModel;
use App\Models\UserModel;
use App\Libraries\Cas;

class Auth extends BaseController
{
    protected $globalModel;
    public function __construct()
    {
        $this->globalModel = new GlobalModel();
    }
    public function index()
    { {
            // d(session()->get());
            if (session()->get('isLogin') && session()->get('role') === '2') {
                return redirect()->to('user');
            }

            $data = [
                'title' => 'Login Page',
                'recaptchaSite' => config('MyConfig')->googleRecaptchaSiteKey,
            ];
            return view('auth/login', $data);
        }
    }

    public function login()
    {

        $model = new UserModel();
        $recaptchaResponse = trim($this->request->getVar('g-recaptcha-response'));
        $captcha = googleCaptachStore($recaptchaResponse);

        if ($captcha['success'] == false) { // Jika captcha error kembalikan dengan input pesan
            session()->setFlashdata('error', 'Warning Error captcha!');
            return redirect()->back()->withInput();
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $data = (array)$model->getUserByEmail($email);
        if ($data) {
            $pass = $data['password'];
            if ($data['active'] != '1') {
                session()->setFlashdata('msg', 'Anda belum aktivasi, silakan klik <a class="text-danger" href="' . base_url('register') . '" >aktivasi</a>.');
                return redirect()->back();
            }
            $verify_pass = password_verify($password, $pass);
            if ($verify_pass) {
                $ses_login = [
                    'id' => $data['id'],
                    'email_u' => $data['email'],
                    'role' => $data['role'],
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'isLogin' => TRUE,
                    'sso' => False
                ];

                // catat ke log
                if (!session()->has('isLogin')) {
                    $this->globalModel->insertLog($data['email'], 'Login');
                }
                session()->set($ses_login);
                return redirect()->to('/user');
            } else {
                session()->setFlashdata('error', 'Password salah!');
                return redirect()->back()->withInput();
            }
        } else {
            session()->setFlashdata('error', 'Email/Username tidak ditemukan');
            return redirect()->back();
        }
    }
    public function logout()
    {
        // catat ke log
        if (session()->has('isLogin')) {
            $this->globalModel->insertLog(session()->get('email_u'), 'Logout');
        }
        session()->remove(['id', 'email_u', 'role', 'firstname', 'lastname', 'isLogin', 'sso']);
        // session()->destroy();
        session()->setFlashdata('msg', 'Berhasil Keluar');
        return redirect()->to('/');
    }

    public function ssologin()
    {
        $sso = new Cas();

        // jika tidak ada session pada sso, paksa login
        if (!!!($sso->is_authenticated()))  return $sso->force_auth();

        // $casAccount = strtolower($sso->user()->userlogin);
        $casAccount = [
            "userLogin" => strtolower($sso->user()->userlogin),
            "attribute" => $sso->user()->attributes
        ];


        $model = new UserModel();
        $data = (array)$model->getUserByEmail($casAccount["userLogin"]);
        if (empty($data)) { // jika akun tidak ditemukan pada database
            session()->setFlashdata('msg', 'Akun ' . $casAccount["userLogin"] . ' tidak ditemukan, <a class="text-danger" href="' . base_url('auth/ssologout') . '" >logout</a>.');
            return redirect()->to(base_url('auth'));
        }

        // dd($casAccount, $data);

        $sess_login = [
            'id' => $data['id'],
            'email_u' => $data['email'],
            'role' => $data['role'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'isLogin' => TRUE,
            'sso' => TRUE
        ];

        // catat ke log
        if (!session()->has('isLogin')) {
            $this->globalModel->insertLog($data['email'], 'Login SSO');
        }
        session()->set($sess_login);
        return redirect()->to('/user');

        // $userAccount = $this->globalModel->userDetailJoin($casAccount);

        // // check status akun user
        // if ($userAccount['status'] !== '1') {
        //     session()->setFlashdata('gagal', 'Status email <strong>' . $userAccount['stat_description'] . '</strong>!');
        //     // Catat log ke database
        //     $this->globalModel->insertLog($casAccount, 'Login Failed (suspend user)');
        //     return redirect()->to(base_url('auth'));
        // }

        // $str = explode("@", $casAccount);
        // $domain = $str[count($str) - 1];
        // $cekAdmin = $userAccount['privilege'];
        // if ($cekAdmin == 1) {
        //     session()->set(['privilege' => 'Admin']);
        // } else {
        //     session()->set(['privilege' => 'User']);
        // }
        // session()->set(['isLogin' => 1]);

        // if ($domain == 'uny.ac.id') {
        //     $role = 'staff';
        //     session()->set([
        //         'email' => $casAccount,
        //         'role' => $role,
        //     ]);
        // } elseif ($domain == 'student.uny.ac.id') {
        //     $role = 'student';
        //     session()->set([
        //         'email' => $casAccount,
        //         'role' => $role,
        //     ]);
        // }
        // // Catat log ke database
        // if (session()->get('email') || session()->get('role')) {
        //     $this->globalModel->insertLog(session()->get('email'), 'Login');
        // }
        // return redirect()->to(base_url('app'));
    }

    public function ssologout()
    {
        $sso = new Cas();
        // Catat log ke database
        if (session()->has('isLogin')) {
            $this->globalModel->insertLog(session()->get('email_u'), 'Logout SSO');
        }
        // session()->destroy();
        session()->remove(['id', 'email_u', 'role', 'firstname', 'lastname', 'isLogin', 'sso']);
        return $sso->logout(base_url("auth"));
    }
}
