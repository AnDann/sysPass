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

namespace SP\Mgmt\Profiles;

use SP\Core\SPException;
use SP\DataModel\ProfileData;
use SP\Log\Email;
use SP\Log\Log;
use SP\Storage\DB;
use SP\Storage\QueryData;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class ProfileUtil
 *
 * @package SP\Mgmt\User
 */
class ProfileUtil
{
    /**
     * Migrar los perfiles con formato anterior a v1.2
     *
     * @return bool
     */
    public static function migrateProfiles()
    {
        $Log = new Log(_('Migrar Perfiles'));

        $query = /** @lang SQL */
            'SELECT userprofile_id AS id,
            userprofile_name AS name,
            BIN(userProfile_pView) AS pView,
            BIN(userProfile_pViewPass) AS pViewPass,
            BIN(userProfile_pViewHistory) AS pViewHistory,
            BIN(userProfile_pEdit) AS pEdit,
            BIN(userProfile_pEditPass) AS pEditPass,
            BIN(userProfile_pAdd) AS pAdd,
            BIN(userProfile_pDelete) AS pDelete,
            BIN(userProfile_pFiles) AS pFiles,
            BIN(userProfile_pConfig) AS pConfig,
            BIN(userProfile_pConfigMasterPass) AS pConfigMasterPass,
            BIN(userProfile_pConfigBackup) AS pConfigBackup,
            BIN(userProfile_pAppMgmtCategories) AS pAppMgmtCategories,
            BIN(userProfile_pAppMgmtCustomers) AS pAppMgmtCustomers,
            BIN(userProfile_pUsers) AS pUsers,
            BIN(userProfile_pGroups) AS pGroups,
            BIN(userProfile_pProfiles) AS pProfiles,
            BIN(userProfile_pEventlog) AS pEventlog
            FROM usrProfiles';

        $Data = new QueryData();
        $Data->setQuery($query);

        DB::setReturnArray();

        $queryRes = DB::getResults($Data);

        if ($queryRes === false) {
            $Log->setLogLevel(Log::ERROR);
            $Log->addDescription(_('Error al obtener perfiles'));
            return false;
        }

        foreach ($queryRes as $oldProfile) {
            $ProfileData = new ProfileData();
            $ProfileData->setUserprofileId($oldProfile->id);
            $ProfileData->setUserprofileName($oldProfile->name);
            $ProfileData->setAccAdd($oldProfile->pAdd);
            $ProfileData->setAccView($oldProfile->pView);
            $ProfileData->setAccViewPass($oldProfile->pViewPass);
            $ProfileData->setAccViewHistory($oldProfile->pViewHistory);
            $ProfileData->setAccEdit($oldProfile->pEdit);
            $ProfileData->setAccEditPass($oldProfile->pEditPass);
            $ProfileData->setAccDelete($oldProfile->pDelete);
            $ProfileData->setConfigGeneral($oldProfile->pConfig);
            $ProfileData->setConfigEncryption($oldProfile->pConfigMasterPass);
            $ProfileData->setConfigBackup($oldProfile->pConfigBackup);
            $ProfileData->setMgmCategories($oldProfile->pAppMgmtCategories);
            $ProfileData->setMgmCustomers($oldProfile->pAppMgmtCustomers);
            $ProfileData->setMgmUsers($oldProfile->pUsers);
            $ProfileData->setMgmGroups($oldProfile->pGroups);
            $ProfileData->setMgmProfiles($oldProfile->pProfiles);
            $ProfileData->setEvl($oldProfile->pEventlog);

            try {
                Profile::getItem($ProfileData)->add();
            } catch (SPException $e) {
                return false;
            }
        }

        $query = /** @lang SQL */
            'ALTER TABLE usrProfiles
            DROP COLUMN userProfile_pAppMgmtCustomers,
            DROP COLUMN userProfile_pAppMgmtCategories,
            DROP COLUMN userProfile_pAppMgmtMenu,
            DROP COLUMN userProfile_pUsersMenu,
            DROP COLUMN userProfile_pConfigMenu,
            DROP COLUMN userProfile_pFiles,
            DROP COLUMN userProfile_pViewHistory,
            DROP COLUMN userProfile_pEventlog,
            DROP COLUMN userProfile_pEditPass,
            DROP COLUMN userProfile_pViewPass,
            DROP COLUMN userProfile_pDelete,
            DROP COLUMN userProfile_pProfiles,
            DROP COLUMN userProfile_pGroups,
            DROP COLUMN userProfile_pUsers,
            DROP COLUMN userProfile_pConfigBackup,
            DROP COLUMN userProfile_pConfigMasterPass,
            DROP COLUMN userProfile_pConfig,
            DROP COLUMN userProfile_pAdd,
            DROP COLUMN userProfile_pEdit,
            DROP COLUMN userProfile_pView';

        $Data->setQuery($query);

        $queryRes = DB::getQuery($Data);

        if ($queryRes) {
            $Log->addDescription(_('Operación realizada correctamente'));
        } else {
            $Log->addDescription(_('Fallo al realizar la operación'));
        }

        $Log->writeLog();

        Email::sendEmail($Log);

        return $queryRes;
    }

    /**
     * Obtener el nombre de los usuarios que usan un perfil.
     *
     * @param $id int El id del perfil
     * @return array|bool
     */
    public static function getProfileInUsersName($id)
    {
        $query = /** @lang SQL */
            'SELECT user_login FROM usrData WHERE user_profileId = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($id);

        DB::setReturnArray();

        return DB::getResults($Data);
    }
}