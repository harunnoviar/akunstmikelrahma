<?php

namespace App\Libraries;

use Exception;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

/**
 * GoogleApi Class
 *
 * This implements Google Api with service account authentication
 * @category Libraries
 * @author Harun Noviar
 */

class GoogleApi
{

   protected $path_secret, $secretJson, $secretArr;
   public function __construct()
   {
      if (!function_exists('curl_init')) {
         throw new \Exception('<strong>ERROR:</strong> You need to install the PHP module
         <strong><a href="http://php.net/curl">curl</a></strong> to be able
         to use GoggleApi Service Account authentication.');
      }
      $this->path_secret = config('MyConfig')->google['path_secret'];
      file_exists($this->path_secret) or die('File secret tidak ada');
      $this->secretJson = (file_get_contents($this->path_secret));
      $this->secretArr = json_decode($this->secretJson, true);
   }

   public function insertUser($data)
   {
      $url = 'https://admin.googleapis.com/admin/directory/v1/users';
      $body = json_encode($data);
      $resp = $this->cUrl($url, 'POST', $this->accessToken(), $body);
      return $resp;
   }

   public function getUser($email)
   {
      $url = 'https://admin.googleapis.com/admin/directory/v1/users/' . $email;
      $resp = $this->cUrl($url, 'GET', $this->accessToken());

      if (isset($resp['body']['error']['code']) && $resp['body']['error']['code'] === 404) {
         // tidak ada user,kembalikan false
         return false;
      }
      return $resp['body'];
   }

   public function patchUser($email, $data)
   {
      $url = 'https://admin.googleapis.com/admin/directory/v1/users/' . $email;
      $body = json_encode($data);
      $resp = $this->cUrl($url, 'PATCH', $this->accessToken(), $body);
      return $resp;
   }

   public function deleteUser($email)
   {
      $url = 'https://admin.googleapis.com/admin/directory/v1/users/' . $email;
      $resp = $this->cUrl($url, "DELETE", $this->secretArr['access_token']);
      return $resp;
   }

   public function getUo()
   {
      $url = 'https://admin.googleapis.com/admin/directory/v1/customer/C02zk0k7b/domains';
      $resp = $this->cUrl($url, 'GET', $this->accessToken());
      return $resp;

      if (isset($resp['body']['error']['code']) && $resp['body']['error']['code'] === 404) {
         // tidak ada user,kembalikan false
         return false;
      }
      return $resp['body'];
   }



   // Private Function

   private function accessToken()
   {
      $token_save = $this->secretArr['access_token'];
      if (!isset($token_save) || empty($token_save)) {
         $this->requestToken();
      }
      $expires_in = $this->secretArr['expires_in'];
      $created_at = $this->secretArr['created_at'];
      $time_token = $created_at + $expires_in - 300;
      // dd($time_token);

      // jika ada token
      if (isset($token_save)) {
         // cek masa berlaku token, jika waktu saat ini lebih besar maka request token
         if (time() > $time_token) {
            return $this->requestToken();
         } else {
            return $token_save;
         }
      } else {
         // jika file secret tidak ada token, langsung request
         return $this->requestToken();
      }
   }

   private function requestToken()
   {
      $url = 'https://oauth2.googleapis.com/token?grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=' . $this->getJWT();
      $resp = $this->cUrl($url, 'POST');
      // dd($resp);

      if (($resp['body']['access_token'])) {
         $access_token = $resp['body']['access_token'];
         $expires_in = $resp['body']['expires_in'];
      } else {
         $access_token = null;
      }
      // gabungkan sa.json lama dengan data baru
      $sa_new = json_encode(array_merge($this->secretArr, [
         'access_token' => $access_token,
         'expires_in' => $expires_in,
         'created_at' => time(),
      ]));
      file_put_contents($this->path_secret, $sa_new);
      return $access_token;
   }

   private function getJWT()
   {
      $prv_key = $this->secretArr['private_key'];
      $payload = [
         "iss" => $this->secretArr['client_email'],
         "sub" => $this->secretArr['sub'],
         "scope" => $this->secretArr['scope'],
         "aud" => $this->secretArr['aud'],
         "exp" => time() + 3600, // expire in 1 hours
         "iat" => time()
      ];
      $jwt = JWT::encode($payload, $prv_key, 'RS256');
      return $jwt;
   }

   private function cUrl($url, $method, $access_token = null, $body = null)
   {

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_ENCODING, '');
      curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
      curl_setopt($ch, CURLOPT_TIMEOUT, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);


      if (empty($access_token)) {
         curl_setopt($ch,  CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            'Content-Type: application/json',
            "Content-length: 0",
         ]);
      } else {
         curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
         curl_setopt($ch,  CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            'Content-Type: application/json',
            // "Content-length: 0",
            "Authorization: Bearer " . $access_token
         ]);
      }

      $body = json_decode(curl_exec($ch), true);
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      $respons = ['httpcode' => $httpcode, 'body' => $body];
      return $respons;
   }
}
