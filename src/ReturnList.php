<?php namespace Iesod;

use Iesod\Controller;

class ReturnList
{
  private $results = [];
  private $params = ['page' => 0, 'rpp' => 0];
  private $count = [
    'pages' => 0,
    'results_page' => 0,
    'results_total' => 0
  ];

  /**
   * Setting params
   * 
   * @param array $params Parametros da lista
   */
  public function setParams ($params) {
    // page current
    if (!isset($params['page'])) {
      $params['page'] = 0;
    }

    // Results(max) per page
    if (!isset($params['rpp'])) {
      $params['rpp'] = 0;
    }
    $this->params = $params;
  }

  /**
   * Setting Resultados
   * 
   * @param array $results Lista de resultados da busca
   * @param int $countResultsPage Numero de resultados nesta pagina
   * @param int $countResultsTotal Numero de resultados total da busca
   */
  public function setResults ($results, $countResultsPage, $countResultsTotal = null) {
    if (is_null($countResultsTotal)) {
      $countResultsPage = $countResultsTotal;
    }
    $pages = 0;
    if ($this->params['rpp'] <= 0) {
      $pages = 1;
    } else {
      $pages = (int)($countResultsTotal / $this->params['rpp']);
      if ($countResultsTotal % $this->params['rpp'] > 0) {
        $pages += 1;
      }
    }
    $this->count['pages'] = $pages;
    $this->count['results_page'] = $countResultsPage;
    $this->count['results_total'] = $countResultsTotal;
    $this->results = $results;
  }
  /**
   * Gera o returnAjax no padrao returnList
   * 
   * @param array $data Dados adicionais. Não deve conter as seguintes index: results, params, count.
   * 
   * @return array Retorno no formato do Controller::returnAjax
   */
  public function execute ($data = [], $returnAjax = true) {
    if (!is_array($data)) {
      $data = [];
    }
    $data['results'] = $this->results;
    $data['params'] = $this->params;
    $data['count'] = $this->count;

    if ($returnAjax) {
      return Controller::returnAjax($data);
    } else {
      return $data;
    }
  }
}