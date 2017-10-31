<?php namespace Iesod;

class Controller{
    private $request;
    public function __construct(){
	    
	}
	public static function returnAjax($data,$strError = "",$codeError = 0,$status = true){
	    $r = [
	        'status' => $status,
	        'strError' => $strError,
	        'codeError' => $codeError,
	        'data' => $data
	    ];
	    
	    return $r;
	}
	public static function returnAjaxError($strError,$codeError = 0, $data = []){
	    return static::returnAjax($data,$strError,$codeError,false);
	}
	/**
	 * 
	 * @return \Iesod\Request
	 */
	public function request(){
	    if(is_null($this->request))
	       $this->request = new Request();
	    
	    return $this->request;
	}
}