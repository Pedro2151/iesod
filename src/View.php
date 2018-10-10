<?php namespace Iesod;

class View {
    static function getDataLang($fileLang){
        $files = [
            Application::$dirModule."lang/{lang}/{$fileLang}",
            Application::$dirModule."lang/{lang}/{$fileLang}.php",
            Application::$dirModule."lang/{$fileLang}_{lang}",
            Application::$dirModule."lang/{$fileLang}_{lang}.php"
        ];
        $i = 0;
        $data = false;
        while( !$data && $i<count($files)){
            $data = Translate::getDataByFile($fileLang,true );
            $fileLang =  $files[$i];
            $i++;
        }

        if(!$data){
            throw new \Exception("FileLang not found");
        }

        return $data;
    }
    static function get($view, $data = [],$fileLang = null){
        $dir = DIR_ROOT.(Application::$pathModule)."View";
        $filename = str_replace("/", DIRECTORY_SEPARATOR, "{$dir}/{$view}");
        
        $lang = Application::getDataLang();
        if(!is_null($fileLang))
            $data['lang'] = static::getDataLang($fileLang);
        
        $Auth = Auth::getUser();
        if($Auth===false)
            $user = false;
        else
            $user = $Auth->toArray();

        // Usa o DWOO?
        if (env('DWOO', 0) == 1) {
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
        } else {
            $files = [
                $filename.".php",
                $filename.".html"
            ];
            extract($data);
            foreach ($files as $file) {
                if (is_file($file)) {
                    require_once $file;
                    exit;
                }
            }
            throw new \Exception("Arquivo não encontrado!\n<br />FILE: {$filename}");
            return false;
        }
    }
}


