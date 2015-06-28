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
 * Esta clase es la encargada de realizar las operaciones sobre los clientes de sysPass
 */
class Customer
{
    public static $customerName;
    public static $customerDescription;
    public static $customerLastId;
    public static $customerHash;

    /**
     * Crear un nuevo cliente en la BBDD.
     *
     * @return bool
     */
    public static function addCustomer()
    {
        $query = 'INSERT INTO customers SET customer_name = :name, customer_description = :description, customer_hash = :hash';

        $data['name'] = self::$customerName;
        $data['description'] = self::$customerDescription;
        $data['hash'] = self::mkCustomerHash();

        if (DB::getQuery($query, __FUNCTION__, $data) === false) {
            return false;
        }

        self::$customerLastId = DB::$lastId;

        Log::writeNewLogAndEmail(_('Nuevo Cliente'), Html::strongText(_('Cliente') . ': ') . self::$customerName);

        return true;
    }

    /**
     * Crear un hash con el nombre del cliente.
     * Esta función crear un hash para detectar clientes duplicados mediante
     * la eliminación de carácteres especiales y capitalización
     *
     * @return string con el hash generado
     */
    private static function mkCustomerHash()
    {
        $charsSrc = array(
            ".", " ", "_", ", ", "-", ";",
            "'", "\"", ":", "(", ")", "|", "/");
        $newValue = strtolower(str_replace($charsSrc, '', DB::escape(self::$customerName)));
        $hashValue = md5($newValue);

        return $hashValue;
    }

    /**
     * Actualizar un cliente en la BBDD.
     *
     * @param int $id con el Id del cliente
     * @return bool
     */
    public static function updateCustomer($id)
    {
        $customerName = self::getCustomerById($id);

        $query = "UPDATE customers "
            . "SET customer_name = :name,"
            . "customer_description = :description,"
            . "customer_hash = :hash "
            . "WHERE customer_id = :id";

        $data['name'] = self::$customerName;
        $data['description'] = self::$customerDescription;
        $data['hash'] = self::mkCustomerHash();
        $data['id'] = $id;

        if (DB::getQuery($query, __FUNCTION__, $data) === false) {
            return false;
        }

        Log::writeNewLogAndEmail(_('Actualizar Cliente'), Html::strongText(_('Cliente') . ': ') . $customerName . ' > ' . self::$customerName);

        return true;
    }

    /**
     * Obtener el Nombre de un cliente por su Id.
     *
     * @param int $id con el Id del cliente
     * @return false|string con el nombre del cliente
     */
    public static function getCustomerById($id)
    {
        $query = 'SELECT customer_name FROM customers WHERE customer_id = :id LIMIT 1';

        $data['id'] = $id;

        $queryRes = DB::getResults($query, __FUNCTION__, $data);

        if ($queryRes === false) {
            return false;
        }

        return $queryRes->customer_name;
    }

    /**
     * Eliminar un cliente de la BBDD.
     *
     * @param int $id con el Id del cliente a eliminar
     * @return bool
     */
    public static function delCustomer($id)
    {
        $customerName = self::getCustomerById($id);

        $query = 'DELETE FROM customers WHERE customer_id = :id LIMIT 1';

        $data['id'] = $id;

        if (DB::getQuery($query, __FUNCTION__, $data) === false) {
            return false;
        }

        Log::writeNewLogAndEmail(_('Eliminar Cliente'), Html::strongText(_('Cliente') . ': ') . $customerName);

        return true;
    }

    /**
     * Comprobar si existe un cliente duplicado comprobando el hash.
     *
     * @param int $id opcional con el Id del cliente
     * @return bool
     */
    public static function checkDupCustomer($id = NULL)
    {
        if ($id === NULL) {
            $query = 'SELECT customer_id FROM customers WHERE customer_hash = :hash';
        } else {
            $query = 'SELECT customer_id FROM customers WHERE customer_hash = :hash AND customer_id <> :id';

            $data['id'] = $id;
        }

        $data['hash'] = self::mkCustomerHash();

        return (DB::getQuery($query, __FUNCTION__, $data) === false || DB::$last_num_rows >= 1);

//        return ($db->getFullRowCount($query) >= 1);
    }

    /**
     * Obtener el Id de un cliente por su nombre
     *
     * @return false|int Con el Id del cliente
     */
    public static function getCustomerByName()
    {
        $query = 'SELECT customer_id FROM customers WHERE customer_hash = :hash LIMIT 1';

        $data['hash'] = self::mkCustomerHash();

        $queryRes = DB::getResults($query, __FUNCTION__, $data);

        if ($queryRes === false) {
            return false;
        }

        return $queryRes->customer_id;
    }

    /**
     * Obtener los datos de un cliente.
     *
     * @param int $id con el Id del cliente a consultar
     * @return array con el nombre de la columna como clave y los datos como valor
     */
    public static function getCustomerData($id = 0)
    {
        $customer = array('customer_id' => 0,
            'customer_name' => '',
            'customer_description' => '',
            'action' => 1);

        if ($id > 0) {
            $customers = self::getCustomers($id);

            if ($customers) {
                foreach ($customers[0] as $name => $value) {
                    $customer[$name] = $value;
                }
                $customer['action'] = 2;
            }
        }

        return $customer;
    }

    /**
     * Obtener el listado de clientes.
     *
     * @param int  $customerId    con el Id del cliente
     * @param bool $retAssocArray para devolver un array asociativo
     * @return array con el id de cliente como clave y el nombre como valor
     */
    public static function getCustomers($customerId = null, $retAssocArray = false)
    {
        $query = 'SELECT customer_id, customer_name, customer_description FROM customers ';
        $data = null;

        if (!is_null($customerId)) {
            $query .= "WHERE customer_id = :id LIMIT 1";
            $data['id'] = $customerId;
        } else {
            $query .= "ORDER BY customer_name";
        }

        DB::setReturnArray();

        $queryRes = DB::getResults($query, __FUNCTION__, $data);

        if ($queryRes === false) {
            return array();
        }

        if ($retAssocArray) {
            $resCustomers = array();

            foreach ($queryRes as $customer) {
                $resCustomers[$customer->customer_id] = $customer->customer_name;
            }

            return $resCustomers;
        }

        return $queryRes;
    }

    /**
     * Comprobar si un cliente está en uso.
     * Esta función comprueba si un cliente está en uso por cuentas.
     *
     * @param int $id con el Id del cliente a consultar
     * @return int Con el número de cuentas
     */
    public static function checkCustomerInUse($id)
    {
        $count['accounts'] = self::getCustomerInAccounts($id);
        return $count;
    }

    /**
     * Obtener el número de cuentas que usan un cliente.
     *
     * @param int $id con el Id del cliente a consultar
     * @return int con el número total de cuentas
     */
    private static function getCustomerInAccounts($id)
    {
        $query = 'SELECT account_id FROM accounts WHERE account_customerId = :id';

        $data['id'] = $id;

        DB::getQuery($query, __FUNCTION__, $data);

        return DB::$last_num_rows;
    }
}
