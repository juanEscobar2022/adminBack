<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoginModel extends Model
{

    public $table = "users";
    public $identificador   = "id";

    public function
    getUser($user)
    {
        $sql = "SELECT 
                    us.id,
                    us.status,
                    us.name,
                    us.cedula,
                    us.password,
                    us.role AS rol,
                    vl.name AS role,
                    us.login
                
                FROM 
                    $this->table us
                left join role vl ON us.role = vl.idRole
                where 
                    us.cedula = ?
                    ";
        $result = DB::select($sql, array($user));
        return $result;
    }

    public function updateData($form, $id)
    {

        $sql = "UPDATE $this->table set ";
        $sqlSets = [];
        $sqlValues = [];
        foreach ($form as $key => $value) {
            $sqlSets[] = " $key = ? ";
            $sqlValues[] = $value;
        }
        $sqlSets = implode(',', $sqlSets);
        $sql .= $sqlSets . " where $this->identificador = ?";

        // id actualizacion
        $sqlValues[] = $id;
        $result = DB::update($sql, $sqlValues);
        return $result;
    }

    public function getUserlog($id)
    {

        $sql = "SELECT us.token_sub, us.login
                FROM $this->table us
                WHERE  us.id = ? ";

        $result = DB::select($sql, array($id));
        return $result;
    }

    
}
