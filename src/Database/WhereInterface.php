<?php

namespace Iesod\Database;

interface WhereInterface
{
  public function __construct($where = null, $bindData = []);
  public function getWhere();
  public function getBindData();
  public function where($field,$operator,$secund = null,$third = null);
  public function whereExpression($expression, $bindData = null);
  public function whereIsNull($field);
  public function whereIsNotNull($field);
  public function whereRaw($where);
  public function transform();
}