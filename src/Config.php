<?php namespace Iesod;

class Config {
    private $config;
    public function __construct($filenameEnv){
        if(!is_file($filenameEnv))
            throw new \Exception("Config File not found");
            
            $content = file_get_contents($filenameEnd);
            $content = str_replace(["\r\n","\n"], "\n",$content);
            $lines = explode("\n", $content);
            foreach ($lines as $numLine=>$line){
                if(preg_match("/^([a-zA-Z0-9_]+)\s*[=]\s*(.*)$/", $line,$matches)){
                    $key = strtoupper( $m[1] );
                    $value = $m[2];
                    
                    $this->config[ $key ] = $value;
                } elseif(preg_match("/^([a-zA-Z0-9_]+)\s*[=]{0,1}\s*$/", $line,$matches)){
                    $key = strtoupper( $m[1] );
                    
                    $this->config[ $key ] = NULL;
                }
            }
    }
    static function createAppToken($length = 32){
        if(!isset($length) || intval($length) <= 8 ){
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return base64_encode( bin2hex( random_bytes($length) ) );
        }
        if (function_exists('mcrypt_create_iv')) {
            return base64_encode( bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) );
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return base64_encode( bin2hex(openssl_random_pseudo_bytes($length)) );
        }
    }
    static function createDefaultFile($filenameEnv){
        $data = [
            'APP_NAME' => "",
            'APP_ENV' => "local",//or production
            'APP_VERSION' => 1,
            'APP_VERSION_TEXT' => '0.0.0',
            'APP_TOKEN' => static::createAppToken(32),
            'APP_DEBUG' => true, 
            'APP_URL' => 'http://localhost', 
            
            'DB_DRIVE' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'iesod',
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
            
            'MAIL_DRIVER' => 'smtp',
            'MAIL_HOST' => 'smtp.mailtrap.io',
            'MAIL_PORT' => '2525',
            'MAIL_USERNAME' => null,
            'MAIL_PASSWORD' => null,
            'MAIL_ENCRYPTION' => null
        ];
        
        return static::saveFileEnv($filenameEnv, $data);
    }
    static function saveFileEnv($filenameEnv, $data){
        $content = "";

        $data['APP_NAME'] = $data['APP_NAME']?? "Iesod" ;
        $data['APP_TOKEN'] = $data['APP_TOKEN']?? (static::createAppToken(32)) ;
        $data['VERSION_ENV'] = isset($data['VERSION_ENV'])? $data['VERSION_ENV']+1 : 1;
        $data['TIMESTAMP_MOD'] = time();
        $data['DATE_MOD'] = date('Y-m-d H:i:s', $data['TIMESTAMP_MOD']);
        $data['DATE_CREATE'] = $data['DATE_CREATE'] ?? $data['DATE_MOD'];
        
        foreach($data as $key=>$value){
            $key = strtoupper( $key );
            $value = $value ?? ''; 
            if(is_bool($value))
                $value = $value?'true':'false';
            $content .= "{$key}={$value}\n";
        }
        
        return (file_put_contents($filenameEnv, $content)!==false);
    }
}
