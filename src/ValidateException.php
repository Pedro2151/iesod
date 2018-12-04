<?php namespace Iesod;

class ValidateException extends \Exception {
    private $name;
    private $MethodName;
    private $value;
    private $param;
    public function setAll ($MethodName = null, $value = null,$param = null,$name = null) {
        $this->name = $name;
        $this->param = $param;
        $this->value = $value;
        if (!is_null($MethodName)) {
            $MethodName = explode(":", $MethodName);
            $MethodName = array_pop($MethodName);
        }
        $this->MethodName = $MethodName;
    }
    public function getName () {
        return $this->name;
    }
    public function setName ($name = null) {
        $this->name = $name;
    }
    public function getMethodName () {
        return $this->MethodName;
    }
    public function setMethodName ($MethodName = null) {
        $this->MethodName = $MethodName;
    }
    public function getValue () {
        return $this->value;
    }
    public function setValue ($value = null) {
        $this->value = $value;
    }
    public function getParam () {
        return $this->param;
    }
    public function setParam ($param = null) {
        $this->param = $param;
    }
}
