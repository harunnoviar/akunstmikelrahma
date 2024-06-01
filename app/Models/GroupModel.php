<?php

namespace App\Models;

use App\Libraries\Uuid;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Model;
use Config\Services;
use Exception;

class GroupModel extends Model
{
    // protected $DBGroup          = 'default';
    protected $table            = 'group';
    // protected $primaryKey       = 'id';
    protected $allowedFields    = ['g_id', 'g_name', 'base_group_dn', 'g_desc', 'protected', 'created_at', 'updated_at'];


    protected $db, $builder, $request;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->request = Services::request();
    }

    public function getGroups()
    {
        return $this->db->table('group a');
    }

    public function getGroup()
    {
        $query = $this->db->table('group g')
            ->select('*')
            ->orderBy('g_id')
            ->get()->getResultObject();
        return $query;
    }

    public function getGroupFilter($domain = null, $protect = 'f', $opsi = 'f')
    {
        $query = $this->db->table('group g')
            ->select('*')
            ->join('user_domain', 'user_domain.id=g.domain_id', 'left')
            ->orderBy('g_id');
        if ($domain && $protect === 't' && $opsi === 't') {
            $query->where(['user_domain.name' => $domain, 'g.protect' => 't', 'g.opsi' => 't']);
        }
        if ($domain && $protect === 't') {
            $query->where(['user_domain.name' => $domain, 'g.protect' => 't']);
        }
        if ($domain) {
            $query->where(['user_domain.name' => $domain]);
        }

        return $query->get()->getResult();
    }

    public function getGroupAjax()
    {
        $query = $this->db->table('group g');
        return $query;
    }

    public function getGroupWithUser($group_id, $user_id = '')
    {
        $builder = $this->db->table('user_group a')
            ->select('*')
            ->join('group b', 'b.g_id=a.group_id')
            ->where('a.group_id', $group_id);

        if ($user_id) $builder->where('a.user_id', $user_id);
        return $builder->get()->getResultArray();
    }

    public function getGrpById($id)
    {
        $query = $this->db->table('group g')
            ->select('*')
            ->where('g.g_id', $id)->get()->getRowArray();
        return $query;
    }

    public function getGrpWithOuById($g_id)
    {
        return $this->db->table('group a')
            ->select('a.g_id,a.g_name,a.g_desc,a.base_group_dn,c.id ou_id,c.name ou_name,c.description ou_desc,c.base_dn')
            // ->select('*')
            ->join('group_with_cat b', 'b.g_id=a.g_id', 'left')
            ->join('user_category c', 'c.id=b.c_id')
            ->where('a.g_id', $g_id)
            ->get()->getFirstRow();
    }

    public function getGrpByIdUser($user_id)
    {
        $query = $this->db->table('group a')
            // ->select('*')
            ->select('a.g_id,a.g_name,a.base_group_dn,c.id,c.email,c.domain')
            ->join('user_group b', 'b.group_id=a.g_id', 'left')
            ->join('user c', 'c.id=b.user_id')
            ->where('c.id', $user_id)
            ->get()->getResult();
        return $query;
    }

    public function getGrpByName($name)
    {
        $query = $this->db->table('group g')
            ->select('*')
            ->where('g.g_name', $name)->get()->getRowArray();
        return $query;
    }

    public function getGrpDetByDomain($domain)
    {
        return $this->db->table('group a')
            ->select('*')
            ->join('group_with_cat b', 'b.g_id=a.g_id', 'left')
            ->join('user_category c', 'c.id=b.c_id', 'left')
            ->join('cat_with_dom d', 'd.c_id=c.id', 'left')
            ->join('user_domain e', 'e.id=d.d_id')
            ->where('e.name', $domain)
            ->get()->getResultArray();
    }

    public function getGroupByOu($ou)
    {
        return $this->db->table('group a')
            ->select('*')
            ->join('group_with_cat b', 'b.g_id=a.g_id', 'right')
            ->join('user_category c', 'c.id=b.c_id', 'left')
            ->where('c.name', $ou)
            ->orderBy('a.g_id')
            ->get()
            ->getResult();
    }

    public function getGroupByOuId($ou_id)
    {
        return $this->db->table('group a')
            ->select('*')
            ->join('group_with_cat b', 'b.g_id=a.g_id', 'right')
            ->join('user_category c', 'c.id=b.c_id', 'left')
            ->where('c.id', $ou_id)
            ->orderBy('a.g_id')
            ->get()
            ->getResult();
    }

    public function insertGroup($data)
    {
        $builder = $this->db->table('group');
        return $builder->insert($data);
    }

    public function insertGroupToCategory($data)
    {
        $builder = $this->db->table('group_with_cat');
        return $builder->insert($data);
    }

    public function updateGroup($id, $data)
    {
        $builder = $this->db->table('group');
        $builder->where('g_id', $id);
        return $builder->update($data);
    }

    public function insertUserToGroup($data)
    {
        try {
            $this->db->table('user_group')
                ->insert($data);
            return $this->db->affectedRows();
        } catch (Exception $e) {
            // die($e->getMessage());
            // return false;
            return $e->getMessage();
        }
    }

    public function deleteAllGroupInUser($user_id)
    {
        $builder = $this->db->table('user_group')
            ->where('user_id', $user_id)
            ->delete();
        return $this->db->affectedRows();
    }

    public function deleteGroup($id)
    {
        $query = $this->db->table('group')
            ->where('g_id', $id)
            ->delete();
        return $this->db->affectedRows();
    }
}
