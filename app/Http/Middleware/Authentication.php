<?php

namespace App\Http\Middleware;
use Firebase\JWT\JWT;
use App\Http\Controllers\MyController;
use Closure;
use Exception;

class Authentication extends MyController{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        try{
            $authorization = $this->proccess_authorization();
            $jwt = $authorization['jwt'];
            $key = $authorization['key'];
            
            $decode = JWT::decode($jwt,$key,array('HS256'));

            if(is_object($decode)){
                $expire  = $this->desencrypt($decode->expire);
                
                if($expire <= time()){
                    $objData = array(
                        'success'   => false,
                        'message'   => 'Su sesión ha caducado',
                        'action'    => 'closeSession'
                    );
                    echo json_encode($objData);
                    exit();
                }else{
                    // echo "aun es valido";
                    return $next($request);
                }
                // return $decode;
            }else{
                // echo "error de token";
                $objData = array(
                    'success'   => false,
                    'message'   => 'Su sesión ha caducadoo',
                    'action'    => 'closeSession'
                );
                echo json_encode($objData);
                exit();
                // return "error";
            }
        }catch(Exception $e){
            // $objData = array(
            //     'success'   => false,
            //     'message'   => 'Su sesión ha caducadoooo',
            //     'action'    => 'closeSession'
            // );
            //  echo json_encode($objData);
             return $next($request);
            // exit();
        }
    }
}