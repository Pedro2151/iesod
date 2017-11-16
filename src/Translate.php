<?php namespace Iesod;

class Translate {
    const ENGLISH = 'en';
    const PORTUGUES_BRASIL = 'ptbr';
    
    static $lang = 'en';
    static function setLang($lang = null){
        $Session = new Session();
        if(is_null($lang)){
            $langSession = $Session->get('lang',null);
            if(is_null($langSession)){
                $lang = Config::getByModel(
                    "LANGUAGE",
                    Config::get(
                        'APP_LANGUAGE',
                        self::ENGLISH
                    )
                );//Set default
            }
        }
        
        if(!is_null($lang)){//save if lang is not null.
            $Session->put($lang,'lang');
            $langSession = $lang;
        }
        
        static::$lang = $langSession;
        
        $Session->__destruct();
    }
    static function getDataByFile($filename, $silence = false){
        $lang = static::$lang;
        $filename1 = str_replace("{lang}", $lang, $filename);
        $filename1 = str_replace(['/',"\\"], DIRECTORY_SEPARATOR, $filename1);
        
        $filename2 = str_replace("{lang}", env("APP_LANGUAGE",'en'), $filename);
        $filename2 = str_replace(['/',"\\"], DIRECTORY_SEPARATOR, $filename2);
        
        if( is_file($filename1) ){
            return include($filename1);
        } elseif( is_file($filename2) ){
            return include($filename2);
        } elseif(!$silence){
            throw new \Exception("File not found".$filename1);
        }
        return false;
    }
    static function getData(){
        $path = DIR_ROOT."/resources/lang/";
        $lang = static::$lang;
        $files = [
            "{$lang}.php",
            (self::ENGLISH).".php"
        ];
        
        foreach($files as $filename){
            $r = static::getDataByFile($path.$filename, true);
            if($r!==false)
                return $r;
        }
        
        return [];
    }
}