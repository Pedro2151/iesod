<?php

namespace Iesod\Command;

use Iesod\Config as ConfigI;
use Iesod\Command;
class Config
{
    private function checkClient($idClient = null){
      if(is_null($idClient) || empty($idClient))
        $idClient = Command::readInput("Informe o idClient", 'string');

      $idClient = strtolower($idClient);
      if(!preg_match("/^[a-z0-9_-]{3,30}$/",$idClient)){
        throw new \Error("idClient invalid, formato válido: '^[a-z0-9_-]{3,30}$'!");
      }
      return $idClient;
    }
    public function createEnvClient($idClient = null){
      $idClient = $this->checkClient($idClient);
      
      ConfigI::createFileClient($idClient);
      return $this->updateEnvClient($idClient);
    }
    public function updateEnvClient($idClient = null){
      $idClient = $this->checkClient($idClient);
      $config = ConfigI::getFileEnv( 
        ConfigI::getFilenameClient($idClient)
      );

      $data = [];
      $cfgs = [
        //Client
        'FULLNAME_CLIENT' => "Nome completo do cliente",
        'ADDRESS_1' => "Endereço(Linha 1)",
        'ADDRESS_2' => "Endereço(Linha 2)"
      ];
      foreach($cfgs as $cfg=>$cfgLabel){
        $cfgValue = Command::readInput($cfgLabel."[".($config[$cfg]??"")."]", 'string');
        if(!empty($cfgValue)){
          $data[$cfg] = $cfgValue;
        }
      }

      $setApp = Command::readInput("Alterar configuração do APP?(y/n)", ['y','N','n','N']);
      if($setApp=='y' || $setApp=='Y'){
        $cfgs = [
          //APP
          'APP_LANGUAGE' => "Lingua(en/ptbr)",
          //STORAGE
          'STORAGE_LIMIT' => "Limite de armazenamento",
          'STORAGE_PATH' => "Path do armazenamento"
        ];

        foreach($cfgs as $cfg=>$cfgLabel){
          $cfgValue = Command::readInput($cfgLabel."[".($config[$cfg]??"")."]", 'string');
          if(!empty($cfgValue)){
            $data[$cfg] = $cfgValue;
          }
        }
      }

      $setApp = Command::readInput("Alterar configuração de banco de dados?(y/n)", ['y','N','n','N']);
      if($setApp=='y' || $setApp=='Y'){
        $cfgs = [
          //DATABASE
          //'DB_DRIVE' => 'mysql',
          'DB_HOST' => "HOSTNAME do database",
          'DB_PORT' => "Porta",
          'DB_DATABASE' => "Database",
          'DB_USERNAME' => "Username",
          'DB_PASSWORD' => "Password"
        ];

        foreach($cfgs as $cfg=>$cfgLabel){
          $cfgValue = Command::readInput($cfgLabel."[".($config[$cfg]??"")."]", 'string');
          if(!empty($cfgValue)){
            $data[$cfg] = $cfgValue;
          }
        }
      }

      return ConfigI::updateFileClient($idClient,$data);
    }
}