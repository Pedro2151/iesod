<?php namespace Iesod;

class Router {
    static $prefix = "";
    static $data;
    public static function getMethod(){
        $method = ($_POST['_method'] ?? ($_GET['_method'] ?? $_SERVER['REQUEST_METHOD']));
        $method = strtoupper($method);

        return $method;
    }
    public static function checkMethod($method){
        return static::getMethod()==strtoupper($method);
    }
    public static function checkUrl($request, $controller, &$params = []){
        //request
        $request = static::$prefix.$request;
        $aRequest = explode('/', strtolower( $request ));
        //uri
        list($uri) = explode("?", $_SERVER['REQUEST_URI']);
        $aUri = explode('/', $uri );

        if(count($aUri)>count($aRequest))
            return false;

        $pattern = '/^\{([^?]+)([?])?\}$/';//{id} ou {id?}
        $params = [];
        foreach($aRequest as $i=>$part){
            if(!preg_match($pattern, $part,$matches)){
                if(!isset($aUri[$i]) || $part!=strtolower($aUri[$i]))
                    return false;
            } else {//Variavel
                $require = !isset($matches[2]) || $matches[2]!='?';

                if($require){
                    if(isset($aUri[$i]) && $aUri[$i]!==''){
                        $params[] = $aUri[$i];
                    } else {
                        return false;
                    }
                } else {
                    $params[] = $aUri[$i]?? null;
                }
            }
        }

        static::$data = [
            'method' => static::getMethod(),
            'route' => $request,
            'controller' => $controller,
            'request_uri' => $uri
        ];
        return true;
    }
    public static function prefix($prefix, $routers, AccessPolicyInterface $AccessPolicy = null){
        $prefixBuffer = static::$prefix;
        static::$prefix .= $prefix;
        $routers($AccessPolicy);
        static::$prefix = $prefixBuffer;
    }
    public static function resource($prefix, $controller,$options = [], AccessPolicyInterface $AccessPolicy = null){
        $options['only'] = $options['only']?? [
            'index',//GET /prefix
            'create',//GET /prefix/create
            'store',//POST /prefix
            'show',//GET /prefix/{id}
            'edit',//GET /prefix/{id}/edit
            'update',//PUT /prefix/{id}
            'destroy'//DELETE /prefix/{id}
        ];
        $options['except'] = $options['except']??[];
        $options['AccessPolicy'] = $options['AccessPolicy'] ?? [];

        if($prefix!='' && substr($prefix,0,1)!='/')
            $prefix = "/".$prefix;

        $requests = [
            'index' => [
                'method' => 'GET',
                'request' => $prefix,
                'AccessPolicy' => $options['AccessPolicy']['index']?? $AccessPolicy
            ],
            'create' => [
                'method' => 'GET',
                'request' => $prefix."/create",
                'AccessPolicy' => $options['AccessPolicy']['create']?? $AccessPolicy
            ],
            'store' => [
                'method' => 'POST',
                'request' => $prefix,
                'AccessPolicy' => $options['AccessPolicy']['store']?? $AccessPolicy
            ],
            'show' => [
                'method' => 'GET',
                'request' => $prefix."/{id}",
                'AccessPolicy' => $options['AccessPolicy']['show']?? $AccessPolicy
            ],
            'edit' => [
                'method' => 'GET',
                'request' => $prefix."/{id}/edit",
                'AccessPolicy' => $options['AccessPolicy']['edit']?? $AccessPolicy
            ],
            'update' => [
                'method' => 'PUT',
                'request' => $prefix."/{id}",
                'AccessPolicy' => $options['AccessPolicy']['update']?? $AccessPolicy
            ],
            'destroy' => [
                'method' => 'DELETE',
                'request' => $prefix."/{id}",
                'AccessPolicy' => $options['AccessPolicy']['destroy']?? $AccessPolicy
            ]
        ];
        
        foreach($requests as $i=>$data){
            if(in_array($i,$options['only']) && !in_array($i,$options['except'])){
                if(static::checkMethod($data['method'])){
                    $r = static::any($data['request'], $controller."@{$i}",$data['AccessPolicy']);
                    if($r!=false)
                        return true;
                }
            }
        }
    }
    public static function get($request,$controller, AccessPolicyInterface $AccessPolicy = null){
       
       if(static::checkMethod("GET"))
           return static::any($request, $controller,$AccessPolicy);
       
       return false;
    }
    public static function post($request,$controller, AccessPolicyInterface $AccessPolicy = null){
        if(static::checkMethod("POST"))
            return static::any($request, $controller,$AccessPolicy);
            
            return false;
    }
    public static function put($request,$controller, AccessPolicyInterface $AccessPolicy = null){
        if(static::checkMethod("PUT"))
            return static::any($request, $controller,$AccessPolicy);
            
            return false;
    }
    public static function delete($request,$controller, AccessPolicyInterface $AccessPolicy = null){
        if(static::checkMethod("DELETE"))
            return static::any($request, $controller,$AccessPolicy);
            
            return false;
    }
    public static function any($request, $controller, AccessPolicyInterface $AccessPolicy = null){
        if(!static::checkUrl($request, $controller,$params)){
            return false;
        }
        
        if(is_null($AccessPolicy)){
            static::unknown($controller,$params);
        } else {
            try {
                $AccessPolicy->testAccess();
                $AccessPolicy->beforeSuccess();
                static::unknown($controller,$params);
            } catch (\Exception $e) {
                $AccessPolicy->afterFail( $e->getCode(),$e->getMessage() );
                throw $e;
            }
        }
    }
    public static function unknown($controller,$args = []){
        if(is_null(static::$data)){
            list($uri) = explode("?", $_SERVER['REQUEST_URI']);
            static::$data = [
                'method' => static::getMethod(),
                'route' => null,
                'controller' => $controller,
                'request_uri' => strtolower( $uri )
            ];
        }
        
        list($class,$method) = explode("@", $controller);
        $class = Application::$pathModule."Controllers/".$class;
        
        $classFile = str_replace("/", DIRECTORY_SEPARATOR, DIR_ROOT."{$class}.php");
        $class = str_replace("/", "\\", $class);
        
        if(!is_file($classFile))
            throw new \Exception("<strong>FILE NOT FOUND:</strong> ".$classFile);
            
        require_once $classFile;
        $return = call_user_func_array(array(new $class, $method), $args);
        
        if($return===false){
            throw new \Exception("Erro inesperado");
        } else {
            if(is_array($return)){
                header("Content-type:text/json;charset=utf-8");
                echo json_encode($return, JSON_PRETTY_PRINT);
                exit;
            }
            
        }
    }   
}
