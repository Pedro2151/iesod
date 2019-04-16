<?php namespace Iesod;

use Iesod\Auth;
use Iesod\Database\Model;

class TableHist extends Model
{
    protected $table = 'table_hist';
    protected $primaryKey = 'id';
    /**
     * Retorna dados alterados
     * @return array|boolean False se não houve alteracao
     */
    static function dataUpdated ($dataNew = null, $dataOld = null) {
        if (!is_null($dataNew)) {
            $dadosAlterados = [];
            if (is_null($dataOld)) {
                foreach ($dataNew as $k => $v) {
                    $dadosAlterados[$k] = ['old' => null, 'new' => $v];
                }
            } else {
                foreach ($dataNew as $k => $v) {
                    $vOld = $dataOld[$k] ?? null;
                    if ($vOld != $v) {
                        $dadosAlterados[$k] = ['old' => $vOld, 'new' => $v];
                    }
                }
            }
            if (!empty($dadosAlterados)) {
                return $dadosAlterados;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    static function addLog ($description, $id, $table, $dataNew = null, $dataOld = null) {
        $data = [
            'table' => $table,
            'id_table' => $id,
            'id_user' => Auth::getUserId(),
            'description' => $description,
            'data' => null
        ];
        $dadosAlterados = static::dataUpdated($dataNew, $dataOld );
        if ($dadosAlterados !== false) {
            $data['data'] = json_encode($dadosAlterados);
        }
        static::insert($data, false);
    }
}