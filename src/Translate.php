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
                $lang = Config::get('APP_LANGUAGE', self::ENGLISH);//Set default
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
        if( is_file($filename) ){
            return include($filename);            
        } elseif(!$silence){
            throw new \Exception("File not found");
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