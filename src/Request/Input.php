<?php namespace Iesod\Request;

class Input {
    private $name;
    private $value;
    private $default;
    private $isset;
    public function __construct($data){
        $this->name = $data['name'];
        $this->value = $data['value'];
        $this->default = $data['default'];
        $this->isset = $data['isset'];
    }
    /**
     * 
     * @param string $strMessage Template of exceptionMessage.User tags: {name} Exemple: {name} is invalid!
     * @throws RequestException
     * @return \Iesod\Request\Input
     */
    public function required($strMessage = null){
        $strMessage = $strMessage?? "{name} is invalid!";
        $strMessage = str_replace("{name}", $this->name(),$strMessage);
        
        if(!$this->isset)
            throw new RequestException($strMessage,RequestException::ERROR_VALIDATE_ISREQUIRED);
            
        return $this;
    }
    /**
     * 
     * @param string $pattern
     * @param string $strMessage Template of exceptionMessage.User tags: {name} | {pattern} | {value} Exemple: {name} is invalid!
     * @throws RequestException
     * @return \Iesod\Request\Input
     */
    public function pattern($pattern,$strMessage = null){
        $strMessage = $strMessage?? "{name} is invalid!";
        $strMessage = str_replace("{name}", $this->name(),$strMessage);
        $strMessage = str_replace("{pattern}", $pattern,$strMessage);
        $strMessage = str_replace("{value}", $this->value,$strMessage);
        
        if(!preg_match ($pattern , $this->value ))
            throw new RequestException($strMessage,RequestException::ERROR_VALIDATE_PATTERN);
        
        return $this;
    }
    public function replace($pattern,$replacement){
        $this->value = preg_replace($pattern, $replacement, $this->value);
        return $this;
    }
    public function quote(){
        $this->value = addslashes( $this->value );
        return $this;
    }
    public function name(){
        return $this->name;
    }
    public function default(){
        return $this->default;
    }
    public function value(){
        return $this->value;
    }
}
