<?php
use Iesod\Validate;

function config($name, $default = null){
    return \Iesod\Config::get($name,$default);
}
function env($name, $default = null){
    return \Iesod\Config::get($name,$default);
}//e4292128a12759205f9e52928061e076d553c4a6f0261436960e5428fa5b4b31
function getTranslate($filename){
    return \Iesod\Translate::getDataByFile($filename, false);
}
function randomString($length = 32) {
	if(function_exists('random_bytes')){
		$randomString = bin2hex( random_bytes($length) );
	} elseif(function_exists('mcrypt_create_iv')){
		$randomString = bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
	} elseif(function_exists('openssl_random_pseudo_bytes')){
		$randomString = bin2hex(openssl_random_pseudo_bytes($length));
	} else {
		$randomString = bin2hex( md5( uniqid( rand(), true ) ) );
	}
	
	return substr($randomString,0,$length);
	/*
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++)
        $randomString .= $characters[rand(0, $charactersLength - 1)];

    return $randomString;*/
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
function session(){
    return new \Iesod\Session();
}
function sessionId(){
    return (new \Iesod\Session())->getId();
}
function sessionCreateId($cloneData = false){
    return (new \Iesod\Session())->createId($cloneData);
}
function sessionClose(){
    return (new \Iesod\Session())->close();
}
function sessionGetData($name = null){
    return (new \Iesod\Session())->get($name);
}
function sessionPut($value, $name = null, $forceType = false){
    return (new \Iesod\Session())->put($value, $name, $forceType);
}
function sessionPutAppend($value, $name, $forceType = false){
    return (new \Iesod\Session())->putAppend($value, $name, $forceType);
}
function csrfInput(){
    return '<input type="hidden" name="csrf-token" value="KbyUmhTLMpYj7CD2di7JKP1P3qmLlkPt" />';

}
function csrfGet(){
    $csrf = $_COOKIE["CSRF-TOKEN"]?? null;

    if(empty($csrf) || is_null($csrf))
      $csrf = csrfCreate();

    return $csrf;
}
function csrfCheck(){
    $domainsCSRF = env('DOMAINS_CSRF','*');
    if($domainsCSRF=='*')
        return true;
    $domainsCSRF = explode(',',$domainsCSRF);
    $publicCSRF = env('PUBLIC_CSRF','');
    $csrf = csrfGet();
    $h = getallheaders();

    $csrfRequest = $_POST['csrf-token']?? null;
    if( is_null($csrfRequest) )
      $csrfRequest = $h['X-CSRF-TOKEN']?? null;
    
    if ($csrfRequest===$csrf){
        header('Access-Control-Allow-Origin: *');
        return true;
    } else {
        if(\Iesod\Router::getMethod()=="GET"){
            header('Access-Control-Allow-Origin: *');
           return true;
        } else {
            $referer = $_SERVER['HTTP_REFERER']?? '';
            preg_match("/^([h][t][t][p][s]{0,1})[:][\/]{2}([^\/]+)(\/.*)/",$referer,$m);
            $scheme = $m[1]?? 'http';
            $domain = $m[2]?? 'none';
            foreach($domainsCSRF as $domainAllow){
                if($domain==$domainAllow)
                    return true;
            }
            if($publicCSRF==$csrfRequest){
                return true;
            }
        }
        http_response_code(400);
        exit;
    }

    header('Access-Control-Allow-Origin: *');
    return true;
}
function csrfCreate($length = 32){
    $csrf = randomString($length);
    setcookie('CSRF-TOKEN', $csrf , 0,"/");
    return $csrf;
    /**
    cookieName: 'CSRF-TOKEN',
    headerName: 'X-CSRF-TOKEN',*/
}
/**
* Converte string em numero(inteiro ou float)
* 
* @param string|number $value Numero de entrada
* @param boolean $float DEFAULT=true; Se retorna Float ou Int
* @param int $dec DEFAULT=2; Se $float==true: Precisao de casas decimais
* @param boolean $unsigned DEFAULT=false; Sem sinal de negativo
*
* @return int|float
*/
function formatNum($value, $float = true,$dec = 2,$unsigned = false)
{
  $value = str_replace(",",".",$value);
  if($unsigned){
    $value = preg_replace('/[^0-9.]/', '',$value);
  } else {
    $value = preg_replace('/[^0-9.-]/', '',$value);
  }

  preg_match("/^(([-]{0,1}[0-9]+)([.]([0-9]+))?)/", $value, $matches);

  if($float){
    if(is_null($dec) || !is_int($dec)){
      $value = $matches[1];// -1111.222222
    } else {
      $value = $matches[2];// -1111
      if(isset($matches[4]) && !is_null($matches[4]))// 222222
        $value .= ".".substr($matches[4],0,$dec);
    }
    $value = floatval($value);
  } else {
    $value = intval($matches[2]);// -1111
  }

  return $value;
}