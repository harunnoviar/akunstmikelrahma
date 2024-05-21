<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class DomainModel extends Model
{
    protected $table            = 'user_domain';
    // protected $primaryKey       = 'id';
    protected $allowedFields    = ['id', 'name', 'description',  'created_at',  'updated_at'];


    protected $db, $builder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        // $this->request = Services::request();
    }


    public function getDomain($domain = null)
    {
        if ($domain) {
            $query = $this->db->table('user_domain d')
                ->select('*')
                ->where('d.name', $domain);
        } else {
            $query = $this->db->table('user_domain d')
                ->select('*');
        }
        return $query;
    }

    public function getDomainJoin()
    {
        $query = $this->db->table('user_domain a')
            // ->select('*')
            ->select('a.id d_id,a.name dom_name,a.description dom_des,a.protected dom_protected,b.c_id ctg_id,c.name ctg_name,c.base_dn')
            ->join('cat_with_dom b', 'b.d_id=a.id')
            ->join('user_category c', 'c.id=b.c_id')
            ->orderBy('a.id');

        return $query;
    }

    public function getDomainAjax()
    {
        $query = $this->db->table('user_domain a');
        return $query;
    }

    public function getDomByName($name)
    {
        $query = $this->db->table('user_domain')
            ->where('name', $name)->get()->getFirstRow();
        return $query;
    }

    public function getDomById($id)
    {
        $query = $this->db->table('user_domain d')
            ->select('*')
            // ->select('c.id c_id,d.id d_id,d.name d_name, c.name c_name, c.description c_des,c.created_at,c.updated_at')
            // ->join('user_domain d', 'd.id=c.domain_id', 'left')
            ->where('id', $id)->get()->getRowArray();
        return $query;
    }

    public function insertDomain($data)
    {
        $builder = $this->db->table('user_domain');
        $builder->insert($data);
        return $this->db->affectedRows();
    }

    public function updateDomain($id, $data)
    {
        $builder = $this->db->table('user_domain');
        $builder->where('id', $id);
        $builder->update($data);
        return $this->db->affectedRows();
    }

    public function deleteDomain($id)
    {
        $query = $this->db->table('user_domain')
            ->where('id', $id)
            ->delete();
        return $this->db->affectedRows();
    }
}
