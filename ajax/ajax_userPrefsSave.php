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

use SP\Http\Request;
use SP\Core\SessionUtil;
use SP\Mgmt\User\UserUtil;
use SP\Util\Checks;

define('APP_ROOT', '..');

require_once APP_ROOT . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'Base.php';

Request::checkReferer('POST');

if (!\SP\Core\Init::isLoggedIn()) {
    \SP\Http\Response::printJSON(_('La sesión no se ha iniciado o ha caducado'), 10);
}

$sk = \SP\Http\Request::analyze('sk', false);

if (!$sk || !SessionUtil::checkSessionKey($sk)) {
    \SP\Http\Response::printJSON(_('CONSULTA INVÁLIDA'));
}

// Variables POST del formulario
$actionId = \SP\Http\Request::analyze('actionId', 0);
$itemId = \SP\Http\Request::analyze('itemId', 0);
$activeTab = \SP\Http\Request::analyze('activeTab', 0);

// Acción al cerrar la vista
$doActionOnClose = "sysPassUtil.Common.doAction($actionId,'',$activeTab);";

if ($actionId === \SP\Core\ActionsInterface::ACTION_USR_PREFERENCES_GENERAL) {
    $userLang = \SP\Http\Request::analyze('userlang');
    $userTheme = \SP\Http\Request::analyze('usertheme', 'material-blue');
    $resultsPerPage = \SP\Http\Request::analyze('resultsperpage', 12);
    $accountLink = \SP\Http\Request::analyze('account_link', false, false, true);
    $sortViews = \SP\Http\Request::analyze('sort_views', false, false, true);
    $topNavbar = \SP\Http\Request::analyze('top_navbar', false, false, true);

    // No se instancia la clase ya que es necesario guardar los atributos ya guardados
    $UserPrefs = \SP\Mgmt\User\UserPreferences::getPreferences($itemId);
    $UserPrefs->setId($itemId);
    $UserPrefs->setLang($userLang);
    $UserPrefs->setTheme($userTheme);
    $UserPrefs->setResultsPerPage($resultsPerPage);
    $UserPrefs->setAccountLink($accountLink);
    $UserPrefs->setSortViews($sortViews);
    $UserPrefs->setTopNavbar($topNavbar);

    if (!$UserPrefs->updatePreferences()) {
        \SP\Http\Response::printJSON(_('Error al actualizar preferencias'));
    }

    // Forzar la detección del lenguaje tras actualizar
    \SP\Core\Language::setLanguage(true);
    \SP\Core\Themes::setTheme(true);
    // Actualizar las preferencias en la sesión y recargar la página
    \SP\Core\Session::setUserPreferences($UserPrefs);
    \SP\Util\Util::reload();

    \SP\Http\Response::printJSON(_('Preferencias actualizadas'), 0, $doActionOnClose);
} else if ($actionId === \SP\Core\ActionsInterface::ACTION_USR_PREFERENCES_SECURITY) {
    if (Checks::demoIsEnabled() && \SP\Core\Session::getUserLogin() === 'demo') {
        \SP\Http\Response::printJSON(_('Ey, esto es una DEMO!!'));
    }

    // Variables POST del formulario
    $twoFaEnabled = \SP\Http\Request::analyze('security_2faenabled', 0, false, 1);
    $pin = \SP\Http\Request::analyze('security_pin', 0);

    $userLogin = UserUtil::getUserLoginById($itemId);
    $twoFa = new \SP\Auth\Auth2FA($itemId, $userLogin);

    if (!$twoFa->verifyKey($pin)) {
        \SP\Http\Response::printJSON(_('Código incorrecto'));
    }

    // No se instancia la clase ya que es necesario guardar los atributos ya guardados
    $UserPrefs = \SP\Mgmt\User\UserPreferences::getPreferences($itemId);
    $UserPrefs->setId($itemId);
    $UserPrefs->setUse2Fa(\SP\Util\Util::boolval($twoFaEnabled));

    if (!$UserPrefs->updatePreferences()) {
        \SP\Http\Response::printJSON(_('Error al actualizar preferencias'));
    }

    \SP\Http\Response::printJSON(_('Preferencias actualizadas'), 0, $doActionOnClose);
} else {
    \SP\Http\Response::printJSON(_('Acción Inválida'));
}