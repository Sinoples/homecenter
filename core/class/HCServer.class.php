
<?php

class FibaroServer {
    

  private $urlServeur;
  private $connected;
  private $blockedTime;
  private $basicAuth;

  private static $GLOBALS_icones = array();
  private static $GLOGAL_instance;

  const API_NAME = array(
    'login' => '/api/loginStatus',
    'infoDevice' => '/api/uiDeviceInfo',
    'device' => '/api/devices',
    'allRooms' => '/api/rooms?visible=true',
    'room' => '/api/rooms',
    'scene' => '/api/scenes',
    'icone' => '/api/icons'
  );


  public static function getInstance() {
    if(!self::$GLOGAL_instance){
      self::$GLOGAL_instance = new FibaroServer();
    }
    return self::$GLOGAL_instance;
  }

  private function __construct() {

    //recuperation de IP, Login, Mdp  (parametre Global de module)
    $userId = config::byKey('fibaroLogin', 'homecenter');
    $userPassword = config::byKey('fibaroMDP', 'homecenter');
    $serverIP = config::byKey('fibaroIP', 'homecenter');

    // On enregistre les données de connextion 
    $this->urlServeur = 'http://'.$serverIP;
    $this->basicAuth = base64_encode( $userId. ':' .$userPassword); 
    log::add('homecenter', 'debug', 'Basic Authorisation:'.$this->basicAuth);
    
    if( !$this->connected ){
      $this->connected = true;
      $json_data = $this->executeRequest($this::API_NAME['login'] ); 
      $this->connected = $json_data->status;
      $this->blockedTime = $json_data->timeLeft;
    }    

  }

  // Récupération de l'IP de la HC3
  public function getIP(){
    return  $this->urlServeur;
  }

  // La connection est elle établie sur la HomeCenter ?
  public function isConnected(){
    return $this->connected;    
  }

  public function isBlocked(){
    return $this->blockedTime;    
  }

  private function executeRequest($_APIName, $_method = 'GET', $_body = null ){ 
    
    if( $this->connected ){

      $request = $this->urlServeur.$_APIName;
      $body = ($_body) ?: '{}';

      log::add('homecenter', 'debug', 'Requete:'.$request . ' / Body : ' .$body );
      $curl = curl_init();
      
      curl_setopt_array($curl, array(
        CURLOPT_URL => $request,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $_method,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
          //'Content-Type: application/json',
          'Accept: application/json',
          'Authorization: Basic ' .$this->basicAuth
        ),
      ));

      $response = curl_exec($curl);
      log::add('homecenter', 'debug', 'Résultat requête:'.$response);

      curl_close($curl);

      $json_data = json_decode($response);
      return $json_data;

    }

  }

  // Récupération des modules visibles Fibaro
  public function getDevicesList( $_withoutInfo = false ){
    $DeviceArray = array();
    
    $n = 0;

    if( !$_withoutInfo ){
      $request = $this::API_NAME['infoDevice'] . '?selectors=properties&visible=true';
      $json_info = $this->executeRequest( $request );
      
      $request = $this::API_NAME['device'] . '?visible=true';
      $json_detail = $this->executeRequest( $request );      
      
      foreach($json_info as $jsonDeviceI){
        $n++;

        if( $jsonDeviceI->roomId != '0' ){

          $jsonDeviceD = null;
          foreach($json_detail as $json_detail_key => $jsonDeviceSel ){
            if( $jsonDeviceI->id == $jsonDeviceSel->id ){
              $jsonDeviceD = $jsonDeviceSel;
              unset($json_detail[$json_detail_key]);
              break;              
            }
          }

          $fibDevice = FibaroDevice::getFromJson($jsonDeviceD, $jsonDeviceI);
          log::add('homecenter', 'debug', 'Devices:'.$fibDevice->getId() .' / ' .$fibDevice->getName());
          $DeviceArray[$n] = $fibDevice;            
        
        }

      }
    }else{
      
      $request = $this::API_NAME['device'] . '?visible=true';
      $json_detail = $this->executeRequest( $request );  
      foreach($json_detail as $jsonDeviceD){
        $n++;

        if( $jsonDeviceD->roomID != '0' ){

          $fibDevice = FibaroDevice::getFromJson( $jsonDeviceD );
          log::add('homecenter', 'debug', 'Devices:'.$fibDevice->getId() .' / ' .$fibDevice->getName());
          $DeviceArray[$n] = $fibDevice;            
        
        }

      }                  
    }

    return $DeviceArray;

  }

  // Récupération d'un module avec son ID
  public function getDeviceByID($_deviceId){

    $json_data = $this->executeRequest($this::API_NAME['device'] . '/' . $_deviceId);
    return $json_data;    
  }
  
  // Récupération des propriétés d'un module
  public function getDeviceProp($_deviceId, $_roomId, $_type){
    
    $request = $this::API_NAME['infoDevice'] . '?visible=true&roomId=' .$_roomId .'&type=' .$_type;
    $json_data = $this->executeRequest( $request );
    foreach( $json_data as $property ){
      if ( $property->id == $_deviceId ) break;
    }

    return $property;    
  }

  // Récupération d'une pièce avec son ID
  public function getRoomByID($_roomId){

    $json_data = $this->executeRequest($this::API_NAME['room'] . '/' . $_roomId);
    return $json_data;    
  }

  // Récupération Toutes les pièces
  public function getAllRooms(){
    
    $RoomArray = array();
    $n = 0;

    $json_data = $this->executeRequest($this::API_NAME['allRooms']);
    
    foreach($json_data as $jsonRoom){
      $n++;
      $fibRoom = new FibaroRoom($jsonRoom);
      log::add('homecenter', 'debug', 'pièce:'.$fibRoom->getId() .' / ' .$fibRoom->getName());
      $RoomArray[$n] = $fibRoom;
    }
    
    return $RoomArray;

  }

  // Exécution d'une action dans Fibaro
  public function excuteAction( $_deviceId, $_actionId, $_args = array() ){

    foreach( $_args as $argKey => $argvalue){
      if (is_numeric( $argvalue )) {
        $body = '{ "args" : [%value%]}';
      }else{
        $body = '{ "args" : ["%value%"]}';
      }
      $body = str_replace( '%value%', $argvalue, $body );
      break;
    }
    
    $request = $this::API_NAME['device'] . '/' . $_deviceId . '/action/' . $_actionId;    
    $this->executeRequest( $request, 'POST', $body);

  }

  // Récupération Toutes les pièces
  public function changeScene( $_contend ){
    
    // Récupération du numéro de scène 
		$sceneId = config::byKey('fibaroScene', 'homecenter');

    // Controle si la scène existe
    if($sceneId) $jsonScene = $this->executeRequest( $this::API_NAME['scene'] . '/' . $sceneId );
    $body = array(   
      "name" => "Jeedom",
      "type" => "lua",
      "mode" => "automatic",
      "maxRunningInstances" => 2,
      "icon" => "scene_auto",
      "hidden" => true,
      "protectedByPin" => false,
      "stopOnAlarm" => false,
      "restart" => true,
      "enabled" => true,
      "content" => '%content%',
      "categories" => [ 1 ]    
      );
    

    $jsonBody = json_encode($body);
    $jsonBodySend = str_replace('%content%', $_contend, $jsonBody);

    if( $jsonScene->id ){
      // Modification de la scène
      $request =  $this::API_NAME['scene'] . '/' . $sceneId;
      $json_result = $this->executeRequest( $request, 'PUT', $jsonBodySend );
      log::add('homecenter', 'debug', 'ERROR:'.$json_result->type ); 
      if( !$json_result->type ) {
        return true;
      }else{
        return false;
      }
    
    }else{
      // Création de la scène 
      $request =  $this::API_NAME['scene'];
      $json_result = $this->executeRequest( $request, 'POST', $jsonBodySend );
      
      if( $json_result->id ){
        config::save('fibaroScene', $json_result->id , 'homecenter');
        return true;
      }else{
        return false;
      }
    
    }

  }

  // Récupération des icones
  public function getIcone( $_iconeId ){

    if ( empty( $this::$GLOBALS_icones ) ){
      $jsonIcons = $this->executeRequest( $this::API_NAME['icone'] );
      foreach($jsonIcons->device as $jsonIcon){
        $url = $this->urlServeur . '/assets/icon/fibaro/$iconName$/$iconName$';
        $url = str_replace('$iconName$', $jsonIcon->iconSetName, $url);
        $this::$GLOBALS_icones[$jsonIcon->id] = $url;
      }
    }

    if( $this::$GLOBALS_icones[$_iconeId] ) return $this::$GLOBALS_icones[$_iconeId] . '100.png';

  }

  // Récupération des icones
  public function getIconeState( $_iconeId ){

    if ( empty( $this::$GLOBALS_icones ) ){
      $jsonIcons = $this->executeRequest( $this::API_NAME['icone'] );
      foreach($jsonIcons->device as $jsonIcon){
        $url = $this->urlServeur . '/assets/icon/fibaro/$iconName$/$iconName$';
        $url = str_replace('$iconName$', $jsonIcon->iconSetName, $url);
        $this::$GLOBALS_icones[$jsonIcon->id] = $url;
      }
    }

    if( $this::$GLOBALS_icones[$_iconeId] ) return $this::$GLOBALS_icones[$_iconeId] . '.png';

  }  

}

?>