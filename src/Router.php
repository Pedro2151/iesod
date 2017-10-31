<?php namespace Iesod;

class Router {
    static $prefix = "";
    static $data;
    public static function prefix($prefix, $routers, AccessPolicyInterface $AccessPolicy = null){
        $prefixBuffer = self::$prefix;
        self::$prefix .= $prefix;
        
        $routers($AccessPolicy);
        self::$prefix = $prefixBuffer;
    }
    public static function get($request,$controller, AccessPolicyInterface $AccessPolicy = null){
       
       if(strtoupper($_SERVER['REQUEST_METHOD'])=="GET")
           return self::any($request, $controller,$AccessPolicy);
       
        
            
       return false;
    }
    public static function post($request,$controller, AccessPolicyInterface $AccessPolicy = null){
        if(strtoupper($_SERVER['REQUEST_METHOD'])=="POST")
            return self::any($request, $controller,$AccessPolicy);
            
            return false;
    }
    public static function put($request,$controller, AccessPolicyInterface $AccessPolicy = null){
        if(strtoupper($_SERVER['REQUEST_METHOD'])=="PUT")
            return self::any($request, $controller,$AccessPolicy);
            
            return false;
    }
    public static function delete($request,$controller, AccessPolicyInterface $AccessPolicy = null){
        if(strtoupper($_SERVER['REQUEST_METHOD'])=="DELETE")
            return self::any($request, $controller,$AccessPolicy);
            
            return false;
    }
    public static function any($request, $controller, AccessPolicyInterface $AccessPolicy = null){
        $r = explode('/', strtolower( self::$prefix.$request ));
        $rUrl = explode('/', strtolower( $_SERVER['REQUEST_URI']) );
        
        $p = [];
        $pattern = '/^\{([^?]+)([?])?\}$/';
        foreach ($r as $i=>$v){
            preg_match_all($pattern, $v, $matches, PREG_SET_ORDER, 0);
            if(is_null($matches) || empty($matches)){
                if(!isset($rUrl[$i]) || $v!=$rUrl[$i])
                    return false;
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
            'method' => $_SERVER['REQUEST_METHOD'],
            'route' => self::$prefix.$request,
            'controller' => $controller,
            'request_uri' => strtolower( $_SERVER['REQUEST_URI'])
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
            static::$data = [
                'method' => $_SERVER['REQUEST_METHOD'],
                'route' => null,
                'controller' => $controller,
                'request_uri' => strtolower( $_SERVER['REQUEST_URI'])
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
