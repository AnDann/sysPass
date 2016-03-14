<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2016 Rubén Domínguez nuxsmin@$syspass.org
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

namespace SP\DataModel;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class GroupData
 *
 * @package SP\DataModel
 */
class GroupData
{
    /**
     * @var int
     */
    public $usergroup_id = 0;
    /**
     * @var string
     */
    public $usergroup_name = '';
    /**
     * @var string
     */
    public $usergroup_description = '';
    /**
     * @var array
     */
    public $users = [];

    /**
     * @return int
     */
    public function getUsergroupId()
    {
        return $this->usergroup_id;
    }

    /**
     * @param int $usergroup_id
     */
    public function setUsergroupId($usergroup_id)
    {
        $this->usergroup_id = $usergroup_id;
    }

    /**
     * @return string
     */
    public function getUsergroupName()
    {
        return $this->usergroup_name;
    }

    /**
     * @return string
     */
    public function getUsergroupDescription()
    {
        return $this->usergroup_description;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return (is_array($this->users)) ? $this->users : [];
    }

    /**
     * @param array $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @param string $usergroup_name
     */
    public function setUsergroupName($usergroup_name)
    {
        $this->usergroup_name = $usergroup_name;
    }

    /**
     * @param string $usergroup_description
     */
    public function setUsergroupDescription($usergroup_description)
    {
        $this->usergroup_description = $usergroup_description;
    }
}