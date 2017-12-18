<?php namespace Iesod;

use Iesod\Database\ModelInterface;

class SaveForm{
    private $Request;
    private  $Model;
    private $data = [];
    private $valitations = [];
    private $fields = [];
    public function __construct(RequestInterface $Request, ModelInterface $Model){
        $this->Request = $Request;
        $this->Model = $Model;
    }
    public function get($name){
        return $this->data[$name];
    }
    public function getData(){
        return $this->data;
    }
    public function getFieldsValues(){
        $data = [];
        foreach ($this->fields as $field=>$name){
            $data[ $field ] = $this->get($name);
        }
        
        return $data;
    }
    public function getValidations(){
        return $this->valitations;
    }
    
    public function set($name, $value){
        $this->data[$name] = $value;
        
        return $this;
    }
    public function implode($name,$glue){
        if(is_array($this->data[$name])){
            $this->data[$name] = implode($glue, $this->data[$name]);
        }
        return $this;
    }
    public function setValidate($name, $validate = null){
        if(is_null($validate) || empty($validate)){
            if(isset($this->valitations[$name]))
                unset($this->valitations[$name]);
        } else {
            $this->valitations[$name] = $validate;
        }
        return $this;
    }
    public function setFieldname($fieldname,$name = null){
        if(is_null($name) || empty($name)){
            if(isset($this->fields[ $fieldname ]))
                unset($this->fields[ $fieldname ]);
        } else {
            $this->fields[ $fieldname ] = $name;
        }
        
        return $this;
    }
    public function setFieldValue($fieldname, $value, $name = null){
        if(is_null($name))
            $name = $fieldname;

        $this->set($name,$value);
        $this->setFieldname($fieldname,$name);

        return $this;
    }
    public function addInput($name, $validate,$fieldname = null,$default = null,$method = null){
        $method = strtoupper( $method?? 'POST' );
        
        if($method=='GET'){
            $value = $this->Request->get($name,$default,true);
        } else {
            $value = $this->Request->post($name,$default,true);
        }
        
        $this->set($name,$value);
        $this->setValidate($name,$validate);
        $this->setFieldname($fieldname,$name);
        
        return $this;
    }
    
    public function preg_replace($name, $pattern, $replacement){
        $this->set(
            $name,
            preg_replace($pattern,$replacement,$this->get($name) )
            );
        return $this;
    }
    public function replace($name, $search, $replace){
        $this->set(
            $name,
            str_replace($search,$replace,$this->get($name) )
            );
        return $this;
    }
    
    public function unmaskphone($name){
        $this->preg_replace($name, '/[^0-9+]/', '');
        return $this;
    }
    public function onlyNumbers($name,$float = false,$dec = null,$unsigned = true){
        
        if($float){
            $this->preg_replace($name, '/[^0-9.-]/', '');
            if(is_null($dec) || !is_int($dec)){
                $pattern = '/^([0-9-]+([.][0-9]+)?)(.*)/';
            } else {
                $pattern = '/^([0-9-]+([.][0-9]{1,'.$dec.'})?)(.*)/';
            }
            $this->preg_replace($name, $pattern, '$1');
        } else {
            $this->preg_replace($name, '/[^0-9-]/', '');
        }
        
        if($unsigned)
            $this->preg_replace($name, '/[^0-9.]/', '');

        return $this;
    }
    public function unmaskMoney($name, $dec = 2){
        $this->replace($name, ',', '.');
        $this->onlyNumbers($name,true,$dec);
        
        return $this;
    }
    public function passwordConfirm($namePassword,$nameConfirm){
        if( $this->get($namePassword)!=$this->get($nameConfirm) ){
            throw new \Exception("Password do not match",1);
        }
        
        return $this;
    }
    public function passwordCrypt($name){
        $this->set(
            $name,
            bcrypt( $this->get($name) )
        );
        return $this;
    }
    public function validate(){
        validate($this->data, $this->valitations);
        return $this;
    }
    public function save($id = null){
        if(is_null($id)){
            return $this->Model->insert( $this->getFieldsValues() );
        } else {
            return $this->Model->update( $this->getFieldsValues() ,$id);
        }
    }
}