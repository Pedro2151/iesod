<?php
use Iesod\Validate;

function config($name, $default = null){
    return \Iesod\Config::get($name,$default);
}
function env($name, $default = null){
    return \Iesod\Config::get($name,$default);
}
function encrypt(
    $data,
    $method=null,
    $password=null,
    $options= null,
    $iv= null
    ){
        if(is_null($method))
            $method = env('ENCRYPT_METHOD',"AES256");
            
        if(is_null($password))
            $password = env('APP_TOKEN',"A8SKFDH6JK8JK0GFS3");
            
        if(is_null($options))
            $options = env('ENCRYPT_OPTION',0);
            
        if(is_null($iv))
            $iv = env('ENCRYPT_IV',"978852");
        
        return openssl_encrypt( $data , $method , $password ,$options, $iv);
}
function decrypt(
    $data,
    $method=null,
    $password=null,
    $options= null,
    $iv= null
    ){
        if(is_null($method))
            $method = env('ENCRYPT_METHOD',"AES256");
        
        if(is_null($password))
            $password = env('APP_TOKEN',"A8SKFDH6JK8JK0GFS3");
        
        if(is_null($options))
            $options = env('ENCRYPT_OPTION',0);
        
        if(is_null($iv))
            $iv = env('ENCRYPT_IV',"978852");
        
        return openssl_decrypt( $data , $method , $password ,$options, $iv);
}
function bcrypt($str){
    if(function_exists('crypt')){
        $salt = '$2a$08$' . env('APP_TOKEN',"A8SKFDH6JK8JK0GFS3") . '$';
        return crypt($str,$salt);
    } else {
        return encrypt($str);
    }
}
function checkHash($str,$hash){
    return bcrypt($str)==$hash;
}
function validate($data,$validations){
    
    return (new Validate($data,$validations))->validate();
}
function method_put(){
    return '<input type="hidden" name="_method" value="put" />';
}
function method_delete(){
    return '<input type="hidden" name="_method" value="delete" />';
}