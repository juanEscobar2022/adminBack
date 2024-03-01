<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MyController;
use Illuminate\Http\Request;
use App\Models\LoginModel;
use Firebase\JWT\JWT;
use Exception;

class LoginController extends MyController
{
    private $model;

    function __construct()
    {
        $this->model = new LoginModel();
    }

    public function authentication(Request $request)
    {

        $objData    = json_decode($request->getContent());
        $user       = $objData->fuser;
        $pass       = $this->desencrypt($objData->fpass);
        $action     = "";

        $options = [
            'cost' => 12
        ];
        $passNew = password_hash($pass, PASSWORD_DEFAULT, $options);
        //echo $pass;


        try {
            $user = $this->model->getUser($user);

            if (sizeOf($user) != 0) {


                if (($user[0]->status == "1/3" || $user[0]->status == "1/4" || $user[0]->status == "1/5")) {

                    //Validar Usuario 
                    if ($user[0]->status == "1/3" and password_verify($pass, $user[0]->password)) {
                        $actUser = (object) [
                            'status' => '1/4'
                        ];
                        //Actualizar Contraseña
                        $this->model->updateData($actUser, $user[0]->id);
                        return $this->returnInfo('¡Ingresar Nueva Contraseña!', 'Por favor ingresar una nueva contraseña');
                    } elseif ($user[0]->status == "1/4") {
                        $actUser = (object) [
                            'status' => '1/5',
                            'password' => $passNew
                        ];
                        //Actualizar Contraseña
                        $this->model->updateData($actUser, $user[0]->id);
                        return $this->returnInfo('¡Verifica La Contraseña!', 'Por favor verificar la nueva contraseña que se diligencio');
                    } elseif ($user[0]->status == "1/5" and password_verify($pass, $user[0]->password)) {
                        $actUser = (object) [
                            'status' => '1/1'
                        ];
                        //Actualizar Contraseña
                        $this->model->updateData($actUser, $user[0]->id);
                        return $this->authentication($request);
                    } 
                     else {
                        return $this->returnError('Por favor verificar la contraseña para completar el proceso de recuperación.');
                    }
                } elseif ($user[0]->login == 1) {

                    return $this->returnError('Tu usuario ya se encuentra en linea, por favor cierra sesión en aquel dispositivo o comunícate con el área de tecnología, Gracias.');
                } elseif($user[0]->status == "1/2"){
                    return $this->returnError('Usuario Inactivo');
                }
                else {
                    $user = $user[0];
                    if (password_verify($pass, $user->password)) {

                        //Variables de ingreso
                        $token_sub = bin2hex(random_bytes((5 - (5 % 2)) / 2));

                        $token = array(
                            'created'       => $this->encrypt(time()),
                            'expire'        => $this->encrypt(time() + (60 * 60 * 16)),
                            'userId'        => $this->encrypt($user->id),
                            'token_sub'     => $token_sub
                        );

                        $jwtToken = JWT::encode($token, $token_sub);
                        $objData = array(
                            'success'     => true,
                            'info'        => false,  
                            'token'       => $jwtToken,
                            'cedula'    => $user->cedula,
                            'userProfile' => $user->role,
                            'rol'         => $user->rol,
                            'action'      => $action,
                            'id'      => $user->id,
                            'user' => $user->name
                        );

                        $actUser = (object) [
                            'token_sub' => $token_sub,
                            'login' => '1'
                        ];
                        $this->model->updateData($actUser, $user->id);

                        return json_encode($objData);
                    } else {
                        return $this->returnError('Usuario o contraseña incorrecta. ' . $_SERVER['REMOTE_ADDR']);
                    }
                }
            } else {
                return $this->returnError('Usuario no existe o ha sido desactivado.');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            echo "error";
        }
    }

    public function closesesion(Request $request)
    {

        try {

            $objData    = json_decode($request->getContent());
            //Consultar Usuario
            $user = $this->model->getUserlog($objData->id);
            //Decodificacion
            $jwtToken = JWT::decode($objData->token, $user[0]->token_sub, array('HS256'));

            if (isset($jwtToken->token_sub)) {

                if ($jwtToken->token_sub == $user[0]->token_sub) {
                    //cerrar sesion
                    $actUser = (object) [
                        'token_sub' => NULL,
                        'login' => '0'
                    ];
                    $this->model->updateData($actUser, $objData->id);
                }
                return $this->returnOk('Cierre Exitoso');
            } else {
                return $this->returnError('Error: Por favor comunicares con Tecnologia.');
            }
        } catch (Exception $e) {
            return $this->returnError('Error: Por favor comunicares con Tecnologia.(E)');
        }
    }

    public function validatetokenss(Request $request)
    {

        try {

            $objData    = json_decode($request->getContent());
            //Consultar Usuario
            $user = $this->model->getUserlog($objData->id);
            //Decodificacion
            $jwtToken = JWT::decode($objData->token, $user[0]->token_sub, array('HS256'));

            if (isset($jwtToken->token_sub)) {

                if ($jwtToken->token_sub == $user[0]->token_sub && $user[0]->login == 1) {
                    return $this->returnOk('Token Valido');
                } else {
                    return $this->returnError('Token Invalido');
                }
            } else {
                return $this->returnError('Error: Token');
            }
        } catch (Exception $e) {
            return $this->returnError('Token Invalido (E): ' . $e->getMessage());
        }
    }

    

   

    
}
