
 
<!--Liste les informations definis comme inconnues por le demon Abeille.-->
<!--exemple objets inconnus, commandes inconnus,......-->
<!--Pour essayer de compléter les modèles, EventConfig-->

<style>
/* Classe obligatoire pour les flèches */
.flecheDesc {
  width: 0; 
  height: 0; 
  float:right;
  margin: 10px;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 5px solid white;
}
.flecheAsc {
  width: 0; 
  height: 0;
  float:right;
  margin: 10px;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid white;
}

.avectri th {cursor:pointer;
	-webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -o-user-select: none;
  user-select: none;
}

.avectri th.selection .flecheDesc {border-bottom-color: white;}
.avectri th.selection .flecheAsc {border-top-color: white;}



</style>

<?php
    
    if (!isConnect('admin')) {
        throw new Exception('{{401 - Accès non autorisé}}');
    }

    require_once __DIR__.'/../../core/class/homecenter.class.php';

    // Récupération de tous les équipements visibles de FIBARO
    $fibaroServ = FibaroServer::getInstance();    
    $modules = $fibaroServ->getDevicesList();

    // Récupératation de tous les équipements
    $plugin = plugin::byId('homecenter');
    $eqLogics = eqLogic::byType($plugin->getId());
    
    // Récpération des pièces dans Fibaro
    $fibaroServ = FibaroServer::getInstance();    
    $fibaroRooms = $fibaroServ->getAllRooms();   
    

?>

<div class="row row-overflow">

        <!-- Barre d outils horizontale  -->
		<div class="col-xs-12">
            Légende : 
            <span class="label label-info" style="font-size:1em; margin-left:4px">HomeCenter</span>
            <span class="label label-success" style="font-size:1em; margin-left:4px">Jeedom</span>
            <a class="btn btn-success eqLogicAction pull-right" id="bt_save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
            <table class="table table-condensed tablesorter avectri" id="table_extratFibaro">
                <thead>
                    <tr>
                        <th class="header" data-toggle="tooltip" title="Trier par">{{ID}}</th>
                        <th class="header" data-toggle="tooltip" title="Trier par">{{Nom équipement}}</th>
                        <th class="header" data-toggle="tooltip" title="Trier par">{{pièce}}</th>
                        <th class="header" data-toggle="tooltip" title="Trier par">{{Types}}</th>
                        <th class="header" data-toggle="tooltip" title="Trier par">{{Etat}}</th>
                        <th class="header" data-toggle="tooltip" title="Trier par">{{Action}}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        
                        // Gestion en fonction des modules Fibaro présents
                        foreach ($modules as $module) {

                            // Recherche de l'équipement correspondant au module Fibaro
                            $eqSelected = null;
                            foreach( $eqLogics as $eqLogicKey => $eqLogic ){
                                if( $module->getId() == $eqLogic->getConfiguration("fibaroIdModule") ){
                                    $eqSelected = $eqLogic;
                                    unset($eqLogics[$eqLogicKey]);
                                    break;
                                }
                            }
                            
                            foreach( $fibaroRooms as $fibaroRoomKey => $fibaroRoom ){
                                if( $module->getRoomId() == $fibaroRoom->getId() ){
                                    $roomSelected = $fibaroRoom;
                                    break;
                                }
                            }   

                            if( !$module->isUsable( ) ){
                                $state = FibaroDevice::STATE_NOT_USABLE;
                            }else{
                                if(!isset($eqSelected)){
                                        $state = FibaroDevice::STATE_READY;
                                }else{
                                    if( !$eqSelected->getIsEnable() or $module->isDead() ){
                                        $state = FibaroDevice::STATE_DEAD;   
                                    }else{                                    
                                        $state = FibaroDevice::STATE_OK;
                                    }
                                }
                            }

                            if(isset($eqSelected)){
                                $jeedomId = $eqSelected->getId();
                                $jeedomName = $eqSelected->getName();
                                
                                if( $eqSelected->getObject() ){
                                    $jeedomRoom = $eqSelected->getObject()->getName();
                                }else{
                                    $jeedomRoom = '/';
                                }
                                
                            }else{
                                $jeedomId = $jeedomName = $jeedomRoom = '';
                            }
                            

                            // Colonne des ID
                            echo "\n\n\n\n<tr>".'<td><span class="label label-info" style="font-size: 1em; cursor: default;">' .$module->getId() .'</span>';
                            if(!$jeedomId){
                                echo '<span class="separatorId" style="display:none"> / </span>';
                            }else{
                                echo '<span> / </span>';
                            }
                            echo '<span class="label label-success jeedomId" style="font-size: 1em; cursor: default;">' .$jeedomId .'</span>';              
                            echo '</td>';
                            
                            // Colonne des noms d'équipement
                            echo '<td><span class="label label-info" style="font-size: 1em; cursor: default;">'.$module->getName(). '</span>';
                            if(!$jeedomName){
                                echo '<span class="separatorName" style="display:none"> / </span>';
                            }else{
                                echo '<span> / </span>';
                            }
                            echo '<span class="label label-success jeedomName" style="font-size: 1em; cursor: default;">' .$jeedomName .'</span>';
                            echo '</td>';

                            // Colonne du nom de la pièce
                            echo '<td>';
                            if(isset($roomSelected)){
                                echo '<span class="label label-info" style="font-size: 1em; cursor: default;">'.$roomSelected->getName(). '</span>';                                
                                if(!$jeedomRoom){
                                    echo '<span class="separatorRoom" style="display:none"> / </span>';
                                }else{
                                    echo '<span> / </span>';
                                }
                            }
                            if( $jeedomRoom == '/'){
                                echo '<span class="label label-danger jeedomRoom" style="font-size: 1em; cursor: default;">{{Aucune}}</span>';
                            }else{
                                echo '<span class="label label-success jeedomRoom" style="font-size: 1em; cursor: default;">' .$jeedomRoom .'</span>';
                            }
                            echo '</td>';

                            // Colonne du nom du type 
                            echo '<td><span class="label label-info" style="font-size: 1em; cursor: default;">'.$module->getTypeName(). '</span></td>';
                            
                            // Colonne sur l'état (Non compatible / Jumelé / Disponible)
                            // Bouton d'action
                            switch ($state){

                                case FibaroDevice::STATE_NOT_USABLE:
                                echo '<td><span class="label label-danger jeedomState" style="font-size: 1em; cursor: default;">{{Non compatible}}</span></td>';
                                echo '<td><input type="checkbox" class="objectAttr" disabled></input></td>';
                                //echo '<td><span id="bt_add_device" title="Jumeler le device"><i class="fas fa-plus-circle" disabled="" style="font-size:160%;color:#80808073;" ></i></span></td>';
                                //echo '&nbsp;&nbsp;&nbsp;';
                                //echo '<span id="bt_add_device" title="modifier le device"><i class="fas fa-edit" style="font-size:160%;color:#80808073;"></i></span></td>';                                   
                                break;

                                case FibaroDevice::STATE_READY:
                                echo '<td><span class="label label-warning jeedomState" style="font-size: 1em; cursor: default;">{{Disponible}}</span></td>'; 
                                echo '<td><input type="checkbox" class="objectAttr" data-fibaro_id="' . $module->getId() . '"></input></td>';
                                //echo '<td><span class="addDevice cursor" data-fibaro_id="' . $module->getId() . '" title="Jumeler le device"><i class="fas fa-plus-circle" disabled="" style="font-size:160%;color:#5bc0de;" ></i></span></td>';
                                //echo '&nbsp;&nbsp;&nbsp;';
                                //echo '<span id="bt_add_device" title="modifier le device"><i class="fas fa-edit" style="font-size:160%;color:#80808073;"></i></span></td>';                                    
                                break;
                                
                                case FibaroDevice::STATE_OK:
                                echo '<td><span class="label label-success jeedomState" style="font-size: 1em; cursor: default;">{{jumelé}}</span></td>';
                                echo '<td><input type="checkbox" class="objectAttr" disabled></input></td>';
                                //echo '<td><span id="bt_add_device" title="Jumeler le device"><i class="fas fa-plus-circle" disabled="" style="font-size:160%;color:#80808073;" ></i></span></td>';
                                //echo '&nbsp;&nbsp;&nbsp;';
                                //echo '<span class="cursor" id="bt_add_device" title="modifier le device"><i class="fas fa-edit" style="font-size:160%;color:#5bc0de;"></i></span></td>';                                    
                                break;                                

                                case FibaroDevice::STATE_DEAD:
                                echo '<td><span class="label jeedomState" style="font-size: 1em; cursor: default;color:white; background-color: purple">{{Sans réponse}}</span></td>';
                                echo '<td><input type="checkbox" class="objectAttr" disabled></input></td>';
                                //echo '<td><span id="bt_add_device" title="Jumeler le device"><i class="fas fa-plus-circle" disabled="" style="font-size:160%;color:#80808073;" ></i></span></td>';
                                //echo '&nbsp;&nbsp;&nbsp;';
                                //echo '<span id="bt_add_device" title="modifier le device"><i class="fas fa-edit" style="font-size:160%;color:#80808073;"></i></span></td>';                                   
                                break;
                            }

                            echo '</tr>';
                        }

                        // Gestion des modules présents dans Jeedom mais désinstaller dans Fibaro
                        foreach( $eqLogics as $eqLogicKey => $eqLogic ){
                            
                            
                            if( $eqLogic->getObject() ){
                                $jeedomRoom = $eqLogic->getObject()->getName();
                            }else{
                                $jeedomRoom = '/';                                
                            }
                            
                            echo '<tr>';
                            echo '<td><span class="label label-success" style="font-size: 1em; cursor: default;">' .$eqLogic->getId() .'</span></td>';
                            echo '<td><span class="label label-success" style="font-size: 1em; cursor: default;">' .$eqLogic->getName() .'</span></td>';
                            if( $jeedomRoom == '/'){
                                echo '<td><span class="label label-danger jeedomRoom" style="font-size: 1em; cursor: default;">{{Aucune}}</span></td>';
                            }else{
                                echo '<td><span class="label label-success jeedomRoom" style="font-size: 1em; cursor: default;">' .$jeedomRoom .'</span></td>';
                            }
                            echo '<td></td>';
                            echo '<td><span class="label jeedomState" style="font-size: 1em; cursor: default;color:white; background-color: purple">{{Sans réponse}}</span></td>';
                            echo '<td><input type="checkbox" class="objectAttr" disabled></input></td>';
                            //echo '<td><span id="bt_add_device" title="Jumeler le device"><i class="fas fa-plus-circle" disabled="" style="font-size:160%;color:#80808073;" ></i></span></td>';
                            echo '</tr>';
                            
                        }
                    ?>
                </tbody>
            </table>
              
        </div>    

    </div> <!-- Fin - Barre d outils horizontale  -->

</div>

<?php include_file('desktop', 'HCState', 'js', 'homecenter');?>
