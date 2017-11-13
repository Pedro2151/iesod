<?php namespace Iesod\Database;

class Build {
    public $primaryKey;
    public $id;
    public $connectionId;
    public $from = [];
    public $columns = [];
    public $join = [];
    public $where = [];
    public $order = [];
    public $start;
    public $limit;
    /**
     * 
     * @param array $columns
     * @return \Iesod\Database\Build
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    public function id($id){
        $this->id = $id;
        return $this;
    }
    public function joinLeft($table, $first, $operator , $secund){
        $this->join[] = "LEFT JOIN {$table} ON {$first} {$operator} {$secund}";
        return $this;
    }
    public function joinInner($table, $first, $operator , $secund){
        $this->join[] = "INNER JOIN {$table} ON {$first} {$operator} {$secund}";
        return $this;
    }
    public function where($field,$operator,$secund = null,$third = null){
        $this->where[] = [$field,$operator,$secund,$third];
        return $this;
    }
    public function whereExpression($expression, $bindData = null){
        $this->where[] = new WhereExpression($expression, $bindData);
        return $this;
    }
    public function whereIsNull($field){
        return $this->where($field,"IS NULL");
    }
    public function whereIsNotNull($field){
        return $this->where($field,"IS NOT NULL");
    }
    public function whereRaw($where){
        $this->where[] = new Raw($where);
        return $this;
    }
    static function Raw($value){
        return new Raw($value);
    }
    public function order($field, $order = "ASC"){
        $this->order[] = "`{$field}` {$order}";
        return $this;
    }
    /** Execulta select
     * 
     * @return boolean|\PDOStatement
     */
    public function get(){
        $bindData = [];
        $sql = "SELECT ";
        if(empty($this->columns)){
            $sql .= "*";
        } else { 
			$sep = '';
			foreach($this->columns as $col){
				if(is_object($col) && get_class($col)=='Iesod\Database\Raw')
					$sql .= $sep.$col->value;
				else
					$sql .= $sep.$col;
				$sep = ',';
			}
        }
        $sql .= " FROM ";
        $sql .= implode(",", $this->from)." ";
        $sql .= implode(" \n", $this->join)." ";
		$where = $this->where;
		
		if(!is_null($this->id))
			$where[] = [$this->primaryKey, '=' , $this->id] ;
		
		
        if(!empty($where)){
            $sql .= "WHERE 1 AND ";
            $sql .= Query::whereTransform($where,$bindData)." ";
        }
        if(!empty($this->order)){
            $sql .= "ORDER BY ";
            $sql .= implode(",", $this->order)." ";
        }
        if(!is_null($this->start) && !is_null($this->limit)){
            $sql .= "LIMIT {$this->start},{$this->limit} ";
        }
        return Query::query($sql,$bindData,$this->connectionId);
    }
    public function find($id = null,$fetch_style = null){
        $id = $id ?? $this->id;
        $this->where($this->primaryKey, '=', $id);
        $result = $this->get();
        if($result==false || $result->rowCount()==0)
            return false;
        
        return $result->fetch(  $fetch_style??\PDO::FETCH_ASSOC );
    }
    public function first($fetch_style = null){
        $start = $this->start;
        $limit = $this->limit;
        $this->start = 0;
        $this->limit = 1;
        
        $result = $this->get();
        
        $this->start = $start;
        $this->limit = $limit;
        
        if($result->rowCount()==0)
            return false;
            
        return $result->fetch( $fetch_style??\PDO::FETCH_ASSOC );
    }
    public function insert($data,$returnInsertId = true){
        $result = Query::insert($data, implode(",", $this->from),$this->connectionId,$returnInsertId);
        if($returnInsertId && $result!=false){
            $this->id = $result;
            return $this->id;
        } else {
            return $result;
        }
    }
    public function update($data){
        if(!is_null($this->start) && !is_null($this->limit)){
            $limit = "{$this->start},{$this->limit}";
        } else {
            $limit = null;
        }
        
        return Query::update($data,implode(",", $this->from), $this->where,$limit,implode(",", $this->order),$this->connectionId);
    }
    public function delete(){
        if(!is_null($this->start) && !is_null($this->limit)){
            $limit = "{$this->start},{$this->limit}";
        } else {
            $limit = null;
        }

        return Query::delete(implode(",", $this->from),$this->where,$limit,implode(",", $this->order),$this->connectionId);
    }
}
