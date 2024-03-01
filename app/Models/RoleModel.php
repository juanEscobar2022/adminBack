<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoleModel extends Model
{
    public $table = 'role';
	public $identificador   = "idRole";
    
    public function getPrueba($id = null)
    {

        if ($id == null) {

            $sql = " SELECT idRole, name, vl.description
                        FROM role 
                        LEFT JOIN values_list AS vl ON role.status = vl.ls_codvalue
                        WHERE role.status = '1/1'";
                        
            $result = DB::select($sql);
        } else {
            $sql = "SELECT idRole, name, vl.description
                    from
                    role 
                    LEFT JOIN values_list AS vl ON role.status = vl.ls_codvalue
                    WHERE idRole = ?";
            $result = DB::select($sql, array($id));
        }
        return $result;
    }
    
    public function insertData($form,$user){

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

    public function getListVal( $list, $not = "" ){

		$condition = "";
		if( !empty($not) ){
			$condition = "AND ls_codvalue not in (".$not.")";
		}
		$sql = "SELECT ls_codvalue, description, complemento FROM `values_list` WHERE list_id = $list AND status = 1 ".$condition." ORDER BY `values_id`  ASC";
		$result = DB::select($sql);
        return $result;
	}
     //Informacion
     public function getParamsUpdate($id = null){
        $sql = "SELECT idRole, name, vl.description
        from
        role 
        LEFT JOIN values_list AS vl ON role.status = vl.ls_codvalue
        WHERE idRole = ? ";
        $result = DB::select($sql, array($id));
      }
      public function updateData($form, $id){

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
}


    

