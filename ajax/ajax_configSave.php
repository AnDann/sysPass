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

use SP\Account\Account;
use SP\Account\AccountHistory;
use SP\Config\Config;
use SP\Config\ConfigDB;
use SP\Core\ActionsInterface;
use SP\Core\Crypt;
use SP\Core\CryptMasterPass;
use SP\Core\Init;
use SP\Core\Session;
use SP\Core\SessionUtil;
use SP\Core\Exceptions\SPException;
use SP\Html\Html;
use SP\Http\JsonResponse;
use SP\Http\Request;
use SP\Log\Email;
use SP\Log\Log;
use SP\Mgmt\CustomFields\CustomFieldsUtil;
use SP\Mgmt\Users\UserPass;
use SP\Util\Checks;
use SP\Util\Json;

define('APP_ROOT', '..');

require_once APP_ROOT . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'Base.php';

Request::checkReferer('POST');

$Json = new JsonResponse();

if (!Init::isLoggedIn()) {
    $Json->setStatus(10);
    $Json->setDescription(_('La sesión no se ha iniciado o ha caducado'));
    Json::returnJson($Json);
}

$sk = Request::analyze('sk', false);


if (!$sk || !SessionUtil::checkSessionKey($sk)) {
    $Json->setDescription(_('CONSULTA INVÁLIDA'));
    Json::returnJson($Json);
}

// Variables POST del formulario
$actionId = Request::analyze('actionId', 0);
$activeTab = Request::analyze('activeTab', 0);

if ($actionId === ActionsInterface::ACTION_CFG_GENERAL
    || $actionId === ActionsInterface::ACTION_CFG_WIKI
    || $actionId === ActionsInterface::ACTION_CFG_LDAP
    || $actionId === ActionsInterface::ACTION_CFG_MAIL
) {
    $Log = Log::newLog(_('Modificar Configuración'));
    $Config = Session::getConfig();

    if ($actionId === ActionsInterface::ACTION_CFG_GENERAL) {
        // General
        $siteLang = Request::analyze('sitelang');
        $siteTheme = Request::analyze('sitetheme', 'material-blue');
        $sessionTimeout = Request::analyze('session_timeout', 300);
        $httpsEnabled = Request::analyze('https_enabled', false, false, true);
        $debugEnabled = Request::analyze('debug', false, false, true);
        $maintenanceEnabled = Request::analyze('maintenance', false, false, true);
        $checkUpdatesEnabled = Request::analyze('updates', false, false, true);
        $checkNoticesEnabled = Request::analyze('notices', false, false, true);

        $Config->setSiteLang($siteLang);
        $Config->setSiteTheme($siteTheme);
        $Config->setSessionTimeout($sessionTimeout);
        $Config->setHttpsEnabled($httpsEnabled);
        $Config->setDebug($debugEnabled);
        $Config->setMaintenance($maintenanceEnabled);
        $Config->setCheckUpdates($checkUpdatesEnabled);
        $Config->setChecknotices($checkNoticesEnabled);

        // Events
        $logEnabled = Request::analyze('log_enabled', false, false, true);
        $syslogEnabled = Request::analyze('syslog_enabled', false, false, true);
        $remoteSyslogEnabled = Request::analyze('remotesyslog_enabled', false, false, true);
        $syslogServer = Request::analyze('remotesyslog_server');
        $syslogPort = Request::analyze('remotesyslog_port', 0);

        $Config->setLogEnabled($logEnabled);
        $Config->setSyslogEnabled($syslogEnabled);

        if ($remoteSyslogEnabled && (!$syslogServer || !$syslogPort)) {
            $Json->setDescription(_('Faltan parámetros de syslog remoto'));
            Json::returnJson($Json);
        } elseif ($remoteSyslogEnabled) {
            $Config->setSyslogRemoteEnabled($remoteSyslogEnabled);
            $Config->setSyslogServer($syslogServer);
            $Config->setSyslogPort($syslogPort);
        } else {
            $Config->setSyslogRemoteEnabled(false);

            $Log->addDescription(_('Syslog remoto deshabilitado'));
        }

        // Accounts
        $globalSearchEnabled = Request::analyze('globalsearch', false, false, true);
        $accountPassToImageEnabled = Request::analyze('account_passtoimage', false, false, true);
        $accountLinkEnabled = Request::analyze('account_link', false, false, true);
        $accountCount = Request::analyze('account_count', 10);
        $resultsAsCardsEnabled = Request::analyze('resultsascards', false, false, true);

        $Config->setGlobalSearch($globalSearchEnabled);
        $Config->setAccountPassToImage($accountPassToImageEnabled);
        $Config->setAccountLink($accountLinkEnabled);
        $Config->setAccountCount($accountCount);
        $Config->setResultsAsCards($resultsAsCardsEnabled);

        // Files
        $filesEnabled = Request::analyze('files_enabled', false, false, true);
        $filesAllowedSize = Request::analyze('files_allowed_size', 1024);
        $filesAllowedExts = Request::analyze('files_allowed_exts');

        if ($filesEnabled && $filesAllowedSize >= 16384) {
            $Json->setDescription(_('El tamaño máximo por archivo es de 16MB'));
            Json::returnJson($Json);
        }

        if (!empty($filesAllowedExts)) {
            $exts = explode(',', $filesAllowedExts);
            array_walk($exts, function (&$value) use ($Json) {
                if (preg_match('/[^a-z0-9_-]+/i', $value)) {
                    $Json->setDescription(sprintf('%s: %s', _('Extensión no permitida'), $value));
                    Json::returnJson($Json);
                }
            });
            $Config->setFilesAllowedExts($exts);
        } else {
            $Config->setFilesAllowedExts([]);
        }

        $Config->setFilesEnabled($filesEnabled);
        $Config->setFilesAllowedSize($filesAllowedSize);

        // Public Links
        $pubLinksEnabled = Request::analyze('publinks_enabled', false, false, true);
        $pubLinksImageEnabled = Request::analyze('publinks_image_enabled', false, false, true);
        $pubLinksMaxTime = Request::analyze('publinks_maxtime', 10);
        $pubLinksMaxViews = Request::analyze('publinks_maxviews', 3);

        $Config->setPublinksEnabled($pubLinksEnabled);
        $Config->setPublinksImageEnabled($pubLinksImageEnabled);
        $Config->setPublinksMaxTime($pubLinksMaxTime * 60);
        $Config->setPublinksMaxViews($pubLinksMaxViews);

        // Proxy
        $proxyEnabled = Request::analyze('proxy_enabled', false, false, true);
        $proxyServer = Request::analyze('proxy_server');
        $proxyPort = Request::analyze('proxy_port', 0);
        $proxyUser = Request::analyze('proxy_user');
        $proxyPass = Request::analyzeEncrypted('proxy_pass');


        // Valores para Proxy
        if ($proxyEnabled && (!$proxyServer || !$proxyPort)) {
            $Json->setDescription(_('Faltan parámetros de Proxy'));
            Json::returnJson($Json);
        } elseif ($proxyEnabled) {
            $Config->setProxyEnabled(true);
            $Config->setProxyServer($proxyServer);
            $Config->setProxyPort($proxyPort);
            $Config->setProxyUser($proxyUser);
            $Config->setProxyPass($proxyPass);

            $Log->addDescription(_('Proxy habiltado'));
        } else {
            $Config->setProxyEnabled(false);

            $Log->addDescription(_('Proxy deshabilitado'));
        }

        $Log->addDetails(_('Sección'), _('General'));
    } elseif ($actionId === ActionsInterface::ACTION_CFG_WIKI) {
        // Wiki
        $wikiEnabled = Request::analyze('wiki_enabled', false, false, true);
        $wikiSearchUrl = Request::analyze('wiki_searchurl');
        $wikiPageUrl = Request::analyze('wiki_pageurl');
        $wikiFilter = Request::analyze('wiki_filter');

        // Valores para la conexión a la Wiki
        if ($wikiEnabled && (!$wikiSearchUrl || !$wikiPageUrl || !$wikiFilter)) {
            $Json->setDescription(_('Faltan parámetros de Wiki'));
            Json::returnJson($Json);
        } elseif ($wikiEnabled) {
            $Config->setWikiEnabled(true);
            $Config->setWikiSearchurl($wikiSearchUrl);
            $Config->setWikiPageurl($wikiPageUrl);
            $Config->setWikiFilter(explode(',', $wikiFilter));

            $Log->addDescription(_('Wiki habiltada'));
        } else {
            $Config->setWikiEnabled(false);

            $Log->addDescription(_('Wiki deshabilitada'));
        }

        // DokuWiki
        $dokuWikiEnabled = Request::analyze('dokuwiki_enabled', false, false, true);
        $dokuWikiUrl = Request::analyze('dokuwiki_url');
        $dokuWikiUrlBase = Request::analyze('dokuwiki_urlbase');
        $dokuWikiUser = Request::analyze('dokuwiki_user');
        $dokuWikiPass = Request::analyzeEncrypted('dokuwiki_pass');
        $dokuWikiNamespace = Request::analyze('dokuwiki_namespace');

        // Valores para la conexión a la API de DokuWiki
        if ($dokuWikiEnabled && (!$dokuWikiUrl || !$dokuWikiUrlBase)) {
            $Json->setDescription(_('Faltan parámetros de DokuWiki'));
            Json::returnJson($Json);
        } elseif ($dokuWikiEnabled) {
            $Config->setDokuwikiEnabled(true);
            $Config->setDokuwikiUrl($dokuWikiUrl);
            $Config->setDokuwikiUrlBase(trim($dokuWikiUrlBase, '/'));
            $Config->setDokuwikiUser($dokuWikiUser);
            $Config->setDokuwikiPass($dokuWikiPass);
            $Config->setDokuwikiNamespace($dokuWikiNamespace);

            $Log->addDescription(_('DokuWiki habiltada'));
        } else {
            $Config->setDokuwikiEnabled(false);

            $Log->addDescription(_('DokuWiki deshabilitada'));
        }

        $Log->addDetails(_('Sección'), _('Wiki'));
    } elseif ($actionId === ActionsInterface::ACTION_CFG_LDAP) {
        // LDAP
        $ldapEnabled = Request::analyze('ldap_enabled', false, false, true);
        $ldapADSEnabled = Request::analyze('ldap_ads', false, false, true);
        $ldapServer = Request::analyze('ldap_server');
        $ldapBase = Request::analyze('ldap_base');
        $ldapGroup = Request::analyze('ldap_group');
        $ldapDefaultGroup = Request::analyze('ldap_defaultgroup', 0);
        $ldapDefaultProfile = Request::analyze('ldap_defaultprofile', 0);
        $ldapBindUser = Request::analyze('ldap_binduser');
        $ldapBindPass = Request::analyzeEncrypted('ldap_bindpass');

        // Valores para la configuración de LDAP
        if ($ldapEnabled && (!$ldapServer || !$ldapBase || !$ldapBindUser)) {
            $Json->setDescription(_('Faltan parámetros de LDAP'));
            Json::returnJson($Json);
        } elseif ($ldapEnabled) {
            $Config->setLdapEnabled(true);
            $Config->setLdapAds($ldapADSEnabled);
            $Config->setLdapServer($ldapServer);
            $Config->setLdapBase($ldapBase);
            $Config->setLdapGroup($ldapGroup);
            $Config->setLdapDefaultGroup($ldapDefaultGroup);
            $Config->setLdapDefaultProfile($ldapDefaultProfile);
            $Config->setLdapBindUser($ldapBindUser);
            $Config->setLdapBindPass($ldapBindPass);

            $Log->addDescription(_('LDAP habiltado'));
        } else {
            $Config->setLdapEnabled(false);

            $Log->addDescription(_('LDAP deshabilitado'));
        }

        $Log->addDetails(_('Sección'), _('LDAP'));
    } elseif ($actionId === ActionsInterface::ACTION_CFG_MAIL) {
        // Mail
        $mailEnabled = Request::analyze('mail_enabled', false, false, true);
        $mailServer = Request::analyze('mail_server');
        $mailPort = Request::analyze('mail_port', 25);
        $mailUser = Request::analyze('mail_user');
        $mailPass = Request::analyzeEncrypted('mail_pass');
        $mailSecurity = Request::analyze('mail_security');
        $mailFrom = Request::analyze('mail_from');
        $mailRequests = Request::analyze('mail_requestsenabled', false, false, true);
        $mailAuth = Request::analyze('mail_authenabled', false, false, true);

        // Valores para la configuración del Correo
        if ($mailEnabled && (!$mailServer || !$mailFrom)) {
            $Json->setDescription(_('Faltan parámetros de Correo'));
            Json::returnJson($Json);
        } elseif ($mailEnabled) {
            $Config->setMailEnabled(true);
            $Config->setMailRequestsEnabled($mailRequests);
            $Config->setMailServer($mailServer);
            $Config->setMailPort($mailPort);
            $Config->setMailSecurity($mailSecurity);
            $Config->setMailFrom($mailFrom);

            if ($mailAuth) {
                $Config->setMailAuthenabled($mailAuth);
                $Config->setMailUser($mailUser);
                $Config->setMailPass($mailPass);
            }

            $Log->addDescription(_('Correo habiltado'));
        } else {
            $Config->setMailEnabled(false);
            $Config->setMailRequestsEnabled(false);
            $Config->setMailAuthenabled(false);

            $Log->addDescription(_('Correo deshabilitado'));
        }

        $Log->addDetails(_('Sección'), _('Correo'));
    }

    try {
        Config::saveConfig();
    } catch (SPException $e) {
        $Log->addDescription(_('Error al guardar la configuración'));
        $Log->addDetails($e->getMessage(), $e->getHint());
        $Log->writeLog();

        Email::sendEmail($Log);

        $Json->setDescription($e->getMessage());
        Json::returnJson($Json);
    }

    $Log->writeLog();

    Email::sendEmail($Log);

    if ($actionId === ActionsInterface::ACTION_CFG_GENERAL) {
        // Recargar la aplicación completa para establecer nuevos valores
        \SP\Util\Util::reload();
    }

    $Json->setStatus(0);
    $Json->setDescription(_('Configuración actualizada'));
    Json::returnJson($Json);
} elseif ($actionId === ActionsInterface::ACTION_CFG_ENCRYPTION) {
    $currentMasterPass = Request::analyzeEncrypted('curMasterPwd');
    $newMasterPass = Request::analyzeEncrypted('newMasterPwd');
    $newMasterPassR = Request::analyzeEncrypted('newMasterPwdR');
    $confirmPassChange = Request::analyze('confirmPassChange', 0, false, 1);
    $noAccountPassChange = Request::analyze('chkNoAccountChange', 0, false, 1);

    if (!UserPass::checkUserUpdateMPass(Session::getUserId())) {
        $Json->setDescription(_('Clave maestra actualizada'));
        $Json->addMessage(_('Reinicie la sesión para cambiarla'));
        Json::returnJson($Json);
    } elseif ($newMasterPass == '' && $currentMasterPass == '') {
        $Json->setDescription(_('Clave maestra no indicada'));
        Json::returnJson($Json);
    } elseif ($confirmPassChange == 0) {
        $Json->setDescription(_('Se ha de confirmar el cambio de clave'));
        Json::returnJson($Json);
    }

    if ($newMasterPass == $currentMasterPass) {
        $Json->setDescription(_('Las claves son idénticas'));
        Json::returnJson($Json);
    } elseif ($newMasterPass != $newMasterPassR) {
        $Json->setDescription(_('Las claves maestras no coinciden'));
        Json::returnJson($Json);
    } elseif (!Crypt::checkHashPass($currentMasterPass, ConfigDB::getValue('masterPwd'), true)) {
        $Json->setDescription(_('La clave maestra actual no coincide'));
        Json::returnJson($Json);
    }

    $hashMPass = Crypt::mkHashPassword($newMasterPass);

    if (!$noAccountPassChange) {
        $Account = new Account();

        if (!$Account->updateAccountsMasterPass($currentMasterPass, $newMasterPass)) {
            $Json->setDescription(_('Errores al actualizar las claves de las cuentas'));
            Json::returnJson($Json);
        }

        $AccountHistory = new AccountHistory();

        if (!$AccountHistory->updateAccountsMasterPass($currentMasterPass, $newMasterPass, $hashMPass)) {
            $Json->setDescription(_('Errores al actualizar las claves de las cuentas del histórico'));
            Json::returnJson($Json);
        }

        if (!CustomFieldsUtil::updateCustomFieldsCrypt($currentMasterPass, $newMasterPass)) {
            $Json->setDescription(_('Errores al actualizar datos de campos personalizados'));
            Json::returnJson($Json);
        }
    }

    if (Checks::demoIsEnabled()) {
        $Json->setDescription(_('Ey, esto es una DEMO!!'));
        Json::returnJson($Json);
    }

//    ConfigDB::readConfig();
    ConfigDB::setCacheConfigValue('masterPwd', $hashMPass);
    ConfigDB::setCacheConfigValue('lastupdatempass', time());

    $Log = new Log(_('Actualizar Clave Maestra'));

    if (ConfigDB::writeConfig()) {
        $Log->addDescription(_('Clave maestra actualizada'));
        $Log->writeLog();

        $Json->setStatus(0);
        Email::sendEmail($Log);
    } else {
        $Log->setLogLevel(Log::ERROR);
        $Log->addDescription(_('Error al guardar el hash de la clave maestra'));
        $Log->writeLog();

        Email::sendEmail($Log);
    }

    $Json->setDescription($Log->getDescription());
    Json::returnJson($Json);
} elseif ($actionId === ActionsInterface::ACTION_CFG_ENCRYPTION_TEMPPASS) {
    $tempMasterMaxTime = Request::analyze('tmpass_maxtime', 3600);
    $tempMasterPass = CryptMasterPass::setTempMasterPass($tempMasterMaxTime);

    $Log = new Log('Generar Clave Temporal');

    if ($tempMasterPass !== false && !empty($tempMasterPass)) {
        $Log->addDescription(_('Clave Temporal Generada'));
        $Log->addDetails(Html::strongText(_('Clave')), $tempMasterPass);
        $Log->writeLog();

        Email::sendEmail($Log);

        $Json->setStatus(0);
    } else {
        $Log->setLogLevel(Log::ERROR);
        $Log->addDescription(_('Error al generar clave temporal'));
        $Log->writeLog();

        Email::sendEmail($Log);
    }

    $Json->setDescription($Log->getDescription());
    Json::returnJson($Json);
} else {
    $Json->setDescription(_('Acción Inválida'));
    Json::returnJson($Json);
}