<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
include_once __DIR__.'/HCRoom.class.php';
include_once __DIR__.'/HCDevice.class.php';
include_once __DIR__.'/HCServer.class.php';




class homecenter extends eqLogic {
 
  public static function health()
  {
    // Statut de la connection 
    $fibaroServ = FibaroServer::getInstance();
    if( $fibaroServ->isConnected() ){
      $result = 'OK';
      $state = true;
    }else{
      $blocekTime = $fibaroServ->isBlocked();
      if( $blocekTime ){
        $result = 'KO - Connection bloquée pendant ' . $blocekTime . ' secondes';
      }else{
        $result = 'KO';
      }
      
      $state = false;


    }

      $return[0] = array(
          'test' => 'Connection : ',             
          'result' => $result,           
          'advice' => 'Connection box FIBARO',    
          'state' => $state,                
      );


      // Scène de synchonisation 
      $sceneId = config::byKey('fibaroScene', 'homecenter');
      if($sceneId){
        $state = true;
        $result = 'OK - Scène ' . $sceneId;
      }else{
        $state = false;
        $result = 'KO - Auncune scène';
      }

      $return[1] = array(
        'test' => 'Scène de synchro : ',             
        'result' => $result,           
        'advice' => 'Scène de synchonisation avec la box FIBARO',    
        'state' => $state,                
    );

      return $return;
  }

  public static function cronHourly() {

    log::add('homecenter', 'cron', 'Exécution du CRON : ' . date("F j, Y, g:i a") );

    // Suppression des messages lié au plugin
    message::removeAll('homecenter');

    $fibaroServ = FibaroServer::getInstance();
    if( !$fibaroServ->isConnected() ){
      message::add( 'homecenter', 'Connection perdu avec la box FIBARO' );
      log::add('homecenter', 'cron', 'Perte de connection à la box FIBARO' );

    }else{
      // Controle sur les équipement morts 
      $plugin = plugin::byId('homecenter');
      $eqLogics = eqLogic::byType($plugin->getId());

      // Mise en méomire des équipements Fibaro
      $fibaroServ->getDevicesList( true );

      foreach ($eqLogics as $eqLogic) {  
        $fibaroId = $eqLogic->getConfiguration("fibaroIdModule");
        $fibaroDevice = FibaroDevice::getFromId($fibaroId);
        if( !isset( $fibaroDevice ) ) { 
          message::add( 'homecenter', 'Connection perdue entre l équipement ' . $eqLogic->getId() . ' / ' . $eqLogic->getName() . ' et la box FIBARO ' );
          log::add('homecenter', 'cron', 'Perte de connection le device ' . $eqLogic->getId() . ' / ' . $eqLogic->getName() );
        }
      }

    }

  }

  public static function defineEqLogic($_fibaroId){

    // Récupération des données Fibaro 
    if( $_fibaroId == '' ) return;

    $fibaroDevice = FibaroDevice::getFromId($_fibaroId);
    if( !isset( $fibaroDevice ) ) return;

    $eqLogic = new homecenter();
    $eqLogic->setName($fibaroDevice->getName());
    $eqLogic->setEqType_name('homecenter');
    $eqLogic->setConfiguration('fibaroIdModule', $_fibaroId);

    if( !$fibaroDevice->isDead() ) $eqLogic->setIsEnable(1);
    $eqLogic->setIsVisible(1);

    // Gestion de la pièce affectée
    foreach (jeeObject::all() as $object ) {
      if( $fibaroDevice->getRoomId() == $object->getConfiguration('idFibaro') ){
          $eqLogic->setObject_id( $object->getId() );
          break;
      }  
    }

    // Gestion des catégories
    $eqLogic->setCategory( "heating", $fibaroDevice->checkCategory( FibaroDevice::FIBARO_CATEGORY['climat'] ) );
    $eqLogic->setCategory( "security", $fibaroDevice->checkCategory( FibaroDevice::FIBARO_CATEGORY['securite'] ) );
    //$eqLogic->setCategory( "energy", $fibaroDevice->checkCategor( FibaroDevice::FIBARO_CATEGORY['climat'] ) );
    $eqLogic->setCategory( "light", $fibaroDevice->checkCategory( FibaroDevice::FIBARO_CATEGORY['lumiere'] ) );
    $eqLogic->setCategory( "automatism", $fibaroDevice->checkCategory( FibaroDevice::FIBARO_CATEGORY['telecommande'] ) );
    $eqLogic->setCategory( "multimedia", $fibaroDevice->checkCategory( FibaroDevice::FIBARO_CATEGORY['multimedia'] ) );
    $eqLogic->setCategory( "default", $fibaroDevice->checkCategory( FibaroDevice::FIBARO_CATEGORY['autre'] ) );

    log::add('HomeCenter', 'debug', 'EqLogic défini:'. json_encode( utils::o2a( $eqLogic ) ) );
    return $eqLogic;

  }

  public static function deamonRunning() {

    $fibaroServ = FibaroServer::getInstance();
    $state = $fibaroServ->isConnected(); 

    return $state;

  }

  /*     * *********************Méthodes d'instance************************* */

  public function preInsert() {    
  }

  public function postInsert() {  
  }

  public function preSave() {
    
    $fibaroId = $this->getConfiguration("fibaroIdModule");
    $fibaroDevice = FibaroDevice::getFromId($fibaroId);

    if( $fibaroDevice ){
      $icone = $fibaroDevice->getIcone();
    }else{
      $icone = 'homecenter_icon.png';
    }
    
    log::add('homecenter', 'debug', 'Fibaro icone:'. $icone );
    $this->setConfiguration('icone' , $icone);
    
  }

  public function getImage() {
    $fibaroId = $this->getConfiguration("fibaroIdModule");
    $fibaroDevice = FibaroDevice::getFromId($fibaroId);
    if( isset( $fibaroDevice ) ){
      $icone = $fibaroDevice->getIcone();
    }else{
      $icone = "plugins/homecenter/desktop/icones/unknown.png";
    }
    log::add('homecenter', 'debug', 'Fibaro icone:'.$icone );
    return $icone;
  }

  public function postSave() {

    // Récupération du module Fibaro
    $fibaroId = $this->getConfiguration("fibaroIdModule");
    $fibaroDevice = FibaroDevice::getFromId($fibaroId);

    if( $fibaroDevice ){

      // Récupération des actions 
      $actions = $fibaroDevice->defineActions( true );

      // Création des commandes en fonction des actions
      $n = 0;
      foreach( $actions as $action ){
        $n++;
        $configList = array();
        
        $command = $this->getCmd( null, $action['id'] );

        if (!is_object($command)) {  
          $command = new homecenterCmd();
          $command->setName(__( $action['id'], __FILE__)); 
        }

        log::add('homecenter', 'debug', 'Fibaro Action: Name -> '. json_encode( $action ) );

        if( $action['type']  == 'info' ){
          $template = $fibaroDevice->getTemplate( $action['id'] );
          if($template){
            $command->setTemplate('dashboard', $template );
            $command->setTemplate('mobile', $template );
            log::add('homecenter', 'debug', 'Fibaro Action: Template -> '. $template );
          }
        }

        $unit = $action['unite'];

        $idRefValue = $fibaroDevice->getRefVal( $action['id'] );
        log::add('homecenter', 'debug', 'Fibaro Action: Value de référence -> '. $idRefValue );
        
        if($idRefValue){
          $cmdRef = $this->getCmd( 'info', $idRefValue ) ;   
          if($cmdRef){
            $jeedomCmdID = $cmdRef->getId();
            $command->setValue( $jeedomCmdID );  
            if($action['subType'] == 'slider') $unit = $cmdRef->getUnite();
          }
        }

        $configList = $action['config'] ?: array();
        $command->setLogicalId( $action['id'] );
        $command->setEqLogic_id( $this->getId() );
        $command->setOrder( $n );
        $command->setIsVisible( 1 );
        $command->setType( $action['type'] );
        $command->setSubType( $action['subType'] );

        if( $unit ) $command->setUnite( $unit );

        $genericType = $fibaroDevice->getGenericType( $action['id'] ); 
        log::add('homecenter', 'debug', 'Fibaro Action: Generic Type -> '. $genericType );
        if( $genericType ) $command->setDisplay('generic_type', $genericType);

        // Exception sur les détecteur d'ouverture
        if( is_a( $fibaroDevice, 'FibaroSensorDoor' ) and  $action['id'] == 'value' ) $command->setDisplay( 'invertBinary', '1' );
        

        // Gestion des liste de valeur
        if( ( isset( $configList ) ) ){       
          log::add('homecenter', 'debug', 'Fibaro Action: Configurations -> '. json_encode($configList) );             
          foreach( $configList as $config ){ 
            if( $config )        
            $command->setConfiguration( $config['name'], $config['value'] );
          }
        }

        $command->save();

      }

    }
    
  }

  public function preUpdate() {
      
  }

  public function postUpdate() {   
  
  }

  public function preRemove() {
      
  }

  public function postRemove() {
      
  }



    /*     * **********************Getteur Setteur*************************** */
}

class homecenterCmd extends cmd {


  public function execute($_options = array()) {
    log::add('homecenter', 'debug', 'Execute Action : '.$this->getLogicalId() .' Arguments : '. json_encode( $_options ) );
    
    $eqLogic = $this->getEqLogic();
    $fibaroId = $eqLogic->getConfiguration('fibaroIdModule');    
    $fibaroDevice = FibaroDevice::getFromId($fibaroId);    

    if( $this->getLogicalId() == 'refresh' ){
      
      // Actualisation des informations du modulec
      $values = $fibaroDevice->getValues();
      foreach( $values as $value ){
        $cmdEtat = $eqLogic->getCmd( $value['type'], $value['name'] );
        if( $cmdEtat ){
          $cmdEtat->event($fibaroDevice->setValue( $value ));
          $cmdEtat->save();
        }   
      }
    
    }elseif( $fibaroDevice->isCustomExe( $this->getLogicalId(), $_options ) ){
      // Execution directement dans la méthode isCustomExe
      log::add('homecenter', 'debug', 'Custom Execute : ' .$this->getLogicalId(), $_options );
    
    }else{
      $fibaroServ = FibaroServer::getInstance();
      $fibaroServ->excuteAction( $fibaroId, $this->getLogicalId(), $_options );

    }
    
  }

}