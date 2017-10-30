<?php namespace Iesod\Database;

class WhereExpression{
    public $expression;
    public $bindData;
    public function __construct($expression,$bindData = null){
        $this->expression = $expression;
        $this->bindData = $bindData;
    }
}