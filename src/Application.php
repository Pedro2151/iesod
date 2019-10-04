<?php namespace Iesod;
require_once 'Session.php';
require_once 'helpers.php';

class Application{
    static $lang = 'en';
    static $module;
    static $pathModule;
    static $dirModule;
    /**
     * 
     * @var integer 0 - View error / 1 - json
     */
    static $typeReturnError = 0;
    public function __construct($module){
        
		csrfCheck();
	  
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
		$dirModule = DIR_ROOT.(static::$pathModule);
		
		
		$dirModule = str_replace(
		    "/",
		    DIRECTORY_SEPARATOR,
		    $dirModule);
        static::$dirModule = $dirModule;
        if (is_file(DIR_ROOT . "/bootstrap/starter.php")) {
            require_once DIR_ROOT . "/bootstrap/starter.php";
        }
		if(is_file($dirModule."Router.php"))
		   require_once $dirModule."Router.php";
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
