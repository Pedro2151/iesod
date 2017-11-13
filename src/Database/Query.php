<?php namespace Iesod\Database;

class Query {
    /**
     * 
     * @var \PDO $connections
     */
    static $connections = [];
    static $cfgConnections = [];
    
    /**
     * 
     * @param string $dbname Nome do banco de dados
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @param string $host Hostname do banco de dados
     * @param string $port Porta do banco DEFAULT=NULL (3306)
     * @param string $username Usuario do Banco
     * @param string $password Senha do usuario
     * @param string $driver Driver de accesso ao banco DEFAULT NULL (Somente: mysql)
     * @throws \Exception
     * @return boolean
     */
    static function setConnection($dbname,$connectionsId = null,$host = NULL,$port=NULL,$username = NULL,$password = NULL,$driver = NULL){
        $connectionsId = $connectionsId??0;
        $driver = $driver??'mysql';
        $host = $host??'localhost';
        $port = $port??'3306';
        $username = $username??'root';
        $password = $password??'';
        
        $driversAceitos = array('mysql');
        if(!in_array($driver,$driversAceitos)){
            throw new \Exception("Driver Driver '{$driver}' not accepted.");
            return false;
        }
        
        self::$cfgConnections[$connectionsId] = array(
            'driver'=>$driver,
            'host'=>$host,
            'port'=>$port,
            'dbname'=>$dbname,
            'username'=>$username,
            'password'=>$password
        );
        return true;
    }
    /**
     * 
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @param boolean $autoConnect Connect if not conected
     * @throws \Exception
     * @return \PDO|boolean
     */
    static public function connect($connectionsId = null, $autoConnect = true){
        $connectionsId = $connectionsId??0;
        
        if(isset(self::$connections[$connectionsId]))
            return self::$connections[$connectionsId];
        
        if(!$autoConnect)
            return false;
        
        if(!isset(self::$cfgConnections[$connectionsId])){
            throw new \Exception("Banco de dados não configurado");
            return false;
        }
        
        $driver = self::$cfgConnections[$connectionsId]['driver'];
        $host = self::$cfgConnections[$connectionsId]['host'];
        $port = self::$cfgConnections[$connectionsId]['port'];
        $dbname = self::$cfgConnections[$connectionsId]['dbname'];
        $username = self::$cfgConnections[$connectionsId]['username'];
        $password = self::$cfgConnections[$connectionsId]['password'];
        
        $options = array();
        $options[\PDO::ATTR_PERSISTENT] = false;
        if($driver=="mysql"){
            //$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";
            $options[1002] = "SET NAMES 'UTF8'";
        }
        
        try {
            self::$connections[$connectionsId] = new \PDO(
                "{$driver}:host={$host};port={$port};dbname={$dbname};charset=UTF8",
                $username,
                $password,
                $options
            );
        } catch (\PDOException $e) {
            self::$connections[$connectionsId] = null;
            throw new \Exception("Connection failed: " . utf8_encode( $e->getMessage() ).".");
            return false;
        }
        
        return self::$connections[$connectionsId];
    }
    /** Execulta query sql
     * 
     * @param string $sql
     * @param array $bindData Array com os valores para o bind.
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @throws \Exception
     * @return boolean|\PDOStatement
     */
    static public function query($sql,$bindData = null,$connectionsId = null){
        $pdo = static::connect($connectionsId);
        
        if(is_null($bindData) || empty($bindData)){
            if(!$query = $pdo->query($sql)){
				list($handle, $codError, $StrError) = $pdo->errorInfo();
				
				throw new \Exception("Error: #{$codError}: {$StrError}<br />\r\n".$sql,$codError);
                return false;
            }
        } else {
            $query = $pdo->prepare($sql);
            $data = '';
			foreach ($bindData as $parameter=>$value){
				$data .= "{$parameter} = '{$value}'\r\n";
            }
            
            if(!$query->execute( $bindData )){
                list($handle, $codError, $StrError) = $query->errorInfo();
				
                throw new \Exception("Error: #{$codError}: {$StrError}<br />\r\n".$query->queryString,$codError);
                return false;
            }
        }
		
        return $query;
    }
    
    /**
     * 
     * @param string $table
     * @param string $fields
     * @param string|array $where
     * @param string $limit Limit(like sql) DEFAULT=NULL
     * @param string $orderBy Order By(Like sql) DEFAULT=NULL
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @return mixed|array|boolean False if Fail
     */
    static public function getData($table, $fields = null, $where = null,$limit = null,$orderBy = null,$connectionsId = null){
        $sql = "SELECT ".(is_null($fields)?'*':$fields)." FROM `{$table}`";
        $bindData = [];
        if(!is_null($where) && !empty($where)){
            $sql .= " WHERE ".static::whereTransform($where, $bindData);
        }
        
        if(!is_null($orderBy)){
            $sql .= " ORDER BY {$orderBy}";
        }
        if(!is_null($limit) && preg_match('/^([0-9]+)\s*([,]\s*[0-9]+)?$/', $limit)){
            $sql .= " LIMIT {$limit}";
        }
        
        if($result = static::query($sql,$bindData,$connectionsId)){
            if($result->rowCount()==1){
                return $result->fetch(\PDO::FETCH_ASSOC);
            } elseif($result->rowCount()>1){
                return $result->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } else {
            return false;
        }
    }
    /**
     * 
     * @param array $data
     * @param string $table
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @param boolean $returnInsertId
     * @return int|boolean Id autoIncrement / False if Fail
     */
    static public function insert($data,$table,$connectionsId = null,$returnInsertId = true){
        $bindData = [];
        $fields = "";
        $values = "";
        $sep = "";
        foreach ($data as $field=>$value){
            $fields .= "{$sep}`{$field}`";
            if( is_null($value) ){
                $values .= "{$sep}NULL";
            } else {
                if(is_object($value) && get_class($value)=='Iesod\Database\Raw'){
                    $values .= "{$sep}".$value->value;
                } else {
                    $values .= "{$sep}:{$field}";
                    $bindData[':'.$field] = $value;
                }
                
            }
            $sep = ",";
        }
        
        $result = static::query("INSERT INTO `{$table}` ({$fields}) VALUES ({$values})",$bindData,$connectionsId);
        if($returnInsertId)
            return static::lastInsertId($connectionsId);
        else 
            return $result;
    }
    /**
     * 
     * @param array $data
     * @param string $table
     * @param string|array $where
     * @param string $limit Limit(like sql) DEFAULT=NULL
     * @param string $orderBy Order By(Like sql) DEFAULT=NULL
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @return boolean|\PDOStatement
     */
    static public function update($data,$table,$where,$limit = null,$orderBy = null,$connectionsId = null){
        $bindData = [];
        $values = "";
        $sep = "";
        foreach ($data as $field=>$value){
            if( is_null($value) ){
                $values .= "{$sep}`{$field}` = NULL";
            } else {
                if(is_object($value) && get_class($value)=='Iesod\Database\Raw'){
                    $values .= "{$sep}`{$field}` = ".$value->value;
                } else {
                    $values .= "{$sep}`{$field}` = :{$field}";
                    $bindData[':'.$field] = $value;
                }
                
            }
            $sep = ",";
        }
        
        $Where = static::whereTransform($where, $bindData);
        
        $sql = "UPDATE `{$table}` SET {$values} WHERE {$Where}";
        if(!is_null($orderBy) && !empty($orderBy)){
            $sql .= " ORDER BY {$orderBy}";
        }
        if(!empty($limit) && preg_match('/^([0-9]+)\s*([,]\s*[0-9]+)?$/', $limit)){
            $sql .= " LIMIT {$limit}";
        }
        
        return static::query($sql,$bindData);
    }
    /**
     * 
     * @param string $table
     * @param string|array $where
     * @param string $limit Limit(like sql) DEFAULT=NULL
     * @param string $orderBy Order By(Like sql) DEFAULT=NULL
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @return boolean|\PDOStatement
     */
    static function delete($table,$where,$limit = null,$orderBy = null,$connectionsId = null){
        $bindData = [];
        
        $Where = static::whereTransform($where, $bindData);
        
        $sql = "DELETE FROM `{$table}` WHERE {$Where}";
        if(!is_null($orderBy) && !empty($orderBy)){
            $sql .= " ORDER BY {$orderBy}";
        }
        if(!empty($limit) && preg_match('/^([0-9]+)\s*([,]\s*[0-9]+)?$/', $limit)){
            $sql .= " LIMIT {$limit}";
        }
        
        return static::query($sql,$bindData);
    }
    static function whereTransform($where, &$bindData = []){
        $Where = '';
        if(is_array($where)){
            $sep = "";
            foreach ($where as $w){
                if(is_object($w) && get_class($w)=='Iesod\Database\Raw'){
                    $Where .= "{$sep}".$w->value;
                } elseif(is_object($w) && get_class($w)=='Iesod\Database\WhereExpression'){
                    $Where .= "{$sep}(".$w->expression.")";
                    $bindData = array_merge($bindData, $w->bindData);
                } else {
                    if(isset($w[2]) && !is_null($w[2]) ){
                        if($w[1]=='BETWEEN'){
                            $Where .= "{$sep}(`{$w[0]}` {$w[1]} ";
                            $Sep = "";
                            if(!isset($w[3]))
                                throw new \Exception("Third index undefined in where");
                                
                                for($i=2;$i<=3;$i++){
                                    if(is_object($w[$i]) && get_class($w[$i])=='Iesod\Database\Raw'){
                                        $Where .= $Sep.$w[$i]->value;
                                    } else {
                                        $nameBind = ":w".($i-2)."_".$w[0];
                                        $Where .= $Sep."'{$nameBind}'";
                                        $bindData[$nameBind] = $w[$i];
                                    }
                                    $Sep = " AND ";
                                }
                                $Where .= ")";
                        } else {
                            if(is_object($w[2]) && get_class($w[2])=='Iesod\Database\Raw'){
                                $Where .= "{$sep}`{$w[0]}` {$w[1]} ".$w[2]->value;
                            } else {
                                $Where .= "{$sep}`{$w[0]}` {$w[1]} :w_{$w[0]}";
                                $bindData[':w_'.$w[0] ] = $w[2];
                            }
                        }
                    } else {//IS NULL ...
                        $Where .= "{$sep}`{$w[0]}` {$w[1]}";
                    }
                }
                $sep = " AND ";
            }
        } else {
            $Where = $where;
        }
        
        return $Where;
    }
    static public function isTable($table,$connectionsId = null){
        if($result = static::query("SHOW TABLES LIKE '{$table}'",null,$connectionsId)){
            return ( $result->rowCount()>0 );
        } else {
            return false;
        }
    }
    static public function getFieldsFromTable($table,$connectionsId = null){
        if(is_null($table) || !static::isTable($table,$connectionsId)){
            throw new \Exception("Table '{$table}' not exists");
            return false;
        }
        //-------------------------------------------------//
        $result = static::query("SELECT * FROM `{$table}` WHERE 0 LIMIT 1",null,$connectionsId);
        $c = $result->columnCount();
        $fields = [];
        
        for($i=0;$i<$c;$i++){
            $f = $result->getColumnMeta($i);
            $fields[ $f['name'] ] = new Field( $f );
        }
        
        return $fields;
    }
    /**
     * 
     * @param int $connectionsId Id da connection DEFAULT=NULL
     * @return boolean|string
     */
    static public function lastInsertId($connectionsId = null,$name=null){
        $pdo = static::connect($connectionsId);
        
        return ((!$pdo)? false : $pdo->lastInsertId($name));
    }
}