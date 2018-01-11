<?php

namespace Iesod\Database;

class Where implements WhereInterface
{
  private $where;
  private $bindData = [];
  public function __construct($where = null, $bindData = []){
    $this->where = $where;
    $this->bindData = $bindData;
  }
  public function getWhere(){
    return $this->where;
  }
  public function getBindData(){
    return $this->bindData;
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
  public function transform(){
    $bindData = $this->bindData;
    $where = $this->where;
    $Where = '';
    if(is_array($where)){
        $sep = "";
        $iBindName = 0;
        foreach ($where as $w){
            if(is_object($w) && get_class($w)=='Iesod\Database\Raw'){
                $Where .= "{$sep}".$w->value;
            } elseif(is_object($w) && get_class($w)=='Iesod\Database\WhereExpression'){
                $Where .= "{$sep}(".$w->expression.")";
                $bindData = array_merge($bindData, $w->bindData);
            } else {
                $f = explode(".",$w[0]);
                if(count($f)==2){
                  $Field = "`{$f[0]}`.`{$f[1]}`";
                } else {
                  $Field = "`{$f[0]}`";
                }
                if(isset($w[2]) && !is_null($w[2]) ){
                    if(is_null($w[1]))
                        $w[1] = "=";
                    if($w[1]=='BETWEEN'){
                        $Where .= "{$sep}({$Field} {$w[1]} ";
                        $Sep = "";
                        if(!isset($w[3]))
                            throw new \Exception("Third index undefined in where");
                            
                            for($i=2;$i<=3;$i++){
                                if(is_object($w[$i]) && get_class($w[$i])=='Iesod\Database\Raw'){
                                    $Where .= $Sep.$w[$i]->value;
                                } else {
                                    $bindName = "w{$iBindName}";
                                    $Where .= $Sep.":{$bindName}";
                                    $bindData[":{$bindName}"] = $w[$i];
                                    $iBindName++;
                                }
                                $Sep = " AND ";
                            }
                            $Where .= ")";
                    } else {
                        if(is_object($w[2]) && get_class($w[2])=='Iesod\Database\Raw'){
                            $Where .= "{$sep}{$Field} {$w[1]} ".$w[2]->value;
                        } else {
                            $bindName = "w{$iBindName}";
                            $Where .= "{$sep}{$Field} {$w[1]} :{$bindName}";
                            $bindData[":{$bindName}"] = $w[2];
                            $iBindName++;
                        }
                    }
                } else {//IS NULL ...
                    if(is_null($w[1]))
                        $w[1] = "IS NULL";

                    $Where .= "{$sep}{$Field} {$w[1]}";
                }
            }
            $sep = " AND ";
        }
    } else {
        $Where = $where;
    }
    
    $this->bindData = $bindData;
    return $Where;
  }
}