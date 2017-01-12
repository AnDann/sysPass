<?php

/**
 * sysPass
 *
 * @author nuxsmin
 * @link http://syspass.org
 * @copyright 2012-2017, Rubén Domínguez nuxsmin@$syspass.org
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
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SP\Core;

use SP\Config\Config;
use SP\Config\ConfigData;
use SP\Core\Exceptions\SPException;
use SP\Log\Email;
use SP\Log\Log;
use SP\Mgmt\CustomFields\CustomFieldsUtil;
use SP\Mgmt\Profiles\ProfileUtil;
use SP\Mgmt\Users\UserMigrate;
use SP\Mgmt\Users\UserPreferencesUtil;
use SP\Storage\DB;
use SP\Storage\QueryData;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Esta clase es la encargada de realizar las operaciones actualización de la aplicación.
 */
class Upgrade
{
    private static $dbUpgrade = [110, 1121, 1122, 1123, 11213, 11219, 11220, 12001, 12002, 1316011001, 1316020501, 1316100601, 2017010901];
    private static $cfgUpgrade = [1124, 1316020501];

    /**
     * Inicia el proceso de actualización de la BBDD.
     *
     * @param int $version con la versión de la BBDD actual
     * @return bool
     * @throws \SP\Core\Exceptions\InvalidClassException
     * @throws \InvalidArgumentException
     * @throws SPException
     */
    public static function doUpgrade($version)
    {
        foreach (self::$dbUpgrade as $upgradeVersion) {
            if ($version < $upgradeVersion) {
                if (self::upgradeDB($upgradeVersion) === false) {
                    throw new SPException(SPException::SP_CRITICAL, _('Error al aplicar la actualización de la Base de Datos'),
                        _('Compruebe el registro de eventos para más detalles'));
                }

                if (self::auxUpgrades($upgradeVersion) === false) {
                    throw new SPException(SPException::SP_CRITICAL,
                        _('Error al aplicar la actualización auxiliar'),
                        _('Compruebe el registro de eventos para más detalles'));
                }
            }
        }

        return true;
    }

    /**
     * Actualiza la BBDD según la versión.
     *
     * @param int $version con la versión a actualizar
     * @returns bool
     */
    private static function upgradeDB($version)
    {
        $Log = new Log(_('Actualizar BBDD'));
        $Log->addDetails(_('Versión'), $version);

        $queries = [];

        switch ($version) {
            case 110:
                $queries = self::getQueriesFromFile($version);
                break;
            case 1121:
                $queries = self::getQueriesFromFile($version);
                break;
            case 1122:
                $queries = self::getQueriesFromFile($version);
                break;
            case 1123:
                $queries = self::getQueriesFromFile($version);
                break;
            case 11213:
                $queries = self::getQueriesFromFile($version);
                break;
            case 11219:
                $queries = self::getQueriesFromFile($version);
                break;
            case 11220:
                $queries = self::getQueriesFromFile($version);
                break;
            case 12001:
                $queries = self::getQueriesFromFile($version);
                break;
            case 12002:
                $queries = self::getQueriesFromFile($version);
                break;
            case 1316011001:
                $queries = self::getQueriesFromFile($version);
                break;
            case 1316020501:
                $queries = self::getQueriesFromFile($version);
                break;
            case 1316100601:
                $queries = self::getQueriesFromFile($version);
                break;
        }

        if (count($queries) === 0) {
            $Log->addDescription(_('No es necesario actualizar la Base de Datos.'));
            $Log->writeLog();
            return true;
        }

        $Data = new QueryData();

        foreach ($queries as $query) {
            try {
                $Data->setQuery($query);
                DB::getQuery($Data);
            } catch (SPException $e) {
                $Log->setLogLevel(Log::ERROR);
                $Log->addDescription(_('Error al aplicar la actualización de la Base de Datos.'));
                $Log->addDetails('ERROR', sprintf('%s (%s)', $e->getMessage(), $e->getCode()));
                $Log->writeLog();

                Email::sendEmail($Log);
                return false;
            }
        }

        $Log->addDescription(_('Actualización de la Base de Datos realizada correctamente.'));
        $Log->writeLog();

        Email::sendEmail($Log);

        return true;
    }

    /**
     * Obtener las consultas de actualización desde un archivo
     *
     * @param $filename
     * @return array|bool
     */
    private static function getQueriesFromFile($filename)
    {
        $file = SQL_PATH . DIRECTORY_SEPARATOR . $filename . '.sql';

        $queries = [];

        if (file_exists($file) && $handle = fopen($file, 'rb')) {
            while (!feof($handle)) {
                $buffer = stream_get_line($handle, 1000000, ";\n");

                if (strlen(trim($buffer)) > 0) {
                    $queries[] = str_replace("\n", '', $buffer);
                }
            }
        }

        return $queries;
    }

    /**
     * Aplicar actualizaciones auxiliares.
     *
     * @param $version int El número de versión
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \SP\Core\Exceptions\SPException
     * @throws \SP\Core\Exceptions\InvalidClassException
     */
    private static function auxUpgrades($version)
    {
        switch ($version) {
            case 12001:
                return (ProfileUtil::migrateProfiles() && UserMigrate::migrateUsersGroup());
            case 12002:
                return UserMigrate::setMigrateUsers();
            case 2017010901:
                Init::loadPlugins();

                return CustomFieldsUtil::migrateCustomFields() && UserPreferencesUtil::migrate();
        }

        return true;
    }

    /**
     * Comprueba si es necesario actualizar la BBDD.
     *
     * @param int $version con el número de versión actual
     * @returns bool
     */
    public static function needDBUpgrade($version)
    {
        $upgrades = array_filter(self::$dbUpgrade, function ($uVersions) use ($version) {
            return ($uVersions > $version);
        });

        return (count($upgrades) > 0);
    }

    /**
     * Comprueba si es necesario actualizar la configuración.
     *
     * @param int $version con el número de versión actual
     * @returns bool
     */
    public static function needConfigUpgrade($version)
    {
        $latestUpgrade = self::$cfgUpgrade[count(self::$cfgUpgrade) - 1];

        return version_compare($version, $latestUpgrade) <= 0;
    }

    /**
     * Migrar valores de configuración.
     *
     * @param int $version El número de versión
     * @return bool
     */
    public static function upgradeConfig($version)
    {
        return false;
    }

    /**
     * Actualizar el archivo de configuración a formato XML
     *
     * @param $version
     * @return bool
     */
    public static function upgradeOldConfigFile($version)
    {
        $Log = new Log(_('Actualizar Configuración'));
        $Config = new ConfigData();

        // Include the file, save the data from $CONFIG
        include CONFIG_FILE;

        if (isset($CONFIG) && is_array($CONFIG)) {
            foreach (self::getConfigParams() as $mapTo => $mapFrom) {
                if (method_exists($Config, $mapTo)) {
                    if (is_array($mapFrom)) {
                        foreach ($mapFrom as $param) {
                            if (isset($CONFIG[$param])) {
                                $Log->addDetails(_('Parámetro'), $param);
                                $Config->$mapTo($CONFIG[$param]);
                            }
                        }
                    } else {
                        if (isset($CONFIG[$mapFrom])) {
                            $Log->addDetails(_('Parámetro'), $mapFrom);
                            $Config->$mapTo($CONFIG[$mapFrom]);
                        }
                    }
                }
            }
        }

        try {
            $Config->setConfigVersion($version);
            Config::saveConfig($Config, false);
            rename(CONFIG_FILE, CONFIG_FILE . '.old');

            $Log->addDetails(_('Versión'), $version);
            $Log->setLogLevel(Log::NOTICE);
            $Log->writeLog();

            return true;
        } catch (\Exception $e) {
            $Log->addDescription(_('Error al actualizar la configuración'));
            $Log->addDetails(_('Archivo'), CONFIG_FILE . '.old');
            $Log->setLogLevel(Log::ERROR);
            $Log->writeLog();
        }

        // We are here...wrong
        return false;
    }

    /**
     * Devuelve array de métodos y parámetros de configuración
     *
     * @return array
     */
    private static function getConfigParams()
    {
        return [
            'setAccountCount' => 'account_count',
            'setCheckUpdates' => 'checkupdates',
            'setDbHost' => 'dbhost',
            'setDbName' => 'dbname',
            'setDbPass' => 'dbpass',
            'setDbUser' => 'dbuser',
            'setDebug' => 'debug',
            'setDemoEnabled' => 'demo_enabled',
            'setGlobalSearch' => 'globalsearch',
            'setInstalled' => 'installed',
            'setMaintenance' => 'maintenance',
            'setPasswordSalt' => 'passwordsalt',
            'setSessionTimeout' => 'session_timeout',
            'setSiteLang' => 'sitelang',
            'setConfigVersion' => 'version',
            'setCheckNotices' => 'checknotices',
            'setConfigHash' => 'config_hash',
            'setProxyEnabled' => 'proxy_enabled',
            'setProxyPass' => 'proxy_pass',
            'setProxyPort' => 'proxy_port',
            'setProxyServer' => 'proxy_server',
            'setProxyUser' => 'proxy_user',
            'setResultsAsCards' => 'resultsascards',
            'setSiteTheme' => 'sitetheme',
            'setAccountPassToImage' => 'account_passtoimage',
            'setFilesAllowedExts' => 'allowed_exts',
            'setFilesAllowedSize' => 'allowed_size',
            'setFilesEnabled' => ['filesenabled', 'files_enabled'],
            'setLdapBase' => ['ldapbase', 'ldap_base'],
            'setLdapBindPass' => ['ldapbindpass', 'ldap_bindpass'],
            'setLdapBindUser' => ['ldapbinduser', 'ldap_binduser'],
            'setLdapEnabled' => ['ldapenabled', 'ldap_enabled'],
            'setLdapGroup' => ['ldapgroup', 'ldap_group'],
            'setLdapServer' => ['ldapserver', 'ldap_server'],
            'setLogEnabled' => ['logenabled', 'log_enabled'],
            'setMailEnabled' => ['mailenabled', 'mail_enabled'],
            'setMailFrom' => ['mailfrom', 'mail_from'],
            'setMailPass' => ['mailpass', 'mail_pass'],
            'setMailPort' => ['mailport', 'mail_port'],
            'setMailRequestsEnabled' => ['mailrequestsenabled', 'mail_requestsenabled'],
            'setMailSecurity' => ['mailsecurity', 'mail_security'],
            'setMailServer' => ['mailserver', 'mail_server'],
            'setMailUser' => ['mailuser', 'mail_user'],
            'setWikiEnabled' => ['wikienabled', 'wiki_enabled'],
            'setWikiFilter' => ['wikifilter', 'wiki_filter'],
            'setWikiPageUrl' => ['wikipageurl' . 'wiki_pageurl'],
            'setWikiSearchUrl' => ['wikisearchurl', 'wiki_searchurl']
        ];
    }
}