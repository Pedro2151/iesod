<?php namespace Iesod;

class View {
    static function get($view, $data = []){
        $dir = DIR_ROOT."/src".(Application::$pathModule)."View";
        $filename = str_replace("/", DIRECTORY_SEPARATOR, "{$dir}/{$view}");
           
        if(is_file($filename.".tpl")){
            $core = new \Dwoo\Core();
            echo $core->get($filename.".tpl", $data);
            return true;
        } elseif(is_file($filename.".html")){
            $core = new \Dwoo\Core();
            echo $core->get($filename.".html", $data);
            return true;
        } elseif(is_file($filename.".php")){
            extract($data);
            require_once $filename.".php";
            return true;
        } else {
            throw new \Exception("Arquivo não encontrado!FILE: {$filename}");
            return false;
        }
    }
}


