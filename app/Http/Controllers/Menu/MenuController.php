<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\MyController;
use App\Models\MenuModel;
use Illuminate\Http\Request;
use Exception;


class MenuController extends MyController{
    private $model;
    
    function __construct(){
        $this->model = new MenuModel();
    }
    

    function getMenu(Request $request){
       try{


            $objData = json_decode($request->getContent());
            // print_r($objData);

            $resultItem  = $this->model->getMenu($objData->role);
            $resultSubI  = $this->model->getSubmenu($objData->role);
            $result = array();
            array_push($result, $resultItem);
            array_push($result, $resultSubI);

            return $this->returnData($result);
        //    return $this->returnData('No tiene permisos en la aplicación actualmente.');
        }catch(Exception $e){
            return $this->returnError('Error al intentar obtener permisos de menu.');
        }
    }


}