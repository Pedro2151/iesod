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

        $requests = [
            'index' => [
                'method' => 'GET',
                'request' => "/".$prefix,
                'AccessPolicy' => $options['AccessPolicy']['index']?? $AccessPolicy
            ],
            'create' => [
                'method' => 'GET',
                'request' => "/".$prefix."/create",
                'AccessPolicy' => $options['AccessPolicy']['create']?? $AccessPolicy
            ],
            'store' => [
                'method' => 'POST',
                'request' => "/".$prefix,
                'AccessPolicy' => $options['AccessPolicy']['store']?? $AccessPolicy
            ],
            'show' => [
                'method' => 'GET',
                'request' => "/".$prefix."/{id}",
                'AccessPolicy' => $options['AccessPolicy']['show']?? $AccessPolicy
            ],
            'edit' => [
                'method' => 'GET',
                'request' => "/".$prefix."/{id}/edit",
                'AccessPolicy' => $options['AccessPolicy']['edit']?? $AccessPolicy
            ],
            'update' => [
                'method' => 'PUT',
                'request' => "/".$prefix."/{id}",
                'AccessPolicy' => $options['AccessPolicy']['update']?? $AccessPolicy
            ],
            'destroy' => [
                'method' => 'DELETE',
                'request' => "/".$prefix."/{id}",
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
        $R = explode('/', strtolower( static::$prefix.$request ));
        $r = [];
        foreach($R as $v){
            if(!empty($v))
                $r[] = $v;
        }
        $r1 = static::$prefix.$request;
        

        list($uri) = explode("?", $_SERVER['REQUEST_URI']);
        if(substr($uri,0,1)=='/')
            $uri = substr($uri,1);
        $rUrl = explode('/', strtolower( $uri ) );
        
        if(count($r)<count($rUrl))
			return false;
        
        $p = [];
        $pattern = '/^\{([^?]+)([?])?\}$/';
        foreach ($r as $i=>$v){
            preg_match_all($pattern, $v, $matches, PREG_SET_ORDER, 0);
            if(is_null($matches) || empty($matches)){
                if(!isset($rUrl[$i]) || $v!=$rUrl[$i]){
                    return false;
                }
            } else {
                $var = $matches[0][1];
                $require = !(isset($matches[0][2]) && $matches[0][2]=='?');
                
                if($require){
                    if(isset($rUrl[$i])){
                        $p[] = $rUrl[$i];
                    } else {
                        return false;
                    }
                } else {
                    if(isset($rUrl[$i])){
                        $p[] = $rUrl[$i];
                    } else {
                        $p[] = null;
                    }
                }
            }
        }
        
        static::$data = [
            'method' => static::getMethod(),
            'route' => static::$prefix.$request,
            'controller' => $controller,
            'request_uri' => strtolower( $uri )
        ];
        
        if(is_null($AccessPolicy)){
            static::unknown($controller,$p);
        } else {
            try {
                $AccessPolicy->testAccess();
                $AccessPolicy->beforeSuccess();
                static::unknown($controller,$p);
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
                echo json_encode($return);
                exit;
            }
            
        }
    }   
}
