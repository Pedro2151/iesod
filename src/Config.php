<?php namespace Iesod;

use Iesod\ConfigModel;

class Config {
    static $config;
    static $envDefault = DIR_ROOT.DIRECTORY_SEPARATOR.".env";
    static function open($filename = null){
        static::$config = static::getFileEnv(static::$envDefault);

        if(is_null($filename)){
            if(!defined("ID_CLIENT"))
                define('ID_CLIENT', 'public');

            $filename = static::getFilenameClient(ID_CLIENT);
        }

        if(is_file($filename))
            static::$config = array_merge(static::$config, static::getFileEnv($filename));
    }
    static function getFilenameClient($idClient){
        return DIR_ROOT.DIRECTORY_SEPARATOR."config_cli".DIRECTORY_SEPARATOR.$idClient.".env";
    }
    static function getFileEnv($filename){
        if(!is_file($filename)){
            throw new \Exception("Config File not found\n".$filename);
            return false;
        }
            
        $content = file_get_contents($filename);
        $content = str_replace(["\r\n","\n"], "\n",$content);
        $lines = explode("\n", $content);
        $config = [];
        foreach ($lines as $numLine=>$line){
            if(preg_match(
                "/^([a-zA-Z0-9_]+)\s*[=]\s*(.*)$/",
                $line,
                $m)
            ){
                $key = strtoupper( $m[1] );
                $value = $m[2];
                
                $config[ $key ] = $value;
            } elseif(preg_match(
                "/^([a-zA-Z0-9_]+)\s*[=]{0,1}\s*$/",
                $line,
                $m)
            ){
                $key = strtoupper( $m[1] );                
                $config[ $key ] = NULL;
            }
        }

        return $config;
    }
    static function getByModel($name, $default = null){
        return ConfigModel::getConfig($name,$default);
    } 
    static function setByModel($name, $value = null){
        ConfigModel::setConfig($name,$value);
    } 
    static function get($name, $default = null){
        if(is_null(static::$config))
            static::open();
        
        return (static::$config[$name]?? $default);
    }
    static function createAppToken($length = 32){
        if(!isset($length) || intval($length) <= 8 ){
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return base64_encode(
                bin2hex(
                    random_bytes($length)
                )
            );
        }
        if (function_exists('mcrypt_create_iv')) {
            return base64_encode(
                bin2hex(
                    mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)
                )
            );
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return base64_encode(
                bin2hex(
                    openssl_random_pseudo_bytes($length)
                )
            );
        }
    }
    static function createFileClient($idClient){
        $config = static::getFileEnv(static::$envDefault);

        $data = [
            //APP
            'APP_URL' => $idClient.".".$config['APP_URL'],
            'APP_LANGUAGE' => $config['APP_LANGUAGE'],
            'APP_ENV' => $config['APP_ENV'],//or production
            //CLIENT
            'ID_CLIENT' => $idClient,
            'FULLNAME_CLIENT' => '',
            'ADDRESS_1' => '',
            'ADDRESS_2' => '',
            //STORAGE
            'STORAGE_LIMIT' => $config['STORAGE_LIMIT'],
            'STORAGE_PATH' => $config['STORAGE_PATH'],
            //DATABASE
            'DB_DRIVE' => 'mysql',
            'DB_HOST' => $config['DB_HOST'],
            'DB_PORT' => '3306',
            'DB_DATABASE' => $idClient,
            'DB_USERNAME' => $config['DB_USERNAME'],
            'DB_PASSWORD' => $config['DB_PASSWORD']
        ];

        $filename = static::getFilenameClient($idClient);
        return static::saveFileEnv($filename, $data);
    }
    static function updateFileClient($idClient, $data){
        $filename = static::getFilenameClient($idClient);
        $data = array_merge(
            static::getFileEnv($filename),
            $data
        );
        
        return static::saveFileEnv($filename, $data);
    }
    static function createDefaultFile(){
        $filename = static::$envDefault;            
        
        $data = [
            //APP
            'APP_NAME' => "",
            'APP_LANGUAGE' => Translate::ENGLISH,
            'APP_ENV' => "local",//or production
            'APP_VERSION' => 1,
            'APP_VERSION_TEXT' => '0.0.0',
            'APP_TOKEN' => static::createAppToken(32),
            'APP_DEBUG' => true, 
            'APP_URL' => 'localhost', 
            //ENCRYPTION
            'ENCRYPT_METHOD' => "AES256",
            'ENCRYPT_OPTION' => 0,
            'ENCRYPT_IV' => "978852",            
            //CLIENT
            'ID_CLIENT' => "default",
            'FULLNAME_CLIENT' => 'Klug Sistemas',
            'ADDRESS_1' => 'Rua Monte Sião, 551 ',
            'ADDRESS_2' => 'Bairro Serra, Belo Horizonte - MG Cep 30240-050',
            //STORAGE
            'STORAGE_LIMIT' => "2GB",
            'STORAGE_PATH' => "/storage/public/",
            //DATABASE
            'DB_DRIVE' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'iesod',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            //EMAIL
            'MAIL_DRIVER' => 'smtp',
            'MAIL_HOST' => 'smtp.mailtrap.io',
            'MAIL_PORT' => '2525',
            'MAIL_USERNAME' => null,
            'MAIL_PASSWORD' => null,
            'MAIL_ENCRYPTION' => null,
            'BUILD' => 1
        ];
        
        return static::saveFileEnv($filename, $data);
    }
    static function addBuild(){
        $filename = static::$envDefault;
        $config = static::getFileEnv(  $filename );
        $data = [
            'BUILD' => $config['BUILD']??0
        ];
        $data['BUILD']++;

        return static::updateEnvDefault($data);
    }
    static function updateEnvDefault($data = []){
        $filename = static::$envDefault;
        $data = array_merge(
            static::getFileEnv(  $filename ),
            $data
        );
        
        return static::saveFileEnv($filename, $data);
    }
    static function saveFileEnv($filename, $data = []){
        $content = "";

        $data['VERSION_ENV'] = isset($data['VERSION_ENV'])? $data['VERSION_ENV']+1 : 1;
        $data['TIMESTAMP_MOD'] = time();
        $data['DATE_MOD'] = date('Y-m-d H:i:s', $data['TIMESTAMP_MOD']);
        $data['DATE_CREATE'] = $data['DATE_CREATE'] ?? $data['DATE_MOD'];
        
        foreach($data as $key=>$value){
            $key = strtoupper( $key );
            $value = $value ?? '';
            if(is_bool($value))
                $value = $value?'true':'false';
            if(is_null($value))
                $value = "";
            $content .= "{$key}={$value}\n";
        }
        
        return (file_put_contents($filename, $content)!==false);
    }
}
