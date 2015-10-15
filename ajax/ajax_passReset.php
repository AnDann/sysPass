<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2015 Rubén Domínguez nuxsmin@syspass.org
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

use SP\Auth\Auth;
use SP\Core\SessionUtil;
use SP\Html\Html;
use SP\Http\Request;
use SP\Http\Response;
use SP\Log\Email;
use SP\Log\Log;
use SP\Mgmt\User\UserPass;
use SP\Mgmt\User\UserPassRecover;
use SP\Mgmt\User\UserUtil;

define('APP_ROOT', '..');

require_once APP_ROOT . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'Base.php';

Request::checkReferer('POST');

$sk = Request::analyze('sk', false);

if (!$sk || !SessionUtil::checkSessionKey($sk)) {
    Response::printJSON(_('CONSULTA INVÁLIDA'));
}

$userLogin = Request::analyze('login');
$userEmail = Request::analyze('email');
$userPass = Request::analyzeEncrypted('pass');
$userPassR = Request::analyzeEncrypted('passR');
$hash = Request::analyze('hash');
$time = Request::analyze('time');

$message['action'] = _('Recuperación de Clave');

if ($userLogin && $userEmail) {
    $Log = new Log(_('Recuperación de Clave'));

    $Log->addDetails(Html::strongText(_('Solicitado para')), sprintf('%s (%s)', $userLogin, $userEmail));

    if (Auth::mailPassRecover($userLogin, $userEmail)) {
        $Log->addDescription(_('Solicitud enviada'));
        $Log->writeLog();

        Response::printJSON(_('Solicitud enviada') . ';;' . _('En breve recibirá un correo para completar la solicitud.'), 0, 'goLogin();');
    }

    $Log->addDescription(_('Solicitud no enviada'));
    $Log->writeLog();

    Email::sendEmail($Log);

    Response::printJSON(_('No se ha podido realizar la solicitud. Consulte con el administrador.'));
} elseif ($userPass && $userPassR && $userPass === $userPassR) {
    $userId = UserPassRecover::checkHashPassRecover($hash);

    $Log = new Log(_('Modificar Clave Usuario'));

    if ($userId) {
        if (UserPass::updateUserPass($userId, $userPass)
            && UserPassRecover::updateHashPassRecover($hash)
        ) {
            $Log->addDescription(_('Clave actualizada'));
            $Log->addDetails(Html::strongText(_('Login')), UserUtil::getUserLoginById($userId));
            $Log->writeLog();

            Response::printJSON(_('Clave actualizada'), 0, 'goLogin();');
        }
    }

    $Log->addDescription(_('Error al modificar la clave'));
    $Log->writeLog();

    Response::printJSON(_('Error al modificar la clave'));
} else {
    Response::printJSON(_('La clave es incorrecta o no coincide'));
}