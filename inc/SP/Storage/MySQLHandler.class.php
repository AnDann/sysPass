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

namespace SP\Storage;

use PDO;
use SP\Config\Config;
use SP\Core\Exceptions\SPException;
use SP\Core\Init;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class MySQLHandler
 *
 * Esta clase se encarga de crear las conexiones a la BD
 */
class MySQLHandler implements DBStorageInterface
{
    /**
     * @var PDO
     */
    private $db;
    /**
     * @var string
     */
    private $dbHost = '';
    /**
     * @var int
     */
    private $dbPort = 0;
    /**
     * @var string
     */
    private $dbName = '';
    /**
     * @var string
     */
    private $dbUser = '';
    /**
     * @var string
     */
    private $dbPass = '';
    /**
     * @var int
     */
    private $dbStatus = 1;

    /**
     * MySQLHandler constructor.
     *
     * @param string $dbHost
     * @param int    $dbPort
     * @param string $dbName
     * @param string $dbUser
     * @param string $dbPass
     */
    public function __construct($dbHost = null, $dbPort = null, $dbName = null, $dbUser = null, $dbPass = null)
    {
        if ($dbHost
            && $dbPass
            && $dbName
            && $dbUser
            && $dbPass
            && $dbPort
        ) {
            $this->dbHost = $dbHost;
            $this->dbPort = $dbPort;
            $this->dbName = $dbName;
            $this->dbUser = $dbUser;
            $this->dbPass = $dbPass;
        } else {
            $this->setConnectionData();
        }
    }

    /**
     * @return mixed
     */
    public function setConnectionData()
    {
        $Config = Config::getConfig();

        $this->dbHost = $Config->getDbHost();
        $this->dbUser = $Config->getDbUser();
        $this->dbPass = $Config->getDbPass();
        $this->dbName = $Config->getDbName();
        $this->dbPort = $Config->getDbPort();
    }

    /**
     * Realizar la conexión con la BBDD.
     * Esta función utiliza PDO para conectar con la base de datos.
     *
     * @throws \SP\Core\Exceptions\SPException
     * @return PDO
     */

    public function getConnection()
    {
        if (!$this->db) {
            $isInstalled = Config::getConfig()->isInstalled();

            if (empty($this->dbHost) || empty($this->dbUser) || empty($this->dbPass) || empty($this->dbName)) {
                if ($isInstalled) {
                    Init::initError(_('No es posible conectar con la BD'), _('Compruebe los datos de conexión'));
                } else {
                    throw new SPException(SPException::SP_CRITICAL,
                        _('No es posible conectar con la BD'),
                        _('Compruebe los datos de conexión'));
                }
            }

            try {
                $dsn = 'mysql:host=' . $this->dbHost . ';port=' . $this->dbPort . ';dbname=' . $this->dbName . ';charset=utf8';
//                $this->db = new PDO($dsn, $dbuser, $dbpass, array(PDO::ATTR_PERSISTENT => true));
                $this->db = new PDO($dsn, $this->dbUser, $this->dbPass);
                $this->dbStatus = 0;
            } catch (\Exception $e) {
                if ($isInstalled) {
                    if ($e->getCode() === 1049) {
                        Config::getConfig()->setInstalled(false);
                        Config::saveConfig();
                    }
                    Init::initError(
                        _('No es posible conectar con la BD'),
                        'Error ' . $e->getCode() . ': ' . $e->getMessage());
                } else {
                    throw new SPException(SPException::SP_CRITICAL, $e->getMessage(), $e->getCode());
                }
            }
        }

        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->db;
    }

    /**
     * Devuelve el estado de conexión a la BBDD
     * OK -> 0
     * KO -> 1
     *
     * @return int
     */
    public function getDbStatus()
    {
        return $this->dbStatus;
    }
}