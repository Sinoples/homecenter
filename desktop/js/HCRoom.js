
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
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.*/
 
$.showLoading();
loadObjectConfiguration(js_eqId);  

$.hideLoading();


$('#SelectRoom').change(function () {
    var roomID = $(this).value();
    $('#idFibaro').value(roomID);
});



function loadObjectConfiguration(_id){

  $(".objectDisplayCard").removeClass('active');
  $('.objectDisplayCard[data-object_id='+_id+']').addClass('active');

  
  $(this).addClass('active');
  jeedom.object.byId({
    id: _id,
    cache: false,
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {

      $('.objectAttr').value('');
      //$('.objectAttr[data-l1key=father_id] option').show();
      //$('#summarytab input[type=checkbox]').value(0);

      $('.object').setValues(data, '.objectAttr');
      $('.div_summary').empty();
      $('.tabnumber').empty();
      if (isset(data.configuration) && isset(data.configuration.summary)) {
        for(var i in data.configuration.summary){
          var el = $('.type'+i);
          if(el != undefined){
            for(var j in data.configuration.summary[i]){
              addSummaryInfo(el,data.configuration.summary[i][j]);
            }
            if (data.configuration.summary[i].length != 0){
              $('.summarytabnumber'+i).append('(' + data.configuration.summary[i].length + ')');
            }
          }
          
        }
      }
      modifyWithoutSave = false;
    }
  });
}

$("#bt_addObject,#bt_addObject2").on('click', function (event) {
  bootbox.prompt("Nom de l'objet ?", function (result) {
    if (result !== null) {
      jeedom.object.save({
        object: {name: result, isVisible: 1},
        error: function (error) {
          $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (data) {
          modifyWithoutSave = false;
          loadPage('index.php?v=d&p=object&id=' + data.id + '&saveSuccessFull=1');
          $('#div_alert').showAlert({message: '{{Sauvegarde effectuée avec succès}}', level: 'success'});
        }
      });
    }
  });
});

jwerty.key('ctrl+s/⌘+s', function (e) {
  e.preventDefault();
  $("#bt_saveObject").click();
});

$('.objectAttr[data-l1key=display][data-l2key=icon]').on('dblclick',function(){
  $('.objectAttr[data-l1key=display][data-l2key=icon]').value('');
});

$("#bt_saveObject").on('click', function (event) {
    var object = $('.object').getValues('.objectAttr')[0];
    
    if (!isset(object.configuration)) {
        object.configuration = {};
    }
    if (!isset(object.configuration.summary)) {
        object.configuration.summary = {};
    }

    $('.object .div_summary').each(function () {
        var type = $(this).attr('data-type');
        object.configuration.summary[type] = [];
        summaries = {};
        $(this).find('.summary').each(function () {
            var summary = $(this).getValues('.summaryAttr')[0];
            object.configuration.summary[type].push(summary);
        });
    });

    jeedom.object.save({
    object: object,
    error: function (error) {
        $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
        modifyWithoutSave = false;
        //window.location = 'index.php?v=d&p=object&id=' + data.id + '&saveSuccessFull=1';
    }
    });
    return false;
});
