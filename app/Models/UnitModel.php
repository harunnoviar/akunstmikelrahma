<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;
use Exception;

class UnitModel extends Model
{
   // protected $DBGroup          = 'default';
   protected $table            = 'unit';
   // protected $primaryKey       = 'id';
   protected $allowedFields    = ['unit_id', 'unit_name', 'description'];


   protected $db, $builder, $request;

   public function __construct()
   {
      $this->db = \Config\Database::connect();
      $this->request = Services::request();
   }

   public function getUnit()
   {
      $query = $this->db->table('unit')
         ->select('*')
         ->orderBy('unit_id')
         ->get()->getResultObject();
      return $query;
   }

   public function getUnitAjax()
   {
      $query = $this->db->table('unit');
      return $query;
   }

   public function getUnitById($id)
   {
      $query = $this->db->table('unit')
         ->select('unit_id,unit_name,description')
         ->where('unit_id', $id)->get()->getRowArray();
      return $query;
   }
   public function updateUnit($id, $data)
   {
      $builder = $this->db->table('unit');
      $builder->where('unit_id', $id);
      return $builder->update($data);
   }

   public function getUnitByName($name)
   {
      $query = $this->db->table('unit')
         ->select('unit_id,unit_name,description')
         ->where('unit_name', $name)->get()->getRowArray();
      return $query;
   }

   public function insertUnit($data)
   {
      $builder = $this->db->table('unit');
      return $builder->insert($data);
   }

   public function deleteUnit($id)
   {
      $builder = $this->db->table('unit')
         ->where('unit_id', $id)
         ->delete();
      return $this->db->affectedRows();
   }
}
