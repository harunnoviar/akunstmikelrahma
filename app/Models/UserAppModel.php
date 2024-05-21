<?php

namespace App\Models;

use CodeIgniter\Model;

class UserAppModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_app';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['username', 'email', 'firstname', 'lastname', 'role', 'active', 'password', 'created_at'];
}
