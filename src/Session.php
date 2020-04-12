<?php namespace Iesod;

use Iesod\Database\Model;

class Session {
    static $id;
    static $data;
    static $browser;
    static $plataform;
    public function __construct() {
        $this->getData();
    }
    private function getData($force = false){
        if(!$force && !is_null(static::$data))
            return static::$data;

        static::$data      = $_SESSION['data'] ?? [];
        static::$browser   = $_SESSION['browser'] ?? $_SERVER['HTTP_USER_AGENT'];
        static::$plataform = $_SESSION['plataform'] ?? 'undefined';

        return static::$data;
    }
    private function saveData () {
        $id = $this->getId();
        if(is_null($id))
            return false;

        if (function_exists('get_browser')) {
            $browser =  get_browser($_SERVER['HTTP_USER_AGENT'],true);
        } else {
            $browser =  [
                'browser_name_pattern' => $_SERVER['HTTP_USER_AGENT'],
                'platform' => 'undefined'
            ];
        }

        $_SESSION['id'] = $id;
        $_SESSION['browser'] = substr($browser['browser_name_pattern'], 254);
        $_SESSION['plataform'] = $browser['platform'];
        $_SESSION['data'] = static::$data;
    }
    public static function close(){
        static::$id = null;
        static::$data = null;
        static::$browser = null;
        static::$plataform = null;
        $_SESSION['data'] = static::$data;
    }
    public static function getId(){
        if (!is_null(static::$id)) {
            return static::$id;
        }
        $sessId = session_id();
        if ($sessId == '') {
            session_start();
            $sessId = session_id();
        }
        static::$id = $sessId;
        return $sessId;
    }
    public function __get ($name)
    {
        $data = $this->getData();
        return $data[$name] ?? null;
    }

    public function __set ($name, $value)
    {
        $this->getData();
        static::$data[$name] = $value;
    }

    public static function get ($name)
    {
        $Sess = new static();
        return $Sess->$name;
    }
    public static function set ($name, $value)
    {
        $Sess = new static();
        $Sess->$name = $value;
        $Sess->__destruct();
    }
    public function __destruct(){
        $this->saveData();
    }
}

