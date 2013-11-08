<?php
/**
 * sysPass
 * 
 * @author nuxsmin
 * @link http://syspass.org
 * @copyright 2012 Rubén Domínguez nuxsmin@syspass.org
 *  
 * This file is part of sysPass.
 *
 * sysPass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sysPass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
 defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

$activeTab = $data['active'];

?>

<div id="title" class="midroundup titleNormal">
    <? echo _('Importar phpPMS'); ?>
</div>

<form METHOD="post" name="frmMigrate" id="frmMigrate">
    <table class="data round">
        <tr>
            <td class="descField">
                <? echo _('Usuario BBDD'); ?>
                <? SP_Common::printHelpButton("config", 0); ?>
            </td>
            <td class="valField">
                <input type="text" name="dbuser" value="" />
            </td>
        </tr>
        <tr>
            <td class="descField">
                <? echo _('Clave BBDD'); ?>
            </td>
            <td class="valField">
                <input type="password" name="dbpass" value=""/>
            </td>
        </tr>	
        <tr>
            <td class="descField">
                <? echo _('Nombre BBDD'); ?>
                <? SP_Common::printHelpButton("config", 1); ?>
            </td>
            <td class="valField">
                <input type="text" name="dbname" value="phppms" />
            </td>
        </tr>
        <tr>
            <td class="descField">
                <? echo _('Servidor BBDD'); ?>
                <? SP_Common::printHelpButton("config", 2); ?>
            </td>
            <td class="valField">
                <input type="text" name="dbhost" value="localhost" />
            </td>
        </tr>
        <tr>
            <td class="descField">
                <? echo _('Confirmar'); ?>
            </td>
            <td class="valField">
                <img src="imgs/warning.png" ALT="<? echo _('Atención'); ?>" class="iconMini" />
                <? echo _('Los datos actuales serán borrados (excepto el usuario actual)'); ?>
                <br><br>
                <label for="chkmigrate"><? echo _('NO'); ?></label>
                <input type="checkbox" name="chkmigrate" id="chkmigrate" class="checkbox" />
            </td>
        </tr>
    </table>

	<input type="hidden" name="active" value="<? echo $activeTab ?>" />
    <input type="hidden" name="action" value="migrate" />
    <input type="hidden" name="sk" value="<? echo SP_Common::getSessionKey(TRUE); ?>">
</form>

<div class="action">
    <ul>
        <li>
            <img src="imgs/start.png" title="<? echo _('Iniciar'); ?>" class="inputImg" OnClick="configMgmt('migrate');" />
        </li>
    </ul>
</div>

<script>
    $('#frmMigrate .checkbox').button();
    $('#frmMigrate .ui-button').click(function(){
        // El cambio de clase se produce durante el evento de click
        // Si tiene la clase significa que el estado anterior era ON y ahora es OFF
        if ( $(this).hasClass('ui-state-active') ){
            $(this).children().html('<? echo _('NO'); ?>');
        } else{
            $(this).children().html('<? echo _('SI'); ?>');
        }
    });
</script>