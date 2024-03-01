<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategoryModel extends Model{

    public $table           = "category";
    public $identificador   = "cat_id";
    public $creador         = "create_User";
    // public $sqlEstado       = "status";

    public function getCategory($user){

        $sql ="SELECT ca.cat_id,
                      ca.id_list,
                      ca.create_User,
                      st.description as category_name
                FROM $this->table as ca
                LEFT JOIN users as us On ca.create_User = us.id
                LEFT JOIN values_list as st ON ca.id_list = st.ls_codvalue
                WHERE ca.create_User = $user
                ";
        $result = DB::select($sql);
        return $result;
    }
    public function getCategoryID($id){
        $sql ="SELECT ca.cat_id,
                      ca.id_list,
                      ca.create_User,
                      st.description as category_name
                FROM $this->table as ca
                LEFT JOIN users as us On ca.create_User = us.id
                LEFT JOIN values_list as st ON ca.id_list = st.ls_codvalue
                WHERE $this->identificador = ".$id;

        $result = DB::select($sql);
        return $result;
    }
    public function getUserID($id){
        $sql ="SELECT us.id,
                      us.name as nombre_perso,
                      us.name,
                      us.cedula,
                      us.role,
                      us.status,
                      us.phone,
                      us.fecha_de_nacimiento,
                      us.codigo_de_ciudad,
                      us.email,
                      us.login
                FROM $this->table as us
                LEFT JOIN role as rol ON us.role = rol.idRole
                WHERE $this->identificador = ".$id;

        $result = DB::select($sql);
        return $result;
    }

   

    public function getListVal( $list, $not = "", $condiExt = "" ){

        $condition = "";
        if( !empty($not) ){
            $condition = "AND ls_codvalue not in (".$not.")";
        }

        $sql = "SELECT ls_codvalue, description, complemento FROM `values_list` WHERE list_id = $list AND status = 1 ".$condition." ".$condiExt." ORDER BY `values_id`  ASC";

        $result = DB::select($sql);
        return $result;
    }


    public function getParamsUpdate(){
         $sql = "SELECT * 
                 FROM  values_list where eliminado = '-1' ";
        $result = DB::select($sql);
        return $result;
     }


    // actualizacion
    public function updateData($form,$id){
        // usuario actualizacion
        // $form->{$this->actualizador} = $user;
        // fecha actualizacion
        // $form->{$this->factualizacion} = 'now()';
        unset($form->create_User);
        $sql = "UPDATE $this->table set ";
        $sqlSets = [];
        $sqlValues = [];
        foreach($form as $key => $value){
            $sqlSets[] = " $key = ? ";
            $sqlValues[] = $value;
        }
        $sqlSets = implode(',',$sqlSets);
        $sql .= $sqlSets . " where $this->identificador = ?";

        // id actualizacion
        $sqlValues[] = $id;
        $result = DB::update($sql,$sqlValues);
        return $result;
    }
    // actualizacion estatus
    public function updateStatus($status,$id,$codigos = null,$user){
        if($id == 'null'){
            $codigos = implode(',',$codigos);
            $sql ="UPDATE $this->table set
                $this->sqlEstado = ?,
                $this->actualizador = ?,
                $this->factualizacion = now()
                where $this->identificador in ($codigos)";
            $result = DB::update($sql,array($status,$user));
        }else{
            $sql ="UPDATE $this->table set
                $this->sqlEstado = ?,
                $this->actualizador = ?,
                $this->factualizacion = now()
                where $this->identificador = ?";
                $result = DB::update($sql,array($status,$user,$id));
        }
        return $result;
    }

    // insercion
    public function insertData($form,$user){
        // print_r($form);
        $form->{$this->creador} = $user;
        // $form->{$this->}
        // $form->{$this->sqlEstado} = '13/1';
        foreach($form as $key=>$value){
            if($value != ''){
                $sqlInsert[]    = $key;
                $sqlBind[]      = '?';
                $sqlValues[]    = $value;
            }
        }
        $sqlInsert = implode(',',$sqlInsert);
        $sqlBind = implode(',',$sqlBind);
        $sql = "INSERT INTO $this->table ($sqlInsert) values($sqlBind)";
        $result = DB::insert($sql,$sqlValues);
        return $result;
    }

    // eliminacion desactivacion
    public function inactive($id,$user){
        $sql    = "UPDATE $this->table set $this->sqlEstado = 0, $this->actualizador = ? where $this->identificador = ?";
        $result = DB::update($sql,array($user,$id));
        return $result;
    }

   

    public function loguotSysAll($id){
        $sql    = "UPDATE $this->table SET login = NULL, token_sub = NULL where login = 1 and $this->identificador <> ? ";
        $result = DB::update($sql,array($id));
        return $result;
    }

   

    public function getRoleUs(){
        $sql = "SELECT rol.*
                FROM role as rol
                WHERE rol.status = '1/1' ";
        $result = DB::select($sql);
        return $result;
    }

    public function getUserSel($condition=""){
        $sql = "SELECT us.*
                FROM user as us
                WHERE 1 = 1 ".$condition;
        $result = DB::select($sql);
        return $result;
    }
    public function deleteInfoData($table, $key, $id)
	{

		return DB::table($table)
			->where($key, $id)
			->delete();
	}
    
}