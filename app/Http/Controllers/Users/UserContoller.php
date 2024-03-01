<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\MyController;
use App\Http\Controllers\Controller;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use App\Models\AppModel;


use Exception;
use Illuminate\Support\Facades\DB;


// excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class UserContoller extends MyController
{

    private $model;
    public  $appModel;
    private $endpoint   = 'usuario';
    public $sql = "";

    function __construct()
    {
        $this->model = new UsersModel();
        $this->appModel = new AppModel();
    }

    //GET
    public function index(Request $request)
    {

        $permission = $this->checkPermission($request, __FUNCTION__);
        $user   = $request->input('id');
        $role   = $request->input('role');
        $action = $request->input('action');
        $result = array();

        if ($permission) {
            $action = $request->input('action');

            switch ($action) {

                case 'getDataUser':

                    $result['getDataUser'] = $this->model->getUsersAdmin($role,$user);
                    return $this->returnData($result);
                    break;

                case 'getParamsUpdate':

                    $result["status"]    = $this->model->getListVal(1);
                    $result["typeRol"]       = $this->model->getRoleUs();
                    $result["city"] = DB::table('city')->get();
                    return $this->returnData($result);
                    break;

                case 'getParamUpdateSet':
                    $id = $request->input('idCod');
                    $result['getDataUpda'] = $this->model->getUserID($id);
                    break;

                case 'logoutSesionSyst':

                    $result['getLogout'] = $this->model->loguotSysAll($user);
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

        $permission = $this->checkPermission($request, __FUNCTION__);
        $user   = $request->input('id');

        if ($permission) {
            try {

                $objData = json_decode($request->getContent());
                $formUsuario    = $objData->usuarios;
                //Validar Existencia Usuario
              
                if (!DB::table('users')->where('cedula', $formUsuario->cedula)->where('email', $formUsuario->email)->exists()) {

                    //Crear Usuario

                    $options = ['cost' => 12];
                    $password = password_hash("Prueba@123456", PASSWORD_DEFAULT, $options);
                    $formUsuario->password = $password;
                    $formUsuario->status   = '1/3';
                    $user = $this->model->insertData($formUsuario, $user);
                    return $this->returnOk('Datos guardados exitosamente. Las credenciales de acceso son Prueba@');
                }else{
                    return $this->returnInfo('¡Alerta!', 'El usuario ya esta registrado. Por favor, verifica los datos e intenta nuevamente.');
                }

            } catch (Exception $e) {
                return $this->returnError('Se produjo un error al insertar datos, o ya existe el usuario');
            }
        } else {
            return $this->notPermission();
        }
    }


    public function show($id, Request $request)
    {

        $permission = $this->checkPermission($request, __FUNCTION__);
        $user   = $request->input('id');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        if ($permission) {
            try {
                $result = $this->model->getUsersAdminID($id);
                return $this->returnData($result, 'No se obtuvo resultado de usuario');
            } catch (Exception $e) {
                return $this->returnError('Error al obtener información de usuario');
            }
        } else {
            return $this->notPermission();
        }
    }

    // method - PUT
    public function update(Request $request, $id)
    {

        $permission = $this->checkPermission($request, __FUNCTION__);
        $user   = $request->input('id');

        if ($permission) {
           
                    try {
                        $objData = json_decode($request->getContent());
                        $formUsuario = $objData->usuario;
                        unset($formUsuario->password);

                        //Actulizar Contraseña
                        if ($formUsuario->status == '1/3') {
                            $options = ['cost' => 12];
                            $password = password_hash("Prueba@123456", PASSWORD_DEFAULT, $options);
                            $formUsuario->password = $password;
                            $formUsuario->login = 0;
                        }

                        $result = $this->model->updateData($formUsuario, $id, $user);
                        return $this->returnOk('Actualizado correctamente');
                    } catch (Exception $e) {
                        return $this->returnError('Se produjo un error al intentar actualizar');
                    }
                    
            }else {
             return $this->notPermission();
        }
    }
    function deleteDataAusen(Request $request)
    {

        $permission = $this->checkPermission($request, 'destroy');

        if ($permission) {
            try {
                $id_delete    = json_decode($request->input('idUser'));
                //Registros a borrar
                // foreach ($formListas as $key => $value) {

                    $resp['df'] = $this->model->deleteInfoData('users', 'id', $id_delete);
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