<?php

namespace App\Models;

use App\Libraries\Uuid;
use CodeIgniter\Model;
use Config\Services;

class GlobalModel extends Model
{
    // protected $DBGroup          = 'default';
    protected $table            = 'user';
    // protected $primaryKey       = 'id';
    protected $allowedFields    = ['email', 'firstname', 'lastname', 'dispname', 'uuid', 'role', 'domain',  'active', 'password', 'pass_ldap',  'created_at', 'created_by', 'updated_at', 'updated_by', 'info', 'request_at', 'ip', 'token_reset', 'recoveryemail', 'forbid'];


    protected $db, $builder, $request;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->request = Services::request();
    }

    public function getNextId()
    {
        return $this->db->query('SELECT last_value FROM public.user_id_seq')
            ->getRow();
    }

    public function getUserAllUse()
    {
        return $this->db->table('user')->select('*');
    }
    public function tableUser()
    {
        return $this->db->table('user');
    }

    public function getUserDetailById($id = null)
    {
        $builder = $this->db->table('user u')
            // ->select('*')
            ->select('u.id u_id,u.email u_email,u.firstname,u.lastname,u.dispname,u.role,u.category,u.active,u.created_at,u.updated_at,u.created_by,u.updated_by,u.recoveryemail,u.nip,u.nidn,u.unit,u.forbid,c.id c_id,c.name c_name,c.description c_desc,g.g_id,g.g_name,g.g_desc')
            ->join('user_category c', 'c.id=u.category', 'left')
            ->join('user_group ug', 'ug.user_id=u.id', 'left')
            ->join('group g', 'g.g_id=ug.group_id', 'left')
            ->where('u.id', $id);
        return $builder->get()->getFirstRow();
    }


    public function saveUser($data)
    {
        $builder = $this->db->table('user');
        return $builder->insert($data);
    }

    public function updateUser($email, $data)
    {
        $builder = $this->db->table('user');
        $builder->where('email', $email);
        return $builder->update($data);
    }

    public function getUnitKerja($id = null)
    {
        $builder = $this->db->table('unit')
            ->select('*');
        if ($id) {
            $builder->where('unit_id', $id);
            return $builder->get()->getFirstRow();
        } else {
            $builder->orderBy('unit.unit_id');
            return $builder->get()->getResultArray();
        }
    }

    public function getUserDelAjax($domain = null)
    {

        $query = $this->db->table('user_deleted');
        return $query;
    }

    public function getAllDomain()
    {
        $query = $this->db->table('user_domain')
            ->select('*')
            ->orderBy('id')
            ->get()->getResultArray();
        return $query;
    }
    public function getDomain($name)
    {
        $query = $this->db->table('user_domain')
            ->select('*')->orderBy('id')
            ->where('name', $name)
            ->get()->getRow();
        return $query;
    }
    public function getDomainById($id)
    {
        $query = $this->db->table('user_domain')
            ->select('*')->orderBy('id')
            ->where('id', $id)
            ->get()->getRow();
        return $query;
    }

    public function getUserAjax()
    {
        // POST data
        ($this->request->getPost());
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $rowperpage = $this->request->getPost('length');
        $columnIndex = $this->request->getPost("order[0][column]");
        $columnName = $this->request->getPost("columns[$columnIndex][data]");
        $columnSortOrder = $this->request->getPost("order[0][dir]");
        $searchValue = $this->request->getPost("search[value]");

        $this->builder = $this->db->table('user');
        $response = [];
        ## Total number of records without filtering
        $this->builder->select('count(*) as allcount');
        $records = $this->builder->get()->getResultArray();
        $totalRecords = $records[0]['allcount'];
        ## Total number of record with filtering
        $this->builder->select('count(*) as allcount');
        if ($searchValue) {
            $this->builder->like('dispname', $searchValue, 'both', null, true)
                ->orLike('email', $searchValue, 'both', null, true);
        }
        $recordsWithFilter = $this->builder->get()->getResultArray();
        $totalRecordsWithFilter = $recordsWithFilter[0]['allcount'];

        ## Fetch records
        $this->builder->select('user.id,user.email,user.dispname,user.category,user.role,user.active,user.created_at,user.created_by,user.updated_at,user.updated_by,user.info,uc.id c_id,uc.name c_name')
            ->join('user_category uc', 'uc.id=user.category', 'left');
        // ->join('user_group', 'user_group.user_id=user.id', 'left')
        // ->join('group', 'group.g_id=user_group.group_id', 'left');
        // ->join('user_domain', 'user_domain.d_id=user_category.d_id');
        if ($searchValue) {
            $this->builder->like('dispname', $searchValue, 'both', null, true)
                ->orLike('email', $searchValue, 'both', null, true);
        }
        $this->builder->orderBy($columnName, $columnSortOrder);
        $this->builder->limit($rowperpage, $start);
        $records = $this->builder->get()->getResult();
        $data = [];
        foreach ($records as $record) {
            $action = "<a type=\"button\" class=\"badge badge-info btn-sm \" href=\"/admin/edituser/" . $record->id . "\"><i class=\"fa fa-edit\"></i> </a> <a type=\"button\" class=\"badge badge-danger btn-sm btn-delete\"  onclick=\"hapus('$record->email','$record->id')\"  ><i class=\"fa fa-trash-alt\"></i> </a>";

            $data[] = [
                'id' => $record->id,
                'action' => $action,
                'email' => $record->email,
                'dispname' => $record->dispname,
                // 'group' => $record->g_name,
                'created_at' => $record->created_at,
                'created_by' => $record->created_by,
                'updated_at' => $record->updated_at,
                'updated_by' => $record->updated_by,
                'info' => $record->info,
            ];
        }

        // Response
        $response = [
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalRecordsWithFilter,
            'aaData' => $data,
        ];
        return $response;
    }

    public function getLog()
    {
        return  $this->db->table('logs');
    }

    public function insertLog($user, $message)
    {
        $uuid = new Uuid();
        $data = [
            'uuid' => $uuid->v4(),
            'user' => $user,
            'created_at' => date('Y-m-d H:i:s'),
            'message' => $message,
            'ip' => $this->request->getIPAddress(),
        ];

        $this->db->table('logs')
            ->insert($data);
        return $this->db->affectedRows();
    }
}
