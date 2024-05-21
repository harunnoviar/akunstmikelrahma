<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleApi;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\LdapModel;
use App\Models\UserModel;

class Reset extends BaseController
{

    protected $globalModel, $groupModel;
    protected $ldapModel, $userModel;

    public function __construct()
    {
        $this->globalModel = new GlobalModel();
        $this->groupModel = new GroupModel();
        $this->ldapModel = new LdapModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // jika tidak enable reset password
        if (config('Auth')->allowReset === false) {
            return redirect()->to(base_url());
        }

        if ($this->request->getMethod() === 'post') {

            $rules = [
                'email' => [
                    'rules' => 'required|valid_email',
                    'errors' => [
                        'required' => '{field} harus diisi',
                        'valid_email' => '{field} harus valid',
                    ],
                ],
            ];
            // validasi dulu
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
            }

            $recaptchaResponse = trim($this->request->getVar('g-recaptcha-response'));
            $captcha = googleCaptachStore($recaptchaResponse);
            // Jika captcha error kembalikan dengan input pesan
            if ($captcha['success'] == false) {
                session()->setFlashdata('err', 'Warning Error captcha!');
                return redirect()->to(base_url('/reset'))->withInput();
            }
            $email = trim($this->request->getVar('email'));
            $cek_user = $this->userModel->getUserByEmail($email);

            if ($cek_user) {
                if ($cek_user->active != '1') { // Jika akun tidak aktif lempar ke auth
                    session()->setFlashdata('error', 'Akun ' . $email . ' belum diaktivasi!');
                    return redirect()->to(base_url('auth'));
                }
                if (empty($cek_user->recoveryemail)) { //jika recoveryemail kosong lempar ke auth
                    session()->setFlashdata('error', 'Email pemulihan dari ' . $email . ' belum didaftarkan!');
                    return redirect()->to(base_url('auth'));
                }

                $explodeEmailRecovery = explode('@', $cek_user->recoveryemail);
                $uidEmailRecovery = stringToSecret($explodeEmailRecovery[0]);
                $domainEmailRecovery = $explodeEmailRecovery[1];
                $data = [
                    'title' => 'Verifikasi Email Pemulihan',
                    'recoveryEmailMask' => $uidEmailRecovery . '@' . $domainEmailRecovery,
                    'recoveryEmail' => enkrip($cek_user->recoveryemail),
                    'id' => enkrip($email),
                    'validation' => \Config\Services::validation(),
                ];
                return view('reset/v_verify', $data);
            } else {
                session()->setFlashdata('err', $email . ' tidak terdaftar!');
                return redirect()->to(base_url('/reset'));
            }
        }

        $data = [
            'title' => 'Reset Password',
            'validation' => \Config\Services::validation(),
            'recaptchaSite' => config('MyConfig')->googleRecaptchaSiteKey,

        ];
        // dd(config('myconfig'), $data);
        return view('reset/v_index', $data);
    }

    public function viaemail()
    {
        if ($this->request->getMethod() === 'post') {
            $email = dekrip($this->request->getVar('id'));
            $recoveryemail = dekrip($this->request->getVar('recoveryemail'));
            $recoveryemail2 = $this->request->getVar('recoveryemail2');

            if ($recoveryemail2 != $recoveryemail) { //jika isian email recovery tidak sama maka redirect back
                session()->setFlashdata('err', 'Email pemulihan salah!');
                return redirect()->back();
            }

            $get_user = $this->userModel->getUserByEmail($email);
            if (!$get_user) {
                session()->setFlashdata('err', 'User tidak ada!');
                return redirect()->back();
            }
            $id = $get_user->id;
            $token_reset = random_string('numeric', 5);
            $encoded = implode(unpack("H*", enkrip($id)));

            // Kirim token melalui email
            $message = 'Email: <strong>' . $email . '</strong><br>Token : <strong><h1>' . $token_reset . '</h1></strong> (Berlaku selama 15 menit)';
            $send_mail = send_email($recoveryemail2, $message);

            if ($send_mail) {
                $data = [
                    'ip' => $this->request->getIPAddress(),
                    'request_at' => date('Y-m-d H:i:s'),
                    'token_reset' => $token_reset,
                ];
                $this->userModel->requestReset($id, $data);
                session()->setFlashdata('msg', 'Token berhasil dikirim ke <strong>' . $recoveryemail2 . '</strong>');

                // catat ke log
                $this->globalModel->insertLog($email, 'Permintaan reset via email ' . $recoveryemail2);
                return redirect()->to(base_url('/reset/changepass/' . $encoded));
            } else {
                session()->setFlashdata('err', 'Gagal kendala kirim email!');
                // catat ke log
                $this->globalModel->insertLog($email, 'Gagal kirim token via email');
                return redirect()->back();
            }
        }
    }

    public function changepass($id = '')
    {
        if (empty($id)) {
            return redirect()->to(base_url());
        }

        try {
            $decryptId = dekrip(pack("H*", $id));
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Link tidak valid!');
            return redirect()->to(base_url('auth'));
        }

        $getUser = $this->userModel->getUser($decryptId);
        if (empty($getUser)) {
            session()->setFlashdata('error', 'Akun tidak ditemukan!');
            return redirect()->to(base_url('auth'));
        }
        $valid_time = 60 * 15; // 900 detik atau 15 menit
        $timereset = strtotime($getUser['request_at']);

        // check kadaluarsa link
        if (time() <= ($timereset + $valid_time)) {
            $data = [
                'title' => 'Atur Ulang Sandi',
                // 'userdetail' => $getUser,
                'email' => enkrip($getUser['email']),
                'validation' => \Config\Services::validation(),
            ];
            return view('reset/v_changepass', $data);
        } else {
            session()->setFlashdata('error', 'Error, Link kadaluarsa!');
            return redirect()->to(base_url('auth'));
        }
    }

    public function setpass()
    {

        if ($this->request->getMethod() != 'post') {
            // Jika Bukan post maka redirect back
            return redirect()->back();
        }

        $email = dekrip($this->request->getVar('id')); // ambil dari id yan isinya enkrip email
        $get_user = $this->userModel->getUserDetailByEmail($email);
        if (empty($get_user)) {
            session()->setFlashdata('error', 'Akun tidak ditemukan!');
            return redirect()->to(base_url('auth'));
        }
        $uid = emailToUid($email);
        $domain = emailToDomain($email);
        // dd($get_user);
        $ou = $get_user->ou_name;
        $dn = 'uid=' . $uid . ',ou=' . $ou . ',' . $get_user->base_dn;
        // dd($get_user, $dn);
        $newpassword = $this->request->getVar('newpassword');
        // $newpassword2 = $this->request->getVar('newpassword2');
        $token = $this->request->getVar('token');
        $token_reset = $get_user->token_reset;
        $id_user = $get_user->u_id;

        $valid_time = 60 * 15; // 900 detik atau 15 menit
        $timereset = strtotime($get_user->request_at);

        // cek masa kadaluarsa token
        if (time() > ($timereset + $valid_time)) {
            session()->setFlashdata('error', 'Error, Token kadaluarsa!');
            return redirect()->to(base_url('auth'));
        }

        $rules = [
            'newpassword' => [
                'rules' => 'required|min_length[8]',
                'errors' => [
                    'required' => 'Sandi harus diisi',
                    'min_length' => 'Sandi minimal 8 karakter'
                ],
            ],
            'newpassword2' => [
                'rules' => 'required|matches[newpassword]',
                'errors' => [
                    'required' => 'Konfirmasi sandi harus diisi',
                    'matches' => 'Konfirmasi sandi harus sama',
                ],
            ],
            'token' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} harus diisi',
                ],
            ],
        ];
        // validasi dulu
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', \Config\Services::validation());
        }

        // Jika token tidak valid
        if ($token != $token_reset) {
            return redirect()->back()->withInput()->with('err', 'Token tidak valid');
        }

        // untuk pass_ldap
        $ldapPass = ldapPass($newpassword);

        $db_data = [
            'password' => password_hash($newpassword, PASSWORD_DEFAULT),
            'pass_ldap' => $ldapPass,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => "self",
            'info' => "Ubah sandi via reset",
        ];

        $ld_data = [
            'userpassword' => $db_data['pass_ldap']
        ];

        // prepare google data
        $g_data = [
            "password" => $newpassword,
            'suspended' => false,
            // 'orgUnitPath' => '/' . $ou,
        ];

        // jika sync dengan google di-enable
        if (Config('MyConfig')->google['sync']) {
            // Proses insert ke google
            $gApi = new GoogleApi();
            $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google
            if ($queryGoogle['httpcode'] === 400 && isset($queryGoogle['body']['error']['message'])) { // jika ada pesan error dari google
                $this->globalModel->insertLog($email, 'Google Error - ' . $queryGoogle['body']['error']['message']);
                session()->setFlashdata('err', 'Google Error - ' . $queryGoogle['body']['error']['message']);
                return redirect()->back()->withInput();
            }
            if (!$queryGoogle) {
                session()->setFlashdata('err', 'Google error - Gagal ubah password ' . $email);
                $this->globalModel->insertLog($email, 'Google error - Gagal ubah password ' . $email);
                return redirect()->back()->withInput();
            }
        }

        // proses di database 
        $db_update_user = $this->userModel->update($id_user, $db_data);
        if (!$db_update_user) { // jika gagal update user 
            session()->setFlashdata('error', 'Database error - Gagal ganti password ' . $email);
            $this->globalModel->insertLog($email, 'Database error - Gagal ganti password ' . $email);
            return redirect()->back()->withInput();
        }

        // proses di ldap
        $queryldap = $this->ldapModel->modifyUser($dn, $ld_data);
        if (!$queryldap) { // jika gagal update user di LDAP
            session()->setFlashdata('error', 'LDAP error - Gagal ganti password ' . $email);
            $this->globalModel->insertLog($email, 'LDAP error - Gagal ganti password ' . $email);
            return redirect()->back()->withInput();
        }

        // Jika semua proses sudah berhasil
        $this->globalModel->insertLog($email, 'Sukses - Ubah password via reset');
        // kirim notif ke email
        $message = 'Email <strong>' . $email . '</strong> berhasil melakukan perubahan sandi.';
        $send_mail = send_email($email, $message);
        session()->setFlashdata('msg', 'Sukses - ubah sandi ' . $email);
        return redirect()->to(base_url('auth'));
    }
}
