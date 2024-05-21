<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // protected $DBGroup          = 'default';
    protected $table            = 'user';
    // protected $primaryKey       = 'id';
    protected $allowedFields    = ['email', 'firstname', 'lastname', 'dispname', 'uuid', 'domain',  'active', 'password', 'created_at', 'created_by', 'updated_at', 'updated_by', 'info', 'request_at', 'ip', 'token_reset', 'pass_ldap', 'recoveryemail', 'nip', 'nidn', 'unit', 'forbid', 'ou'];

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getUsers()
    {
        return $this->db->table('user a');
    }

    public function getUser($id = null)
    {
        $query = $this->db->table('user')->select('*');
        if ($id) {
            return $query->where('id', $id)
                ->get()->getRowArray();
        }
        return $query->get()->getResultArray();
    }

    public function getUserDetailById($id)
    {
        return $this->db->table('user a')
            ->select('a.id u_id,a.email u_email,a.firstname,a.lastname,a.dispname,a.domain dom_id,a.active,a.created_at,a.updated_at,a.created_by,a.updated_by,a.recoveryemail,a.nip,a.nidn,a.unit,a.forbid,a.ou,b.name ou_name,b.base_dn,c.name dom_name,e.g_id,e.g_name,e.base_group_dn ')
            ->join('user_category b', 'b.id=a.ou', 'left')
            ->join('user_domain c', 'c.id=a.domain', 'left')
            ->join('user_group d', 'd.user_id=a.id', 'left')
            ->join('group e', 'e.g_id=d.group_id', 'left')
            ->where('a.id', $id)
            ->get()
            ->getFirstRow();
    }

    // public function getUserDetailById($id)
    // {
    //     return $this->db->table('user a')
    //         // ->select('*')
    //         ->select('a.id u_id,a.email u_email,a.firstname,a.lastname,a.dispname,a.domain dom_id,a.active,a.created_at,a.updated_at,a.created_by,a.updated_by,a.recoveryemail,a.nip,a.nidn,a.unit,a.forbid,a.ou,b.name dom_name,c.c_id ctg_id,d.name ctg_name,d.base_dn,f.g_id,f.g_name,f.base_group_dn ')
    //         ->join('user_domain b', 'b.id=a.domain', 'left')
    //         ->join('cat_with_dom c', 'c.d_id=b.id', 'left')
    //         ->join('user_category d', 'd.id=c.c_id', 'left')
    //         ->join('user_group e', 'e.user_id=a.id', 'left')
    //         ->join('group f', 'f.g_id=e.group_id', 'left')
    //         ->where('a.id', $id)
    //         ->get()->getFirstRow();
    // }

    public function getUserDetailByEmail($email)
    {
        return $this->db->table('user a')
            // ->select('*')
            ->select('a.id u_id,a.email u_email,a.firstname,a.lastname,a.dispname,a.domain dom_id,a.active,a.created_at,a.updated_at,a.created_by,a.updated_by,a.recoveryemail,a.nip,a.nidn,a.unit,a.forbid,a.token_reset,a.request_at,a.ou,b.name dom_name,c.name ou_name,c.base_dn')
            ->join('user_domain b', 'b.id=a.domain', 'left')
            ->join('user_category c', 'c.id=a.ou', 'left')
            ->where('a.email', $email)
            ->get()->getFirstRow();
    }
    // public function getUserDetailByEmail($email)
    // {
    //     return $this->db->table('user a')
    //         // ->select('*')
    //         ->select('a.id u_id,a.email u_email,a.firstname,a.lastname,a.dispname,a.domain dom_id,a.active,a.created_at,a.updated_at,a.created_by,a.updated_by,a.recoveryemail,a.nip,a.nidn,a.unit,a.forbid,a.token_reset,a.request_at,a.ou,b.name dom_name,c.c_id ctg_id,d.name ctg_name,d.base_dn,f.g_id,f.g_name,f.base_group_dn ')
    //         ->join('user_domain b', 'b.id=a.domain', 'left')
    //         ->join('cat_with_dom c', 'c.d_id=b.id', 'left')
    //         ->join('user_category d', 'd.id=c.c_id', 'left')
    //         ->join('user_group e', 'e.user_id=a.id', 'left')
    //         ->join('group f', 'f.g_id=e.group_id', 'left')
    //         ->where('a.email', $email)
    //         ->get()->getFirstRow();
    // }

    public function saveUser($data)
    {
        $this->db->table('user')
            ->insert($data);
        return $this->db->affectedRows();
    }

    public function getUserByEmail($email)
    {
        return $this->db->table('user a')->select('*')
            ->where('email', $email)
            ->get()->getFirstRow();
    }

    public function insertDelUser($data)
    {
        $this->db->table('user_deleted')->insert($data);
        $this->db->table('user')
            ->where('id', $data['id'])
            ->delete();
        return $this->db->affectedRows();
    }

    public function requestReset($id, $data)
    {
        $this->db->table('user')
            ->where('id', $id)
            ->update($data);
        return $this->db->affectedRows();
    }

    public function getUsersDeleted()
    {
        return $this->db->table('user_deleted a');
    }

    public function getNextId()
    {
        return $this->db
            ->query('SELECT last_value FROM public.user_id_seq')
            ->getRow();
    }
}
