<?php namespace Iesod;

use Iesod\Database\Model;

class ConfigModel extends Model  {
    protected $table = 'configs';
    protected $primaryKey = 'id';
    static function getConfig($id,$default = null){
        $Build = static::Build($id);
        $result = $Build->first();

        if($result===false){
            return $default;
        } else {
            if(is_null($result['value']))
                return null;
            
            $value = $result['value'];
            switch($result['type']){
                case 2://JSON
                    $value = json_decode( $value, true);
                    break;
                case 3://Boolean
                    $value = (strtoupper($value)=="TRUE" || $value==1);
                    break;
                default:
                    //Outros
                    break;
            }
            return $value;
        }
    }
    static function setConfig($id, $value){
        $type = 0;//0 - String / 1 - Number / 2 - Json / 3 - Boolean
        switch( gettype($value) ){
            case "boolean":
                $value = $value?"TRUE":"FALSE";
                $type = 3;
                break;
            case "integer":
            case "double":
            case "float":
                $type = 1;
                break;
            case "array":
                $value = json_encode($value);
                $type = 2;
                break;
            default:
                $type = 0;
                break;
        }
        $data = ['id' => $id,'value' => $value,'type' => $type];
        
        $Build = static::Build($id);
        if($Build->first()===false)
            $Build->insert($data);
        else
            $Build->update($data);
    }
}