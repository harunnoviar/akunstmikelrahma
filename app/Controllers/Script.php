<?php

namespace App\Controllers;

use App\Libraries\GoogleApi;
use App\Libraries\Uuid;
use App\Models\CategoryModel;
use App\Models\DomainModel;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\LdapModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use Config\Ldap;
use CodeIgniter\CLI\CLI;


class Script extends Controller
{
   /**
    * Instance of the main Request object.
    *
    * @var CLIRequest|IncomingRequest
    */
   protected $request, $globalModel, $uuid, $ldapConf, $ldap, $ldapModel, $db;

   public function __construct()
   {
      $this->globalModel = new GlobalModel();
      $this->uuid = new Uuid();
      $this->ldapConf = new Ldap();
      $this->ldap = $this->ldapConf->stmikelrahma;
      $this->ldapModel = new LdapModel();
      $this->db = \Config\Database::connect();
   }
   public function index()
   {

      $data = [];
      print_r($data);
   }

   public function passwdChange($email)
   {
      helper(['text', 'function']);
      $arr_email = explode('@', $email);
      $domain = $arr_email[1];
      $uid = $arr_email[0];
      $str = explode('.', $domain);
      $dn = "uid=" . $arr_email[0] . ",ou=people,dc=" . (implode(",dc=", $str));
      $passw = "passw123334";

      $data = ["userPassword" => ldapPass($passw)];
      $modemail = $this->ldapModel->modifyUser($dn, $data);
      dd($dn, $data, $modemail);
   }

   public function update_ou($file_import)
   {
      helper(['text', 'function']);
      $path = WRITEPATH . 'uploads/';

      $file = $path . '/' . $file_import;
      $arr_name = explode('.', $file_import);
      $ext_file = end($arr_name);
      if ($ext_file === 'csv') {
         $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
      } elseif ($ext_file === 'xls') {
         $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
      } else {
         $reader     = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
      }
      $spreadsheet = $reader->load($file);
      $data = $spreadsheet->getActiveSheet()->toArray();
      $col_name = $data[0];
      // proses olah data yang diperoleh dari sheet
      foreach ($data as $k => $v) {
         if ($k === 0) continue;  // lewati karena isinya header field
         $c = $k - 1;

         for ($i = 0; $i < count($v); $i++) { // array dirangkai biar sesuai field
            $j[$col_name[$i]] = $v[$i];
         }
         $data_new[] = $j; // masukkan array yang sudah tertata ke array kosong
      }

      // Mulai proses semua data untuk diimport
      $total = 0;
      foreach ($data_new as $k => $v) {
         $userModel = new UserModel;
         $categoryModel = new CategoryModel;
         $ldapModel = new LdapModel;
         $groupModel = new GroupModel;

         if (empty($v['email'])) { // jika email kosong
            CLI::write($k + 1 . ' Email kosong', 'red');
         }
         if (empty($v['ou'])) { // jika ou kosong
            CLI::write($k + 1 . ' Ou kosong', 'red');
         }

         $email = $v['email'];
         $ou = $v['ou'];

         $get_category = $categoryModel->getCatByName($ou);
         $ou_id_new = $get_category->id;
         $ou_name_new = $get_category->name;
         $ou_base_dn_new = $get_category->base_dn;

         $user = $userModel->getUsers()
            ->select('a.id u_id,a.email,a.firstname,a.lastname,a.dispname,a.domain,a.active,a.password,a.created_at,a.updated_at,a.created_by,a.updated_by,a.info,a.pass_ldap,a.ou ou_id,b.id ou_id,b.name ou_name,b.base_dn')
            ->join('user_category b', 'b.id=a.ou')
            ->where('a.email', $email)
            ->get()->getFirstRow();

         if (empty($user)) {
            CLI::write($k + 1 . ' ' . $email . ' tidak ada di database', 'red');
            file_put_contents($path . $arr_name[0] . '_zero.txt', $email . " tidak ada di database\n", FILE_APPEND); // jika user tidak ada, catat ke file
         } else {

            // proses update OU di database
            $get_old_groups = $userModel->getUsers()
               ->select('b.user_id,b.group_id,c.g_name,c.base_group_dn')
               ->join('user_group b', 'b.user_id=a.id')
               ->join('group c', 'c.g_id=b.group_id')
               ->where('a.email', $email)
               ->get()->getResult();

            $old_groups = [];
            if ($get_old_groups) {
               foreach ($get_old_groups as $go) {
                  $old_groups[] = $go->g_name;
               }
            }
            $get_groups = $groupModel->getGroupByOu($ou_name_new);

            $groupModel->deleteAllGroupInUser($user->u_id); // hapus semua user dari group yang ada
            if (!(in_array('inet-eduroam', $old_groups))) { // jika group lama tidak ada inet-eduroam
               foreach ($get_groups as $j => $gg) {
                  if ($gg->g_name === 'inet-eduroam') {  // buang array yang ada inet-eduroam
                     unset($get_groups[$j]);
                  }
               }
            }
            foreach ($get_groups as $gg) {
               $data_group = [
                  'user_id' => $user->u_id,
                  'group_id' => $gg->g_id,
               ];
               $insert_user_to_group = $groupModel->insertUserToGroup($data_group); // masukkan user ke semua group
            }

            $db_data = [
               'ou' => $ou_id_new,
               'updated_at' => date('Y-m-d H:i:s'),
               'updated_by' => 'script',
            ];
            $db_update_user = $userModel->update($user->u_id, $db_data);  // update

            // proses di google
            // $g_data = [
            //    'orgUnitPath' => '/' . $ou_id_new,
            // ];
            // $gApi = new GoogleApi();
            // $queryGoogle = $gApi->patchUser($email, $g_data); // proses data ke google

            //Mulai proses di LDAP
            // $uid = emailToUid($email);
            // $ou_name_old = $user->ou_name;
            // $ou_base_dn_old = $user->base_dn;
            // $dn_old = 'uid=' . $uid . ',ou=' . $ou_name_old . ',' . $ou_base_dn_old;
            // $new_ou = 'ou=' . $ou_name_new . ',' . $ou_base_dn_new;
            // $get_user_memberOf = $ldapModel->getUserMemberOf($ou_base_dn_old, $email);
            // if ($get_user_memberOf) {
            //    foreach ($get_user_memberOf as $g) {
            //       $group_dn = 'cn=' . $g . ',' . 'ou=groups,dc=stmikelrahma,dc=ac,dc=id';
            //       $delUserFromGroup = $this->ldapModel->delUserFromGroup($group_dn, $dn_old);  //hapus user dari semua grup dulu
            //    }
            // }

            // $ld_get_user = $this->ldapModel->getMatchUser($ou_base_dn_old, $email);
            // if (isset($ld_get_user["count"]) && $ld_get_user["count"] === 1) { // cek jika user ada
            //    $ld_get_user = $ld_get_user[0];
            //    $ld_data = [
            //       'objectClass' => [
            //          0 => 'inetOrgPerson',
            //          1 => 'posixAccount',
            //       ],
            //       'userPassword' => $ld_get_user['userpassword'][0],
            //       'displayName' =>  $ld_get_user['displayname'][0],
            //       'cn' =>  $ld_get_user['cn'][0],
            //       'givenName' =>  $ld_get_user['givenname'][0],
            //       'sn' => $ld_get_user['sn'][0],
            //       'gidNumber' =>  $ld_get_user['gidnumber'][0],
            //       'uidNumber' =>  $ld_get_user['uidnumber'][0],
            //       'uid' => $ld_get_user['uid'][0],
            //       'mail' =>  $ld_get_user['mail'][0],
            //       'homeDirectory' => $ld_get_user['homedirectory'][0],
            //    ];

            //    // d($ld_data, $dn_old);
            //    $this->ldapModel->deleteUser($dn_old); // hapus user di ldap

            //    // Proses insert user di LDAP
            //    $dn_new = 'uid=' . $uid . ',ou=' . $ou_name_new . ',' . $ou_base_dn_new;
            //    $ld_add_user = $this->ldapModel->createUser($dn_new, $ld_data);

            //    // dd($get_groups);
            //    foreach ($get_groups as $gr) {
            //       // dd($this->groupModel->getGrpById($g));
            //       $group_dn = 'cn=' . $gr->g_name . ',' . $gr->base_group_dn;  // menjadi format cn=xxx,ou=groups,dc=xxx,dc=yyy,dc=zzz
            //       $addUserToGroup = $this->ldapModel->addUserToGroup($group_dn, $dn_new);  //tambahkan user ke group
            //       // dd($group_dn, $dn_new, $addUserToGroup);
            //    }


            //    if ($ld_add_user) {
            //       CLI::write($k + 1 . ' ' . $email . ' oke', 'green');
            //    }
            // }
         }
      }
   }

   public function import($filecsv, $length = 100000, $separator = ';')
   {
      helper(['text', 'function']);
      $path_file = WRITEPATH . 'uploads';
      $file = $path_file . '/' . $filecsv;
      if (!!!file_exists($file)) return CLI::Write('File ' . $file . ' tidak ada!', 'red');
      $open_csv = fopen($file, "r");
      $row = 1;
      $field = fgetcsv($open_csv, $length, $separator);
      $password = "passw123334";


      while (($data = fgetcsv($open_csv, $length, $separator)) !== FALSE) {
         $num = count($data);
         $firstname = $data[0];
         $lastname = $data[1];
         $fullname = $firstname . ' ' . $lastname;
         $email = $data[2];
         $arr_email = explode('@', $email);
         $uid = $arr_email[0];
         $domain = $arr_email[1];
         $str = explode('.', $domain); //split domain jadi array

         $get_user_from_db = $this->db->table("user")->select('email')->where('email', $email)->get()->getFirstRow(); //cari jika user sudah ada
         if ($get_user_from_db) {
            CLI::write('Abaikan ' . $email . ' ' . $row, "red");
         } else {

            $get_domain_from_db = $this->db->table('user_domain a')
               // ->select('*')
               // ->select('a.name domain')
               ->select('a.id, a.name domain, a.protected,c.id ou_id,c.name ou,c.base_dn,c.base_group_dn')
               ->join('cat_with_dom b', 'b.d_id=a.id', 'left')
               ->join('user_category c', 'c.id=b.c_id', 'left')
               ->where('a.name', $domain)
               ->get()
               ->getFirstRow();
            // dd($domain, $get_domain_from_db);
            if (empty($get_domain_from_db)) die($row);  //jika domain tidak ketemu, hentikan, catat row nya

            // prepare data for database
            $db_data = [
               'email' => $email,
               'firstname' => $firstname,
               'lastname' => $lastname,
               'dispname' => $fullname,
               'domain' => $get_domain_from_db->id,
               'created_at' => date('Y-m-d H:i:s'),
               'created_by' => "import csv",
               'pass_ldap' => ldapPass($password),
            ];

            // prepare data for LDAP
            $ld_data = [
               "objectClass" => [
                  0 => "inetOrgPerson",
                  1 => "posixAccount",
               ],
               "cn" => $fullname,
               "gidNumber" => $get_domain_from_db->ou_id,
               "homeDirectory" => "/home/" . $arr_email[0],
               "sn" => $lastname,
               "uid" => $uid,
               "uidNumber" => $row,
               "displayName" => $fullname,
               "givenName" => $firstname,
               "mail" => $email,
               "userPassword" => ldapPass($password),
            ];
            $dn = "uid=" . $uid . ",ou=" . $get_domain_from_db->ou . "," . $get_domain_from_db->base_dn;
            // dd($db_data, $ld_data, $dn);

            // Proses import ke ldap
            $addUserLdap = $this->ldapModel->createUser($dn, $ld_data);
            if (!($addUserLdap))  die($email . " " . $dn . " " . $row . " gagal Ldap\n");

            // import ke database
            $addUserDb = $this->db->table('user')
               ->insert($db_data);
            if (!($addUserDb))  die($email . " " . $dn . " " . $row . " gagal Database\n");

            CLI::write($email . ' ' . $dn . ' ' . $row, 'green');
         }
         $row++;
      }
      fclose($open_csv);

      die();
   }

   public function import2($filecsv, $length = 100000, $separator = ';')
   {
      helper(['text', 'function']);
      $ou = ['staff', 'student'];
      $path_file = WRITEPATH . 'uploads';
      $file = $path_file . '/' . $filecsv;
      if (!!!file_exists($file)) return CLI::Write('File ' . $file . ' tidak ada!', 'red');
      $open_csv = fopen($file, "r");
      $row = 28822;
      $field = fgetcsv($open_csv, $length, $separator);
      $x = 0;
      $y = 0;


      while (($data = fgetcsv($open_csv, $length, $separator)) !== FALSE) {
         $num = count($data);
         $firstname = $data[0];
         $lastname = $data[1];
         $fullname = $firstname . ' ' . $lastname;
         $email = $data[2];
         $arr_email = explode('@', $email);
         $domain = $arr_email[1];
         $uid = $arr_email[0];
         $password = "passw123334";

         $data = [
            "objectClass" => [
               0 => "inetOrgPerson",
               1 => "posixAccount",
            ],
            "cn" => $fullname,
            // "gidNumber" => 100,
            "homeDirectory" => "/home/" . $uid,
            "sn" => $lastname,
            "uid" => $uid,
            "uidNumber" => $row,
            "displayName" => $fullname,
            "givenName" => $firstname,
            "mail" => $email,
            "userPassword" => ldapPass($password),
         ];


         if ($domain === 'mhs.stmikelrahma.ac.id') { // jika domain email adalah student
            $dn = "uid=" . $uid . ",ou=" . $ou[1] . ",dc=stmikelrahma,dc=ac,dc=id";
            $data = array_merge(["gidNumber" => 2], $data); // gid nya student
            $x++;
            // CLI::write($dn);
         } else {
            $dn = "uid=" . $uid . ",ou=" . $ou[0] . ",dc=stmikelrahma,dc=ac,dc=id";
            $data = array_merge(["gidNumber" => 1], $data);  //gid staff
            $y++;
            // CLI::write($dn, 'green');
         }

         // Proses import ke ldap
         $addUser = $this->ldapModel->createUser($dn, $data);
         // ($addUser) ? CLI::write($dn . ' Oke', 'green') : die($dn . ' - error');
         if ($addUser) {
            CLI::write($dn . ' Oke', 'green');
         } else {
            CLI::write($dn . ' fail', 'red');
            var_dump($x, $y);
            die();
         }


         $row++;
      }
      fclose($open_csv);
      var_dump($x, $y);
   }

   public function fix_double_ou_in_ldap()
   {
      helper(['function']);
      $base_dn = ',dc=stmikelrahma,dc=ac,dc=id';
      $ou_dosen = 'ou=dosen';
      $ou_staff = 'ou=staff';
      $base_dn_dosen = $ou_dosen . $base_dn;
      $base_dn_staff = $ou_staff . $base_dn;
      // var_dump($base_dn_dosen, $base_dn_staff);
      // exit;
      $userModel = new UserModel();
      $ldapModel = new LdapModel();
      $get_user_ou = $userModel->getUsers()
         ->select('*')
         ->join('user_category b', 'b.id=a.ou', 'left')
         ->where('a.ou', 3)
         // ->where('a.active', 1)
         ->get()
         ->getResult();

      foreach ($get_user_ou as $i => $o) {
         $file_log = WRITEPATH . "/logs/log_double_ou.log";
         $i = $i + 1;
         $email = $o->email;
         $uid = emailToUid($email);
         $dn_dosen = "uid=$uid,$base_dn_dosen";
         $dn_staff = "uid=$uid,$base_dn_staff";
         $cek_user_in_ldap_dosen = $ldapModel->getMatchUser($base_dn_dosen, $email);
         $cek_user_in_ldap_staff = $ldapModel->getMatchUser($base_dn_staff, $email);
         if ($cek_user_in_ldap_dosen && $cek_user_in_ldap_dosen['count'] === 1) CLI::Write("$i $email - $dn_dosen - ada di ou dosen", "green");
         if ($cek_user_in_ldap_staff && $cek_user_in_ldap_staff['count'] === 1) CLI::Write("$i $email - $dn_staff - ada di ou staff", "blue");
         // var_dump($cek_user_in_ldap_staff['count']);
         // exit;
         if ($cek_user_in_ldap_dosen['count'] === 1 && $cek_user_in_ldap_staff['count'] === 1) { // jika ada di kedua ou hapus yang ou staff
            CLI::Write("$i $email - ada double  ou", "yellow");
            // $del_user_in_ou_staff = $ldapModel->deleteUser($dn_staff);
            // if ($del_user_in_ou_staff) {
            //    CLI::Write("$i $email - $dn_staff - terhapus");
            //    file_put_contents($file_log, "$i $email - $dn_staff - terhapus\n", FILE_APPEND);
            // }
         }

         // var_dump($dn_dosen, $dn_staff);
         // var_dump($cek_user_in_ldap_dosen['count'], $cek_user_in_ldap_staff['count']);

         // exit;
      }
      // var_dump($email);
   }

   public function cek_user_in_ou($ou)
   {
      helper(['function']);
      $base_dn = 'dc=stmikelrahma,dc=ac,dc=id';
      $base_dn_ou = "ou=$ou,$base_dn";
      $userModel = new UserModel();
      $ldapModel = new LdapModel();
      $get_user_ou = $userModel->getUsers()
         ->select('*')
         ->join('user_category b', 'b.id=a.ou', 'left')
         ->where('b.name', $ou)  // 
         // ->where('a.active', 1)
         ->get()
         ->getResult();
      // var_dump(count($get_user_ou));
      // exit;

      foreach ($get_user_ou as $i => $o) {
         $file_log = WRITEPATH . "/logs/log_cek_ou.log";
         $i = $i + 1;
         $email = $o->email;
         $uid = emailToUid($email);
         $dn = "uid=$uid,$base_dn_ou";
         $cek_user_in_ldap = $ldapModel->getMatchUser($base_dn_ou, $email);
         if ($cek_user_in_ldap['count'] === 1) {
            CLI::Write("$i $email - $dn - ada di ou $ou", "green");
         } else {
            CLI::Write("$i $email - $dn - tidak di ou $ou", "red");
            // $ou_staff = "staff";
            // $base_dn_ou_staff = "ou=$ou_staff,dc=stmikelrahma,dc=ac,dc=id";
            // $dn_staff = "uid=$uid,$base_dn_ou_staff";
            // $cek_user_in_ou_staff = $ldapModel->getMatchUser($base_dn_ou_staff, $email);
            // if ($cek_user_in_ou_staff['count'] === 1) {
            //    CLI::Write("$i $email - $dn_staff - ada di ou $ou_staff", "yellow");
            // $user_awal = $userModel->getUsers()
            //    ->select('a.id u_id,a.ou ou_id,b.name ou_name')
            //    ->join('user_category b', 'b.id=a.ou', 'left')
            //    ->where('a.email', $email)
            //    ->get()
            //    ->getFirstRow();
            // $groupModel = new GroupModel();
            // $del_user_from_group = $groupModel->deleteAllGroupInUser($user_awal->u_id);
            // $get_group_for_staff = $groupModel->getGroups()
            //    ->select('a.g_id,a.g_name')
            //    ->join('group_with_cat b', 'b.g_id=a.g_id', 'left')
            //    ->join('user_category c', 'c.id=b.c_id', 'left')
            //    ->where('c.name', $ou_staff)
            //    ->get()
            //    ->getResult();
            // $userModel->update($user_awal->id, ['ou' => 1]); // update ke ou staff (1)
            // foreach ($get_group_for_staff as $gs) {
            //    $data_group = [
            //       'user_id' => $user_awal->u_id,
            //       'group_id' => $gs->g_id
            //    ];

            //    $groupModel->insertUserToGroup($data_group);
            // }
            // var_dump($user_awal, $get_group_for_staff);
            //    exit;
            // }
         }

         // var_dump($cek_user_in_ldap);
         // exit;
      }
      // var_dump($email);
   }

   public function tools($ou)
   {
      dd($this->ldapModel->createOu($ou, "ou=" . $ou . ",dc=stmikelrahma,dc=ac,dc=id"));
      $ou = ['groups', 'people'];
      $db = \Config\Database::connect();
      $dataDomName = $db->query("SELECT id,name FROM user_domain ORDER BY id")->getResultArray();  // Ambil data domain dari tabel user_domain
      foreach ($dataDomName as $k => $v) {
         // var_dump($v);
         $str = explode(".", $v['name']);
         $newstr = "dc=" . implode(",dc=", $str); // ubah jadi format dn
         if ($newstr === 'dc=stmikelrahma,dc=ac,dc=id') {
            CLI::write($newstr, 'red');
         } else {
            CLI::write($newstr);
            // var_dump($this->ldapModel->createDc($str[0], $newstr)); // create domain component
            foreach ($ou as $o) {
               $new_dn = "ou=" . $o . ',' . $newstr;
               d($new_dn);
               $addOu = $this->ldapModel->createOu($o, $new_dn);
               var_dump($addOu);
            }
         }
      }
      die();
   }

   public function createGroup($group_name)
   {
      // dd($this->ldapModel->createGroup($group_name, "cn=" . $group_name . ",ou=groups,dc=stmikelrahma,dc=ac,dc=id", "uid=app,dc=stmikelrahma,dc=ac,dc=id"));
      dd($this->ldapModel->createGroup($group_name, "cn=" . $group_name . ",ou=groups,dc=stmikelrahma,dc=ac,dc=id", "uid=admin,dc=stmikelrahma,dc=ac,dc=id"));
   }

   public function mv_user_ou($ou, $email)
   {
      helper(['text', 'function']);
      $ldapModel = new LdapModel();
      $base_dn = 'dc=stmikelrahma,dc=ac,dc=id';

      $get_user = $ldapModel->getMatchUser($base_dn, $email);
      // dd($get_user);

      if ($get_user['count'] === 1) {
         $uid = emailToUid($email);
         $new_rdn = "uid=$uid";
         $dn = $get_user[0]['dn'];
         // dd($dn);
         // $dn = "uid=$uid,ou=staff,dc=stmikelrahma,dc=ac,dc=id";
         $new_parrent = "ou=$ou,dc=stmikelrahma,dc=ac,dc=id";
         $update = $ldapModel->updateOu($dn, $new_rdn, $new_parrent, true);
         $get_new_user = $ldapModel->getMatchUser($base_dn, $email);
         dd($dn, $new_rdn, $new_parrent, $update, $get_new_user);
      }
   }

   public function google_api()
   {
      $gApi = new GoogleApi();
      // dd(time());
      dd($gApi->getUo());
   }

   public function update_user()
   {
      // $globalModel = new GlobalModel;
      $db = \Config\Database::connect();
      $dataDomName = $db->query("SELECT id,name FROM user_domain ORDER BY id")->getResultArray();
      foreach ($dataDomName as $k => $v) {
         // var_dump($v);
         $str = explode(".", $v['name']);
         $newstr = "dc=" . implode(",dc=", $str);

         $db->query("UPDATE user_domain SET dc_format='" . $newstr . "' WHERE id='" . $v['id'] . "'");
         d($db->affectedRows());
         CLI::write($newstr);
      }

      die();
   }


   // public function cek()
   // {
   //    for ($i = 0; $i < 10000; $i++) {
   //       # code...
   //       echo "cekaja" . PHP_EOL;
   //       $data[] = ['msg' => 'anu_' . $i];
   //    }
   //    echo count($data) . PHP_EOL;

   //    // $color = CLI::prompt('What is your favorite color?');
   //    // echo $color . PHP_EOL;
   //    // CLI::write('File overwritten.', 'light_red');
   //    // CLI::write('The rain in Spain falls mainly on the plains.');
   //    // CLI::write('File created.', 'green');
   //    // for ($i = 0; $i <= 10; $i++) {
   //    //    CLI::write($i);
   //    // }

   //    // CLI::error('Cannot write to file: ');

   //    // $tasks = ['1', '2', '3'];
   //    // $totalSteps = count($tasks);
   //    // $currStep   = 1;

   //    // foreach ($tasks as $task) {
   //    //    CLI::showProgress($currStep++, $totalSteps);
   //    //    // $task->run();
   //    // }

   //    // // Done, so erase it...
   //    // CLI::showProgress(false);
   // }
}
