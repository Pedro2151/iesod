<?php

namespace Iesod\Command;

use Iesod\Command;

class Model
{
  public function help($command){
    echo "Haha não tem {$command}\n";
    return true;
  }
  public function create($module = null,$model = null, $table = null, $primaryKey = null){
    if(empty($module)) $module = "main";
    Module::createIfNotExists($module);
    $module = Command::nameTransform($module);
    $dirModule = Command::$dir."Apps/{$module}/";

    if(is_null($table) || empty($table)) {
      $table = Command::readInput("Informe a tabela", 'string');
    }
    if(is_null($primaryKey) || empty($primaryKey)) {
      $primaryKey = "id";
    }
    if(is_null($model) || empty($model)) {
      $model = Command::readInput("Informe o model", 'string');
    }
    $model = Command::nameTransform($model);
    $model = strtoupper(substr($model,0,1)).substr($model,1);
    $filename = $dirModule.$model.".php";      
    
    if(!is_file($filename)) {
      $data = "<?php namespace Apps\\{$module};\n\n".
      "use Iesod\\Database\\Model;\n".
      "\n".
      "class {$model} extends Model\n".
      "{\n".
      "    protected \$table = '{$table}';\n".
      "    protected \$primaryKey = '{$primaryKey}';\n".
      "    // protected \$connectionId;\n".
      "\n".
      "}";
      
      file_put_contents($filename,$data);
      chmod($filename,0777);
      echo "Create Model '{$model}'\n";
    } else {
      echo "Model já existe";
    }
    return true;
  }
}