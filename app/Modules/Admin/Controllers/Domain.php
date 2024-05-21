<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\DomainModel;
use App\Models\GlobalModel;
use App\Models\UserModel;

class Domain extends BaseController
{
    private $globalModel, $domainModel, $userModel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->globalModel = new GlobalModel();
        $this->domainModel = new DomainModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {

        $data = [
            'title' => 'Domain Email',
            'content' => 'admin/v_domain',
            'a_menu' => 'dom',
        ];

        return view('layout/v_wrapper', $data);
    }

    public function domainfetch()
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

        $builder = $this->domainModel->getDomainAjax();
        $builder->select('*')->orderBy('id');
        ## Total number of record without filtering
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
            $get_user_in_domain = $this->userModel->getUsers()
                ->select('a.id')
                ->join('user_domain b', 'b.id=a.domain', 'left')
                ->where('b.id', $r->id)
                ->countAllResults(true);

            ($r->protected === 't' || $get_user_in_domain) ? $btn_del = '' : $btn_del = '<button class="btn btn-xs btn-danger delete" title="Hapus" onclick="ctg_del(' . $r->id . ')" ><i class="fas fa-trash"></i></button>';
            ($get_user_in_domain) ? $btn_detail = '<a href="domdet/' . $r->id . '" class="btn btn-xs btn-info" title="Detil" ><i class="fas fa-users"></i> <span>' . $get_user_in_domain . '</span></a>' : $btn_detail = '';

            $action = '<div class="text-center" > <button type="button" name="edit" class="edit btn btn-xs btn-warning" data-id="' . $r->id . '" title="Ubah"><i class=" fa fa-edit"></i></button> ' . $btn_del . $btn_detail . ' </div>';

            $data[] = [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
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

    public function domaindelete()
    {
        $id = $this->request->getPost('id');
        $d_name = $this->domainModel->getDomById($id)['name'];
        $get_user_in_domain = $this->userModel->getUsers()
            ->join('user_domain b', 'b.id=a.domain', 'left')
            ->where('a.domain', $id)
            ->get()->getResult();

        if ($get_user_in_domain) {
            return json_encode(['error' => 'Domain  <strong>' . $d_name . '</strong> masih terpakai']);
        }
        $query = $this->domainModel->deleteDomain($id);
        if ($query) {
            // Catat log ke database
            $this->globalModel->insertLog(session()->get('email'), 'Hapus Domain ' . $d_name);
            return json_encode(['msg' => 'Sukses Hapus: <strong>' . $d_name . '</strong>']);
        } else {
            return json_encode(['error' => 'Gagal!']);
        }
    }

    public function domaincreate()
    {
        // var_dump($this->request->getPost());
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $cek_name = $this->domainModel->getDomByName($data['name']);

        // jika nama sudah ada kirim error
        if ($cek_name) {
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error!!</br>Nama Kategori: <i>' . $data['name'] . '</i> sudah ada</div>';
            echo json_encode(['message' => $message]);
        } else {
            $query = $this->domainModel->insertDomain($data);
            if ($query) {
                $this->globalModel->insertLog(session()->get('email'), 'Sukses Tambah Domain ' . $data['name']);
                $message = '<div class="alert alert-default-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses tambah domain ' . $data['name'] . '</div>';
                echo json_encode(['message' => $message]);
            } else {
                $this->globalModel->insertLog(session()->get('email'), 'Gagal Tambah Domain ' . $data['name']);
                $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error tambah domain ' . $data['name'] . '</div>';
                echo json_encode(['message' => $message]);
            }
        }
    }

    public function editfetch()
    {
        $id = $this->request->getPost('id');
        $domain = $this->domainModel->getDomById($id);
        echo json_encode($domain);
    }

    public function editaction()
    {
        $id = $this->request->getPost('hidden_id');
        if ($id) {
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->domainModel->updateDomain($id, $data);
            // catat log
            $this->globalModel->insertLog(session()->get('email'), 'Update kategori ' . $id);
            $message = '<div class="alert alert-default-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses update ' . $id . '</div>';
            echo json_encode(['message' => $message]);
        } else {
            echo json_encode(['message' => '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error</div>']);
        }
    }

    public function detail($id)
    {
        if (empty($id)) return redirect()->back();
        $get_domain = $this->domainModel->getDomById($id);

        if (!$get_domain) return redirect()->back();

        $data = [
            'title' => 'Daftar Akun di Domain ',
            'content' => 'admin/v_domaindetail',
            'a_menu' => 'dom',
            'd_name' => $get_domain['name'],
            'd_id' => $id,
        ];
        return view('layout/v_wrapper', $data);
    }

    public function detailfetch($id)
    {
        $d_id = dekrip($id);
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $rowperpage = $this->request->getPost('length');
        $columnIndex = $this->request->getPost("order[0][column]");
        $columnName = $this->request->getPost("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getPost("order[0][dir]");
        // var_dump($columnName, $columnSortOrder);
        // exit;
        // $searchValue = '';
        $searchValue = $this->request->getPost("search[value]");
        $draw = $this->request->getPost('draw');
        $response = [];

        $builder = $this->userModel->getUsers()
            ->join('user_domain b', 'b.id=a.domain', 'left')
            ->where('b.id', $d_id);
        $builder->select('a.id user_id,a.email,a.dispname,a.active,a.created_at,a.updated_at,a.created_by,a.updated_by,a.info');
        // ->orderBy('a.id');
        ## Total number of record without filtering
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

        $response = [
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalRecordsWithFilter,
            'aaData' => $data,
        ];
        echo  json_encode($response);
    }
}
