<?php namespace Iesod;

class View {
    static function get($view, $data = [],$fileLang = null){
        $dir = DIR_ROOT.(Application::$pathModule)."View";
        $filename = str_replace("/", DIRECTORY_SEPARATOR, "{$dir}/{$view}");
        
        $lang = Application::getDataLang();
        if(!is_null($fileLang))
            $data['lang'] = Application::getDataLang( $fileLang );
        
        $Auth = Auth::getUser();
        if($Auth===false)
            $user = false;
        else
            $user = $Auth->toArray();
        
        if(is_file($filename.".tpl")){
            $core = new \Dwoo\Core();
            $core->addGlobal('lang', $lang);
            $core->addGlobal('user', $user);
            echo $core->get($filename.".tpl", $data);
            exit;
        } elseif(is_file($filename.".html")){
            $core = new \Dwoo\Core();
            $core->addGlobal('lang', $lang);
            $core->addGlobal('user', $user);
            echo $core->get($filename.".html", $data);
            exit;
        } elseif(is_file($filename.".php")){
            extract($data);
            require_once $filename.".php";
            exit;
        } else {
            throw new \Exception("Arquivo não encontrado!FILE: {$filename}");
            return false;
        }
    }
}


