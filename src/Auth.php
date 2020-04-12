<?php namespace Iesod;

use Iesod\Database\Model;


class Auth {
    const USERGROUP_USER = 0;
    const USERGROUP_ADMIN = 1;
    
    static $AuthData;
    /**
     * @var null|boolean NULL - Caso nao verificado / TRUE - Se autenticado / FALSE - Se nao
     */
    static $Authenticate;
    private $User;
    
    public function __construct(UserInterface $User){
        $this->User = $User;
    }
    /** Get user data authenticated | Pegar dados de usuário autenticado
     * 
     * @param boolean $force Force refresh
     * @return \Iesod\AuthUser|boolean False if fail
     */
    static function getUser($force = false){
        
        
        if(!$force && !is_null(static::$AuthData)) {
            static::$Authenticate = true;
            return ( new AuthUser( static::$AuthData ) );
        }
        
        if (static::$Authenticate===false) {
            return false;
        }

        $AuthData = \Iesod\Session::get('authData');

        if (is_null($AuthData)) {
            static::$AuthData = null;
            static::$Authenticate = false;
            return false;
        } else {
            static::$AuthData = $AuthData;
            static::$Authenticate = true;
            return ( new AuthUser( $AuthData ) );
        }
    }
    
    static function getUserId($default = null){
        $AuthUser = static::getUser();
        return ($AuthUser===false) ? $default : $AuthUser->getId() ;
    }
    
    static function getUserGroup($default = -1){
        $AuthUser = static::getUser();
        return ($AuthUser===false) ? $default : $AuthUser->getUserGroup() ;
    }
    /** Execute signIn
     * 
     * @param string $username
     * @param string $password
     * @throws AuthException
     * @return boolean
     */
    public function login($username,$password)
    {
        $data = $this->User->whereLogin($username)
                        ->first();
        if($data == false) {
            throw new AuthException(
                "Usuário não encontrado",
                AuthException::E_USER_NOT_FOUND
            );
            return false;
        }
        
        if($data['active'] == 0){//0 - No / 1 - Yes
            throw new AuthException(
                "Cadastro inativo",
                AuthException::E_USER_UNVERIFICATION
            );
            return false;
        }
        
        if(!checkHash($password, $data['password'])){
            throw new AuthException(
                "Usuário ou Senha incorretos",
                AuthException::E_PASSWORD_INVALID
            );
            return false;
        }
        
        //SIGNIN ------------------
        $AuthData = [
            'id_user' => $data['id'],
            'active' => $data['active'] ?? 0,
            'usergroup' => $data['usergroup']?? 0,
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            /* 'phone' => $data['phone'] */
        ];
        
        \Iesod\Session::set('authData', $AuthData);
        
        static::$AuthData = $AuthData;
        //SIGNIN ------------------
        return true;
    }
    /** Execute logout and Clear cookie of session
     * 
     */
    static public function logout(){
        \Iesod\Session::close();
    }
}

/** Get user data authenticated | Pegar dados de usuário autenticado
 * 
 * @param boolean $force Force refresh
 * @return \Iesod\AuthUser|boolean False if fail
 */
function getUserAuth($force = false){
    return Auth::getUser($force);
}