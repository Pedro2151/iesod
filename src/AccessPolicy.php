<?php namespace Iesod;

use Iesod\Database\Model;

class AccessPolicy implements AccessPolicyInterface {
    public function testAccess(){
        return true;
    }
    public function registerFail($codeError = null, $strError = null){
        $request = new Request();
        $requestValues = $request->all(true);
        
        if(is_null(Router::$data)){
            $routerData = [
                'method' => $_SERVER['REQUEST_METHOD'],
                'route' => null,
                'controller' => null,
                'request_uri' => strtolower( $_SERVER['REQUEST_URI'])
            ];
        } else {
            $routerData = Router::$data;
        }
        
        $data = [
            'id_user' => Auth::getUserId() ,
            'router' => $routerData['route'],
            'method' => $routerData['method'],
            'controller' => $routerData['controller'],
            'request_uri' => $routerData['request_uri'],
            'code_error' => $codeError,
            'str_error' => $strError,
            'request_values' => json_encode( $requestValues )
        ];
        
        $Model = new class() extends Model {
            protected $table = 'access_policy_fail';
            protected $primaryKey = 'id';
        };
        $Model->insert($data);
    }
    public function beforeSuccess($alerts = []){
        
    }
    public function afterFail($codeError = null, $strError = null){
        $this->registerFail($codeError,$strError);
    }
}