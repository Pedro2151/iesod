<?php namespace Iesod\Database;

interface BuildInterface {
    /*
    public $primaryKey;
    public $id;
    public $connectionId;
    public $from = [];
    public $columns = [];
    public $join = [];
    public $where = [];
    public $order = [];
    public $start;
    public $limit; */
    
    public function select($columns = ['*']);
    public function joinLeft($table, $first, $operator , $secund);
    public function joinInner($table, $first, $operator , $secund);
    public function where($field,$operator,$secund = null,$third = null);
    public function whereIsNull($field);
    public function whereIsNotNull($field);
    public function whereRaw($where);
    static function Raw($value);
    public function order($field, $order = "ASC");
    public function get();
    public function find($id = null);
    public function first();
    public function insert($data);
    public function update($data);
}
