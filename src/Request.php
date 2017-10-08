<?php namespace Iesod;

use Iesod\Request\{Input,InputFile};

class Request {
    public function __construct(){
        
    }
    public function get($name = null, $default = null, $returnValue = false){
        if(is_null($name)){
            if($returnValue){
                return $_GET;
            } else {
                $r = [];
                foreach ($_GET as $name=>$value){
                    $r[$name] = $this->get($name);
                }
                
                return $r;
            }
        } else {
            $data = [
                'name' => $name,
                'value' => $_GET[$name] ?? $default,
                'default' => $default,
                'isset' => isset($_GET[$name])
            ];
            
            if($returnValue){
                return (new Input($data))->value();
            } else {
                return new Input($data);
            }
        }
    }
    public function post($name = null, $default = null, $returnValue = false){
        if(is_null($name)){
            if($returnValue){
                return $_POST;
            } else {
                $r = [];
                foreach ($_POST as $name=>$value){
                    $r[$name] = $this->post($name);
                }
                
                return $r;
            }
        } else {
            $data = [
                'name' => $name,
                'value' => $_POST[$name] ?? $default,
                'default' => $default,
                'isset' => isset($_POST[$name])
            ];
            
            if($returnValue){
                return (new Input($data))->value();
            } else {
                return new Input($data);
            }
        }
    }
    public function file($name = null, $returnValue = false){
        if(is_null($name)){
            if($returnValue){
                return $_FILES;
            }
            
            $r = [];
            if(is_uploaded_file()){
                foreach ($_FILES as $name=>$value){
                    $r[$name] = $this->file($name);
                }
            }
            
            return $r;
        } else {
            if(!is_uploaded_file() || !isset($_FILES[$name]['error']))
                throw new \Exception('No file was uploaded',UPLOAD_ERR_NO_FILE);
            
            if($returnValue)
                return $_FILES[$name];
            return new InputFile($_FILES[$name]);
        }
    }
    public function all($returnValue = false){
        return [
            'get'   => $this->get(null,null,$returnValue),
            'post'  => $this->post(null,null,$returnValue),
            'files' => $this->file(null,$returnValue)
        ];
    }
}
