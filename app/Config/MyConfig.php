<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MyConfig extends BaseConfig
{
   public $siteName  = 'Akun STMIK EL RAHMA';
   public $siteDomain = 'akun.stmikelrahma.ac.id';
   public $siteEmail = 'admin@websiteku.com';
   public $google = [
      'redirect_url' => 'https://',  // redirect url setelah user berhasil login melalui google
      'client_id' => 'dasdasdasdasd',  // client id google
      'client_secret' => '1982ekjqwhjdq98',  // client secert google
      'path_secret' => null,  // secret untuk koneksi service account google active directory
      'sync' => false
   ];
   public $googleRecaptchaSiteKey = 'recaptchakey';
   public $googleRecaptchaSecretKey = 'recaptchasecret';

   public $register = false;
}
