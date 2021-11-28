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

 
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}


// Test de connection 
//echo "<script>alert('A')</script>";
$deamonRunning = homecenter::deamonRunning();
//echo "<script>alert('B')</script>";
?>

<form class="form-horizontal">
    <fieldset>
        <?php
            echo '<div class="form-group">';
            echo '<label class="col-sm-4 control-label">{{Communication HomeCenter}}</label>';
            if (!$deamonRunning) {
                echo '<div class="col-sm-1"><span class="label label-danger">NOK</span></div>';
            } else {
                echo '<div class="col-sm-1"><span class="label label-success">OK</span></div>';
            }
            echo '</div>';
        ?>        
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Adresse IP de la HomeCenter}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="fibaroIP" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Login}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="fibaroLogin" value="80" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Mot de Passe}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" type="password" data-l1key="fibaroMDP" value="80" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Scène HomeCenter de MAJ}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" disabled='' data-l1key="fibaroScene" value="80" />
            </div>
        </div>
  </fieldset>
</form>

