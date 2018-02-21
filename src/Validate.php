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
        if(empty($value)) return true;
        if( !static::regexp($value,"^[-]?[0-9]+$",$name,true) )
            throw new \Exception("'{$name}' invalid integer");
    }
    static function intunsigned($value,$param,$name){
        if(empty($value)) return true;
        if( !static::regexp($value,"^[0-9]+$", $name,true) )
            throw new \Exception("'{$name}' invalid integer");
    }
    static function min($value,$param,$name){
        if(empty($value)) return true;
        $value = floatval($value);
        
        if($value<$param)
            throw new \Exception("'{$name}' min={$param}");
    }
    static function max($value,$param,$name){
        if(empty($value)) return true;
        $value = floatval($value);
        
        if($value>$param)
            throw new \Exception("'{$name}' max={$param}");
    }
    static function minlen($value,$param,$name){
        if(empty($value)) return true;
        $value = (string)$value;
        
        if(strlen($value)<$param)
            throw new \Exception("'{$name}' length min={$param}");
    }
    static function len($value,$param,$name){
        if(empty($value)) return true;
        $value = (string)$value;
        
        if(strlen($value)!=$param)
            throw new \Exception("'{$name}' length={$param}");
    }
    static function maxlen($value,$param,$name){
        if(empty($value)) return true;
        $value = (string)$value;
        
        if(strlen($value)>$param)
            throw new \Exception("'{$name}' length max={$param}");
    }
    static function username($value,$param,$name){
        if(empty($value)) return true;
        if(!static::regexp($value,"^[\w._-]+$", $name,true) || static::minlen($value, 3,$name))
            throw new \Exception("'{$name}' invalid");
    }
    static function date($value,$param,$name){
        if(empty($value)) return true;
        if(!static::regexp(
            $value,
            "^(00|19|20)[0-9]{2}-([0][0-9]|[1][0-2])-([0-2][0-9]|[3][0-1])([ ][0-9:]{5,8})?$",
            $name,
            true)
        )
            throw new \Exception("'{$name}' invalid");
    }
    static function datetime($value,$param,$name){
        if(empty($value)) return true;
        if(!static::regexp(
            $value,
            "^(00|19|20)[0-9]{2}-([0][0-9]|[1][0-2])-([0-2][0-9]|[3][0-1])".
            "([0-1][0-9]|[2][0-3])[:][0-5][0-9]([:][0-5][0-9])?$",
            $name,
            true)
        )
            throw new \Exception("'{$name}' invalid");
    }
    /** test: 1 | 1.2 | -1 | .1 | 1. 
     * 
     * @param string $value
     * @return boolean
     */
    static function number($value,$param,$name){
        if(empty($value)) return true;
        if(!static::regexp($value,"^([-]?[0-9]*\.)?[0-9]*$", $name,true) )
            throw new \Exception("'{$name}' invalid number");
    }
    static function email($value,$param,$name){
        if(empty($value)) return true;
        if(!static::regexp($value,"^[\w._-]+@[\w._-]+\.[\w_-]+$", $name,true) )
            throw new \Exception("'{$name}' invalid email");
    }
    static function domain($value,$param,$name){
        if(empty($value)) return true;
        if(!static::regexp($value,"^[\w._-]+\.[\w_-]+$", $name,true) )
            throw new \Exception("'{$name}' invalid");
    }
    static function cpfcnpj($value, $param,$name){
        if(empty($value)) return true;
        $value = preg_replace("/[^\d]/","",$value);
        
        if(strlen($value)==11)
            static::cpf($value,$param,$name);
        elseif(strlen($value)==14)
            static::cnpj($value,$param,$name);
        else
            throw new \Exception("'{$name}' invalid CPF or CNPJ");
    }
    static function cpf($value, $param, $name){
        if(empty($value)) return true;
        $value = preg_replace("/[^\d]/","",$value);
    
        if(strlen($value)!=11 || in_array(
            $value,[
            '00000000000',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999'
        ]))
            throw new \Exception("'{$name}' invalid CPF");

        //Iniciando teste...
        $value = str_split($value);
        $a = [];
	    $b = 0;
        $c = 11;
        
	    for($i=0; $i<11; $i++){
	        $a[$i] = $value[$i];
            if($i < 9)
                $b += ($a[$i] * --$c);
        }
        $x = $b % 11; 
	    if($x < 2){
            $a[9] = 0;
        } else {
            $a[9] = 11-$x;
        }

	    $b = 0;
	    $c = 11;
        for($i=0;$i<10;$i++){
            $b += ($a[$i] * $c--);
        }
        $x = $b % 11;
	    if($x < 2){
            $a[10] = 0;
        } else {
            $a[10] = 11-$x;
        }

        if( ($value[9] != $a[9]) || ($value[10]!=$a[10]) )
            throw new \Exception("'{$name}' invalid CPF");

    }
    static function cnpj($value, $param, $name){
        if(empty($value)) return true;
        $value = preg_replace("/[^\d]/","",$value);
        
        if(strlen($value)!=14 || in_array(
            $value,[
            '00000000000000',
            '11111111111111',
            '22222222222222',
            '33333333333333',
            '44444444444444',
            '55555555555555',
            '66666666666666',
            '77777777777777',
            '88888888888888',
            '99999999999999'
        ]))
            throw new \Exception("'{$name}' invalid CNPJ");

        //Iniciando teste...
        $value = str_split($value);
        
        $n = 0;	
        $d = [0,0];
        for($i=0;$i<12;$i++){
            $n = $value[11-$i];
            $e0 = ($i<8)? $i+2 : $i-6;
            $e1 = ($i<7)? $i+3 : $i-5;
            $d[0] += $n * $e0;//n12*2 + n11*3 + n10*4 + n9*5 + n8*6 + n7*7 + n6*8 + n5*9 + n4*2 + n3*3 + n2*4 + n1*5;
            $d[1] += $n * $e1;//n12*3 + n11*4 + n10*5 + n9*6 + n8*7 + n7*8 + n6*9 + n5*2 + n4*3 + n3*4 + n2*5 + n1*6;
        }
        $d[0] = 11 - ($d[0] % 11);//$d[0]:= 11 - ($d[0] mod 11)
        if($d[0] >= 10)
            $d[0] = 0;
        
        $d[1] += $d[0]*2;
        $d[1] = 11 - ($d[1] % 11);//$d[1]:= 11 - ($d[1] mod 11);
        if($d[1] >= 10)
            $d[1] = 0;
        
        if($value[12]!=$d[0] || $value[13]!=$d[1])
            throw new \Exception("'{$name}' invalid CNPJ");
    }
    static function regexp($value,$pattern,$name,$silence = false){
        if(empty($value)) return true;
        if(preg_match("/".$pattern."/", $value)==false){
            if($silence)
                return false;
            throw new \Exception("'{$name}' invalid");
        }
        return true;
    }
}