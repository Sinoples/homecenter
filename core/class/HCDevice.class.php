
<?php

    abstract class FibaroDevice {

        const STATE_NOT_USABLE = '1';
        const STATE_READY = '2';
        const STATE_OK = '3';
        const STATE_DEAD = '4';

        const FIBARO_TYPE = array(
            'prisePlugFib' => 'com.fibaro.FGWP102',
            'prise' => 'com.fibaro.FGWOEF011',
            'luminosite' => 'com.fibaro.lightSensor',
            'temperature' => 'com.fibaro.temperatureSensor',
            'voletRoulant' => 'com.fibaro.rollerShutter',
            'sismomètre' => 'com.fibaro.seismometer',
            'accelerometre' => 'com.fibaro.accelerometer',
        );

        const FIBARO_BASETYPE = array(
            'binarySwitch' => 'com.fibaro.binarySwitch',
            'binarySwitch2' => 'com.fibaro.actor',
            'prisePlug' => 'com.fibaro.FGWP',
            'voletRoulant' => 'com.fibaro.baseShutter',
            'thermostat' => 'com.fibaro.hvacSystem',
            'temperature' => 'com.fibaro.multilevelSensor',
            'capteurOuv'  => 'com.fibaro.doorWindowSensor',
            'capteurMouv' => 'com.fibaro.securitySensor',
            'capteurMouv2' => 'com.fibaro.FGMS001',
            'capteurFuite' => 'com.fibaro.floodSensor',
            'serrure' => 'com.fibaro.securityMonitoring'
        );

        const FIBARO_CATEGORY = array(
            'lumiere' => 'lights',
            'voletRoulant' => 'blinds',
            'portail' => 'gates',
            'ambiance' => 'ambience',
            'climat' => 'climate',
            'surete' => 'safety',
            'securite' => 'security',
            'multimedia' => 'multimedia',
            'telecommande' => 'remotes',
            'autre' => 'other',
        );          

        private $name, $id, $roomId, $type, $baseType, $dead = false, $iconeId, $unit;
        protected $values = array(), $categories = array(), $genericTypes = array(), $actions = array();
        
        public static $GLOBALS_devices = array();

        public static function getFromId($_deviceId){
            
            if ( !isset( self::$GLOBALS_devices[$_deviceId] ) ){

                $fibaroServ = FibaroServer::getInstance();
                $JsonDetail = $fibaroServ->getDeviceByID($_deviceId);

                if( $JsonDetail ) {               
                    $device = self::createDevice( $JsonDetail );
                    self::$GLOBALS_devices[$_deviceId] = $device;
                }else{
                    log::add('homecenter', 'debug', 'Fibaro Device '. $_deviceId .': Inconnu' );
                }

            }else{
                log::add('homecenter', 'debug', 'Fibaro Device: GET FROM MEMORY' );
                $device = self::$GLOBALS_devices[$_deviceId];
            }

            log::add('homecenter', 'debug', 'Fibaro Device:'. json_encode( utils::o2a( $device ) ) );
            return $device;
            
        }

        public static function getFromJson($_jsonDetail, $_jsonInfo = null){
            $device = self::createDevice($_jsonDetail, $_jsonInfo);
            self::$GLOBALS_devices[ $device->getId() ] = $device;
            return $device;
        }

        private static function createDevice($_jsonDetail, $_jsonInfo = null){
            
            switch ( $_jsonDetail->baseType ) {
                
                case self::FIBARO_BASETYPE['serrure'] :
                    $device = new FibaroLock($_jsonInfo, $_jsonDetail);   
                    break;

                case self::FIBARO_BASETYPE['capteurFuite'] :
                    $device = new FibaroSensorWater($_jsonInfo, $_jsonDetail);   
                    break;                    

                case self::FIBARO_BASETYPE['capteurOuv'];
                case self::FIBARO_BASETYPE['capteurOuv2'] :
                    $device = new FibaroSensorDoor($_jsonInfo, $_jsonDetail);   
                    break;

                case self::FIBARO_BASETYPE['capteurMouv'];
                case self::FIBARO_BASETYPE['capteurMouv2'] :
                    $device = new FibaroSensorMotion($_jsonInfo, $_jsonDetail);   
                    break;

                case self::FIBARO_BASETYPE['temperature'] :
                    $device = new FibarolevelSensor($_jsonInfo, $_jsonDetail);   
                    break;

                case self::FIBARO_BASETYPE['thermostat'] :
                    $device = new FibaroThermostat($_jsonInfo, $_jsonDetail);   
                    break;
                
                case self::FIBARO_BASETYPE['binarySwitch2'] :    
                case self::FIBARO_BASETYPE['binarySwitch'] :
                    $device = new FibaroBinarySwitch($_jsonInfo, $_jsonDetail);                    
                    break;

                case self::FIBARO_BASETYPE['prisePlug'] :
                    $device = new FibaroPlug($_jsonInfo, $_jsonDetail);                    
                    break;

                case self::FIBARO_BASETYPE['voletRoulant'] :
                    $device = new FibaroRollerShutter($_jsonInfo, $_jsonDetail);                    
                    break;
                
                default :
                    $device = new Fibarogeneric($_jsonInfo, $_jsonDetail); 
                    break;                
            }

            return $device;
            
        }

        function __construct($_jsonInfo, $_jsonDetail) {
            $this->name = $_jsonDetail->name;
            $this->id = $_jsonDetail->id;
            $this->roomId = $_jsonDetail->roomID;
            $this->type  = $_jsonDetail->type;
            $this->baseType = $_jsonDetail->baseType;

            foreach( $this::FIBARO_CATEGORY as $category ) {
                $found = false;
                foreach ( $_jsonDetail->properties->categories as $jsoncategory ) {
                    if( $jsoncategory == $category ) {
                        $this->categories += [ $category => true ];
                        $found = true;
                        break;
                    }
                };

                if( !$found )
                $this->categories += [ $category => false ];
            };

            $this->dead = $_jsonDetail->properties->dead;
            $this->iconeId = $_jsonDetail->properties->deviceIcon;
            $this->unit = $_jsonDetail->properties->unit;

            // Récupérationd des actions
            if ( $_jsonInfo ) $this->setProperties($_jsonInfo);

        }
        
        public function getIconeId() {
            return $this->iconeId;
        }

        public function getName() {
            return $this->name;
        }

        public function getId() {
            return $this->id;
        }
        
        public function getType() {
            return $this->type;
        }

        public function getBaseType() {
            return $this->baseType;
        }

        public function getRoomId() {
            return $this->roomId;
        }   
        
        public function getUnit() {
            return $this->unit;
        }  

        public function checkCategory($_Category) {
            if ( $this->categories[$_Category] == true ) {
                return true;
            }else{
                return false;
            }
        }   
        
        public function isUsable() {
            if( is_a( $this, 'Fibarogeneric' ) ){
                $result =  false;
            }else{
                $result = true;
            }
            return $result;

        }

        public function isDead() {
            return $this->dead;
        }    
        
        public function defineActions( ){

            if( !$this->actions ){  
                $fibaroServ = FibaroServer::getInstance();
                $JsonProp = $fibaroServ->getDeviceProp($this->id, $this->roomId, $this->type);
                $this->setProperties($JsonProp);
            }
            
            return $this->actions;
            
        }

        public function getValues(  ){    
            return $this->values;
        }

        public function getTemplate( $info ){

        }
        
        public function getRefVal( $_actionId ) {
            foreach ( $this->values as $value ){ 
                if ( $value['IsValueRef'] == $_actionId ) {
                    return $value['name'];
                    break;
                }
            }
        }

        public function getGenericType( $_type ){
            return $this->genericTypes[$_type];
        }    
        
        public function setValue( $_value = array()){
            return $_value['value'];
        }

        public function isCustomExe( $_id, $_value = array() ){
            return false;
        }

        // Méthodes abstraites
        public abstract function getCondition();
        public abstract function getTypeName();  
        public abstract function getIcone();

        // Private section 
        //----------------------------------
        private function setProperties( $_jsonInfo ){
            
            // Ajout de toutes les actions du module
            $n = 1;
            // Ajout de toutes les informations d'état en fonction des propriétés
            foreach( $_jsonInfo->properties as $property ){
                $n++;
                $configList = array();

                switch( $property->type ){
                    case 'bool':
                        $subType = 'binary';
                        break;

                    case 'string':
                        $subType = 'string';
                        break;
                    
                    case 'real'; 
                    case 'temperature':
                        $subType = 'numeric';
                        $unit    = $this->getUnit();
                        break;

                    case 'percent' :
                        $subType = 'numeric';
                        $configList[ 0 ] = array(  'name' => 'minValue',
                                                   'value' => 0 );

                        $configList[ 1 ] = array( 'name' => 'maxValue',
                                                  'value' => 100 );                         
                        break;  

                    case 'int' :                         
                        $subType = 'numeric';
                        break;   
                        
                    default :
                        $subType = 'string';
                        log::add('homecenter', 'debug', 'Type inconnu : '. $property->type );
                        break;
                }

                if( $property->min ) $configList[ 0 ] = array(  'name' => 'minValue',
                                                                'value' => $property->min );

                if( $property->max ) $configList[ 1 ] = array( 'name' => 'maxValue',
                                                               'value' => $property->max );  

                if($property->unit) $unit = $property->unit;
                $actionTmp = array( 'id' => $property->name,
                                    'label' => $property->label,
                                    'type' => 'info',
                                    'subType' => $subType,
                                    'unite'   => $unit,
                                    'config' => $configList );
                
                $this->actions[$n] = $actionTmp;
            }   

            foreach( $_jsonInfo->actions as $action ){
                $n++;
                $configList = array( );

                // Impossible de gérer plus d'un argument dans JEEDOM
                if ( count( $action->args ) > 1 ) break;

                $argument = $action->args[ 0 ];
                switch( $argument->picker ){
                    case 'enum':
                        $subType = 'select';
                        
                        $config = '';
                        foreach( $argument->enumValues->items as $itemkey => $item ){
                            if( $itemkey >= 1 ) $config = $config . ';';
                            $config = $config . $item->value . '|' . $item->label;
                        }

                        $configList[ 0 ] = array( 'name' => 'listValue',
                                                  'value' => $config );

                        break;  

                    case 'slider':
                        if( $argument->min ) $configList[ 0 ] = array( 'name' => 'minValue',
                                                                       'value' => $argument->min );

                        if( $argument->max ) $configList[ 1 ] = array( 'name' => 'maxValue',
                                                                       'value' => $argument->max );   
                        $subType = 'slider';
                        break;              
                    
                    case 'input' :
                        // On ne gère pas les type Input 
                        $exit = true;
                        break;

                    default :
                        $subType = 'other';
                        break;
                }

                if( $exit ) break;

                $actionTmp = array( 'id' => $action->name,
                                    'label' => $action->label,
                                    'type' => 'action',
                                    'subType' => $subType,
                                    'unite' => null,
                                    'config' => $configList );
                
                $this->actions[$n] = $actionTmp;
            }
            
            // Ajout d'action supplémentaire
            $this->addCustomActions( $n );

        }

        // Protected section 
        //----------------------------------
        protected function addCustomActions( $_count ){
            // Ajout de l'action de refresh
            $n = $_count;
            $n++;
            $actionTmp = array( 'id' => 'refresh',
                                'label' => 'refresh',
                                'type' => 'action',
                                'subType' => 'other' );

            $this->actions[$n] = $actionTmp;
            return $n;
        }

    }

    /***********************************************************************************/

    // Classe générique pour les devices non connus
    class Fibarogeneric extends FibaroDevice{
        public function getCondition(){

        }
        
        public function getIcone(){
            $icone = 'plugins/homecenter/desktop/icones/homecenter_icon.png';
            return $icone;            
        }     
        

        public function getTypeName() {
            return $this->getBaseType();
        }   

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){

            }
            return $template;
        }    
        
    }

    /***********************************************************************************/

    abstract class FibaroSwitch extends FibaroDevice{

        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);

            $this->values[ 0 ] = array( 'name'  => 'state',
                                        'value' => $_jsonDetail->properties->state,
                                        'type'   => 'info',
                                        'slider' => false );

            $this->values[ 1 ] = array( 'name'  => 'energy',
                                        'value' => $_jsonDetail->properties->energy,
                                        'type'  => 'info',
                                        'slider' => false );
            
            $this->values[ 2 ] = array( 'name'  => 'power',
                                        'value' => $_jsonDetail->properties->power,
                                        'type'  => 'info',
                                        'slider' => false );

            $isLight = $this->categories[ $this::FIBARO_CATEGORY['lumiere'] ];
            if($isLight){
                $this->genericTypes[ 'state' ] = 'LIGHT_STATE';
                $this->genericTypes[ 'energy' ] = 'POWER';
                $this->genericTypes[ 'power' ] = 'CONSUMPTION';
                $this->genericTypes[ 'turnOn' ] = 'LIGHT_ON';
                $this->genericTypes[ 'turnOff' ] = 'LIGHT_OFF';                                        
                $this->genericTypes[ 'toggle' ] = 'LIGHT_TOGGLE';    
            
            }else{
                $this->genericTypes[ 'state' ] = 'ENERGY_STATE';
                $this->genericTypes[ 'turnOn' ] = 'ENERGY_ON';
                $this->genericTypes[ 'turnOff' ] = 'ENERGY_OFF';                                        
                $this->genericTypes[ 'toggle' ] = '';  

            }
              
        }

        public function getCondition(){
            $condition = ' {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"==\\\\\\",\\\\n      property = \\\\\\"state\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = true\\\\n    }, {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"==\\\\\\",\\\\n      property = \\\\\\"state\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = false\\\\n    }';
            $condition = str_replace('%idFibaro%', $this->getId(), $condition );
            return $condition;
        }
        
        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                $isLight = $this->categories[ $this::FIBARO_CATEGORY['lumiere'] ];
                if( $info == 'state' and $isLight) $template = 'light'; 
            }
            return $template;
        }   
        
        public function getIcone(){
            $fibaroServ = FibaroServer::getInstance();
            return $fibaroServ->getIcone( $this->getIconeId() );
        }        
              
    }

    class FibaroBinarySwitch extends FibaroSwitch{

        public  function getIcone(){
            $icone = parent::getIcone() ?: 'plugins/homecenter/desktop/icones/switch.png';
            return $icone;                 
        }  

        public function getTypeName() {
            if( $this->getType() == self::FIBARO_TYPE['prise'] ){
                return '{{prise}}';
            }else{
                return '{{Interrupteur}}';
            }
            
        } 
          

    }

    class FibaroPlug extends FibaroSwitch{

        public  function getIcone(){
            $icone = parent::getIcone() ?: 'plugins/homecenter/desktop/icones/wallplug.png';
            return $icone;            
        }  

        public function getTypeName() {
            return '{{Prise Connectée}}';
        }     

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template and $info == 'state') $template = 'prise'; 
            return $template;
        } 

         
    }

    /***********************************************************************************/

    class FibaroRollerShutter extends FibaroDevice{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);

            $this->values[ 0 ] = array( 'name'  => 'value',
                                        'value' => $_jsonDetail->properties->value,
                                        'type'  => 'info', 
                                        'slider' => true );

            $this->genericTypes[ 'value' ] = 'FLAP_STATE';
            $this->genericTypes[ 'setValue' ] = 'FLAP_SLIDER';
            $this->genericTypes[ 'open' ] = 'FLAP_UP';
            $this->genericTypes[ 'close' ] = 'FLAP_DOWN';
            $this->genericTypes[ 'stop' ] = 'FLAP_STOP';
          
        }

        public function getCondition(){
            $condition = '  {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\">=\\\\\\",\\\\n      property = \\\\\\"value\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = 0\\\\n    }';
            $condition = str_replace('%idFibaro%', $this->getId(), $condition );
            return $condition;
        }

        
        public  function getIcone(){
            $fibaroServ = FibaroServer::getInstance();
            return $fibaroServ->getIcone( $this->getIconeId() ) ?: 'plugins/homecenter/desktop/icones/store.png';;         
        }     
        

        public function getTypeName() {
            return '{{Store}}';
        }   
        
        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                $isBlind = $this->categories[ $this::FIBARO_CATEGORY['voletRoulant'] ];
                if( $info == 'value' and $isBlind) $template = 'shutter';                 
            }
            return $template;
        }
       
    }


      /***********************************************************************************/

    class FibaroThermostat extends FibaroDevice{

        private $sensorId, $sensorValue;

        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);
            $room = FibaroRoom::getById( $this->getRoomId() );
            if( isset( $room ) ) {
                $this->sensorId = $room->getSensorTempId();
                $device = FibaroDevice::getFromId( $this->sensorId );
                if( $device->getType() == self::FIBARO_TYPE['temperature'] ) {
                    $values = $device->getValues();
                    $this->sensorValue = $values[0]['value'];
                }
            }
            $this->values[ 0 ] = array( 'name'  => 'batteryLevel',
                                        'value' => $_jsonDetail->properties->batteryLevel,
                                        'type'  => 'info' );
          
            $this->values[ 1 ] = array( 'name'  => 'heatingThermostatSetpoint',
                                        'value' => $_jsonDetail->properties->heatingThermostatSetpoint,
                                        'type'  => 'info', 
                                        'IsValueRef' => 'setHeatingThermostatSetpoint' );

            $this->values[ 2 ] = array( 'name'  => 'thermostatMode',
                                        'value' => $_jsonDetail->properties->thermostatMode,
                                        'type'  => 'info', 
                                        'IsValueRef' => 'setThermostatMode' );                                           
 
            $this->values[ 3 ] = array( 'name'  => 'currentTemp',
                                        'value' => $this->sensorValue,
                                        'type'  => 'info' );           

            $this->genericTypes[ 'heatingThermostatSetpoint' ] = 'THERMOSTAT_SETPOINT';
            $this->genericTypes[ 'setHeatingThermostatSetpoint' ] = 'THERMOSTAT_SET_SETPOINT';
            $this->genericTypes[ 'batteryLevel' ] = 'BATTERY';
            $this->genericTypes[ 'currentTemp' ] = 'THERMOSTAT_TEMPERATURE';

            // Gestion pour HomeBridge
            /*
            $this->genericTypes[ 'mode' ] = 'THERMOSTAT_MODE';
            $this->genericTypes[ 'setMode' ] = 'THERMOSTAT_SET_MODE';

            $this->values[ 3 ] = array( 'name'  => 'mode',
                                        'value' => 'AUTO',
                                        'type'  => 'info', 
                                        'IsValueRef' => 'setMode' );              
            */
                                                                                            
        }

        
        public function getCondition(){
            $condition = ' {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\">=\\\\\\",\\\\n      property = \\\\\\"heatingThermostatSetpoint\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = 0\\\\n    }, {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"!=\\\\\\",\\\\n      property = \\\\\\"thermostatMode\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = \\\\\\"O\\\\\\"\\\\n    }';
            $condition = str_replace('%idFibaro%', $this->getId(), $condition );
            return $condition;
        }
        
        public function getIcone(){
            $fibaroServ = FibaroServer::getInstance();
            return $fibaroServ->getIcone( $this->getIconeId() ) ?: 'plugins/homecenter/desktop/icones/Tmp.png';
        } 
        
        public function getTypeName() {
            return '{{Thermostat}}';
        }

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                if( $info == 'currentTemp' ) $template = 'tile';                 
            }
            return $template;
        }    
        // Gestion d'un mode propre à Homebrige mais ne fonctionne pas, seul le plugin Thermostat de Jeedom est 
        // Compatible --> A voir donc avec ce plugin.. 

        /*
        public function setValue( $_value = array() ){
            
            
            $value = parent::setValue( $_value );

            if( $_value['name'] == 'mode' ){
                log::add('fibaro', 'debug', 'Refresh Name : ' . $this->values[ 2 ]['name'] .' / Value : ' . $this->values[ 2 ]['value'] );
                switch( $this->values[ 2 ]['value'] ){
                    case 'Off' :
                        $value = 'OFF';
                        break;
                    
                    default :
                        $value = 'AUTO';
                        break;
                }
            }

            return $value;
            
        }


        public function isCustomExe( $_id, $_value = array() ){

        
            if( $_id == 'setMode' ){
                switch( array_shift(array_values($_value)) ){
                    case 'OFF' :
                        $option[0] = 'Off';
                        break;
                    
                    case 'AUTO' :
                        $option[0] = 'Heat';
                        break;
                }                

                $fibaroServ = FibaroServer::getInstance();
                $fibaroServ->excuteAction( $this->getId(), 'setThermostatMode', $option );
                return true;  

            }else{
                return false;
            }
        }
        */
        protected function addCustomActions( $_count ){
            
            
            $count = parent::addCustomActions( $_count );
            
            $count++;
            
            $configList[ 0 ] = array( 'name' => 'SensorId',
                                      'value' => $this->sensorId );

            $actionTmp = array( 'id' => 'currentTemp',
                                'label' => 'Current Temperature',
                                'type' => 'info',
                                'subType' => 'numeric',
                                'unite'   => $this->getUnit(),
                                'config' => $configList );

            $this->actions[$count] = $actionTmp; 

            /*
            $count++;
            $actionTmp = array( 'id' => 'mode',
                                'label' => 'Mode',
                                'type' => 'info',
                                'subType' => 'string' );

            $this->actions[$count] = $actionTmp;     

            
            $count++;
            $configList[ 0 ] = array( 'name' => 'listValue',
                                      'value' => 'OFF|Off;AUTO|Thermostat' );
                                      
            $actionTmp = array( 'id' => 'setMode',
                                'label' => 'Set mode',
                                'type' => 'action',
                                'subType' => 'select',
                                'config' => $configList );

            $this->actions[$count] = $actionTmp;                 
            */
        }
         
    }    

    class FibarolevelSensor extends FibaroDevice{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);
          
            $this->values[ 0 ] = array( 'name'  => 'value',
                                        'value' => $_jsonDetail->properties->value,
                                        'type'  => 'info' );
            
            $this->values[ 1 ] = array( 'name'  => 'batteryLevel',
                                        'value' => $_jsonDetail->properties->batteryLevel,
                                        'type'  => 'info' );                                        
            
            switch( $this->getType() ){
                case self::FIBARO_TYPE['luminosite'] :
                    $this->genericTypes[ 'value' ] = 'BRIGHTNESS';
                    break;

                case self::FIBARO_TYPE['sismomètre'] :
                    $this->genericTypes[ 'value' ] = 'SHOCK';
                    break;

                case self::FIBARO_TYPE['accelerometre'] :
                    $this->genericTypes[ 'value' ] = 'SABOTAGE';
                    break;
                                                                        
                default :
                    $this->genericTypes[ 'value' ] = 'TEMPERATURE';                
                    break;
            }

            $this->genericTypes[ 'batteryLevel' ] = 'BATTERY';

        }

        public function getCondition(){
            $condition = '  {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"!=\\\\\\",\\\\n      property = \\\\\\"value\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = 0\\\\n    }';
            $condition = str_replace('%idFibaro%', $this->getId(), $condition );
            return $condition;
        }

        
        public function getIcone(){
            $fibaroServ = FibaroServer::getInstance();
            if( $this->getType() == self::FIBARO_TYPE['sismomètre'] ) {
                return $fibaroServ->getIcone( $this->getIconeId() ) ?: 'plugins/homecenter/desktop/icones/seismometer.png';
            }else{
                return $fibaroServ->getIconeState( $this->getIconeId() ) ?: 'plugins/homecenter/desktop/icones/Tmp.png';
            }
            
        }  
        

        public function getTypeName() {
            switch( $this->getType() ){
                case self::FIBARO_TYPE['luminosite'] :
                    return '{{Capteur luminosité}}';
                    break;

                case self::FIBARO_TYPE['temperature']  :
                    return '{{Capteur température}}';
                    break;

                case self::FIBARO_TYPE['sismomètre'] :
                    return '{{Capteur sismomètre}}';
                    break;

                default :
                    return '{{Capteur}}';
                    break;
            }            
            
        }   

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                if( $info == 'value' ) $template = 'tile';                 
            }
            return $template;
        }            
       
    }

    abstract class FibaroSensor extends FibaroDevice{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);
            
            $this->values[ 0 ] = array( 'name'  => 'value',
                                        'value' => $_jsonDetail->properties->value,
                                        'type'  => 'info' );

            $this->values[ 1 ] = array( 'name'  => 'batteryLevel',
                                        'value' => $_jsonDetail->properties->batteryLevel,
                                        'type'  => 'info' );                                        

            $this->values[ 2 ] = array( 'name'  => 'tamper',
                                        'value' => $_jsonDetail->properties->tamper,
                                        'type'  => 'info' );   

            $this->genericTypes[ 'batteryLevel' ] = 'BATTERY';
            
        }

        public function getCondition(){
            $condition = ' {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"==\\\\\\",\\\\n      property = \\\\\\"value\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = true\\\\n    }, {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"==\\\\\\",\\\\n      property = \\\\\\"value\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = false\\\\n    }';
            $condition = str_replace('%idFibaro%', $this->getId(), $condition );
            return $condition;
        } 
        
        public function getIcone(){
            $fibaroServ = FibaroServer::getInstance();
            return $fibaroServ->getIcone( $this->getIconeId() ) ?: 'plugins/homecenter/desktop/icones/homecenter_icon.png';
        }         
       
    }
    
    class FibaroSensorDoor extends FibaroSensor{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);

            if( $this->gettype() == 'com.fibaro.doorWindowSensor') {
                $this->genericTypes[ 'value' ] = 'OPENING_WINDOW';
            }else{
                $this->genericTypes[ 'value' ] = 'OPENING';
            }
            
        }

        public function getTypeName() {
            return '{{Capteur Ouverture}}';
        }  

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                if( $info == 'value' ) $template = 'door';                 
            }
            return $template;
        }   
               
       
    }    

    class FibaroSensorMotion extends FibaroSensor{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);

            $this->genericTypes[ 'value' ] = 'PRESENCE';
            
        }

        public function getTypeName() {
            return '{{Capteur présence}}';
        }  

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                if( $info == 'value' ) $template = 'presence';                 
            }
            return $template;
        }     
               
    }    

    class FibaroSensorWater extends FibaroSensor{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);

            $this->genericTypes[ 'value' ] = 'FLOOD';
            
        }

        public function getTypeName() {
            return '{{Capteur fuite}}';
        }  

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                if( $info == 'value' ) $template = 'alert';                 
            }
            return $template;
        }         
          
    }       

    class FibaroLock extends FibaroDevice{


        function __construct($_jsonInfo, $_jsonDetail) {
            
            parent::__construct($_jsonInfo, $_jsonDetail);
            
            $this->values[ 0 ] = array( 'name'  => 'value',
                                        'value' => $_jsonDetail->properties->value,
                                        'type'  => 'info' );

            $this->values[ 1 ] = array( 'name'  => 'batteryLevel',
                                        'value' => $_jsonDetail->properties->batteryLevel,
                                        'type'  => 'info' );                                        

            $this->genericTypes[ 'batteryLevel' ] = 'BATTERY';
            $this->genericTypes[ 'value' ] = 'LOCK_STATE';
            $this->genericTypes[ 'secure' ] = 'LOCK_CLOSE';
            $this->genericTypes[ 'unsecure' ] = 'LOCK_OPEN';

        }

        public function getCondition(){
            $condition = ' {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"==\\\\\\",\\\\n      property = \\\\\\"value\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = true\\\\n    }, {\\\\n      id = %idFibaro%,\\\\n      isTrigger = true,\\\\n      operator = \\\\\\"==\\\\\\",\\\\n      property = \\\\\\"value\\\\\\",\\\\n      type = \\\\\\"device\\\\\\",\\\\n      value = false\\\\n    }';
            $condition = str_replace('%idFibaro%', $this->getId(), $condition );
            return $condition;
        } 
        
        public function getTypeName() {
            return '{{Serrure connectée}}';
        }  

        public function getTemplate( $info ){
            $template = parent::getTemplate($info);
            if(!$template){
                if( $info == 'value' ) $template = 'lock';                 
            }
            return $template;
        }           

        public function getIcone(){
            $fibaroServ = FibaroServer::getInstance();
            return $fibaroServ->getIcone( $this->getIconeId() ) ?: 'plugins/homecenter/desktop/icones/lock.png';
        }         
    }    
?>