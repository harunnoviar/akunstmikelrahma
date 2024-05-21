<?php

namespace App\Models;

use CodeIgniter\Config\Services;
use Config\Ldap;
use Exception;

class LdapModel
{
    protected $config, $ldapconn, $request, $ldConf, $ldap;
    public function __construct()
    {
        helper('function');
        $this->request = Services::request();
        $this->ldConf = new Ldap;
        $this->ldap = $this->ldConf->stmikelrahma;  // pilih koneksi config LDAP
        $ldapServer = $this->ldap['proto'] . $this->ldap['host'] . ':' . $this->ldap['port'];
        $this->ldapconn = ldap_connect($ldapServer) or die("That LDAP-URI was not parseable");
        ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);

        // check port dan ip ldap
        if (!(pingDomain($this->ldap['host'], $this->ldap['port']))) {
            echo "Koneksi LDAP sedang bermasalah, silakan cek terlebih dahulu!";
            throw \CodeIgniter\Exceptions\PageNotFoundException::forEmptyController();
        }

        // check ldap bind
        try {
            ldap_bind($this->ldapconn, $this->ldap['user'], $this->ldap['pass']);
        } catch (Exception $e) {
            // echo "message: " . $e->getMessage();
            echo "Koneksi LDAP sedang bermasalah, silakan cek terlebih dahulu!";
            throw \CodeIgniter\Exceptions\PageNotFoundException::forEmptyController();
        }
    }

    public function getUser($base_group_dn, $email)
    {
        $attr = array('cn', 'gidNumber', 'homeDirectory', 'sn', 'uid', 'uidNumber', 'displayName', 'givenName', 'mail', 'userPassword', 'memberOf');
        // $filter = "(mail=*)";
        $filter = "(mail=*$email*)";
        // $filter = "(|(cn=*$mail*)(mail=*$mail*))";
        // echo $filter;
        $search = ldap_search($this->ldapconn, $base_group_dn, $filter, $attr);
        $entry = ldap_get_entries($this->ldapconn, $search);
        // ldap_close($this->ldapconn);
        return $entry;
    }

    public function getMatchUser($base_group_dn, $email)
    {
        $attr = array('cn', 'gidNumber', 'homeDirectory', 'sn', 'uid', 'uidNumber', 'displayName', 'givenName', 'mail', 'userPassword', 'memberOf');
        // $filter = "(mail=*)";
        $filter = "(mail=$email)";
        // $filter = "(|(cn=*$mail*)(mail=*$mail*))";
        // echo $filter;
        $search = ldap_search($this->ldapconn, $base_group_dn, $filter, $attr);
        $entry = ldap_get_entries($this->ldapconn, $search);
        // ldap_close($this->ldapconn);
        return $entry;
    }

    public function getUserMemberOf($base_group_dn, $email)
    {
        $attr = array('cn', 'gidNumber', 'homeDirectory', 'sn', 'uid', 'uidNumber', 'displayName', 'givenName', 'mail', 'userPassword', 'memberOf');
        // $attr = array('cn', 'displayname', 'sn', 'givenname', 'mail', 'category', 'memberOf');
        $filter = "(mail=$email)";
        $search = ldap_search($this->ldapconn, $base_group_dn, $filter, $attr);
        $entry = ldap_get_entries($this->ldapconn, $search);
        // dd($entry);
        if (isset($entry[0]['memberof'])) {
            $memberof = $entry[0]['memberof'];
            $count = $memberof['count'];
            unset($memberof['count']);

            for ($i = 0; $i < $count; $i++) {
                $groups[] = get_string_between($memberof[$i], 'cn=', ',ou=');
            }
            // kembalikan array groups
            return $groups;
        } else {
            // user tidak punya group
            return false;
        }
    }

    public function getUserMemberOfDetail($base_group_dn, $email)
    {
        $attr = array('cn', 'gidNumber', 'homeDirectory', 'sn', 'uid', 'uidNumber', 'displayName', 'givenName', 'mail', 'userPassword', 'memberOf');
        $filter = "(mail=$email)";
        $search = ldap_search($this->ldapconn, $base_group_dn, $filter, $attr);
        $entry = ldap_get_entries($this->ldapconn, $search);
        // dd($entry);
        if (isset($entry[0]['memberof'])) {
            $memberof = $entry[0]['memberof'];
            unset($memberof['count']);
            // kembalikan array memberof
            return $memberof;
        } else {
            // user tidak punya group
            return false;
        }
    }

    public function createUser($dn, $data)
    {
        try {
            ldap_add($this->ldapconn, $dn, $data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function modifyUser($dn, $data)
    {
        try {
            return ldap_modify($this->ldapconn, $dn, $data);
            // ldap_close($this->ldapconn);
            // return true;
        } catch (\Throwable $e) {
            // throw $e;
            // return 0;
            // die($e);
            return $e->getMessage();
        }
    }

    public function getUserList()
    {
        $attr = array('cn', 'displayname', 'sn', 'givenname', 'mail', 'category');
        $filter = "(mail=*@mhs.uingusdur.ac.id)";
        $base_dn = "ou=people,dc=mhs,dc=uingusdur,dc=ac,dc=id";
        $result = ldap_search($this->ldapconn, $base_dn, $filter, $attr);
        if (FALSE !== $result) {
            $entries = ldap_get_entries($this->ldapconn, $result);
            for ($x = 0; $x < $entries['count']; $x++) {
                if (!empty($entries[$x]['mail'][0])) {
                    // CLI::write($x);
                    // CLI::write($entries[$x]['mail'][0]);
                    // $email = $entries[$x]['mail'][0];
                    $members['member'] = $entries[$x]['dn'];
                    ldap_mod_add($this->ldapconn, 'cn=internet,ou=groups,dc=mhs,dc=uingusdur,dc=ac,dc=id', $members);
                    print_r($members);
                    // die();
                }
                // dd($entries);
                // if (
                //     !empty($entries[$x]['givenname'][0]) &&
                //     !empty($entries[$x]['mail'][0]) &&
                //     !empty($entries[$x]['samaccountname'][0]) &&
                //     !empty($entries[$x]['sn'][0]) &&
                //     'Shop' !== $entries[$x]['sn'][0] &&
                //     'Account' !== $entries[$x]['sn'][0]
                // ) {
                //     $ad_users[strtoupper(trim($entries[$x]['samaccountname'][0]))] = array('email' => strtolower(trim($entries[$x]['mail'][0])), 'first_name' => trim($entries[$x]['givenname'][0]), 'last_name' => trim($entries[$x]['sn'][0]));
                // }
            }
            // return $users;
            die();
        }
    }

    public function deleteUser($dn)
    {
        try {
            ldap_delete($this->ldapconn, $dn);
            return true;
        } catch (Exception $e) {
            // echo "message: " . $e->getMessage();
            echo "Tidak berhasil menghapus di ldap!";
            // throw \CodeIgniter\Exceptions\PageNotFoundException::forEmptyController();
        }
    }

    public function createDc($dc, $dn)
    {
        $data = [
            'objectClass' => [
                0 => "dcObject",
                1 => "organization",
            ],
            'dc' => $dc,
            'o' => $dc

        ];

        try {
            ldap_add($this->ldapconn, $dn, $data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function createOu($ou, $dn)
    {
        // phpinfo();
        $data = [
            'objectClass' => [
                0 => "organizationalUnit",
            ],
            'ou' => $ou,
        ];

        try {
            return ldap_add($this->ldapconn, $dn, $data);
            // return true;
        } catch (Exception $e) {
            // return false;
            return $e;
        }
    }

    public function getOu($ou, $base_dn)
    {
        $filter = "(ou=" . $ou . ")";
        try {
            $search = ldap_search($this->ldapconn, $base_dn, $filter);
            $entry = ldap_get_entries($this->ldapconn, $search);
            return $entry;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteOu($dn)
    {
        try {
            return  ldap_delete($this->ldapconn, $dn);
        } catch (Exception $e) {
            return false;
            // echo "message: " . $e->getMessage();
        }
    }

    public function updateOu($dn, $new_rdn, $new_parrent, $delete_old_rd = false)
    {
        $query = ldap_rename($this->ldapconn, $dn, $new_rdn, $new_parrent, $delete_old_rd);
        if ($query) {
            return true;
        } else {
            return ldap_error($this->ldapconn);
        }
    }

    public function createGroup($group_name, $dn, $members)
    {
        $data['cn'] = "$group_name";
        // $data['objectClass'][0] = "top";
        $data['objectClass'][0] = "groupOfNames";
        $data['member'] = $members;
        // $data["sAMAccountName"] = $group_name;

        try {
            ldap_add($this->ldapconn, $dn, $data);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function updateGroup($group_old_dn, $group_new_dn, $parent)
    {
        try {
            return ldap_rename($this->ldapconn, $group_old_dn, $group_new_dn, $parent, TRUE);
        } catch (Exception $e) {
            return $e->getMessage();
            // return false;
        }
    }

    public function getGroup($g_name, $base_dn)
    {
        $filter = "(cn=" . $g_name . ")";
        try {
            $search = ldap_search($this->ldapconn, $base_dn, $filter);
            $entry = ldap_get_entries($this->ldapconn, $search);
            return $entry;
        } catch (Exception $e) {
            return false;
        }
    }

    public function addUserToGroup($group_dn, $member)
    {
        $data['member'] = $member;
        try {
            return ldap_mod_add($this->ldapconn, $group_dn, $data);
        } catch (Exception $e) {
            // die($e->getMessage());
            return $e->getMessage();
            // return false;
        }
    }

    public function delUserFromGroup($group_dn, $member)
    {
        $data['member'] = $member;

        try {
            return ldap_mod_del($this->ldapconn, $group_dn, $data);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function deleteGroup($dn)
    {
        try {
            return  ldap_delete($this->ldapconn, $dn);
        } catch (Exception $e) {
            return false;
            // echo "message: " . $e->getMessage();
            // echo "Tidak berhasil menghapus di ldap!";
        }
    }
}
