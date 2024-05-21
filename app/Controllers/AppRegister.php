<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserAppModel;

class AppRegister extends BaseController
{
    public function index()
    {
        if (!config('MyConfig')->register) {
            return redirect()->to('auth');
        }

        helper('form');
        $data = [
            'title' => 'Register App',
        ];
        return view('auth/app_register', $data);
    }

    public function save()
    {
        //include helper form
        helper(['form']);
        //set rules validation form
        $rules = [
            'username'          => 'required|min_length[3]|max_length[20]',
            'firstname'          => 'required',
            'lastname'          => 'required',
            'email'         => 'required|min_length[6]|max_length[50]|valid_email|is_unique[user_app.email]',
            'password'      => 'required|min_length[6]|max_length[200]',
            'confpassword'  => 'matches[password]'
        ];

        if ($this->validate($rules)) {
            $model = new UserAppModel();
            $data = [
                'username'     => $this->request->getVar('username'),
                'firstname'     => $this->request->getVar('firstname'),
                'lastname'     => $this->request->getVar('lastname'),
                'email'    => $this->request->getVar('email'),
                'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT)
            ];
            $model->save($data);
            return redirect()->to('/');
        } else {
            $data['validation'] = $this->validator;
            return view('auth/app_register', $data);
        }
    }
}
