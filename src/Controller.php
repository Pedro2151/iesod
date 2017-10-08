<?php namespace Iesod;

class Controller{
    private $request;
    public function __construct(){
	    
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