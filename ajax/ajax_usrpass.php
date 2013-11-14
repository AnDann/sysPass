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

define('APP_ROOT', '..');
include_once (APP_ROOT."/inc/init.php");

SP_Util::checkReferer('GET');

if ( ! SP_Init::isLoggedIn() ){
    return;
}

$userId = SP_Common::parseParams('g', 'usrid', FALSE);

if ( ! $userId ) {
    return;
}

$strError = '<div id="fancyView" class="msgError">'._('No tiene permisos para realizar esta operación').'</div>';

SP_Users::checkUserAccess("acceditpass",$userId) || die ($strError);

?>

<div id="fancyContainer" align="center">
    <h2 class="midround"><?php echo _('Cambio de Clave'); ?></h2>
    <form method="post" name="updUsrPass" id="frmUpdUsrPass">
        <table class="fancydata">
        <tr>
            <td class="descField">
                <?php echo _('Clave'); ?>
            </td>
            <td class="valField">
                <input type="password" id="usrpass" name="pass" title="<?php echo _('Clave'); ?>" class="txtpass" OnFocus="$('#passLevel').show(); $('#resFancyAccion').hide();" OnKeyUp="checkPassLevel(this.value, 'fancyContainer')" />
                <img src="imgs/genpass.png" title="<?php echo _('Generar clave aleatoria'); ?>" class="inputImg" OnClick="$('#resFancyAccion').hide(); password(11,true,false,'fancyContainer');" />
            </td>
        </tr>
        <tr>
            <td class="descField">
                <?php echo _('Clave (repetir)'); ?></td>
            <td class="valField">
                <input type="password" id="usrpassv" name="passv" title="<?php echo _('Clave (repetir)'); ?>" class="txtpassv" />
            </td>
        </tr>
    </table>
    <input type="hidden" name="id" value="<?php echo $userId; ?>" />
    <input type="hidden" name="type" value="1" />
    <input type="hidden" name="action" value="3" />
    <input type="hidden" name="sk" value="<?php echo SP_Common::getSessionKey(); ?>">
</form>

    <div id="resCheck">
        <span id="passLevel" title="<?php echo _('Nivel de fortaleza de la clave'); ?>" ></span>
        <BR><BR>
        <span id="resFancyAccion"></span>
    </div>
    <div class="action-in-box">
        <ul>
            <li>
                <img src="imgs/check.png" title="<?php echo _('Guardar'); ?>" class="inputImg" OnClick="usersMgmt('frmUpdUsrPass')" alt="<?php echo _('Guardar'); ?>"/>
            </li>
        </ul>
    </div>
</div>