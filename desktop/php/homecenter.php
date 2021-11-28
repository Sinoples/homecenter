<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
  
  $plugin = plugin::byId('homecenter');
  sendVarToJS('eqType', $plugin->getId()); 
  $eqLogics = eqLogic::byType($plugin->getId());
  $allObject = jeeObject::all(true);

  // Chargement de tous les équipements Fibaro en mémoire
  $fibaroServ = FibaroServer::getInstance( );

  // On test la connexion 
  $isConnected = $fibaroServ->isConnected();
  sendVarToJS('isConnected', $isConnected ); 
  
  $isBlocked = $fibaroServ->isBlocked();
  sendVarToJS('isBlocked', $isBlocked );   


  $fibaroServ->getDevicesList( true );

?>

<div class="row row-overflow">
  <!-- <form action="plugins/Abeille/desktop/php/AbeilleFormAction.php" method="post"> -->

  <!-- Barre d outils horizontale  -->
  <div class="col-xs-12 eqLogicThumbnailDisplay">

    <!-- Gestion des modales / Commandes  -->
    <?php include '010_HCGestionPart.php'; ?>
    <br>

    <!-- Gestion des pièces  -->
    <?php include '020_HCPiecePart.php'; ?>

    <!-- Gestion des équipements  -->
    <?php include '030_HCEquipementPart.php'; ?>


  </div>

  <!-- </form> -->
  
  <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">

    <!-- Gestion bouttons  -->
    <?php include '110_HCDisplayBT.php'; ?>

    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
  
      <!-- Onglet Equipement  -->
      <?php include '111_HCDisplayEquip.php'; ?>
      
      <!-- Onglet Commandes  -->
      <?php include '112_HCDisplayCmd.php'; ?>    
      
    </div>

  </div>

</div>


<!-- Scripts -->
<?php include '990_HCScript.php'; ?>

