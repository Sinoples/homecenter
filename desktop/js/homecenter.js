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

$.showLoading();
if(isBlocked){
  $('#div_alert').showAlert({message: 'Connection bloquée par la Box FIBARO, temps avant déblocage : ' + isBlocked + ' seconde' , level: 'danger'});
}else{
  if(!isConnected)
  $('#div_alert').showAlert({message: 'Erreur de configuration : Impossible de contacter la box FIBARO (voir la configuration)', level: 'danger'});

}
$.hideLoading();


$('#bt_add_all').on('click', function () {
    $('#md_modal').dialog({title: "{{Ajout en masse}}"});
    $('#md_modal').load('index.php?v=d&plugin=homecenter&modal=HCState').dialog('open');
 });


$('#bt_maj_scene').on('click', function () {

  $.ajax({
    type: 'POST',
    url: 'plugins/homecenter/core/ajax/homecenter.ajax.php',
    data: {
        action: 'UpdateScene',
    },
    dataType: 'json',
    global: false,
    
    error: function (request, status, error) {  
      $('#div_alert').showAlert({message: error.message, level: 'danger'});   
    },

    success: function (json_res) {

      res = JSON.parse(json_res.result);
      if(res.result){
        $('#div_alert').showAlert({message: '{{MAJ de la scène effectuée avec succès}}', level: 'success'});      
      }else{
        $('#div_alert').showAlert({message: '{{Erreur lors de la MAJ de la scène}}', level: 'danger'});  
      }

    }

  });

});

// Gestion d'un équipement
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

// Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  	tr += '<td>';
    tr += '<div class="row">';
    tr += '<div class="col-sm-6">';
    tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fa fa-flag"></i> Icone</a>';
    tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
    tr += '</div>';
    tr += '<div class="col-sm-6">';
  
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name">';
    tr += '</div>';
    tr += '</div>';
    tr += '<select class="cmdAttr form-control tooltips input-sm" data-l1key="value" style="display : none;margin-top : 5px;" title="La valeur de la commande vaut par defaut la commande">';
    tr += '<option value="">Aucune</option>';
    tr += '</select>';
    tr += '</td>';
  
  	tr += '<td class="expertModeVisible">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';

  	//tr += '<td class="expertModeVisible"><input class="cmdAttr form-control input-sm" data-l1key="logicalId" value="0">';
    //tr += '</td>';

    tr += '<td>';
  	tr += '<span><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" />{{Historiser}} <br/></span>';
 	tr += '<span><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" />{{Afficher}} <br/></span>';

 	tr += '</td>';		
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
  
    //$('#table_cmd tbody').append(tr);
    //$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    //if (isset(_cmd.type)) {
        //$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    //}
    //jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
$('#table_cmd tbody').append(tr);
    var tr = $('#table_cmd tbody tr:last');
    jeedom.eqLogic.builSelectCmd({
        id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
        filter: {type: 'info'},
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result);
            tr.setValues(_cmd, '.cmdAttr');
            jeedom.cmd.changeType(tr, init(_cmd.subType));
        }
    });  
}

// Gestion des pièces
$('#SelectRoom').change(function () {
  var roomID = $(this).value();
  $('#idFibaro').value(roomID);
});


$('#in_searchObject').keyup(function() {
  var search = $(this).value()
  if (search == '') {
    $('.objectDisplayCard').show()
    $('.objectListContainer').packery()
    return
  }
  search = normTextLower(search)

  $('.objectDisplayCard').hide()
  var text

  $('.objectDisplayCard').each(function() {
    text = normTextLower($(this).text())
    if (text.indexOf(search) >= 0) {
      $(this).closest('.objectDisplayCard').show()
    }
  })
  $('.objectListContainer').packery()
})


setTimeout(function(){
  $('.objectListContainer').packery();
},100);

$(".objectDisplayCard").on('click', function () {
  console.log("objectDisplayCard click");
  
  if (!isset($(this).attr('data-object_id'))) {
    console.log("ERROR: 'data-object_id' is not defined");
    return;
  }
  
  var roomId = $(this).attr('data-object_id');
  console.log("rommId="+roomId);
  window.location.href = "index.php?v=d&m=homecenter&p=HCRoom&id="+roomId;
});


// Bouton de test DEV

$('#bt_add_test').on('click', function () {

  $('#md_modal').dialog({title: "{{Code homecenter}}"});
  $('#md_modal').load('index.php?v=d&plugin=homecenter&modal=HCCode').dialog('open');


});

