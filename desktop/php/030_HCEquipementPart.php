<legend><i class="fa fa-table"></i> {{Mes équipements}}</legend>
<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>  
<div class="eqLogicThumbnailContainer">
    <?php
        foreach ($eqLogics as $eqLogic) {  
            $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
            if(!$isConnected) $opacity = 'disableCard';
            echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '" >';
            echo '<img src="' . $eqLogic->getImage() . '" style="min-height: 75px !important;"/>';
            echo '<br>';
            echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
            echo '</div>';
        }
    ?>
</div>


