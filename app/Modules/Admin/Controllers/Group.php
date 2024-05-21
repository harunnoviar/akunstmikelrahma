<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\GlobalModel;
use App\Models\GroupModel;
use App\Models\LdapModel;
use App\Models\UserModel;

class Group extends BaseController
{
    private  $globalModel, $ldapModel, $groupModel, $userModel;
    protected $dummy_user = 'uid=dummyuser,dc=stmikelrahma,dc=ac,dc=id';  //user dummy ke ldap untuk buat group
    // protected $protectGroup = [];
    protected $protectGroup = ['tendik', 'dosen', 'mahasiswa'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->globalModel = new GlobalModel();
        $this->groupModel = new GroupModel();
        $this->ldapModel = new LdapModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Group',
            'content' => 'admin/v_group',
            'a_menu' => 'grp',
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
        $columnName = $this->request->getPost("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getPost("order[0][dir]");
        $searchValue = $this->request->getPost("search[value]");
        $response = [];

        $builder = $this->groupModel->getGroupAjax();
        $builder->select('*');
        $totalRecords = $builder->countAllResults(false);

        ## Total number of record with filtering
        if ($searchValue) {
            $builder->like('g.g_name', $searchValue, 'both', null, true);
            $builder->orLike('g.g_desc', $searchValue, 'both', null, true);
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
            $get_user_in_group = $this->userModel->getUsers()
                ->select('a.id')
                ->join('user_group b', 'b.user_id=a.id', 'left')
                ->join('group c', 'c.g_id=b.group_id', 'left')
                ->where('c.g_id', $r->g_id)
                ->countAllResults(true);

            ($r->protected === 't' || $get_user_in_group) ? $btn_del = '' : $btn_del = '<button class="btn btn-xs btn-danger delete" title="Hapus" onclick="grp_del(`' . $r->g_id . '`,`' . $r->g_name . '`)" ><i class="fas fa-trash"></i></button>';
            ($get_user_in_group) ? $btn_detail = '<a href="grpdet/' . $r->g_id . '" class="btn btn-xs btn-info" title="Detil" ><i class="fas fa-users"></i> <span>' . $get_user_in_group . '</span></a>' : $btn_detail = '';

            $action = '<div class="text-center" > <button type="button" name="edit" class="edit btn btn-xs btn-warning" data-id="' . enkrip($r->g_id) . '" title="Ubah"><i class=" fa fa-edit"></i></button> ' . $btn_del . $btn_detail . ' </div>';

            $data[] = [
                'g_id' => $r->g_id,
                'g_name' => $r->g_name,
                'base_group_dn' => $r->base_group_dn,
                'g_desc' => $r->g_desc,
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

    public function delete()
    {
        $group_id = $this->request->getPost('id');
        // $group_id = 1;
        // $cek_group_with_user = $this->groupModel->getGroupWithUser($group_id);
        // if ($cek_group_with_user) {
        //     return json_encode(['error' => 'Group  <strong>' . $group_id . '</strong> masih terpakai']);
        // }

        // cari dn group berdasarkan id kemudian sesuaikan dengan treegroupnya
        $get_group = $this->groupModel->getGrpById($group_id);
        // var_dump($get_group);
        // exit;
        $group = $get_group['g_name'];
        $base_group_dn = $get_group['base_group_dn'];
        $dn = 'cn=' . $group . ',' . $base_group_dn;


        $query_ldap = $this->ldapModel->deleteGroup($dn);
        if (!$query_ldap) {
            $this->globalModel->insertLog(session()->get('email'), 'LDAP error - hapus group ' . $group);  // catat log
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>LDAP error - hapus group : ' . $group . '</div>';
            echo json_encode(['message' => $message]);
            exit;
        }

        $query_db = $this->groupModel->deleteGroup($group_id);
        if (!$query_db) {
            $this->globalModel->insertLog(session()->get('email'), 'Database error - hapus group ' . $group);  // catat log
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Database error - hapus group : ' . $group . '</div>';
            echo json_encode(['message' => $message]);
            exit;
        }

        // Jika tidak ada masalah, berarti sukses delete.
        $this->globalModel->insertLog(session()->get('email'), 'Sukses - hapus group ' . $group);  // catat log
        $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses - hapus group : ' . $group . '</div>';
        return json_encode(['msg' => 'Sukses Hapus: <strong>' . $group . '</strong>']);
    }

    public function create()
    {
        $data = [
            'g_name' => trim_group_name($this->request->getPost('g_name')), // nama group huruf kecil, spasi diganti -
            'base_group_dn' => $this->request->getPost('base_group_dn'),
            'g_desc' => $this->request->getPost('g_desc'),
        ];

        $dn = 'cn=' . $data['g_name'] . ',' . $data['base_group_dn'];
        $members[] = $this->dummy_user;

        $group_in_db = $this->groupModel->getGrpByName($data['g_name']);
        $group_in_ldap = $this->ldapModel->getGroup($data['g_name'], $data['base_group_dn']);
        // var_dump($group_in_ldap['count'] === 0);
        // die();
        if ($group_in_db) { //jika froup sudah ada di database, kirim error
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Database error - group : ' . $data["g_name"] . ' sudah ada </div>';
            echo json_encode(['message' => $message]);
            exit;
        }
        if (isset($group_in_ldap['count']) && $group_in_ldap['count'] != 0) { // jika group sudah ada di ldap, kirim error
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>LDAP error - group : ' . $data["g_name"] . ' sudah ada </div>';
            echo json_encode(['message' => $message]);
            exit;
        }

        $query_ldap = $this->ldapModel->createGroup($data["g_name"], $dn, $members);
        if (!$query_ldap) { // jika gagal tambah group di ldap
            $this->globalModel->insertLog(session()->get('email'), 'LDAP error - tambah group ' . $data['g_name']);  // catat log
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>LDAP error - tambah group : ' . $data["g_name"] . '</div>';
            echo json_encode(['message' => $message]);
            exit;
        }

        $query_db = $this->groupModel->insertGroup($data);
        if (!$query_db) { //jika gagal tambah group di database
            $this->globalModel->insertLog(session()->get('email'), 'Database error - tambah group ' . $data['g_name']);  // catat log
            $message = '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Database error - tambah group : ' . $data["g_name"] . ' </div>';
            echo json_encode(['message' => $message]);
            exit;
        }

        // jika sukses semua
        // catat log
        $this->globalModel->insertLog(session()->get('email'), 'Sukses - tambah group ' . $data['g_name']);
        $message = '<div class="alert alert-default-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses - tambah group : ' . $data["g_name"] . ' </div>';
        echo json_encode(['message' => $message]);
    }

    public function editfetch()
    {
        $id = dekrip($this->request->getPost('id'));
        $group = $this->groupModel->getGrpById($id);
        $group_id_enc = enkrip($group['g_id']);
        $group = array_merge($group, ['g_id' => $group_id_enc]);
        echo json_encode($group);
    }

    public function editaction()
    {
        // var_dump($this->request->getPost());
        // die();
        $id = dekrip($this->request->getPost('hidden_id'));
        if (!$id) {
            echo json_encode(['message' => '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Error</div>']);
            exit;
        }

        $rules = [
            'g_name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nama Group harus diisi',
                ],
            ],
            'base_group_dn' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Keterangan harus diisi',
                ],
            ],
            'g_desc' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Keterangan harus diisi',
                ],
            ]
        ];

        if (!($this->validate($rules))) {  // jika validasi error kirim pesan
            $validation = \Config\Services::validation();
            echo json_encode(['error' => $validation->getErrors()]);
            exit;
        }

        $data = [
            'g_name' => trim_group_name($this->request->getPost('g_name')),
            'base_group_dn' => $this->request->getPost('base_group_dn'),
            'g_desc' => $this->request->getPost('g_desc'),
        ];

        // Cari detail group lama berdasarkan id
        $get_group = $this->groupModel->getGrpById($id);
        $group_old = $get_group['g_name'];
        // $dn_old = 'cn=' . $group_old . ',' . $get_group['base_group_dn'];
        $dn_old = 'cn=' . $group_old . ',' . $get_group['base_group_dn'];


        $queryldap = $this->ldapModel->updateGroup($dn_old, 'cn=' . $data['g_name'], null);
        if (!$queryldap) { //jika error update di ldap kirim pesan error
            // catat log
            $this->globalModel->insertLog(session()->get('email'), 'LDAP error - gagal rename group ' . $group_old . ' => ' . $data['g_name']);
            echo json_encode(['message' => '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>LDAP error - gagal rename group ' . $group_old . ' => ' . $data['g_name'] . '</div>']);
            exit;
        }

        $query_db = $this->groupModel->updateGroup($id, $data);
        if (!$query_db) {  // jika tidak berhasil update di database kirim error
            // catat log
            $this->globalModel->insertLog(session()->get('email'), 'Database error - gagal rename group ' . $group_old . ' => ' . $data['g_name']);
            echo json_encode(['message' => '<div class="alert alert-default-danger alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Database error - gagal rename group ' . $group_old . ' => ' . $data['g_name'] . '</div>']);
            exit;
        }
        // var_dump($get_group, $queryldap, $query_db);
        // die();

        // catat log
        $this->globalModel->insertLog(session()->get('email'), 'Sukses - update group ' . $group_old . ' => ' . $data['g_name']);
        $message = '<div class="alert alert-default-success alert-dismissible"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Sukses - update group ' . $group_old . ' => ' . $data['g_name'] . '</div>';
        echo json_encode(['message' => $message]);
    }

    public function fecthWithUser()
    {
        $user_id_enc = $this->request->getPost('user_id');
        $user_id = dekrip($user_id_enc);

        $groupByUserId = $this->groupModel->getGrpByIdUser($user_id);
        // var_dump($user_id, $groupByUserId);
        if ($groupByUserId) {
            foreach ($groupByUserId as $k) {
                $value[] = $k->g_id;
            }
            return json_encode($value);
        }
    }

    public function fecthWithOu()
    {
        $ou_id_enc = $this->request->getPost('ou_id');
        $ou_id = dekrip($ou_id_enc);

        $groupByOuId = $this->groupModel->getGroupByOuId($ou_id);
        // var_dump($ou_id, $groupByOuId);
        if ($groupByOuId) {
            foreach ($groupByOuId as $k) {
                $value[] = [
                    'g_id' => $k->g_id,
                    'g_name' => $k->g_name
                ];
            }
            return json_encode($value);
        } else {
            return json_encode(null);
        }
    }

    public function detail($id)
    {
        if (empty($id)) {
            return redirect()->back();
        }
        $group = $this->groupModel->getGrpById($id);
        if (empty($group)) {
            return redirect()->back();
        }

        $data = [
            'title' => 'Daftar Akun di Group',
            'content' => 'admin/v_groupdetail',
            'a_menu' => 'grp',
            'g_id' => $id,
            'g_name' => $this->groupModel->getGrpById($id)['g_name'],
        ];

        return view('layout/v_wrapper', $data);
    }

    public function detailfetch($id)
    {
        $g_id = dekrip($id);
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

        $builder = $this->groupModel->getGroupAjax();
        ## Total number of records without filtering
        $builder->select('count(*) as allcount')
            ->join('user_group ug', 'ug.group_id=g.g_id', 'left')
            ->join('user u', 'ug.user_id=u.id', 'left')
            ->where('g.g_id', $g_id);
        $records = $builder->get()->getResultArray();
        $totalRecords = $records[0]['allcount'];

        ## Total number of record with filtering
        $builder->select('count(*) as allcount')
            ->join('user_group ug', 'ug.group_id=g.g_id', 'left')
            ->join('user u', 'ug.user_id=u.id', 'left')
            ->where('g.g_id', $g_id);
        if ($searchValue) {
            $builder->like('dispname', $searchValue, 'both', null, true)
                ->orLike('email', $searchValue, 'both', null, true);
        }
        $recordsWithFilter = $builder->get()->getResultArray();
        $totalRecordsWithFilter = $recordsWithFilter[0]['allcount'];

        ## Fetch records
        $builder
            // ->select('*')
            ->select('g.g_id g_id,g.g_name g_name,u.id,u.email,u.dispname,u.created_at,u.updated_at,u.created_by,u.updated_by,u.info,u.unit,un.unit_name unit_name')
            ->join('user_group ug', 'ug.group_id=g.g_id', 'left')
            ->join('user u', 'ug.user_id=u.id', 'left')
            ->join('unit un', 'un.unit_id=u.unit', 'left')
            ->where('g.g_id', $g_id);
        if ($searchValue) {
            $builder->like('dispname', $searchValue, 'both', null, true)
                ->orLike('email', $searchValue, 'both', null, true);
        }
        $builder->orderBy($columnName, $columnSortOrder);
        $builder->limit($rowperpage, $start);
        $records = $builder->get()->getResult();
        $data = [];
        foreach ($records as $r) {
            $action = "<a type=\"button\" class=\"badge badge-info btn-sm \" href=\"/admin/user_edit/" . $r->id . "\"><i class=\"fa fa-edit\"></i> </a> <a type=\"button\" class=\"badge badge-danger btn-sm btn-delete\"  onclick=\"hapus('$r->email','$r->id')\"  ><i class=\"fa fa-trash-alt\"></i> </a>";

            $data[] = [
                'id' => $r->id,
                'action' => $action,
                'email' => $r->email,
                'dispname' => $r->dispname,
                'created_at' => $r->created_at,
                'created_by' => $r->created_by,
                'updated_at' => $r->updated_at,
                'updated_by' => $r->updated_by,
                'unit_name' => $r->unit_name,
                'info' => $r->info,
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
