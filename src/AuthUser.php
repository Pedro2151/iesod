<?php namespace Iesod;

class AuthUser {
    
    private $dataUser = [];
    public function __construct($dataUser){
        if(!is_array($dataUser))
            throw new \Exception("dataUser invalid!");
        
        $this->dataUser = $dataUser;
    }
    public function getId(){
        return $this->dataUser['id_user'] ?? false;
    }
    public function getUserGroup(){
        return $this->dataUser['usergroup'] ?? false;
    }
    public function getUsername(){
        return $this->dataUser['username'] ?? false;
    }
    public function getName(){
        return $this->dataUser['name'] ?? false;
    }
    public function getEmail(){
        return $this->dataUser['email'] ?? false;
    }
    public function getPhone(){
        return $this->dataUser['phone'] ?? false;
    }
    public function getData ($name) {
        return $this->dataUser[$name] ?? false;
    }
    public function setName($value){
        $this->dataUser['name'] = $value;
    }
    public function setEmail($value){
        $this->dataUser['email'] = $value;
    }
    public function setPhone($value){
        $this->dataUser['phone'] = $value;
    }
    public function toArray(){
        return $this->dataUser;
    }
}