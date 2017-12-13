<?php namespace Iesod\Database;

use Iesod\Request;
use Iesod\RequestInterface;
use Iesod\Request\RequestException;

class Model implements ModelInterface {
    protected $table;
    protected $primaryKey;
    protected $connectionId;
    private $id;
    private $fields = [];
    private $exceptions = [];
    
    public function __construct($id = null){
        $this->id = $id;
        $this->fields = $this->getFieldsFromTable();
        if(!is_null($this->primaryKey))
            $this->fields[ $this->primaryKey ]->inRequest( false );
    }
    public function getTable(){
        return $this->table;
    }
    public function getPrimaryKey(){
        return $this->primaryKey;
    }
    public function getId(){
        return $this->id;
    }
    public function getConnectionId(){
        return $this->connectionId;
    }
    /**
     * 
     * @return boolean
     */
    public function isTable(){
        $table = $this->getTable();
        return Query::isTable($table, $this->getConnectionId());
    }
    /**
     * 
     * @return boolean|\Iesod\Database\Field[]
     */
    public function getFieldsFromTable(){
        $table = $this->getTable();
        return Query::getFieldsFromTable($table,$this->getConnectionId() );
    }
    /**
     * 
     * @return array|boolean|\Iesod\Database\Field[]
     */
    public function getFields(){
        return $this->fields;
    }
    static function getFieldsNameValue(){
        $m = new static();
        $fields = $m->getFields();
        if(!$fields)
            return false;
        
        $data = [];
        foreach($fields as $i=>$field){
            $data[$i] = $field->getDefault();
        }

        return $data;
    }
    /**
     * 
     * @param string $fieldName
     * @return boolean|\Iesod\Database\Field
     */
    public function getField($fieldName){
        return $this->fields[$fieldName]?? false ;
    }
    public function setField($fieldName,$params = []){
        if(isset($this->fields[$fieldName])){
            if(isset($params['default']))
                $this->fields[$fieldName]->setDefault($params['default']);
            if(isset($params['label']))
                $this->fields[$fieldName]->setLabel($params['label']);
            if(isset($params['type']))
                $this->fields[$fieldName]->setType($params['type']);
            if(isset($params['pattern']))
                $this->fields[$fieldName]->setPattern($params['pattern']);
            if(isset($params['required']))
                $this->fields[$fieldName]->setRequired($params['required']);
                                
            return $this;
        } else {
            return false;
        }
    }
    /**
     *
     * @param RequestInterface $request
     * @param string $strMessage Template of exceptionMessage.User tags: {name} | {label} | {table} | {pattern} Exemple: {label} is invalid!
     * @throws DatabaseException
     * @return boolean|\Iesod\Database\Field
     */
    public function validate(RequestInterface $request = null,$strMessage = null){
        if(is_null($request))
            $request = new Request();
            
            $this->exceptions = [];
            $valid = true;
            foreach($this->getFields() as $fieldName=>$field){
                if( $field->inRequest() ){
                    try {
                        $value = $request->post($fieldName);
                        
                        if($field->isRequired())
                            $value->required($strMessage);
                            
                            $field->validate($value->value(),$strMessage);
                    } catch (DatabaseException $e) {
                        $this->exceptions[ $fieldName ] = [
                            $e->getCode(),
                            $e->getMessage(),
                            'DatabaseException'
                        ];
                        $valid = false;
                    } catch (RequestException $e){
                        $this->exceptions[ $fieldName ] = [
                            $e->getCode(),
                            $e->getMessage(),
                            'RequestException'
                        ];
                        $valid = false;
                    }
                }
            }
            
            if(!$valid){
                throw new DatabaseException("Invalid!", DatabaseException::ERROR_INVALID);
                return false;
            }
            
            return $this;
    }
    static function Build($id = null){
        
        $m = new static($id);
        $r = new class($m) extends Build{
            public $Model;
            public function __construct($Model){
                $this->Model = $Model;
            }
            public function afterInsert($id = null, $data = []){
                $this->Model->afterInsert($id, $data);
            }
            public function beforeInsert(&$data = []){
                $this->Model->beforeInsert($data);
            }
            public function afterUpdate($id = null, $data = []){
                $this->Model->afterUpdate($id, $data);
            }
            public function beforeUpdate($id = null, &$data = []){
                $this->Model->beforeUpdate($id, $data);
            }
            public function afterDelete($id = null){
                $this->Model->afterDelete($id, $data);
            }
            public function beforeDelete($id = null){
                $this->Model->beforeDelete($id, $data);
            }
        };
        $r->connectionId = $m->getConnectionId();
        $r->from[] = $m->getTable();
        $r->primaryKey = $m->getPrimaryKey();
        $r->id = $m->getId();
        if(!is_null($r->id))
            $r->where($r->primaryKey, '=', $r->id);
        
        return $r;
    }
    /**
     * 
     * @param array $columns
     * @return \Iesod\Database\Build
     */
    static function select($columns = ['*']){
        $columns = is_array($columns) ? $columns : func_get_args();
        $r = static::Build();
        
        return $r->select($columns);
    }
    static function Raw($value){
        return new Raw($value);
    }
    /**
     * 
     * @param string $table
     * @param string $first
     * @param string $operator = > < <> ...
     * @param string $secund
     * @return \Iesod\Database\Build
     */
    static function joinLeft($table, $first, $operator , $secund){
        $r = static::Build();
        return $r->joinLeft($table, $first, $operator , $secund);
    }
    static function joinInner($table, $first, $operator , $secund){
        $r = static::Build();
        return $r->joinInner($table, $first, $operator , $secund);
    }
    static function whereId($id){
        return static::Build($id);
    }
    /**
     * 
     * @param string $first
     * @param string $operator = > < <> ...
     * @param string $secund
     * @return \Iesod\Database\Build
     */
    static function where($first, $operator , $secund){
        $r = static::Build();
        return $r->where($first, $operator , $secund);
    }
    static function whereExpression($expression, $bindData = null){
        $r = static::Build();
        return $r->whereExpression($expression,$bindData);
    }
    static function whereIsNull($field){
        $r = static::Build();
        return $r->whereIsNull($field);
    }
    static function whereIsNotNull($field){
        $r = static::Build();
        return $r->whereIsNotNull($field);
    }
    static function whereRaw($where){
        $r = static::Build();
        return $r->whereRaw($where);
    }
    
    static function order($field, $order = "ASC"){
        $r = static::Build();
        return $r->order($field, $order);
    }
    static function groupby($field){
        $r = static::Build();
        return $r->groupby($field);
    }
    static function find($id,$fetch_style = null){
        $r = static::Build($id);
        return $r->find($id,$fetch_style);
    }
    static function first($fetch_style = null){
        $r = static::Build();
        
        return $r->first($fetch_style);
    }
    static function get(){
        $r = static::Build();
        return $r->get();
    }

    public function afterInsert($id = null, $data = []){
        
    }
    public function beforeInsert(&$data = []){

    }
    public function afterUpdate($id = null, $data = []){
        
    }
    public function beforeUpdate($id = null, &$data = []){

    }
    public function afterDelete($id = null){
        
    }
    public function beforeDelete($id = null){

    }
    public static function insert($data,$returnInsertId = true){
        $r = static::Build();
        $result = $r->insert($data,$returnInsertId);
        return $result;
    }
    public static function update($data,$id = null){        
        $r = static::Build($id);
        $result = $r->update($data);
        return $result;
    }
}