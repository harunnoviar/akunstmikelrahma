<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\GlobalModel;

class Log extends BaseController
{
    private $userAppModel, $globalModel, $uuid, $groupModel, $categoryModel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->globalModel = new GlobalModel();
    }

    public function logs()
    {
        $data = [
            'title' => 'Log Aplikasi',
            'content' => 'admin/v_logs',
            'a_menu' => 'logs',
            'validation' => \Config\Services::validation(),
        ];
        return view('layout/v_wrapper', $data);
    }

    public function logfetch()
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

        $builder = $this->globalModel->getLog();
        $builder->select('count(*) as allcount');
        $records = $builder->get()->getResultArray();
        $totalRecords = $records[0]['allcount'];

        ## Total number of record with filtering
        $builder->select('count(*) as allcount');
        // d($searchValue);
        if ($searchValue) {
            $builder->like('user', $searchValue, 'both', true);
            $builder->orLike('message', $searchValue, 'both', true);
        }
        $recordsWithFilter = $builder->get()->getResultArray();
        $totalRecordsWithFilter = $recordsWithFilter[0]['allcount'];

        ## Fetch records
        $builder->select('*');
        if ($searchValue) {
            $builder->like('user', $searchValue, 'both',  true);
            $builder->orLike('message', $searchValue, 'both',  true);
        }
        $builder->orderBy($columnName, $columnSortOrder);
        $builder->limit($rowperpage, $start);
        $records = $builder->get()->getResult();
        $data = [];
        $nomer = 1;
        foreach ($records as $r) {
            $data[] = [
                'created_at' => $r->created_at,
                'user' => $r->user,
                'message' => $r->message,
                'ip' => $r->ip,
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
