<?php namespace Iesod;

use Iesod\Database\Model;

class Session implements \Serializable {
    static $id;
    static $data;
    static $dataSerialized = "";
    public function __construct() {
		$idSession = null;
		if(isset($_COOKIE["IESOD_SESSION"]) && !empty($_COOKIE["IESOD_SESSION"])){
			$idSession = $_COOKIE["IESOD_SESSION"];
		}
		if(isset($_GET["IESOD_SESSION"]) && !empty($_GET["IESOD_SESSION"])){
			$idSession = $_GET["IESOD_SESSION"];
		}
		if(isset($_POST["IESOD_SESSION"]) && !empty($_POST["IESOD_SESSION"])){
			$idSession = $_POST["IESOD_SESSION"];
		}
		
        if( is_null($idSession) ){
            $this->createId();
        } else { 
            static::$id = $idSession;
            $this->getData();
        }
    }
    private function getData($createIfNotExists = true,$force = false){
        if(!$force && !is_null(static::$data))
            return static::$data;
        
        $Model = new class() extends Model {
            protected $table = 'session';
            protected $primaryKey = 'id';
        };
        
        $result = $Model->select(['plataform', 'data'])
            ->id( $this->getId() )
            ->first();
        
        if(!$result || $result['plataform'] != md5($_SERVER['HTTP_USER_AGENT'])) {
            static::$data = [];
            if($createIfNotExists)
                $this->saveData();
        } else {
            static::$dataSerialized = $result['data'];
            $this->unserialize( $result['data'] );
        }
        
        return static::$data;
    }
    private function saveData () {
        $id = $this->getId();
        if(is_null($id))
            return false;
        
        $Model = new class() extends Model {
            protected $table = 'session';
            protected $primaryKey = 'id';
        };
        
        $result = $Model->select('id')->id( $id )->first();
			
        if(!$result){
            $browser =  get_browser($_SERVER['HTTP_USER_AGENT'],true);
            $result = $Model->insert([
                'id' => $id,
                'browser' => substr($browser['browser_name_pattern'], 254),
                'plataform' => $browser['platform'],
                /* 'browser' => substr($_SERVER['HTTP_USER_AGENT'], 0, 254),
                'plataform' => md5($_SERVER['HTTP_USER_AGENT']), */
                'data' => $this->serialize()
            ], false);
        } else {
            $serialize = $this->serialize();
            if(static::$dataSerialized != $serialize){
                $result = $Model->update(
                    [ 'data' => $serialize ],
                    $id
                );
                
                if($result!==false)
                    static::$dataSerialized = $serialize;
            }
        }
        
        return ($result!==false);
    }
    public function createId($cloneData = false){
        static::$dataSerialized = serialize([]);
        
        if($cloneData && !is_null(static::$id)){
            $this->getData(false);
        } else {
            static::$data = [];
        }
                
        $id = randomString(32);
        
        $this->close();
        
        static::$id = $id;
        setcookie('IESOD_SESSION', static::$id , 0,"/");
        $this->saveData();
        
        return $id;
    }
    public function close(){
        unset($_COOKIE['IESOD_SESSION']);
        setcookie('IESOD_SESSION', null, -1, "/");
        static::$id = null;
        static::$data = null;
        static::$dataSerialized = "";
    }
    public function serialize() {
        $data = static::$data;
        $data['IP_CLIENT'] = $_SERVER['REMOTE_ADDR'];
        return serialize($data);
    }
    /**
     * @param $serialized
     */
    public function unserialize($serialized) {
        static::$data = unserialize($serialized);
    }
    public static function getId(){
        return static::$id;
    }
    public function get($name = null,$default = null) {
        if(is_null($name))
            return static::$data;
        
        $names = explode(".", $name);
        $data = $this->getData(false,is_null(static::$data));
        foreach($names as $Name){
            if(!empty($Name)){
                if(is_array($data) && isset($data[$Name])){
                    $data = $data[$Name];
                } else {
                    return $default;
                }
            }
        }
        
        return $data;
    }
    public function put($value, $name = null,$forceType = false){
        if(is_null($name)){
            static::$data = $value;
            return true;
        }
        $data = (static::$data)?? [];
        $r = $this->_put($data, $value,$name,$forceType);
		
		static::$data = $data;
		return $r;
    }
    private function _put(&$data, $value,$name,$forceType = false){
        $names = explode(".", $name);
        $Name = '';
        while( empty($Name) && count($names)>0 ){
            $Name = array_shift($names);
        }
        
        if( empty($Name) ){
            throw new \Error("Name is empty");//Fail
            return false;
        }
        
        if( count($names)==0 ){
            $data[$Name] = $value;
            return true;
        }
        
        if( isset($data[$Name]) ){
            return $this->_put(
                $data[$Name],
                $value,
                implode(".", $names),
                $forceType);
        } else {
            if($forceType || is_array($data) || is_null($data)){
                if(!is_array($data)){
                    $data = [];
                    $data[$Name] = null;
                }
                
                return $this->_put(
                    $data[$Name],
                    $value,
                    implode(".", $names),
                    $forceType);
            } else {
                throw new \Error("Variable type error(Not array)");//Fail
                return false;
            }
        }
    }
    public function putAppend($value,$name,$forceType = false){
        try {
            $Value = $this->get($name);
            if(is_array($Value)){
                $Value[] = $value;
            } elseif(is_string($Value)){
                $Value .= $value;
            } else {
                throw new \Error("Variable type error");//Fail
                return false;
            }
        } catch (\Error $e) {
            $Value = [ $value ];
        }
        
        return $this->put($Value, $name, $forceType);
    }
    public function __destruct(){
        $this->saveData();
    }
}

