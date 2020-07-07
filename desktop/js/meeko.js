
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

$('#nurseryModal').on('click', function() {
$('#md_modal').dialog({title: 'Créche '+$(this).data('name')});
$('#md_modal').load('index.php?v=d&plugin=meeko&modal=meeko.nurseries').dialog('open');
});

 $('.eqLogicAction[data-action=syncMeeko]').on('click', function () {
   $.ajax({
     type: "POST",
     url: "plugins/meeko/core/ajax/meeko.ajax.php",
     data: {
         action: "syncMeeko"
     },
     error: function (error) {
       $('#div_alert').showAlert({message: error.message, level: 'danger'});
     },
     success: function (data) {
       window.location = 'index.php?v=d&m=meeko&p=meeko&syncSuccessFull=1'
     }
   });
 });

 if (getUrlVars('syncSuccessFull') == 1) {
      $('#div_alert').showAlert({message: '{{Données récupérées avec succès}}', level: 'success'})
      PREVIOUS_PAGE=window.location.href.split('&syncSuccessFull')[0]+window.location.hash
      window.history.replaceState({}, document.title, window.location.href.split('&syncSuccessFull')[0]+window.location.hash)
 }

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
 /*
  * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
  */
 function addCmdToTable(_cmd) {
     if (!isset(_cmd)) {
         var _cmd = {configuration: {}};
     }
     if (!isset(_cmd.configuration)) {
         _cmd.configuration = {};
     }
     var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
     tr += '<td>';
     tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> {{Icône}}</a>';
     tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left:10px;"></span>';
     tr += '</td>';
     tr += '<td>';
     tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
     tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}">';
     tr += '</td>';
     tr += '<td>';
     tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
     tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
     tr += '</td>';
     tr += '<td class="text-right">';
     tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/> {{Afficher}} </label></span> ';
     if(!isset(_cmd.type) || _cmd.type == 'info') {
       tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/> {{Historiser}} </label></span> ';
     }
     tr += '</td>';
     tr += '<td>';
     if (is_numeric(_cmd.id)) {
         tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
         tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
     }
     tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
     tr += '</td>';
     tr += '</tr>';
     $('#table_cmd tbody').append(tr);
     $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
     if (isset(_cmd.type)) {
         $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
     }
     jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
 }
