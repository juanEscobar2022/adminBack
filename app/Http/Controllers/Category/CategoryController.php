<?php

namespace App\Http\Controllers\Category;


use App\Http\Controllers\MyController;

use App\Models\AppModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryModel;


use Exception;

class CategoryController extends MyController
{
  
    private $model;
    public  $appModel;
    private $endpoint   = 'categ';

    function __construct()
    {
        $this->model = new CategoryModel();
        $this->appModel = new AppModel();
    }

    public function index(Request $request)
    {

        $permission = $this->checkPermission($request, __FUNCTION__);
        $user   = $request->input('id');
        $role   = $request->input('role');
        $result = array();

        if ($permission) {

            $action = $request->input('action');
            switch ($action) {
                case 'getDataUser':
                    $result['getDataUser'] = $this->model->getCategory($user);
                    break;
                
                case 'getParamsUpdate':

                    $result['getCategory'] = $this->model->getListVal(2);
                    break;
                case 'getParamsUpdateSub':
                    $id = $request->input('idCat');
                    $result['getSelecUpdat'] = DB::table('category')->where('cat_id', $id)->get();
                    break;
                    case 'getDelinfo':

                        $result['df'] = $this->deleteDataAusen($request);
                        break;
            }
            return $this->returnData($result);
        } else {
            return $this->notPermission();
        }
    }
    public function store(Request $request)
    {

        $user   = $request->input('id');
        $permission = $this->checkPermission($request, __FUNCTION__);
        if ($permission) {
            try {
                $objData = json_decode($request->getContent());

                $formListas    = $objData->listas;
                $user = $this->model->insertData($formListas, $user);
                return $this->returnOk('Datos guardados exitosamente');
            } catch (Exception $e) {
                return $this->returnError('Error al insertar datos, o ya existe el campo');
            }
        } else {
            return $this->notPermission();
        }
    }
    public function show($id, Request $request)
    {
        $user   = $request->input('id');
        $permission = $this->checkPermission($request, __FUNCTION__);

        if ($permission) {
            // try {

                $result = $this->model->getCategoryID($id);
                return $this->returnData($result, 'Se obtuvo información con exito');
            // } catch (Exception $e) {

                return $this->returnError('Error al obtener información de la lista');
            // }
        } else {
            return $this->notPermission();
        }
    }
    public function update(Request $request, $id)
    {
        $user       = $request->input('id');
        $permission = $this->checkPermission($request, __FUNCTION__);

        if ($permission) {

            try {
                $objData = json_decode($request->getContent());
                $formListas = $objData->listas;
                $result = $this->model->updateData($formListas, $id);
                return $this->returnOk('Actualizado correctamente');
            } catch (Exception $e) {

                return $this->returnError('Se produjo un error al intentar actualizar');
            }
        } else {
            return $this->notPermission();
        }
    }
    function deleteDataAusen(Request $request)
    {

        $permission = $this->checkPermission($request, 'destroy');

        if ($permission) {
            try {
                $id_delete    = json_decode($request->input('idCat'));
                //Registros a borrar
                // foreach ($formListas as $key => $value) {

                    $resp['df'] = $this->model->deleteInfoData('category', 'cat_id', $id_delete);
                // }

                return array('estatus' => 'ok');
            } catch (Exception $e) {

                return array('estatus' => 'error: ' . $resp['df']);
            }
        } else {
            return $this->notPermission();
        }
    }

}
