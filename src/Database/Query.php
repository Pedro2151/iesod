<?php namespace Iesod\Database;

class Query {
    /**
     * 
     * @var \PDO $connections
     */
    static $connections = [];
    static $cfgConnections = [];
    static $tables = [];
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
            self::$connections[$connectionsId]->exec("SET time_zone = 'America/Sao_Paulo'");
        } catch (\PDOException $e) {
            self::$connections[$connectionsId] = null;
            throw new \Exception("Connection failed: " . utf8_encode( $e->getMessage() ).".");
            return false;
        }
        
        return self::$connections[$connectionsId];
    }
    static public function beginTransaction($connectionsId = null){
        $pdo = static::connect($connectionsId);
        return $pdo->beginTransaction();
    }
    static public function commit($connectionsId = null){
        $pdo = static::connect($connectionsId);
        return $pdo->commit();
    }
    static public function rollBack($connectionsId = null){
        $pdo = static::connect($connectionsId);
        return $pdo->rollBack();
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
        $debug = false;
        $fileDebug = "/home/jonas/Public/debug.sql";
        // DEBUG
        if ($debug) {
            $f = fopen($fileDebug, 'a');
            fwrite($f, "\n# SQL\n");
            fwrite($f, $sql . "\n");
        }
        // END DEBUG */
        $timeStart = microtime(true);
        if(is_null($bindData) || empty($bindData)){
            if(!$query = $pdo->query($sql)){
				list($handle, $codError, $StrError) = $pdo->errorInfo();
				if ($debug) {
                    $timeEnd = microtime(true);
                    fwrite($f, "# ERROR: {$codError} - {$StrError}\n");
                    fwrite($f, "# timeExec: " . ($timeEnd - $timeStart) . "\n");
                    fclose($f);
                }
				throw new \Exception("Error: #{$codError}: {$StrError}<br />\r\n".$sql,$codError);
                return false;
            } elseif ($debug) {
                $timeEnd = microtime(true);
                fwrite($f, "# timeExec: " . ($timeEnd - $timeStart) . "\n");
                fclose($f);
            }
        } else {
            $query = $pdo->prepare($sql);
            if ($debug) {
                foreach ($bindData as $parameter=>$value){
                    fwrite($f, "# {$parameter} = '{$value}'\n");
                }
            }
            
            if(!$query->execute( $bindData )){
                list($handle, $codError, $StrError) = $query->errorInfo();
				if ($debug) {
                    $timeEnd = microtime(true);
                    fwrite($f, "\n# ERROR: {$codError} - {$StrError}\n");
                    fwrite($f, "# timeExec: " . ($timeEnd - $timeStart) . "\n");
                    fclose($f);
                }
                throw new \Exception("Error: #{$codError}: {$StrError}<br />\r\n".$query->queryString,$codError);
                return false;
            } elseif ($debug) {
                $timeEnd = microtime(true);
                fwrite($f, "# timeExec: " . ($timeEnd - $timeStart) . "\n");
                fclose($f);
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
        $i = 0;
        foreach ($data as $field=>$value){
            $f = explode(".",$field);
            if(count($f)==2){
                $Field = "`{$f[0]}`.`{$f[1]}`";
            } else {
                $Field = "`{$f[0]}`";
            }
            $bindName = "f{$i}";

            $fields .= "{$sep}{$Field}";
            if( is_null($value) ){
                $values .= "{$sep}NULL";
            } else {
                if(is_object($value) && get_class($value)=='Iesod\Database\Raw'){
                    $values .= "{$sep}".$value->value;
                } else {
                    $values .= "{$sep}:{$bindName}";
                    $bindData[':'.$bindName] = $value;
                    $i++;
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
        $i = 0;
        foreach ($data as $field=>$value){
            $f = explode(".",$field);
            if(count($f)==2){
                $Field = "`{$f[0]}`.`{$f[1]}`";
            } else {
                $Field = "`{$f[0]}`";
            }
            $bindName = "f{$i}";
            if( is_null($value) ){
                $values .= "{$sep}{$Field} = NULL";
            } else {
                if(is_object($value) && get_class($value)=='Iesod\Database\Raw'){
                    $values .= "{$sep}{$Field} = ".$value->value;
                } else {
                    $values .= "{$sep}{$Field} = :{$bindName}";
                    $bindData[':'.$bindName] = $value;
                    $i++;
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
        $Where = new Where($where, $bindData);
        $where = $Where->transform();
        $bindData = $Where->getBindData();

        return $where;
    }
    static public function isTable($table,$connectionsId = null){
        if (isset(static::$tables[$connectionsId])) {
            if (in_array($table, static::$tables[$connectionsId])) {
                return true;
            }
        }
        // OLD: SHOW TABLES LIKE '{$table}'
        $result = static::query("SHOW TABLES",null,$connectionsId);
        static::$tables[$connectionsId] = [];
        while($row = $result->fetch()) {
            static::$tables[$connectionsId][] = $row[0];
        }
        return in_array($table, static::$tables[$connectionsId]);
    }
    static public function getFieldsFromTable($table,$connectionsId = null, $arrayNames = false){
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
            if ($arrayNames) {
                $fields = $f['name'];
            } else {
                $fields[ $f['name'] ] = new Field( $f );
            }
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