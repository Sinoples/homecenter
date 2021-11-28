
<?php

    class FibaroRoom {

        private $name, $id, $sensorTempId, $sensorLightId, $sensorHumidId;
        public static $GLOBALS_rooms = array();

        static public function getById( $_roomId ){
            if ( !isset( self::$GLOBALS_rooms[$_roomId] ) ){
                $fibaroServ = FibaroServer::getInstance();
                $JsonRoom = $fibaroServ->getRoomByID($_roomId);
                
                if( $JsonRoom ) {              
                    $room = new FibaroRoom( $JsonRoom );
                    self::$GLOBALS_rooms[$_roomId] = $room;
                }else{
                    log::add('homecenter', 'debug', 'Fibaro pièce '. $_roomId .': Inconnue' );
                }

            }else{
                $room = self::$GLOBALS_rooms[$_roomId];
            }

            log::add('homecenter', 'debug', 'Fibaro Room:'. json_encode( utils::o2a( $room ) ) );
            return $room;

        }

        function __construct($jsonData) {
            $this->name = $jsonData->name;
            $this->id = $jsonData->id;
            $this->sensorTempId = $jsonData->defaultSensors->temperature;
            $this->sensorLightId = $jsonData->defaultSensors->light;
            $this->sensorHumidId = $jsonData->defaultSensors->humidity;
        }

        public function getName() {
            return $this->name;
        }

        public function getId() {
            return $this->id;
        }

        public function getSensorTempId() {
            return $this->sensorTempId;
        }
    }
    
?>