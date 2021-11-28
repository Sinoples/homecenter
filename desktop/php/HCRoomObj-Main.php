<div class="col-xs-12 object"  id="div_conf">
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
        <div role="tabpanel" class="tab-pane active" id="objecttab">
            <br/>
            <form class="form-horizontal">    
                <fieldset>
                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <h3 class="col-sm-5" style="text-align:left">{{JEEDOM}}</h3>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ID de la pièce}}</label>
                        <div class="col-sm-4 cmd" data-type="info" data-subtype="string" data-version="dashboard">				  
                            <input class="form-control objectAttr" type="text" data-l1key="id" disabled=""/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Nom de la pièce}}</label>
                        <div class="col-sm-4 cmd" data-type="info" data-subtype="string" data-version="dashboard">	
                            <input class="form-control objectAttr" type="text" data-l1key="name" disabled=""/>
                        </div>
                    </div>

                    <br>

                    <div class="form-group">
                        <div class="col-sm-3"></div>
                        <h3 class="col-sm-5" style="text-align:left">{{HomeCenter}}</h3>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ID de la pièce}}</label>
                        <div class="col-lg-3 col-md-4 col-sm-5 col-xs-6">
                            <?php
                                echo  '<input class="form-control objectAttr" disabled="" id="idFibaro">'
                            ?>
                        </div>        
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{Nom de la pièce}}</label>
                        <div class="col-sm-3">
                            <select class="form-control objectAttr" class="form-control objectAttr" type="text" data-l1key="configuration" data-l2key="idFibaro" id="SelectRoom">
                                <option value="0">{{Aucune pièce}}</option>
                                    <?php
                                        foreach ( $fibaroRooms as $room) {
                                            echo '<option value="'.$room->getId().'">'.$room->getName().'</option>';
                                        }
                                    ?>
                            </select>
                        </div>        
                    </div>
                </fieldset>                
            </form>
        </div>
    </div>
</div>