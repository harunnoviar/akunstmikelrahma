<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Uuid;
use App\Models\CategoryModel;
use App\Models\GlobalModel;
use App\Models\LdapModel;
use App\Models\UserAppModel;
use App\Models\UserModel;
use Config\Ldap;

class Category extends BaseController
{
    private $userAppModel, $globalModel, $categoryModel, $ldapConf, $userModel, $ldapModel, $uuid;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->userAppModel = new UserAppModel();
        $this->globalModel = new GlobalModel();
        $this->categoryModel = new CategoryModel();
        $this->uuid = new Uuid();
        $this->ldapConf = new Ldap();
        $this->userModel = new UserModel();
        $this->ldapModel = new LdapModel();
    }



    public function index()
    {

        $data = [
            'title' => 'Kategori (OU)',
            'content' => 'admin/v_category',
            'a_menu' => 'ctg',
        ];

        // d($data);
        return view('layout/v_wrapper', $data);
    }

    public function categoryfetch()
    {

        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $rowperpage = $this->request->getPost('length');
        $columnIndex = $this->request->getPost("order[0][column]");
        $columnName = $this->request->getPost("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getPost("order[0][dir]");
        $searchValue = $this->request->getPost("search[value]");
        $draw = $this->request->getPost('draw');
        $response = [];

        $builder = $this->categoryModel->getCategoryAjax();
        $builder->select('*');
        // $builder->select('c.id, c.name, c.description, c.base_dn, c.base_group_dn, c.created_at, c.updated_at, c.protected')
        //     ->groupBy('c.id, c.name, c.description, c.base_dn, c.base_group_dn, c.created_at, c.updated_at, c.protected');
        $totalRecords = $builder->countAllResults(false);


        ## Total number of record with filtering
        if ($searchValue) {
            $builder->like('a.name', $searchValue, 'both', null, true);
            $builder->orLike('a.description', $searchValue, 'both', null, true);
        }
        $totalRecordsWithFilter = $builder->countAllResults(false);

        ## Fetch records
        $builder->orderBy($columnName, $columnSortOrder);
        $builder->limit($rowperpage, $start);
        $records = $builder->get()->getResult();
        // var_dump($records);
        // exit();
        $data = [];
        $nomer = 1;

        foreach ($records as $r) {
            $get_user_in_category = $this->userModel->getUsers()
                ->select('a.id')
                ->where('a.ou', $r->id)
                ->countAllResults(true);

            ($r->protected === 't' || $get_user_in_category) ? $btn_del = '' : $btn_del = '<button class="btn btn-xs btn-danger delete" title="Hapus" onclick="ctg_del(' . $r->id . ')" ><i class="fas fa-trash"></i></button>';
            ($get_user_in_category) ? $btn_detail = '<a href="ctgdet/' . $r->id . '" class="btn btn-xs btn-info" title="Detil" ><i class="fas fa-users"></i> <span>' . $get_user_in_category . '</span></a>' : $btn_detail = '';
            $action = '<div class="text-center" > <button type="button" name="edit" class="edit btn btn-xs btn-warning" data-id="' . $r->id . '" title="Ubah"><i class=" fa fa-edit"></i></button> ' . $btn_del . $btn_detail . ' </div>';

            $data[] = [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'base_dn' => $r->base_dn,
                'created_at' => $r->created_at,
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

    public function categorydelete()
    {
        $id = $this->request->getPost('id');
        $ctgById = $this->categoryModel->getCtgById($id);
        $c_name = $ctgById->name;
        $base_dn = $ctgById->base_dn;
        $dn = 'ou=' . $c_name . ',' . $base_dn;


        $query_ldap = $this->ldapModel->deleteOu($dn);
        if ($query_ldap) {
            $query_db = $this->categoryModel->deleteCategory($id);
            if ($query_db) {
                // Catat log ke database
                $this->globalModel->insertLog(session()->get('email'), 'Hapus kategori ' . $c_name);
                return json_encode(['msg' => 'Sukses Hapus kategori <strong>' . $c_name . '</strong>']);
            } else {
                return json_encode(['error' => 'Gagal hapus kategori <strong>' . $c_name . '</strong> - Database error!']);
            }
        } else {
            // Catat log ke database
            $this->globalModel->insertLog(session()->get('email'), 'Hapus kategori ' . $c_name);
            return json_encode(['error' => 'Gagal hapus kategori <strong>' . $c_name . '</strong> - Ldap error!']);
        }
    }

    public function categorycreate()
    {
        // var_dump($this->request->getPost());
        $data = [
            'name' => $this->request->getPost('name'),
            'base_dn' => $this->request->getPost('base_dn'),
            'description' => $this->request->getPost('description'),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $ou = $data['name'];
        $dn = 'ou=' . $ou . ',' . $data['base_dn'];

        $cek_name_in_db = $this->categoryModel->getCatByName($data['name']);
        $cek_name_in_ldap = $this->ldapModel->getOu($ou, $data['base_dn']);
        if ($cek_name_in_db) {  // jika nama sudah ada di database kirim error
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error!!</br>Nama OU: <i>' . $ou . '</i> sudah ada di database</div>';
            return  json_encode(['message' => $message]);
            exit;
        }

        if (!$cek_name_in_ldap) {
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error Ldap!</div>';
            return  json_encode(['message' => $message]);
            exit;
        }
        if (isset($cek_name_in_ldap) && $cek_name_in_ldap['count'] === 1) {  // jika nama sudah ada di ldap kirim error
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error!!</br>Nama OU: <i>' . $ou . '</i> sudah ada di ldap</div>';
            return  json_encode(['message' => $message]);
            exit;
        }

        $query_ldap = $this->ldapModel->createOu($ou, "ou=" . $ou . "," . $data['base_dn']); // proses tambah di ldap
        if ($query_ldap) {  // jika query ldap sukses
            $query_db = $this->categoryModel->insertCategory($data); // proses tambah kategori di database
            if ($query_db) { // jika query db sukses
                $message = '<div class="alert alert-default-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses tambah kategori ' . $ou . ' </div>';
                echo json_encode(['message' => $message]);
            } else {
                $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error tambah kategori </div>';
                echo json_encode(['message' => $message]);
            }
        } else {
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error tambah kategori di ldap </div>';
            echo json_encode(['message' => $message]);
        }
    }

    public function editfetch()
    {
        $id = $this->request->getPost('id');
        $category = $this->categoryModel->getCtgById($id);
        // var_dump($category);
        // die();
        echo json_encode($category);
    }

    public function editaction()
    {
        $id = $this->request->getPost('hidden_id');

        if ($id) {
            $ctgById = $this->categoryModel->getCtgById($id);
            $c_name = $ctgById->name;
            $data = [
                'name' => $this->request->getPost('name'),
                'base_dn' => $this->request->getPost('base_dn'),
                'description' => $this->request->getPost('description'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            // var_dump($data);
            // dd($data);

            $this->categoryModel->updateCategory($id, $data);
            // catat log
            $this->globalModel->insertLog(session()->get('email'), 'Update kategori ' . $c_name);
            $message = '<div class="alert alert-default-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses update ' . $c_name . '</div>';
            echo json_encode(['message' => $message]);
        } else {
            echo json_encode(['message' => '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error</div>']);
        }
    }

    public function detail($id)
    {
        if (empty($id)) return redirect()->back();

        $getOu = $this->categoryModel->getCategoryAll()
            ->select('*')
            ->where('a.id', $id)
            ->get()->getFirstRow();
        if (empty($getOu)) return redirect()->back();

        $data = [
            'title' => 'Daftar Akun di Group',
            'content' => 'admin/v_categorydetail',
            'a_menu' => 'ctg',
            'c_id' => $id,
            'c_name' => $getOu->name,
        ];

        return view('layout/v_wrapper', $data);
    }

    public function detailfetch($id)
    {
        $c_id = dekrip($id);
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $rowperpage = $this->request->getPost('length');
        $columnIndex = $this->request->getPost("order[0][column]");
        $columnName = $this->request->getPost("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getPost("order[0][dir]");
        // $searchValue = '';
        $searchValue = $this->request->getPost("search[value]");
        $draw = $this->request->getPost('draw');
        $response = [];

        $builder = $this->userModel->getUsers()
            // ->join('cat_with_dom b', 'b.d_id=a.domain', 'left')
            ->join('user_category b', 'b.id=a.ou', 'left')
            ->where('b.id', $c_id);
        $builder->select('a.id user_id,a.email,a.dispname,a.active,a.created_at,a.updated_at,a.created_by,a.updated_by,a.info');
        // $builder->select('*');
        // Total number of records
        $totalRecords = $builder->countAllResults(false);

        ## Total number of record with filtering
        if ($searchValue) {
            $builder->like('a.email', $searchValue, 'both', null, true);
            $builder->orLike('a.dispname', $searchValue, 'both', null, true);
        }
        $totalRecordsWithFilter = $builder->countAllResults(false);

        ## Fetch records
        $builder->orderBy($columnName, $columnSortOrder);
        $builder->limit($rowperpage, $start);
        $records = $builder->get()->getResult();
        // var_dump($records);
        // exit();
        $data = [];

        foreach ($records as $r) {
            $action = "<a type=\"button\" class=\"badge badge-info btn-sm \" href=\"/admin/user_edit/" . $r->user_id . "\"><i class=\"fa fa-edit\"></i> </a> <a type=\"button\" class=\"badge badge-danger btn-sm btn-delete\"  onclick=\"hapus('$r->email','$r->user_id')\"  ><i class=\"fa fa-trash-alt\"></i> </a>";

            $data[] = [
                'user_id' => $r->user_id,
                'action' => $action,
                'email' => $r->email,
                'dispname' => $r->dispname,
                'created_at' => $r->created_at,
                'created_by' => $r->created_by,
                'updated_at' => $r->updated_at,
                'updated_by' => $r->updated_by,
                'info' => $r->info,
                'active' => $r->active,
            ];
        }
        // Response
        $response = [
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalRecordsWithFilter,
            'aaData' => $data,
        ];
        return json_encode($response);
        // var_dump($response);
    }
}
