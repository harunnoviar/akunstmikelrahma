<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\GlobalModel;
use App\Models\LdapModel;
use App\Models\UnitModel;
use Config\Ldap;

class Unit extends BaseController
{
   private  $globalModel, $ldapConf, $ldapModel, $unitModel;
   /**
    * Constructor.
    */
   public function __construct()
   {
      $this->globalModel = new GlobalModel();
      $this->unitModel = new UnitModel();
      $this->ldapConf = new Ldap();
      $this->ldapModel = new LdapModel();
   }

   public function index()
   {
      $data = [
         'title' => 'Satker',
         'content' => 'admin/v_unit',
         'a_menu' => 'unit',
         'validation' => \Config\Services::validation(),
      ];

      return view('layout/v_wrapper', $data);
   }

   public function fetch()
   {
      $draw = $this->request->getPost('draw');
      $start = $this->request->getPost('start');
      $rowperpage = $this->request->getPost('length');
      $columnIndex = $this->request->getPost("order[0][column]");
      $columnName = $this->request->getVar("columns[$columnIndex][data]");
      $columnSortOrder = $this->request->getVar("order[0][dir]");
      $searchValue = $this->request->getVar("search[value]");
      $draw = $this->request->getPost('draw');
      $response = [];

      $builder = $this->unitModel->getUnitAjax();
      $builder->select('unit_id,unit_name,description');
      // $builder->select('*');
      $totalRecords = $builder->countAllResults(false);

      ## Total number of record with filtering
      if ($searchValue) {
         $builder->like('unit_name', $searchValue, 'both', null, true);
         $builder->orLike('description', $searchValue, 'both', null, true);
      }
      $totalRecordsWithFilter = $builder->countAllResults(false);

      ## Fetch records
      $builder->orderBy($columnName, $columnSortOrder);
      $builder->limit($rowperpage, $start);
      $records = $builder->get()->getResult();
      // var_dump($records);
      // die();
      $data = [];

      foreach ($records as $r) {

         $btn_del = '<button class="btn btn-xs btn-danger delete" title="Hapus" onclick="unit_del(`' . $r->unit_id . '`,`' . $r->unit_name . '`)" ><i class="fas fa-trash"></i></button>';

         $btn_detail = '<a href="grpdet/' . $r->unit_id . '" class="btn btn-xs btn-info" title="Detil" ></a>';

         $action = '<div class="text-center" > <button type="button" name="edit" class="edit btn btn-xs btn-warning" data-id="' . enkrip($r->unit_id) . '" title="Ubah"><i class=" fa fa-edit"></i></button> ' . $btn_del . ' </div>';

         $data[] = [
            'unit_id' => $r->unit_id,
            'unit_name' => $r->unit_name,
            'description' => $r->description,
            'action' => $action,
         ];
      }

      $response = [
         'draw' => intval($draw),
         'iTotalRecords' => $totalRecords,
         'iTotalDisplayRecords' => $totalRecordsWithFilter,
         'aaData' => $data,
      ];
      echo  json_encode($response);
   }

   public function add()
   {
      $data = [
         'unit_name' => $this->request->getPost('unit_name'),
         'description' => $this->request->getPost('unit_desc'),
      ];

      $cek_name = $this->unitModel->getUnitByName($data['unit_name']);

      // jika nama sudah ada kirim error
      if ($cek_name) {
         $message = '<div class="alert alert-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error!!</br>Nama Unit: <i>' . $data['unit_name'] . '</i> sudah ada</div>';
         echo json_encode(['message' => $message]);
      } else {
         $query = $this->unitModel->insertUnit($data);
         if ($query) {
            // catat log
            $this->globalModel->insertLog(session()->get('email'), 'Tambah unit  => ' . $data['unit_name']);
            $message = '<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses tambah unit </div>';
            echo json_encode(['message' => $message]);
         } else {
            $message = '<div class="alert alert-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error tambah unit </div>';
            echo json_encode(['message' => $message]);
         }
      }
   }

   public function delete()
   {
      $unit_id = $this->request->getPost('id');
      // $unit_id = 1;
      // $cek_unit_with_user = $this->unitModel->getUnitWithUser($unit_id);
      // if ($cek_unit_with_user) {
      //    return json_encode(['error' => 'unit  <strong>' . $unit_id . '</strong> masih terpakai']);
      // }

      $query = $this->unitModel->deleteUnit($unit_id);
      if ($query) {
         // Catat log ke database
         $this->globalModel->insertLog(session()->get('email'), 'Hapus unit ' . $unit_id);
         return json_encode(['msg' => 'Sukses Hapus: <strong>' . $unit_id . '</strong>']);
      } else {
         return json_encode(['error' => 'Gagal!']);
      }
   }

   public function editfetch()
   {
      $id = dekrip($this->request->getPost('id'));
      $unit = $this->unitModel->getUnitById($id);
      $unit_id_enc = enkrip($unit['unit_id']);
      $data = array_merge($unit, ['unit_id' => $unit_id_enc]);
      echo json_encode($data);
   }

   public function editaction()
   {
      // var_dump($this->request->getPost());
      // die();
      $id = dekrip($this->request->getPost('hidden_id'));
      if ($id) {
         $rules = [
            'unit_name' => [
               'rules' => 'required',
               'errors' => [
                  'required' => 'Nama Unit harus diisi',
               ],
            ],
         ];

         if (!($this->validate($rules))) {
            $validation = \Config\Services::validation();
            echo json_encode(['error' => $validation->getErrors()]);
         } else {
            $data = [
               'unit_name' => $this->request->getPost('unit_name'),
               'description' => $this->request->getPost('unit_desc'),
            ];

            // var_dump($data);
            // die();
            $queryDb = $this->unitModel->updateUnit($id, $data);
            // catat log
            $this->globalModel->insertLog(session()->get('email'), 'Update unit  => ' . $data['unit_name']);
            $message = '<div class="alert alert-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses update ' . $id . '</div>';
            echo json_encode(['message' => $message]);
         }
      } else {
         echo json_encode(['message' => '<div class="alert alert-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error</div>']);
      }
   }
}
