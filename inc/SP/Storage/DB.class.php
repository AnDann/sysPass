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

namespace SP\Storage;

use PDO;
use SP\Log\Log;
use SP\Core\SPException;
use SP\Util\Util;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Esta clase es la encargada de realizar las operaciones con la BBDD de sysPass.
 */
class DB
{
    /**
     * @var string
     */
    public static $txtError = '';
    /**
     * @var int
     */
    public static $numError = 0;
    /**
     * @var int
     */
    public static $lastNumRows = 0;
    /**
     * @var int
     */
    public static $lastId = null;
    /**
     * @var bool Resultado como array
     */
    private static $_retArray = false;
    /**
     * @var bool Resultado como un objeto PDO
     */
    private static $_returnRawData = false;
    /**
     * @var bool Contar el número de filas totales
     */
    private static $_fullRowCount = false;
    /**
     * @var int Número de registros obtenidos
     */
    private $_numRows = 0;
    /**
     * @var int Número de campos de la consulta
     */
    private $_numFields = 0;
    /**
     * @var array Resultados de la consulta
     */
    private $_lastResult = null;

    /**
     * @return int
     */
    public static function getLastId()
    {
        return self::$lastId;
    }

    /**
     * Establecer si se devuelve un array de objetos siempre
     */
    public static function setReturnArray()
    {
        self::$_retArray = true;
    }

    /**
     * Obtener los resultados de una consulta.
     *
     * @param  $queryData  QueryData Los datos de la consulta
     * @return bool|array devuelve bool si hay un error. Devuelve array con el array de registros devueltos
     */
    public static function getResults(QueryData $queryData)
    {
        if (empty($queryData->getQuery())) {
            self::resetVars();
            return false;
        }

        try {
            $db = new DB();
            $doQuery = $db->doQuery($queryData, self::$_returnRawData);
            self::$lastNumRows = (self::$_fullRowCount === false) ? $db->_numRows : $db->getFullRowCount($queryData);
        } catch (SPException $e) {
            self::logDBException($queryData->getQuery(), $e->getMessage(), $e->getCode(), __FUNCTION__);
            return false;
        }

        if (self::$_returnRawData
            && is_object($doQuery)
            && get_class($doQuery) === "PDOStatement"
        ) {
            return $doQuery;
        } elseif ($db->_numRows == 0) {
            self::resetVars();
            return false;
        } elseif ($db->_numRows == 1 && self::$_retArray === false) {
            self::resetVars();
            return $db->_lastResult[0];
        }

        self::resetVars();
        return $db->_lastResult;
    }

    /**
     * Restablecer los atributos estáticos
     */
    private static function resetVars()
    {
        self::$_returnRawData = false;
        self::$_fullRowCount = false;
        self::$_retArray = false;
    }

    /**
     * Realizar una consulta a la BBDD.
     *
     * @param $queryData   QueryData Los datos de la consulta
     * @param $getRawData  bool    realizar la consulta para obtener registro a registro
     * @return false|int devuelve bool si hay un error. Devuelve int con el número de registros
     * @throws SPException
     */
    public function doQuery(QueryData $queryData, $getRawData = false)
    {
        $isSelect = preg_match("/^(select|show)\s/i", $queryData->getQuery());

        // Limpiar valores de caché y errores
        $this->_lastResult = array();

        try {
            $queryRes = $this->prepareQueryData($queryData);
        } catch (SPException $e) {
            throw $e;
        }

        if ($isSelect) {
            if (!$getRawData) {
                $this->_numFields = $queryRes->columnCount();
                $this->_lastResult = $queryRes->fetchAll(PDO::FETCH_OBJ);
            } else {
                return $queryRes;
            }

//            $queryRes->closeCursor();

            $this->_numRows = count($this->_lastResult);
        }
    }

    /**
     * Asociar los parámetros de la consulta utilizando el tipo adecuado
     *
     * @param $queryData QueryData Los datos de la consulta
     * @param $isCount   bool   Indica si es una consulta de contador de registros
     * @return bool|\PDOStatement
     * @throws SPException
     */
    private function prepareQueryData(QueryData $queryData, $isCount = false)
    {
        if ($isCount === true) {
            // No incluimos en el array de parámetros de posición los valores
            // utilizados para LIMIT
            preg_match_all('/(\?|:)/', $queryData->getQuery(), $count);

            // Indice a partir del cual no se incluyen valores
            $paramMaxIndex = (count($count[1]) > 0) ? count($count[1]) : 0;
        }

        try {
            $db = DBConnectionFactory::getFactory()->getConnection();

            if (is_array($queryData->getParams())) {
                $sth = $db->prepare($queryData->getQuery());
                $paramIndex = 0;

                foreach ($queryData->getParams() as $param => $value) {
                    // Si la clave es un número utilizamos marcadores de posición "?" en
                    // la consulta. En caso contrario marcadores de nombre
                    $param = (is_int($param)) ? $param + 1 : ':' . $param;

                    if ($isCount === true && count($count) > 0 && $paramIndex >= $paramMaxIndex) {
                        continue;
                    }

                    if ($param === 'blobcontent') {
                        $sth->bindValue($param, $value, PDO::PARAM_LOB);
                    } elseif (is_int($value)) {
//                        error_log("INT: " . $param . " -> " . $value);
                        $sth->bindValue($param, $value, PDO::PARAM_INT);
                    } else {
//                        error_log("STR: " . $param . " -> " . $value);
                        $sth->bindValue($param, $value, PDO::PARAM_STR);
                    }

                    $paramIndex++;
                }

                $sth->execute();
            } else {
                $sth = $db->query($queryData->getQuery());
            }

            DB::$lastId = $db->lastInsertId();

            return $sth;
        } catch (\Exception $e) {
            error_log("Exception: " . $e->getMessage());
            throw new SPException(SPException::SP_CRITICAL, $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Obtener el número de filas de una consulta realizada
     *
     * @param $queryData QueryData Los datos de la consulta
     * @return int Número de files de la consulta
     * @throws SPException
     */
    private function getFullRowCount(QueryData $queryData)
    {
        if (empty($queryData->getQuery())) {
            return 0;
        }

        $num = 0;
        $patterns = array(
            '/(LIMIT|ORDER BY|GROUP BY).*/i',
            '/SELECT DISTINCT\s([\w_]+),.* FROM/iU',
            '/SELECT [\w_]+,.* FROM/iU',
        );
        $replace = array('', 'SELECT COUNT(DISTINCT \1) FROM', 'SELECT COUNT(*) FROM', '');

//        preg_match('/SELECT DISTINCT\s([\w_]*),.*\sFROM\s([\w_]*)\s(LEFT|RIGHT|WHERE).*/iU', $queryData->getQuery(), $match);

        $query = preg_replace($patterns, $replace, $queryData->getQuery());

        try {
            $db = DBConnectionFactory::getFactory()->getConnection();

            if (!is_array($queryData->getParams())) {
                $queryRes = $db->query($query);
                $num = intval($queryRes->fetchColumn());
            } else {
                if ($queryRes = $this->prepareQueryData($queryData, true)) {
                    $num = intval($queryRes->fetchColumn());
                }
            }

            $queryRes->closeCursor();

            return $num;
        } catch (SPException $e) {
            error_log("Exception: " . $e->getMessage());
            throw new SPException(SPException::SP_CRITICAL, $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Método para registar los eventos de BD en el log
     *
     * @param $query     string  La consulta que genera el error
     * @param $errorMsg  string  El mensaje de error
     * @param $errorCode int     El código de error
     * @param $queryFunction
     */
    private static function logDBException($query, $errorMsg, $errorCode, $queryFunction)
    {
        $caller = Util::traceLastCall($queryFunction);

        $Log = new Log($caller, Log::ERROR);
        $Log->setLogLevel(Log::ERROR);
        $Log->addDescription($errorMsg . '(' . $errorCode . ')');
        $Log->addDetails('SQL', DBUtil::escape($query));
        $Log->writeLog();

        error_log($query);
        error_log($errorMsg);
    }

    /**
     * Realizar una consulta y devolver el resultado sin datos
     *
     * @param QueryData       $queryData   Los datos para realizar la consulta
     * @param                 $getRawData  bool   Si se deben de obtener los datos como PDOStatement
     * @return bool
     */
    public static function getQuery(QueryData $queryData, $getRawData = false)
    {
        if (empty($queryData->getQuery())) {
            return false;
        }

        try {
            $db = new DB();
            $db->doQuery($queryData, $getRawData);
            DB::$lastNumRows = $db->_numRows;
        } catch (SPException $e) {
            self::logDBException($queryData->getQuery(), $e->getMessage(), $e->getCode(), __FUNCTION__);
            self::$txtError = $e->getMessage();
            self::$numError = $e->getCode();

            return false;
        }

        return true;
    }

    /**
     * Establecer si se devuelven los datos obtenidos como PDOStatement
     *
     * @param bool $on
     */
    public static function setReturnRawData($on = true)
    {
        self::$_returnRawData = (bool)$on;
    }

    /**
     * Establecer si es necesario contar el número total de resultados devueltos
     */
    public static function setFullRowCount()
    {
        self::$_fullRowCount = true;
    }
}
