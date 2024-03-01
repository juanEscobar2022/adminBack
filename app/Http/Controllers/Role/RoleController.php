<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\MyController;
use App\Http\Controllers\Controller;

use App\Models\AppModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\RoleModel;

// excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Exception;

class RoleController extends MyController
{

    private $model;
    public  $appModel;
    private $endpoint   = 'rol';

    function __construct()
    {
        $this->model = new RoleModel();
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
                case 'getPrueba':
                    $result = $this->model->getPrueba();
                    break;
                default:
                    $result = $this->model->getPrueba();
                    break;
                case 'getParamsUpdate':

                    $result['getDataRole'] = $this->model->getListVal(1);
                    break;
                case 'getParamsUpdateSub':
                    $id = $request->input('idRol');
                    $result['getSelecUpdat'] = DB::table('role')->where('idRole', $id)->get();
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

                $result = $this->model->getPrueba($id, $user);
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
}
