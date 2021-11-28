    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l équipement template}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l équipement}}"/>
                </div>
                </div>
                <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
                foreach (jeeObject::all() as $object) {
                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                }
                ?>
                </select>
              </div> 
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-9">
                <?php
                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                echo '<label class="checkbox-inline">';
                echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                echo '</label>';
                }
                ?>
              </div>
            </div>
            
            <div class="form-group">
              <label class="col-sm-3 control-label"></label>
              <div class="col-sm-9">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{ID Device}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" disabled="" data-l1key="configuration" data-l2key="fibaroIdModule" placeholder="ID du Module HomeCenter"/>
              </div>
            </div>
          </fieldset>
        </form>
      </div>