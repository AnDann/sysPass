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

namespace SP\Mgmt\User;

use SP\Core\SPException;
use SP\Storage\DB;
use SP\Util\Util;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class UsersPreferences para la gestion de las preferencias de usuarios
 *
 * @package SP
 */
class UserPreferences
{
    /**
     * @var int
     */
    private $_id = 0;
    /**
     * Usar autentificación en 2 pasos
     *
     * @var bool
     */
    private $_use2Fa = false;
    /**
     * Lenguaje del usuario
     *
     * @var string
     */
    private $_lang = '';
    /**
     * Tema del usuario
     *
     * @var string
     */
    private $_theme = '';
    /**
     * @var int
     */
    private $_resultsPerPage = 0;
    /**
     * @var bool
     */
    private $_accountLink = null;
    /**
     * @var bool
     */
    private $_sortViews = false;
    /**
     * @var bool
     */
    private $_topNavbar = false;

    /**
     * Obtener las preferencas de un usuario
     *
     * @param $id int El id del usuario
     * @return bool|UserPreferences
     * @throws SPException
     */
    public static function getPreferences($id)
    {
        $query = 'SELECT user_preferences FROM usrData WHERE user_id = :id LIMIT 1';

        $data['id'] = $id;

        $queryRes = DB::getResults($query, __FUNCTION__, $data);

        if ($queryRes === false || is_null($queryRes->user_preferences)) {
            return new UserPreferences();
        }

        $preferences = unserialize($queryRes->user_preferences);

        if (get_class($preferences) === '__PHP_Incomplete_Class') {
            return Util::castToClass('SP\Mgmt\User\UserPreferences', $preferences);
        }

        return $preferences;
    }

    /**
     * @return boolean
     */
    public function isTopNavbar()
    {
        return $this->_topNavbar;
    }

    /**
     * @param boolean $topNavbar
     */
    public function setTopNavbar($topNavbar)
    {
        $this->_topNavbar = $topNavbar;
    }

    /**
     * @return boolean
     */
    public function isSortViews()
    {
        return $this->_sortViews;
    }

    /**
     * @param boolean $sortViews
     */
    public function setSortViews($sortViews)
    {
        $this->_sortViews = $sortViews;
    }

    /**
     * @return int
     */
    public function getResultsPerPage()
    {
        return $this->_resultsPerPage;
    }

    /**
     * @param int $resultsPerPage
     */
    public function setResultsPerPage($resultsPerPage)
    {
        $this->_resultsPerPage = $resultsPerPage;
    }

    /**
     * @return boolean
     */
    public function isAccountLink()
    {
        return $this->_accountLink;
    }

    /**
     * @param boolean $accountLink
     */
    public function setAccountLink($accountLink)
    {
        $this->_accountLink = $accountLink;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->_theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->_theme = $theme;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;
    }

    /**
     * @return boolean
     */
    public function isUse2Fa()
    {
        return $this->_use2Fa;
    }

    /**
     * @param boolean $use2Fa
     */
    public function setUse2Fa($use2Fa)
    {
        $this->_use2Fa = $use2Fa;
    }

    /**
     * Modificar las preferencias de un usuario
     *
     * @return bool
     */
    public function updatePreferences()
    {
        $query = 'UPDATE usrData SET '
            . 'user_preferences = :preferences '
            . 'WHERE user_id = :id LIMIT 1';

        $data['id'] = $this->getId();
        $data['preferences'] = serialize($this);

        if (DB::getQuery($query, __FUNCTION__, $data) === false) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }


}