<?php namespace Iesod;
require_once 'Session.php';
require_once 'helpers.php';

class Application{
    static $lang = 'en';
    static $module;
    static $pathModule;
    static $dirModule;
    public function __construct($module){
        \Iesod\Database\Query::setConnection(
            env('DB_DATABASE','iesod'),
            null,
            env('DB_HOST','127.0.0.1'),
            env('DB_PORT',3306),
            env('DB_USERNAME','root'),
            env('DB_PASSWORD',''),
            env('DB_DRIVE',null)
        );
        
        static::$module = $module;
		static::$pathModule = "/Apps/{$module}/";
		static::setLang();
		static::$lang = Translate::$lang;
		
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
	static function setLang($lang = null){
		Translate::setLang($lang);
		static::$lang = Translate::$lang;
	}
	static function getDataLang($filename = null){
	    return is_null($filename)? Translate::getData() : Translate::getDataByFile($filename);
	}
}
