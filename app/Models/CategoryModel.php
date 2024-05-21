<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class CategoryModel extends Model
{
    protected $table            = 'user_category';
    // protected $primaryKey       = 'id';
    protected $allowedFields    = ['id', 'domain_id', 'name', 'description',  'created_at',  'updated_at'];


    protected $db, $builder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        // $this->request = Services::request();
    }


    public function getCategory($domain = null)
    {
        if ($domain) {
            $query = $this->db->table('user_domain d')
                ->select('d.id d_id,d.name d_name, d.description d_des,c.id c_id, c.name c_name, c.description c_des, c.created_at,updated_at')
                ->join('user_category c', 'c.domain_id=d.id', 'left')
                ->where('d.name', $domain);
        } else {
            $query = $this->db->table('user_domain d')
                ->select('d.id d_id,d.name d_name, d.description d_des,c.id c_id, c.name c_name, c.description c_des, c.created_at,updated_at')
                ->join('user_category c', 'c.domain_id=d.id', 'left');
        }
        return $query;
    }

    public function getCategoryAll()
    {
        return $this->db->table('user_category a');
    }

    public function getCatByDomId($domain_id)
    {
        return $this->db->table('user_category a')
            ->select('a.id ctg_id,a.name ctg_name,a.description ctg_des,a.protected ctg_protected,a.base_dn,c.id dom_id,c.description dom_des,c.protected dom_protected')
            ->join('cat_with_dom b', 'b.c_id=a.id', 'left')
            ->join('user_domain c', 'c.id=b.d_id', 'left')
            ->where('b.d_id', $domain_id)
            ->get()->getFirstRow();
    }


    public function getCategoryWithDomain($domain = null)
    {

        $query = $this->db->table('user_domain a')
            // ->select('d.id d_id,d.name d_name, d.description d_des,c.id c_id, c.name c_name, c.description c_des, c.created_at,updated_at')
            ->select('*')
            // ->select('a.id c_id,a.name c_name, a.description c_des,a.protected c_protected,a.base_dn,c.id d_id,c.name d_name,c.description d_des,c.protected d_protected')
            // ->join('cat_with_dom b', 'b.c_id=a.id', 'left');
            ->join('cat_with_dom b', 'b.d_id=a.id', 'left')
            // ->join('user_domain c', 'c.id=b.d_id', 'left');
            ->join('user_category c', 'c.id=b.c_id', 'left');
        if ($domain) {
            $query->where('d.name', $domain);
        }
        return $query;
    }



    public function getCategoryAjax($domain = null)
    {
        $query = $this->db->table('user_category a');
        return $query;
    }

    public function getCatByName($name)
    {
        $query = $this->db->table('user_category')
            ->where('name', $name)->get()->getFirstRow();
        return $query;
    }

    public function getCtgById($id)
    {
        $query = $this->db->table('user_category c')
            ->select('*')
            ->where('id', $id)->get()->getFirstRow();
        return $query;
    }

    public function insertCategory($data)
    {
        $builder = $this->db->table('user_category');
        $builder->insert($data);
        return $this->db->affectedRows();
    }

    public function updateCategory($id, $data)
    {
        $builder = $this->db->table('user_category');
        $builder->where('id', $id);
        $builder->update($data);
        return $this->db->affectedRows();
    }

    public function deleteCategory($id)
    {
        $query = $this->db->table('user_category')
            ->where('id', $id)
            ->delete();
        return $this->db->affectedRows();
    }
}
