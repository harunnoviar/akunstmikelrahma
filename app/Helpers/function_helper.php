<?php

use CodeIgniter\Commands\Utilities\Publish;
use Config\Ldap;

function emailToDomain($email)
{
   $str = explode("@", $email);
   $domain = $str[1];
   return $domain;
}

function emailToUid($email)
{
   $str = explode("@", $email);
   $uid = $str[0];
   return $uid;
}

function emailBreak($email)
{
   $data = [
      'uid' => explode("@", $email)[0],
      'domain' => explode("@", $email)[1],
   ];
   return $data;
}

function ldapPass($pass)
{
   $salt = random_string('alnum', 8);
   $hash = hash('sha512', $pass . $salt, true);
   $hashed_password = '{SSHA512}' . base64_encode($hash . $salt);
   return $hashed_password;
}

function get_string_between($string, $start, $end)
{
   $string = ' ' . $string;
   $ini = strpos($string, $start);
   if ($ini == 0) return '';
   $ini += strlen($start);
   $len = strpos($string, $end, $ini) - $ini;
   return substr($string, $ini, $len);
}

function enkrip($arg)
{
   $encrypter = \Config\Services::encrypter();
   $val = base64_encode(urlencode($encrypter->encrypt($arg)));
   return $val;
}

function dekrip($arg)
{
   $encrypter = \Config\Services::encrypter();
   $val = $encrypter->decrypt(urldecode(base64_decode($arg)));
   return $val;
}

function googleCaptachStore($recaptchaResponse)
{
   // $recaptchaResponse = trim($this->request->getVar('g-recaptcha-response'));
   // $userIp = $this->request->ip_address();

   $secret = config('MyConfig')->googleRecaptchaSecretKey;

   $credential = array(
      'secret' => $secret,
      'response' => $recaptchaResponse,
   );

   $verify = curl_init();
   curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
   curl_setopt($verify, CURLOPT_POST, true);
   curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($credential));
   curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
   $response = curl_exec($verify);
   $status = json_decode($response, true);
   curl_close($verify);
   return $status;
}

function stringToSecret(string $string = null)
{
   if (!$string) {
      return null;
   }
   $length = strlen($string);
   $visibleCount = (int) round($length / 4);
   $hiddenCount = $length - ($visibleCount * 2);
   return substr($string, 0, $visibleCount) . str_repeat('*', $hiddenCount) . substr($string, ($visibleCount * -1), $visibleCount);
}

function trim_group_name($group)
{
   return preg_replace('/[[:blank:]]+/', '-', strtolower($group));
}

function send_email($mailto, $message)
{
   $email = \Config\Services::email();

   try {
      $email = \Config\Services::email();
      $email->setTo($mailto);
      $email->setSubject(config('MyConfig')->siteDomain . ' - Reset Password Email');
      $email->setMessage($message);
      // return $email->printDebugger();
      return $email->send();
   } catch (\Throwable $e) {
      return $email->printDebugger();
   }
}

// check domain
function pingDomain($domain, $port)
{
   $starttime = microtime(true);
   $stoptime  = microtime(true);

   try {
      $file      = fsockopen($domain, $port, $errno, $errstr, 3);
      fclose($file);
      // $status = (($stoptime - $starttime) * 1000);
      // return $status;
      return true;
   } catch (\Throwable $e) {
      return false;
      // dd($e->getMessage());
   }
}
