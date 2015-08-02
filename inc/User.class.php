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

namespace SP;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class User
 *
 * @package SP
 */
class User extends UserBase
{
    /**
     * Actualizar la clave maestra del usuario en la BBDD.
     *
     * @param string $masterPwd con la clave maestra
     * @return bool
     */
    public function updateUserMPass($masterPwd)
    {
        $configMPass = Config::getConfigDbValue('masterPwd');

        if (!$configMPass) {
            return false;
        }

        if (Crypt::checkHashPass($masterPwd, $configMPass)) {
            $strUserMPwd = Crypt::mkCustomMPassEncrypt(self::getCypherPass($this->_userPass), $masterPwd);

            if (!$strUserMPwd) {
                return false;
            }
        } else {
            return false;
        }

        $query = 'UPDATE usrData SET '
            . 'user_mPass = :mPass,'
            . 'user_mIV = :mIV,'
            . 'user_lastUpdateMPass = UNIX_TIMESTAMP() '
            . 'WHERE user_id = :id LIMIT 1';

        $data['mPass'] = $strUserMPwd[0];
        $data['mIV'] = $strUserMPwd[1];
        $data['id'] = $this->_userId;

        return DB::getQuery($query, __FUNCTION__, $data);
    }

    /**
     * Obtener una clave de cifrado basada en la clave del usuario y un salt.
     *
     * @return string con la clave de cifrado
     */
    private function getCypherPass()
    {
        $configSalt = Config::getConfigDbValue('passwordsalt');
        $cypherPass = substr(sha1($configSalt . $this->_userPass), 0, 32);

        return $cypherPass;
    }

    /**
     * Desencriptar la clave maestra del usuario para la sesión.
     *
     * @param bool $showPass opcional, para devolver la clave desencriptada
     * @return false|string Devuelve bool se hay error o string si se devuelve la clave
     */
    public function getUserMPass($showPass = false)
    {
        $query = 'SELECT user_mPass, user_mIV FROM usrData WHERE user_id = :id LIMIT 1';

        $data['id'] = $this->_userId;

        $queryRes = DB::getResults($query, __FUNCTION__, $data);

        if ($queryRes === false) {
            return false;
        }

        if ($queryRes->user_mPass && $queryRes->user_mIV) {
            $clearMasterPass = Crypt::getDecrypt($queryRes->user_mPass, $this->getCypherPass(), $queryRes->user_mIV);

            if (!$clearMasterPass) {
                return false;
            }

            if ($showPass == true) {
                return $clearMasterPass;
            } else {
                $mPassPwd = Util::generate_random_bytes(32);
                Session::setMPassPwd($mPassPwd);

                $sessionMasterPass = Crypt::mkCustomMPassEncrypt($mPassPwd, $clearMasterPass);

                Session::setMPass($sessionMasterPass[0]);
                Session::setMPassIV($sessionMasterPass[1]);
                return true;
            }
        }

        return false;
    }
}