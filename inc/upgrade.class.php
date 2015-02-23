<?php

/**
 * sysPass
 *
 * @author nuxsmin
 * @link http://syspass.org
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

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Esta clase es la encargada de realizar las operaciones actualización de la aplicación.
 */
class SP_Upgrade
{
    private static $dbUpgrade = array(110, 1121, 1122, 1123, 11213, 12001);
    private static $cfgUpgrade = array(1124);

    /**
     * Inicia el proceso de actualización de la BBDD.
     *
     * @param int $version con la versión de la BBDD actual
     * @returns bool
     */
    public static function doUpgrade($version)
    {
        foreach (self::$dbUpgrade as $upgradeVersion) {
            if ($version < $upgradeVersion) {
                if (self::upgradeTo($upgradeVersion) === false) {
                    SP_Init::initError(
                        _('Error al aplicar la actualización de la Base de Datos'),
                        _('Compruebe el registro de eventos para más detalles') . '. <a href="index.php?nodbupgrade=1">' . _('Acceder') . '</a>');
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
    private static function upgradeTo($version)
    {
        $result['action'] = _('Actualizar BBDD');

        switch ($version) {
            case 110:
                $queries[] = 'ALTER TABLE `accFiles` CHANGE COLUMN `accfile_name` `accfile_name` VARCHAR(100) NOT NULL';
                $queries[] = 'ALTER TABLE `accounts` ADD COLUMN `account_otherGroupEdit` BIT(1) NULL DEFAULT 0 AFTER `account_dateEdit`, ADD COLUMN `account_otherUserEdit` BIT(1) NULL DEFAULT 0 AFTER `account_otherGroupEdit`;';
                $queries[] = 'CREATE TABLE `accUsers` (`accuser_id` INT NOT NULL AUTO_INCREMENT,`accuser_accountId` INT(10) UNSIGNED NOT NULL,`accuser_userId` INT(10) UNSIGNED NOT NULL, PRIMARY KEY (`accuser_id`), INDEX `idx_account` (`accuser_accountId` ASC));';
                $queries[] = 'ALTER TABLE `accHistory` ADD COLUMN `accHistory_otherUserEdit` BIT NULL AFTER `acchistory_mPassHash`, ADD COLUMN `accHistory_otherGroupEdit` VARCHAR(45) NULL AFTER `accHistory_otherUserEdit`;';
                $queries[] = 'ALTER TABLE `accFiles` CHANGE COLUMN `accfile_type` `accfile_type` VARCHAR(100) NOT NULL ;';
                break;
            case 1121:
                $queries[] = 'ALTER TABLE `categories` ADD COLUMN `category_description` VARCHAR(255) NULL AFTER `category_name`;';
                $queries[] = 'ALTER TABLE `usrProfiles` ADD COLUMN `userProfile_pAppMgmtMenu` BIT(1) NULL DEFAULT b\'0\' AFTER `userProfile_pUsersMenu`,CHANGE COLUMN `userProfile_pConfigCategories` `userProfile_pAppMgmtCategories` BIT(1) NULL DEFAULT b\'0\' AFTER `userProfile_pAppMgmtMenu`,ADD COLUMN `userProfile_pAppMgmtCustomers` BIT(1) NULL DEFAULT b\'0\' AFTER `userProfile_pAppMgmtCategories`;';
                break;
            case 1122:
                $queries[] = 'ALTER TABLE `usrData` CHANGE COLUMN `user_login` `user_login` VARCHAR(50) NOT NULL ,CHANGE COLUMN `user_email` `user_email` VARCHAR(80) NULL DEFAULT NULL ;';
                break;
            case 1123:
                $queries[] = 'CREATE TABLE `usrPassRecover` (`userpassr_id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `userpassr_userId` SMALLINT UNSIGNED NOT NULL,`userpassr_hash` VARBINARY(40) NOT NULL,`userpassr_date` INT UNSIGNED NOT NULL,`userpassr_used` BIT(1) NOT NULL DEFAULT b\'0\', PRIMARY KEY (`userpassr_id`),INDEX `IDX_userId` (`userpassr_userId` ASC, `userpassr_date` ASC)) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;';
                $queries[] = 'ALTER TABLE `log` ADD COLUMN `log_ipAddress` VARCHAR(45) NOT NULL AFTER `log_userId`;';
                $queries[] = 'ALTER TABLE `usrData` ADD COLUMN `user_isChangePass` BIT(1) NULL DEFAULT b\'0\' AFTER `user_isMigrate`;';
                break;
            case 11213:
                $queries[] = 'ALTER TABLE `usrData` CHANGE COLUMN `user_mPass` `user_mPass` VARBINARY(32) NULL DEFAULT NULL ,CHANGE COLUMN `user_lastLogin` `user_lastLogin` DATETIME NULL DEFAULT NULL ,CHANGE COLUMN `user_lastUpdate` `user_lastUpdate` DATETIME NULL DEFAULT NULL, CHANGE COLUMN `user_mIV` `user_mIV` VARBINARY(32) NULL ;';
                $queries[] = 'ALTER TABLE `accounts` CHANGE COLUMN `account_login` `account_login` VARCHAR(50) NULL DEFAULT NULL ;';
                break;
            case 12001:
                $queries[] = 'ALTER TABLE `accounts` CHANGE COLUMN `account_userEditId` `account_userEditId` TINYINT(3) UNSIGNED NULL DEFAULT NULL, CHANGE COLUMN `account_dateEdit` `account_dateEdit` DATETIME NULL DEFAULT NULL;';
                $queries[] = 'ALTER TABLE `accHistory` CHANGE COLUMN `acchistory_userEditId` `acchistory_userEditId` TINYINT(3) UNSIGNED NULL DEFAULT NULL, CHANGE COLUMN `acchistory_dateEdit` `acchistory_dateEdit` DATETIME NULL DEFAULT NULL;';
                $queries[] = 'ALTER TABLE `accHistory` CHANGE COLUMN `accHistory_otherGroupEdit` `accHistory_otherGroupEdit` BIT NULL DEFAULT b\'0\';';
                break;
            default :
                $result['text'][] = _('No es necesario actualizar la Base de Datos.');
                return true;
        }

        foreach ($queries as $query) {
            try{
                DB::getQuery($query, __FUNCTION__);
            } catch(SPDatabaseException $e){
                $result['text'][] = _('Error al aplicar la actualización de la Base de Datos.') . ' (v' . $version . ')';
                $result['text'][] = 'ERROR: ' . $e->getMessage() . ' (' . $e->getCode() . ')';
                SP_Log::wrLogInfo($result);
                return false;
            }
        }

        $result['text'][] = _('Actualización de la Base de Datos realizada correctamente.') . ' (v' . $version . ')';
        SP_Log::wrLogInfo($result);

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
        $upgrades = array_filter(self::$dbUpgrade, function ($uVersions) use ($version){ return ($uVersions >= $version); } );

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
        return (in_array($version, self::$cfgUpgrade));
    }

    /**
     * Migrar valores de configuración.
     *
     * @param int $version con el número de versión
     * @return bool
     */
    public static function upgradeConfig($version)
    {
        $mapParams = array(
            'files_allowed_exts' => 'allowed_exts',
            'files_allowed_size' => 'allowed_size',
            'demo_enabled' => 'demoenabled',
            'files_enabled' => 'filesenabled',
            'ldap_base' => 'ldapbase',
            'ldap_bindpass' => 'ldapbindpass',
            'ldap_binduser' => 'ldapbinduser',
            'ldap_enabled' => 'ldapenabled',
            'ldap_group' => 'ldapgroup',
            'ldap_server' => 'ldapserver',
            'log_enabled' => 'logenabled',
            'mail_enabled' => 'mailenabled',
            'mail_from' => 'mailfrom',
            'mail_pass' => 'mailpass',
            'mail_port' => 'mailport',
            'mail_requestsenabled' => 'mailrequestsenabled',
            'mail_security' => 'mailsecurity',
            'mail_server' => 'mailserver',
            'mail_user' => 'mailuser',
            'wiki_enabled' => 'wikienabled',
            'wiki_filter' => 'wikifilter',
            'wiki_pageurl' => 'wikipageurl',
            'wiki_searchurl' => 'wikisearchurl'
        );

        $currData = SP_Config::getKeys(true);

        foreach ( $mapParams as $newParam => $oldParam){
            if ( array_key_exists($oldParam,$currData)){
                SP_Config::setValue($newParam,$currData[$oldParam]);
                SP_Config::deleteKey($oldParam);
            }
        }

        $result['action'] = _('Actualizar Configuración');
        $result['text'][] = _('Actualización de la Configuración realizada correctamente.') . ' (v' . $version . ')';
        SP_Log::wrLogInfo($result);

        return true;
    }
}