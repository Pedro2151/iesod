<?php

namespace Iesod\Command;

use Iesod\Command;


class Router
{
    public function create($module = null){
      if(empty($module))
        $module = "main";

      $module = Command::nameTransform($module);
      $dirM = Command::$dir."Apps/{$module}";
      if(!is_dir($dirM)){
        mkdir($dirM,0777,true);
        chmod($dirM,0777);
        echo "Create directory: '{$dirM}'\n";
      }
      
      $content = "<?php namespace Apps\\{$module};\n".
      "use Iesod\\{Router,Auth,View};\n".
      "\n".
      "try {\n".
      "  if(Auth::getUser()!=false){\n".
      "    //Router::get('/exemplo','ExemploController@exemplo');\n".
      "  }\n".
      "  //Error 404\n".
      "  View::get('error',['code' => 404, 'message' => 'Page not found']);\n".
      "} catch (\\Exception \$e) {\n".
      "  if(\\Iesod\\Application::\$typeReturnError==0){\n".
      "    View::get('error',[\n".
      "      'code' => \$e->getCode(),\n".
      "      'message' => \$e->getMessage(),\n".
      "      'trace' => \$e->getTrace()\n".
      "    ]);\n".
      "  } else {\n".
      "    header('Content-type:text/json;charset=utf-8');\n".
      "    echo json_encode([\n".
      "      'strError' => \$e->getMessage(),\n".
      "      'codeError' => \$e->getCode(),\n".
      "      'status' => false,\n".
      "      'data' => [],\n".
      "      'trace' => \$e->getTrace()\n".
      "    ]);\n".
      "    exit;\n".
      "  }\n".
      "}\n";

      if(!is_file($dirM."/Router.php")){
        file_put_contents($dirM."/Router.php",$content);
        chmod($dirM."/Router.php",0777);
        echo "Create file 'Router.php' in '{$dirM}'\n";
      }

      return true;
    }
}