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

	try {	
		require_once __DIR__.'/../../../../core/php/core.inc.php';
		require_once __DIR__.'/../class/homecenter.class.php';

		include_file('core', 'authentification', 'php');
		if (!isConnect('admin')) {
			throw new Exception(__('401 - Accès non autorisé', __FILE__));
		}

		function startConditions(){
			$condition = '{\\"conditions\\":\\"{\\\\n  conditions = {';
			return $condition;
		}

		function endConditions(){
			$condition = ' },\\\\n  operator = \\\\\\"any\\\\\\"\\\\n}\\",';			
			return $condition;
		}		

		function startActions(){
			$condition = '\\"actions\\":\\"\\\\r\\\\n-- Associations [ID Fibaro] = ID Jeedom\\\\r\\\\nlocal HC2Jeedom = {\\\\r\\\\n--ID_HC2 = ID_Jeedom, ';
			return $condition;
		}

		 function addModuleToActions( array $_mapping ){
			$condition = '\\\\r\\\\n  [%idFibaro%]=%jeedomCmdID%,';
			$condition = str_replace('%idFibaro%', $_mapping['fibaroID'], $condition );
			$condition = str_replace('%jeedomCmdID%', $_mapping['jeedomCmdID'], $condition );

			return $condition;
		}

		function endActions(  ){

			$_jeedomIP = network::getNetworkAccess();
			$jeedomAPIKey = jeedom::getApiKey();

			$condition = '\\\\r\\\\n}\\\\r\\\\n\\\\r\\\\nIP_Jeedom = \\\\\\"%jeedomIP%\\\\\\" -- IP Jeedom\\\\r\\\\napiKeyJeedom = \\\\\\"%jeedomAPIKey%\\\\\\" -- API key Jeedom\\\\r\\\\n---- Fin de paramètrage utilisateur ----\\\\r\\\\n\\\\r\\\\n\\\\r\\\\n--- /!\\\\\\\\ Ne rien modifier a partir d\'ici /!\\\\\\\\ ---\\\\r\\\\nlocal trigger = sourceTrigger\\\\r\\\\n\\\\r\\\\n--Construction de URL \\\\r\\\\nlocal http = net.HTTPClient()\\\\r\\\\nlocal url = IP_Jeedom .. \\\\\\"/core/api/jeeApi.php%3Fapikey%3D\\\\\\" ..  apiKeyJeedom .. \\\\\\"%26type%3Dcmd%26id%3D\\\\\\" .. HC2Jeedom[trigger.id]\\\\r\\\\n\\\\r\\\\nprint(url)\\\\r\\\\n\\\\r\\\\nlocal http = net.HTTPClient()\\\\r\\\\nhttp:request(url, {\\\\r\\\\n   options = {\\\\r\\\\n        method = \'GET\',\\\\r\\\\n            },\\\\r\\\\n    success = function(response)\\\\r\\\\n        print(response.status)\\\\r\\\\n        if response.status == 200 then\\\\r\\\\n            print(\'OK, réponse : \'.. response.data)\\\\r\\\\n        else\\\\r\\\\n            print(\\\\\\"Erreur : status=\\\\\\" .. tostring(response.status))\\\\r\\\\n        end\\\\r\\\\n    end,\\\\r\\\\n    error = function(message)\\\\r\\\\n        print(\\\\\\"Erreur : \\\\\\" .. message)\\\\r\\\\n    end\\\\r\\\\n})\\\\r\\\\n\\"}';			
				$condition = str_replace('%jeedomIP%', $_jeedomIP, $condition );
				$condition = str_replace('%jeedomAPIKey%', $jeedomAPIKey, $condition );
			return $condition;
		}		

		ajax::init();

		if (init('action') == 'DefineEqLogic' ) {

			$fibaroIds = init('fibaroIds');
			$status = 0;
			$error = "";
			$eqLogics = array();
			$n = 0;

			foreach( $fibaroIds as $fibaroId ){
				$eqLogic = homecenter::defineEqLogic($fibaroId);
				$eqLogics[$n] = utils::o2a($eqLogic);
				$n++;
			}
			
			ajax::success(json_encode(array('status' => $status, 'error' => $error, 'eqLogics' => $eqLogics )));
		}

		if (init('action') == 'UpdateScene' ) {
			$status = 0;
			$error = "";
			
			// Récupération de l'ensemble des équipements Jeedom 
			$plugin = plugin::byId('homecenter');
			$eqLogics = eqLogic::byType($plugin->getId());
			
			// Récupération de l'ensemble des équipements Fibaro
			$fibaroServ = FibaroServer::getInstance();    
			$modules = $fibaroServ->getDevicesList( true );

			//Préparation de la partie déclaration
			$contend = startConditions();
		
			$first = true;
			$n = 0;
			$mappingList = array();
			foreach( $eqLogics as $eqLogic ){
				foreach( $modules as $moduleKey => $module ){				
					if( $module->getId() == $eqLogic->getConfiguration("fibaroIdModule") ) {
						
						if( is_a( $module, 'Fibarogeneric') ){
							unset($modules[$moduleKey]);
							break;							
						}
						
						// Ajout d'un séparateur si ce n'est pas le premier module
						if( !$first ) $contend = $contend . ',';
						
						// Ajout de la condtion du module
						$contend = $contend .$module->getCondition();

						// Récupération de l'ID de l'action de refresh du module Jeedom
						$jeedomCmdID = $eqLogic->getCmd('action', 'refresh')->getId();
						

						//Préparation de l'action
						$mapping = array( 'fibaroID' 	=> $module->getId(),
										  'jeedomCmdID' => $jeedomCmdID );
						$mappingList[ $n ] = $mapping;
						unset($modules[$moduleKey]);
						$first = false;
						$n++;
						break;						
					}				
				}
			}

			$contend = $contend . endConditions();			

			//Préparation de la partie actions
			$contend = $contend . startActions();	
			foreach( $mappingList as $mapping ){
				$contend = $contend . addModuleToActions( $mapping );	
			}

			$contend = $contend . endActions( );	

			// Exécution si on a au moins un module à configurer
			if( !$n = 0 ) {
				//Mise à jour de la scene
				$result = $fibaroServ->changeScene( $contend );
			}
			
			ajax::success(json_encode(array('status' => $status, 'error' => $error, 'result' => $result )));			
		}

		$error = "La méthode '".init('action')."' n'existe pas dans 'homecenter.ajax.php'";
		throw new Exception($error, -1);
	} catch (Exception $e) {
		/* Catch exeption */
		ajax::error(json_encode(array('status' => $e->getCode(), 'error' => $e->getMessage() )));
	}

?>
