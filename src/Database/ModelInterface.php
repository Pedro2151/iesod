<?php namespace Iesod\Database;

use Iesod\RequestInterface;

interface ModelInterface {
    protected $table;
    protected $primaryKey;
    protected $connectionId;
    private $id;
    private $fields = [];
    private $exceptions = [];    
    public function __construct($id = null);
    public function getTable();
    public function getPrimaryKey();
    public function getId();
    public function getConnectionId();
    public function isTable();
    public function getFieldsFromTable();
    public function getFields();
    public function getField($fieldName);
    public function setField($fieldName,$params = []);
    public function validate(RequestInterface $request = null,$strMessage = null);
    static function Build($id = null);
    static function select($columns = ['*']);
    static function Raw($value);
    static function joinLeft($table, $first, $operator , $secund);
    static function joinInner($table, $first, $operator , $secund);
    static function whereId($id);
    static function where($first, $operator , $secund);
    static function whereIsNull($field);
    static function whereIsNotNull($field);
    static function whereRaw($where);
    static function order($field, $order = "ASC");
    static function find($id);
    static function get();
    static function insert($data);
    static function update($data,$id = null);
}