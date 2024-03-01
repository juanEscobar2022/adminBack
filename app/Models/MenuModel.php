<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuModel extends Model{

    public function getMenu($role){
        $sql = "SELECT
        DISTINCT i.id,
        i.name AS item,
        i.icon AS icon,
        i.id AS id
        FROM
        role_item ri
        LEFT JOIN
        itesub it ON it.id = ri.iditem
        LEFT JOIN
        item i ON i.id = it.iditem
        WHERE
        ri.idrol = ? ";
        $result = DB::select($sql,array($role));
        return $result;
    }

    public function getSubmenu($role){
        $sql = "SELECT 
        ri.idrol AS rol,
        ri.perm AS perm,
        isb.name AS name, 
        isb.url  AS url,
        isb.iditem AS item
        FROM 
        role_item ri
        LEFT JOIN
        itesub isb ON isb.id = ri.iditem
        where 
        ri.idrol = ? ";
        $result = DB::select($sql,array($role));
        return $result;
    }

}