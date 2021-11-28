<?php

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

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<style>
    .bs-sidenav .list-group-item {
        padding : 2px 2px 2px 2px;
    }
    .topBar {
        height: 40px;
        margin-top: 20px;
        margin-bottom: 10px;
    }
</style>

<style>
    input[type=radio],
    input[type=checkbox] {
        display: none;
    }

    input[type=radio] + label,
    input[type=checkbox] + label {
        display: inline-block;
        margin: -2px;
        padding: 4px 12px;
        margin-bottom: 0;
        font-size: 14px;
        line-height: 20px;
        color: #333;
        text-align: center;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
        vertical-align: middle;
        cursor: pointer;
        background-color: #f5f5f5;
        background-image: -moz-linear-gradient(top, #fff, #e6e6e6);
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fff), to(#e6e6e6));
        background-image: -webkit-linear-gradient(top, #fff, #e6e6e6);
        background-image: -o-linear-gradient(top, #fff, #e6e6e6);
        background-image: linear-gradient(to bottom, #fff, #e6e6e6);
        background-repeat: repeat-x;
        border: 1px solid #ccc;
        border-color: #e6e6e6 #e6e6e6 #bfbfbf;
        border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
        border-bottom-color: #b3b3b3;
        filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffe6e6e6', GradientType=0) filter: progid: DXImageTransform.Microsoft.gradient(enabled=false);
        -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
        -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    input[type=radio]:checked + label,
    input[type=checkbox]:checked + label {
        background-image: none;
        outline: 0;
        -webkit-box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 0 1px 2px rgba(0, 0, 0, 0.05);
        -moz-box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 0 1px 2px rgba(0, 0, 0, 0.05);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15), 0 1px 2px rgba(0, 0, 0, 0.05);
        background-color: #e0e0e0;
    }
</style>

<div class="row row-overflow">
    <div class="col-xs-12" style="border-left: solid 1px #EEE; height:650px; padding-left: 25px;overflow-y:hidden;overflow-x:hidden;">
        <div class="topBar">
            <span id="" style="width:90%; font-weight: bold">{{Configuration dans la HomeCenter 3 afin de récupérer le retour d'état des modules : }}</span>
            <br>
            <span id="" style="width:90%">{{Créer une nouvelle scène dans la HC3 en LUA et copier/coller le code ci-dessous dans chaque partie de la scène, au niveau de la déclaration puis au niveau de l'action}}</span>
        </div>
        <br>
        <DIV ALIGN="CENTER" class="radio-group">
            <input type="radio" id="radio1" name="radios" value="all" checked>
            <label for="radio1">Déclaration</label>
            <input type="radio" id="radio2" name="radios" value="false">
            <label for="radio2">Action</label>
        </div>     
        <pre id="idPreResults" style="height:80%;width:100%;margin-top:5px;">  
        </pre>
    </div>
</div>

<?php include_file('desktop', 'HCCode', 'js', 'homecenter');?>
