<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GlobalModel;
use App\Models\UserAppModel;

class AppAuth extends BaseController
{
    protected $globalModel;

    public function __construct()
    {
        $this->globalModel = new GlobalModel();
    }

    public function index()
    {
        if (session()->get('isLogin') && session()->get('role') === '1') {
            return redirect()->to('user');
        }

        $data = [
            'title' => 'Admin Login',
            'recaptchaSite' => config('MyConfig')->googleRecaptchaSiteKey,
        ];
        return view('auth/app_login', $data);
    }
    public function login()
    {
        $recaptchaResponse = trim($this->request->getVar('g-recaptcha-response'));
        $captcha = googleCaptachStore($recaptchaResponse);
        if ($captcha['success'] == false) { // Jika captcha error kembalikan dengan input pesan
            session()->setFlashdata('error', 'Warning Error captcha!');
            return redirect()->back()->withInput();
        }

        $session = session();
        $model = new UserAppModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $data = $model->where('username', $username)->first();
        if ($data) {
            $pass = $data['password'];
            $verify_pass = password_verify($password, $pass);
            if ($verify_pass) {
                $ses_login = [
                    'id' => $data['id'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'isLogin' => TRUE
                ];

                // catat ke log
                if (!session()->has('isLogin')) {
                    $this->globalModel->insertLog($data['email'], 'Login');
                }
                $session->set($ses_login);
                return redirect()->to('/admin');
            } else {
                // catat ke log
                if (!session()->has('isLogin')) {
                    $this->globalModel->insertLog($data['email'], 'Sandi salah');
                }
                $session->setFlashdata('error', 'Sandi salah');
                return redirect()->back()->withInput();
            }
        } else {
            $session->setFlashdata('error', 'Username tidak ditemukan');
            return redirect()->to('/appauth');
        }
    }

    public function logout()
    {
        // catat ke log
        if (session()->has('isLogin')) {
            $this->globalModel->insertLog(session()->get('email'), 'Logout');
        }
        session()->remove(['id', 'username', 'email', 'role', 'firstname', 'lastname', 'isLogin']);
        session()->setFlashdata('msg', 'Berhasil keluar');
        return redirect()->to('/appauth');
    }
}
