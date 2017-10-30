<?php namespace Iesod;

class Validate
{
    public $data = [];
    public $validations = [];
    public function __construct($data, $validations = []){
        $this->data = $data;
        $this->validations = $validations;
    }
    public function validate(){
        foreach($this->validations as $name=>$validation){
            if($this->validateInput($this->data[$name]??'' , $validation,$name)==false)
                return false;
        }
        return true;
    }
    public function validateInput($value, $validation,$name){
        $tests = explode("|", $validation);
        $pattern = "/^([^:]+)([:]([^:]+))?$/";
        foreach ($tests as $test){
            preg_match($pattern, $test,$matches);
            $testName   = $matches[1];
            $testValue  = $matches[3] ?? null;
            
            if(is_callable([$this, $testName])){
                if(call_user_func([$this,$testName],$value,$testValue,$name)===false){
                    throw new \Exception("Invalid input '{$name}'");
                    return false;
                }
            } else {
                throw new \Exception("Invalid validate method '{$testName}'");
            }
        }
        return true;
    }
    static function require($value,$param,$name){
        if(is_null($value) || empty($value))
            throw new \Exception("'{$name}' is required");
    }
    static function int($value,$param,$name){
        if( !static::regexp($value,"^[-]?[0-9]+$",$name,true) )
            throw new \Exception("'{$name}' invalid integer");
    }
    static function intunsigned($value,$param,$name){
        if( !static::regexp($value,"^[0-9]+$", $name,true) )
            throw new \Exception("'{$name}' invalid integer");
    }
    static function min($value,$param,$name){
        $value = floatval($value);
        
        if($value<$param)
            throw new \Exception("'{$name}' min={$param}");
    }
    static function max($value,$param,$name){
        $value = floatval($value);
        
        if($value>$param)
            throw new \Exception("'{$name}' max={$param}");
    }
    static function minlen($value,$param,$name){
        $value = (string)$value;
        
        if(strlen($value)<$param)
            throw new \Exception("'{$name}' length min={$param}");
    }
    static function len($value,$param,$name){
        $value = (string)$value;
        
        if(strlen($value)<>$param)
            throw new \Exception("'{$name}' length={$param}");
    }
    static function maxlen($value,$param,$name){
        $value = (string)$value;
        
        if(strlen($value)>$param)
            throw new \Exception("'{$name}' length max={$param}");
    }
    static function username($value,$param,$name){
        if(!static::regexp($value,"^[\w._-]+$", $name,true) || static::minlen($value, 8,$name))
            throw new \Exception("'{$name}' invalid");
    }
    /** test: 1 | 1.2 | -1 | .1 | 1. 
     * 
     * @param string $value
     * @return boolean
     */
    static function number($value,$param,$name){
        if(!static::regexp($value,"^([-]?[0-9]*\.)?[0-9]*$", $name,true) )
            throw new \Exception("'{$name}' invalid number");
    }
    static function email($value,$param,$name){
        if(!static::regexp($value,"^[\w._-]+@[\w._-]+\.[\w_-]+$", $name,true) )
            throw new \Exception("'{$name}' invalid email");
    }
    static function regexp($value,$pattern,$name,$silence = false){
        if(preg_match("/".$pattern."/", $value)==false){
            if($silence)
                return false;
            throw new \Exception("'{$name}' invalid");
        }
        return true;
    }
}