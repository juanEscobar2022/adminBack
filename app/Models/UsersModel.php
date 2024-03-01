<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsersModel extends Model{

    public $table           = "users";
    public $identificador   = "id";
    public $creador         = "createdBy";
    public $sqlEstado       = "status";

    public function getUsersAdmin($role,$user){
        $condition = "";
        if(!($role == 1)){
            $condition = "WHERE us.id = $user";
        }
        $sql ="SELECT us.id,
                      us.name as nombre_perso,
                      us.cedula,
                      us.role,
                      us.status,
                      rol.name,
                      st.description as estado_usuario,
                      us.phone,
                      us.email
                FROM $this->table as us
                LEFT JOIN role as rol ON us.role = rol.idRole
                LEFT JOIN values_list as st ON us.status = st.ls_codvalue
                " . $condition . "";
                
        $result = DB::select($sql);
        return $result;
    }
    public function getUsersAdminID($id){
        $sql ="SELECT us.id,
                      us.name as nombre_perso,
                      us.name,
                      us.cedula,
                      us.role,
                      us.status,
                      rol.name,
                      us.phone,
                      us.fecha_de_nacimiento,
                      us.codigo_de_ciudad,
                      us.email,
                      us.login,
                      st.description as estado_usuario,
                      cit.nombre
                FROM $this->table as us
                LEFT JOIN role as rol ON us.role = rol.idRole
                LEFT JOIN values_list as st ON us.status = st.ls_codvalue
                LEFT JOIN city as cit ON us.codigo_de_ciudad = cit.idCity 
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
    public function updateData($form,$id,$user){
        // usuario actualizacion
        // $form->{$this->actualizador} = $user;
        // fecha actualizacion
        // $form->{$this->factualizacion} = 'now()';

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
        // $form->{$this->creador} = $user;
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