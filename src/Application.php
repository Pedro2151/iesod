<?php namespace Iesod;

class Application{
    static $module;
    static $pathModule;
    static $dirModule;
    public function __construct($module){
        static::$module = $module;
		static::$pathModule = "/Apps/{$module}/";
		static::$dirModule = str_replace(
		    "/",
		    DIRECTORY_SEPARATOR,
		    DIR_ROOT.static::$pathModule);
		if(is_file(static::$dirModule."Router.php"))
		    require_once static::$dirModule."Router.php";
		else 
		    throw new \Exception(
                "Route file not found in ".static::$dirModule."Router.php");
    }
}

function encrypt(
    $data,
    $method=null,
    $password=null,
    $options= null,
    $iv= null
){
        if(is_null($method)){
            if(defined('ENCRYPT_METHOD'))
                $method = ENCRYPT_METHOD;
                else
                    $method = "AES256";
        }
        if(is_null($password)){
            if(defined('ENCRYPT_PASSWORD'))
                $password = ENCRYPT_PASSWORD;
                else
                    $password = "JHFU478FNHPSH38FNMBOPSB38VMK9FD";
        }
        if(is_null($options)){
            if(defined('ENCRYPT_OPTION'))
                $options = ENCRYPT_OPTION;
                else
                    $options = 0;
        }
        if(is_null($iv)){
            if(defined('ENCRYPT_IV'))
                $iv = ENCRYPT_IV;
                else
                    $iv = "978852";
        }
        
        
        return openssl_encrypt( $data , $method , $password ,$options, $iv);
}
function decrypt(
    $data,
    $method=null,
    $password=null,
    $options= null,
    $iv= null
    ){
        if(is_null($method)){
            if(defined('ENCRYPT_METHOD'))
                $method = ENCRYPT_METHOD;
                else
                    $method = "AES256";
        }
        if(is_null($password)){
            if(defined('ENCRYPT_PASSWORD'))
                $password = ENCRYPT_PASSWORD;
                else
                    $password = "FiFtOrmpEp72";
        }
        if(is_null($options)){
            if(defined('ENCRYPT_OPTION'))
                $options = ENCRYPT_OPTION;
                else
                    $options = 0;
        }
        if(is_null($iv)){
            if(defined('ENCRYPT_IV'))
                $iv = ENCRYPT_IV;
                else
                    $iv = "978852";
        }
        
        
        return openssl_decrypt( $data , $method , $password ,$options, $iv);
}