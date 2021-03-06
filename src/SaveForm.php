<?php namespace Iesod;

use Iesod\Database\ModelInterface;

class SaveForm{
    private $Request;
    private  $Model;
    private $data = [];
    private $valitations = [];
    private $fields = [];
    public function __construct(RequestInterface $Request, ModelInterface $Model){
        $this->Request = $Request;
        $this->Model = $Model;
    }
    public function get($name){
        return $this->data[$name];
    }
    public function getData(){
        return $this->data;
    }
    public function getFieldsValues(){
        $data = [];
        foreach ($this->fields as $field=>$name){
            $data[ $field ] = $this->get($name);
        }
        
        return $data;
    }
    public function getValidations(){
        return $this->valitations;
    }
    /**
     * Altera um valor do Data
     * 
     * @param string $name Nome no data
     * @param string $value Valor
     * 
     * @return SaveForm
     */
    public function set($name, $value){
        $this->data[$name] = $value;
        
        return $this;
    }
    /**
     * Altera os valores do Data
     * 
     * @param array $data
     * @param string $msgError Se nao for array executa except
     * 
     * @return SaveForm
     */
    public function setData($data, $msgError = null) {
        if ( !is_array($data) ) {
            if (is_null($msgError)) {
                $msgError = "Data invalid!";
            }
            throw new \Exception($msgError);
            return $this;
        }
        $this->data = $data;
        return $this;
    }
    public function implode($name,$glue){
        if(is_array($this->data[$name])){
            $this->data[$name] = implode($glue, $this->data[$name]);
        }
        return $this;
    }
    public function setValidate($name, $validate = null){
        if(is_null($validate) || empty($validate)){
            if(isset($this->valitations[$name]))
                unset($this->valitations[$name]);
        } else {
            $this->valitations[$name] = $validate;
        }
        return $this;
    }
    /**
     * Colocar/Retirar correlação com data <-> fields
     * 
     * @param string $fieldname Nome do campo no DB
     * @param string $name SE NULL retira correlacao.Nome do parametro no DATA e na requisicao
     * 
     * @return SaveForm
     */
    public function setFieldname($fieldname,$name = null){
        if(is_null($name) || empty($name)){
            if(isset($this->fields[ $fieldname ]))
                unset($this->fields[ $fieldname ]);
        } else {
            $this->fields[ $fieldname ] = $name;
        }
        
        return $this;
    }
    /**
     * Colocar valor em campo
     * 
     * @param string $fieldname Nome do campo no DB
     * @param string $value Valor do parametro
     * @param string $name Nome do parametro no DATA e na requisicao
     * 
     * @return SaveForm
     */
    public function setFieldValue($fieldname, $value, $name = null){
        if(is_null($name))
            $name = $fieldname;

        $this->set($name,$value);
        $this->setFieldname($fieldname,$name);

        return $this;
    }
    /**
     * Adiciona campo para gravar no DB e pega valor da requisicao HTTP
     * 
     * @param string $name Nome do parametro no DATA e na requisicao
     * @param string $validade Termos da validacao
     * @param string $fieldname Nome do campo no DB
     * @param string $default Valor padrao do parametro(caso nao tenha)
     * @param string $method Tipo da requisicao (GET ou POST)
     * 
     * @return SaveForm
     */
    public function addInput($name, $validate, $fieldname = null, $default = null, $method = null){
        $method = strtoupper( $method ?? 'POST' );
        
        if($method=='GET'){
            $value = $this->Request->get($name, $default,true);
        } else {
            $value = $this->Request->post($name, $default, true);
        }
        if (is_null($value) || $value === '') {
            $value = $default;
        }
        
        $this->set($name, $value); // Grava o valor a DATA
        return $this->addField ($name, $validate, $fieldname, $value);
    }
    /**
     * Adiciona campo para gravar no DB
     * 
     * @param string $name Nome do parametro no DATA
     * @param string $validate Termos da validacao
     * @param string $fieldname Nome do campo no DB
     * @param string $default Valor padrao do parametro(caso nao tenha)
     * 
     * @return SaveForm
     */
    public function addField ($name, $validate = null, $fieldname = null, $default = null) {
        if (!isset($this->data[$name]) || empty($this->data[$name])) {
            $this->set($name, $default); // Grava o valor a DATA
        }
        $this->setValidate($name, $validate); //  Grava dados para validacao
        $this->setFieldname($fieldname, $name); // Grava Co-relacao com banco de dados
        return $this;
    }
    public function preg_replace($name, $pattern, $replacement){
        $this->set(
            $name,
            preg_replace($pattern,$replacement,$this->get($name) )
            );
        return $this;
    }
    public function replace($name, $search, $replace){
        $this->set(
            $name,
            str_replace($search,$replace,$this->get($name) )
            );
        return $this;
    }
    
    public function unmaskphone($name){
        $this->preg_replace($name, '/[^0-9+]/', '');
        return $this;
    }
    /**
    * Retira pontos e outros caracteres, somente deixando numeros
    * 
    * @param string $name Nome do parametro no DATA e na requisicao
    * @param string|number $value Numero de entrada
    * @param boolean $float DEFAULT=true; Se retorna Float ou Int
    * @param int $dec DEFAULT=2; Se $float==true: Precisao de casas decimais
    * @param boolean $unsigned DEFAULT=false; Sem sinal de negativo
    *
    * @return SaveForm
    */
    public function onlyNumbers($name,$float = false,$dec = null,$unsigned = true){
        $this->replace($name, ',', '.');
        if($float){
            $this->preg_replace($name, '/[^0-9.-]/', '');
            if(is_null($dec) || !is_int($dec)){
                $pattern = '/^([0-9-]+([.][0-9]+)?)(.*)/';
            } else {
                $pattern = '/^([0-9-]+([.][0-9]{1,'.$dec.'})?)(.*)/';
            }
            $this->preg_replace($name, $pattern, '$1');
        } else {
            $this->preg_replace($name, '/[^0-9-]/', '');
        }
        
        if($unsigned)
            $this->preg_replace($name, '/[^0-9.]/', '');
        return $this;
    }
    /**
    * Formata numero
    *
    * @param string $name Nome do parametro no DATA e na requisicao
    * @param int $dec DEFAULT=2; Se $float==true: Precisao de casas decimais
    * @param boolean $unsigned DEFAULT=false; Sem sinal de negativo
    *
    * @return SaveForm
    */
    public function numberFormat ($name,$dec = null,$unsigned = true) {
        $float = !(is_null($dec) || !is_int($dec));
        $dec = abs($dec);
        
        $value = $this->get($name);
        $this->set($name, formatNum($value, $float, $dec, $unsigned));
        
        return $this;
    }
    public function unmaskMoney($name, $dec = 2){
        $this->replace($name, ',', '.');
        $this->onlyNumbers($name,true,$dec);
        
        return $this;
    }
    public function passwordConfirm($namePassword,$nameConfirm){
        if( $this->get($namePassword)!=$this->get($nameConfirm) ){
            throw new \Exception("Password do not match",1);
        }
        
        return $this;
    }
    public function passwordCrypt($name){
        $this->set(
            $name,
            bcrypt( $this->get($name) )
        );
        return $this;
    }
    /** Checa validade dos dados em DATA
     * 
     * @return SaveForm */
    public function validate(){
        validate($this->data, $this->valitations);
        return $this;
    }
    /** Salva dados no Database
     * 
     * @param int $id SE NULL inserir dado em tabela,se Int Alterar dadi em tabela
     * @param boolean $checkValid Checar validade do dados antes?DEFAULT=FALSE
     * @param callback $callbackOnSave($id, $dataNew[, $dataOld = null]) Execultado apos salvar o registro.
     * @param callback $callbackOnError($id, $errorDescription, $dataNew[, $dataOld = null]) Execultado ao acontecer erro ao salvar o registro.
     * 
     * @return int|boolean|\PDOStatement FALSE se erro. OU id do insert(Int). OU PDOStatement do update. */
    public function save ($id = null, $checkValid = false, $callbackOnSave = null, $callbackOnError = null){
        if ($checkValid) {
            $this->validate();
        }
        $dataNew = $this->getFieldsValues();
        $dataOld = null;
        $return = false;
        if (is_null($id)) {
            try {
                $id = $this->Model->insert( $this->getFieldsValues() );
                if (!$id) {
                    throw new \Exception("Error undefined");
                    return $id;
                }
                $return = $id;
            } catch (\Exception $e) {
                if (!is_null($callbackOnError)) {
                    $callbackOnError(
                        $id,
                        $e->getCode().": ".$e->getMessage(),
                        $dataNew,
                        null
                    );
                }
                throw $e;
                return false;
            }
        } else {
            try {
                $dataOld = $this->Model->select()->find($id);
                if (!$dataOld) {
                    $dataOld = null;
                }
            } catch (\Exception $e) {
                $dataOld = null;
            }
            try {
                $return = $this->Model->update( $this->getFieldsValues() ,$id);
                if (!$return) {
                    throw new \Exception("Error undefined");
                    return $return;
                }
            } catch (\Exception $e) {
                if (!is_null($callbackOnError)) {
                    $callbackOnError(
                        $id,
                        $e->getCode().": ".$e->getMessage(),
                        $dataNew,
                        $dataOld
                    );
                }
                throw $e;
                return false;
            }
        }
        if (!is_null($callbackOnSave)) {
            $callbackOnSave(
                $id,
                $dataNew,
                $dataOld
            );
        }
        return $return;
    }
}