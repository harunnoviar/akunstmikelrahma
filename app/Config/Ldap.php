<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Ldap extends BaseConfig
{
   // semua config ini mengacu pada schema yang digunakan di server LDAP
   // default config
   public $default = [
      'host' => 'localhost',
      'port' => 389,
      // 'server' => 'ldap://localhost:389/',
      'user' => 'cn=admin,dc=example,dc=com',
      'pass' => 'password123',
      'usetls' => true,
      'searchbase' => 'dc=example,dc=com',
      'treestaff' => 'ou=staff,dc=example,dc=com',
      'treegroupstaff' => 'ou=groups,dc=example,dc=com',
      'treemhs' => 'ou=student,dc=example,dc=com',
      'treegroupmhs' => 'ou=groups,dc=example,dc=com',
      'loginattribute' => 'uid',
   ];

   public $stmikelrahma = [
      'host' => 'localhost',
      'port' => 389,
      'proto' => 'ldap://',
      // 'server' => 'ldap://localhost:389/',
      'user' => 'cn=admin,dc=example,dc=com',
      'pass' => 'password123',
      'usetls' => true,
      'searchbase' => 'dc=example,dc=com',
      'treestaff' => 'ou=staff,dc=example,dc=com',
      'treegroupstaff' => 'ou=groups,dc=example,dc=com',
      'treemhs' => 'ou=student,dc=example,dc=com',
      'treegroupmhs' => 'ou=groups,dc=example,dc=com',
      'loginattribute' => 'uid',
   ];
}
