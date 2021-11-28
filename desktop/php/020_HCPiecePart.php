<legend><i class="fas fa-image"></i> {{Mes pièces}}</legend>
<input class="form-control" placeholder="{{Rechercher}}" style="margin-bottom:4px;" id="in_searchObject" />
<div class="objectListContainer">
    <?php
    foreach ($allObject as $object) {
        //echo "<script>alert(". $object->getConfiguration("idFibaro") .")</script>";

        echo '<div class="objectDisplayCard cursor w-icons" data-object_id="' . $object->getId() . '" data-object_name="' . $object->getName() . '" data-object_icon=\'' . $object->getDisplay('icon', '<i class="fas fa-lemon-o"></i>') . '\' style=" height : 160px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
        echo "<center style='margin-top:10px;'>";
        echo str_replace('></i>', ' style="font-size : 5em;color:#767676;"></i>', $object->getDisplay('icon', '<i class="fas fa-lemon-o"></i>'));
        echo "</center>";
        echo '<center><span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"';
        if( $object->getConfiguration("idFibaro") > 0 and $isConnected == true ){
            echo 'class="label label-success">';
        }else{
            echo 'class="label label-danger">';
        }
        echo $object->getName() . '</span></center><br/>';
        //class="label label-success">' 
        //echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center class="name">' . $object->getName() . '</center></span><br/>';
        echo '<center style="font-size :0.7em">';
        echo $object->getHtmlSummary();
        echo "</center>";
        echo '</div>';
        
    }
    ?>
</div>
