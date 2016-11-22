<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2015 Rubén Domínguez nuxsmin@$syspass.org
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

namespace SP\Mgmt\Users;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

use SP\Core\Exceptions\SPException;
use SP\DataModel\UserData;
use SP\DataModel\UserPreferencesData;
use SP\Mgmt\ItemInterface;
use SP\Storage\DB;
use SP\Storage\QueryData;
use SP\Util\Util;

/**
 * Class UsersPreferences para la gestion de las preferencias de usuarios
 *
 * @package SP
 */
class UserPreferences extends UserPreferencesBase implements ItemInterface
{
    /**
     * @return mixed
     */
    public function add()
    {
        // TODO: Implement add() method.
    }

    /**
     * @param $id int
     * @return mixed
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return $this
     * @throws \SP\Core\Exceptions\SPException
     */
    public function update()
    {
        $query = /** @lang SQL */
            'UPDATE usrData 
            SET user_preferences = ?
            WHERE user_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam(serialize($this->itemData));
        $Data->addParam($this->itemData->getUserId());

        if (DB::getQuery($Data) === false) {
            throw new SPException(SPException::SP_ERROR, _('Error al actualizar preferencias'));
        }

        return $this;
    }

    /**
     * @param $id int
     * @return UserPreferencesData
     */
    public function getById($id)
    {
        $query = /** @lang SQL */
            'SELECT user_id, user_preferences FROM usrData WHERE user_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setMapClassName('SP\DataModel\UserData');
        $Data->setQuery($query);
        $Data->addParam($id);

        /** @var UserData $queryRes */
        $queryRes = DB::getResults($Data);

        if ($queryRes === false || null === $queryRes->getUserPreferences()) {
            return $this->getItemData();
        }

        $UserPreferencesData = unserialize($queryRes->getUserPreferences());

        if ($UserPreferencesData === false) {
            return new UserPreferencesData();
        } elseif (get_class($UserPreferencesData) === '__PHP_Incomplete_Class') {
            $UserPreferencesData = Util::castToClass($this->getDataModel(), $UserPreferencesData);
        }

        return $UserPreferencesData;
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @param $id int
     * @return mixed
     */
    public function checkInUse($id)
    {
        // TODO: Implement checkInUse() method.
    }

    /**
     * @return bool
     */
    public function checkDuplicatedOnUpdate()
    {
        // TODO: Implement checkDuplicatedOnUpdate() method.
    }

    /**
     * @return bool
     */
    public function checkDuplicatedOnAdd()
    {
        // TODO: Implement checkDuplicatedOnAdd() method.
    }
}