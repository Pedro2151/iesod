<?php

namespace Iesod\Command;

use Iesod\Command;


class Module
{
    public static function create($module){
      $module = Command::nameTransform($module);
      $dirM = Command::$dir."Apps/{$module}";
      $dirs = [
        $dirM,
        $dirM."/Controllers",
        $dirM."/lang",
        $dirM."/lang/en",
        $dirM."/lang/ptbr",
        $dirM."/View"
      ];
      foreach($dirs as $dir){
        if(!is_dir($dir)){
          mkdir($dir,0777,true);
          chmod($dir,0777);
          echo "Create directory: '{$dir}'\n";
        }
      }
      Router::create($module);
    }
    public static function createIfNotExists($module){
      if(!static::isExist($module)){
        return static::create($module);
      }
      return true;
    }
    public static function isExist($module){
      $module = Command::nameTransform($module);
      return (is_dir(Command::$dir."Apps/{$module}"));
    }
}