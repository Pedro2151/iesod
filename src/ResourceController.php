<?php namespace Iesod;

use Iesod\ReturnList;
use Iesod\TableHist;

class ResourceController extends Controller {
    public $Model;
    public $saveLog = false;
    public $cols;
    public $params;
    public function index () {
        try {
            $primaryKey = $this->Model->getPrimaryKey();
            $request = $this->request();
            $params = [
                'q' => $request->get('q', '', true),
                'page' => $request->get('page', 0, true),
                'rpp' => $request->get('rpp', 50, true),
                /** @desc Ordem
                 * SINTAXE:
                 *      Para ASC: col
                 *      Para DESC: -col
                 *      Para Varios colocar separado por "|"
                 * @example name|-usergroup ==> name ASC, usergroup DESC
                 * */
                'o' =>  $request->get('o', $primaryKey, true)
            ];

            return $this->returnAjax($this->getList($params));
        } catch (\Exception $e) {
            return $this->returnAjaxError($e->getMessage(), $e->getCode());
        }
    }
    public function whereGetList ($Build, $params = []) {
        if (isset($params['q']) && $params['q'] != '') {
            $wexp = 'id = :id';
            $wexpBind = [':id' => $params['q']];
            $Build->whereExpression($wexp, $wexpBind);
        }
        return $Build;
    }
    public function getList ($params = [], $cols = null) {
        $primaryKey = $this->Model->getPrimaryKey();
        if (is_null($this->params)) {
            $paramsDefault = [
                'q' => '',
                'page' => 0,
                'rpp' => 50,
                'o' => $primaryKey
            ];
        } else {
            $paramsDefault = $this->params;
        }
        foreach ($paramsDefault as $n => $v) {
            if (!isset($params[$n])) $params[$n] = $v;
        }
        if (is_null($cols)) {
            if (is_null($this->cols)) {
                $this->cols = $this->Model->getFields(true);
            }
            $cols = $this->cols;
        }
        $Build = $this->whereGetList($this->Model->Build(), $params);
        //COUNT------------------------------------------------
        $BuildCount = $Build;
        list($count) = $BuildCount
            ->select([ $this->Model->Raw('count(id)') ])
            ->first(\PDO::FETCH_NUM);
        $numResults = (int)$count;
        
        if ($params['rpp'] > 0) {
            $pages = (int)($numResults / $params['rpp']);
            if($numResults % $params['rpp']>0) {
                $pages++;
            }
            
            $Build->start = $params['page'] * $params['rpp'];
            $Build->limit = $params['rpp'];
        } else {
            $pages = 1;
        }
        //COUNT-------------------------------------------------
        if (empty($params['o'])) {
            $Build->order($primaryKey,"ASC");
        } else {
            $ordens = explode("|", $params['o']);
            foreach ($ordens as $ordem) {
                $asc = substr($ordem,0,1)!='-';
                if (!$asc) {
                    $ordem = substr($ordem,1);
                }
                $asc = $asc?"ASC":"DESC";
                if (in_array($ordem, $cols)) {
                    $Build->order($ordem, $asc);
                }
            }
        }
        $results = $Build->select($cols)->get();
        $ReturnList = new ReturnList();
        $ReturnList->setParams($params);
        $ReturnList->setResults(
            $results->fetchAll(\PDO::FETCH_ASSOC),
            $results->rowCount(),
            $numResults
        );

        return $ReturnList->execute([], false);
    }
    public function create () { return false; }
    public function edit () { return false; }
    public function show ($id) {
        try {
        $data = $this->Model->where('id', '=', $id)->first();
        if (!$data) {
            throw new \Exception("Nenhum registro encontrado");
        }
        return $this->returnAjax($data);
        } catch (\Exception $e) {
            return $this->returnAjaxError($e->getMessage(), $e->getCode());
        }
    }
    private function getValidate ($field) {
        $type = $field->getType();
        /*
        $field->getLen();
        $field->getPrecision();
        */
        $types = [
            'string',
            'string',
            'int',
            'number',
            'date',
            'datetime',
            'time',
            '',
            '',
            ''
        ];
        return $types[$type] ?? null;
    }
    public function store ($idQuadro) { return $this->update($idQuadro); }
    public function update ($id) {
        try {
            $isNew = is_null($id) || $id <= 0;
            if ($isNew) $id = null;
            $table = $this->Model->getTable();
            $primaryKey = $this->Model->getPrimaryKey();
            // Salvar ==============
            $SaveForm = new SaveForm($this->request(), $this->Model);
            // Receber os dados
            $fields = $this->Model->getFields();
            foreach ($fields as $f) {
                if ($f->getName() == $primaryKey) continue;
                $SaveForm->addInput(
                    $f->getName(),
                    $this->getValidate($f),
                    $f->getName(),
                    null,
                    'POST'
                );
            }
            $rSave = $SaveForm->save(
                $id,
                true,
                function ($id, $dataNew, $dataOld = null) {
                    TableHist::addLog(
                        is_null($dataOld)? 'SAVE' : 'UPDATE',
                        $id,
                        $table,
                        $dataNew,
                        $dataOld
                    );
                },
                function ($id, $errorDescription, $dataNew, $dataOld = null) {
                    TableHist::addLog(
                        "SAVE ERROR",
                        $id,
                        $table,
                        $dataNew,
                        $dataOld
                    );
                }
            );
            if (!$rSave) {
                throw new \Exception("Erro ao salvar");
            } elseif ($isNew) {
                $id = $rSave;
            }

            return $this->returnAjax(['id' => (int)$id]);
        } catch (\Exception $e) {
            return $this->returnAjaxError($e->getMessage(), $e->getCode());
        }
    }
    public function destroy ($id) {
        try {
            $this->Model->where('id', '=', $id)->delete();
            TableHist::addLog('DELETE', $id, $this->Model->getTable());
            return $this->returnAjax([]);
        } catch (\Exception $e) {
            return $this->returnAjaxError($e->getMessage(), $e->getCode());
        }
    }
}