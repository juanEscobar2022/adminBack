<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use SebastianBergmann\Environment\Console;

class AppModel extends Model{

public function checkPermission($user,$endpoint){
    $sql = "SELECT users.cedula, users.token_sub, 
            users.id, users.login, users.role, role_item.iditem,
            role_item.perm, itesub.url, itesub.name
            FROM users
            LEFT JOIN role_item on users.role = role_item.idrol
            LEFT JOIN itesub ON role_item.iditem = itesub.id
            WHERE users.id = ? AND itesub.url = ?";
    $result = DB::select($sql,array($user,$endpoint));
    return $result;
}


        }