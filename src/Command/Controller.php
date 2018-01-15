<?php

namespace Iesod\Command;

use Iesod\Command;

class Controller
{
  public function help($command){
    echo "Haha não tem {$command}\n";
    return true;
  }
  public function create($controller = null,$module = null){
    if(empty($module))
      $module = "main";
    Module::createIfNotExists($module);
    $module = Command::nameTransform($module);
    $dirController = Command::$dir."Apps/{$module}/Controllers/";

    if(empty($controller))
      $controller = Command::readInput("Informe o controller", 'string');
    $controller = Command::nameTransform($controller);
    if(substr($controller,-10)!="controller" && substr($controller,-10)!="Controller"){
      $controller = $controller."Controller";
    } else {
      $controller = substr($controller,0,-10)."Controller";
    }
    $controller = strtoupper(substr($controller,0,1)).substr($controller,1);
    $filename = Command::$dir."Apps/{$module}/Controllers/".$controller.".php";      
    
    if(!is_file($filename)){
      $data = "<?php namespace Apps\\{$module}\\Controllers;\n\n".
      "use Iesod\\Controller;\n".
      "\n".
      "class {$controller} extends Controller\n".
      "{\n".
      "\n".
      "}";
      
      file_put_contents($filename,$data);
      chmod($filename,0777);
      echo "Create Controller '{$controller}'\n";
    } else {
      echo "Controller já existe";
    }
    return true;
  }
  public function createResource($controller = null,$module = null,$model = null){
    if(empty($module))
      $module = "main";
    Module::createIfNotExists($module);
    $module = Command::nameTransform($module);
    $dirController = Command::$dir."Apps/{$module}/Controllers/";

    if(empty($controller))
      $controller = Command::readInput("Informe o controller", 'string');
    
    if(empty($model)){
      $use = "";
      $construct = "";
    } else {
      $use = "use {$model};\n";
      $model = explode("\\",$model);
      $construct = "  public function __construct(){\n".
        "    \$this->Model = new ".array_pop($model)."();\n". 
        "  }\n";
    }

    $controller = Command::nameTransform($controller);
    if(substr($controller,-10)!="controller" && substr($controller,-10)!="Controller"){
      $controller = $controller."Controller";
    } else {
      $controller = substr($controller,0,-10)."Controller";
    }
    $filename = Command::$dir."Apps/{$module}/Controllers/".$controller.".php";      
    
    if(!is_file($filename)){
      $data = "<?php namespace Apps\\{$module}\\Controllers;\n\n".
      "use Iesod\\ResourceController;\n".
      $use.
      "\n".
      "class {$controller} extends ResourceController\n".
      "{\n".
      $construct.
      "  public function index(){ /* ... */ }\n".
      "  public function create(){ /* ... */ }\n".
      "  public function store(){ /* ... */ }\n".
      "  public function show(\$id){ /* ... */ }\n".
      "  public function edit(\$id){ /* ... */ }\n".
      "  public function update(\$id){ /* ... */ }\n".
      "  public function destroy(\$id){ /* ... */ }\n".
      "\n".
      "}";
      
      file_put_contents($filename,$data);
      chmod($filename,0777);
      echo "Create Controller '{$controller}'\n";
    } else {
      echo "Controller já existe";
    }
    return true;
  }
}