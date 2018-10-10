<?php

namespace Iesod;

class Command
{
  static $argv = [];
  static $argc = 0;
  static $dir = "./";
  function __construct($argv = [],$argc = 0){
    static::$argv = $argv;
    static::$argc = $argc;
    static::$dir = getcwd()."/";
  }
  function cmd($command, $action, $namespace = null) {
    $args = $this->checkCommand($command);
    if($args===false){
      return false;
    }

    list($classname,$method) = explode("@", $action);

    if (is_null($namespace)) {
      $namespace = "/Iesod/Command/";
    }
    $class = $namespace.$classname;
    $class = str_replace("/","\\",$class);
    
    $return = call_user_func_array( array(new $class, $method), $args );
    exit;
  }
  static function nameTransform($name){
    $nameA = explode("_", str_replace(["_",'+','-'] ,"_",strtolower($name)) );
      $name = '';
      foreach($nameA as $v){
        $name .= strtoupper( substr($v, 0,1)).substr($v,1);
      }
      return $name;
  }
  private function checkCommand($command){
    //$command = preg_replace('/(\s\s+)/', ' ', $command);
    $aCmd = explode(' ', strtolower( $command ));
    
    if(static::$argc>count($aCmd)+1)
      return false;
    
    $pattern = '/^\{([^?]+)([?])?\}$/';//{id} ou {id?}
    $params = [];
    foreach($aCmd as $i=>$part){
        if(!preg_match($pattern, $part,$matches)){
            if(!isset(static::$argv[$i+1]) || $part!=strtolower(static::$argv[$i+1]))
                return false;
        } else {//Variavel
            $require = !isset($matches[2]) || $matches[2]!='?';

            if($require){
                if(isset(static::$argv[$i+1]) && static::$argv[$i+1]!=''){
                    $params[] = static::$argv[$i+1];
                } else {
                    return false;
                }
            } else {
                $params[] = static::$argv[$i+1]?? null;
            }
        }
    }

    return $params;
  }
  static function readInput($prompt, $valid_inputs, $max = 3){ 
    $i = 0;
    while(
      $i<=$max && (
        !isset($input) || 
        (is_array($valid_inputs) && !in_array($input, $valid_inputs)) || 
        ($valid_inputs == 'is_file' && !is_file($input)) ||
        ($valid_inputs == 'string' && !is_string($input) )
      )
    ) {
        echo $prompt.": "; 
        $input = trim( fgets(STDIN,100) ); 
        $i++;
    } 
    /*
    if(empty($input)){
      throw new \Error("Não informado!");
      return null;
    } */
    return $input; 
  } 
  public function help(){
    echo "Informe corretamente os comandos:\n\n";
    echo "createController [controller*] [module*]\n";
    echo "createResource [controller*] [module*] [model*]\n";
    echo "createModule [module]\n";
    echo "config createClient [idClient*]\n";
    echo "config updateClient [idClient*]\n";
    echo "\n* Opcional\n";
    exit;
  }
}