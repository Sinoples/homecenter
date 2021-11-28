<!-- This is equipement page opened when clicking on it.
     Displays main infos + specific params + commands. -->

<?php

    //require_once __DIR__.'/../../core/class/fibaro.class.php';

    if (!isset($_GET['id']))
        exit("ERROR: Missing 'id'");
    if (!is_numeric($_GET['id']))
        exit("ERROR: 'id' is not numeric");

    $eqId = $_GET['id'];  
    $RoomObject = jeeObject::byId($eqId);
    $Name = $RoomObject->getName();
    
    
    $fibaroServ = FibaroServer::getInstance();    
    $fibaroRooms = $fibaroServ->getAllRooms();  
    
    echo '<script>var js_eqId = '.$eqId.';</script>'; // PHP to JS
    
    //echo "<script>alert('OK')</script>";       
    
?>

<div class="row row-overflow" id="abeilleModal">
</div>

<div class="col-xs-12 eqLogic" style="padding-top: 5px">
    <div class="col-xs-12 object">
        <?php
            if( $fibaroServ->isConnected() )
            echo '<a class="btn btn-success eqLogicAction pull-right" id="bt_saveObject"><i class="far fa-check-circle"></i> {{Sauvegarder}}</a>';
        ?>

        <ul class="nav nav-tabs" role="tablist">
            <li role="tab"               ><a href="index.php?v=d&m=homecenter&p=homecenter"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="tab" class="active"><a href="#objecttab"><i class="fas fa-home"></i> {{pièce}}</a></li>
        </ul>

        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">

            <!-- Displays Jeedom specifics  -->
            <div role="tabpanel" class="tab-pane active" id="objecttab">
                <?php include 'HCRoomObj-Main.php'; ?>
            </div>

        </div>
    </div>
</div>


<?php include_file('core', 'plugin.template', 'js'); ?>
<?php include_file('desktop', 'HCRoom', 'js', 'homecenter');?>
