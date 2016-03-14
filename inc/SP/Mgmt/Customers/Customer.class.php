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

namespace SP\Mgmt\Customers;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

use SP\DataModel\CustomerData;
use SP\Log\Email;
use SP\Mgmt\ItemInterface;
use SP\Storage\DB;
use SP\Html\Html;
use SP\Log\Log;
use SP\Core\SPException;
use SP\Storage\DBUtil;
use SP\Storage\QueryData;

/**
 * Esta clase es la encargada de realizar las operaciones sobre los clientes de sysPass
 */
class Customer extends CustomerBase implements ItemInterface
{
    /**
     * @return $this
     * @throws SPException
     */
    public function add()
    {
        if ($this->checkDuplicatedOnAdd()) {
            throw new SPException(SPException::SP_WARNING, _('Cliente duplicado'));
        }

        $query = /** @lang SQL */
            'INSERT INTO customers
            SET customer_name = ?,
            customer_description = ?,
            customer_hash = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getCustomerName());
        $Data->addParam($this->itemData->getCustomerDescription());
        $Data->addParam($this->itemData->getCustomerHash());

        if (DB::getQuery($Data) === false) {
            throw new SPException(SPException::SP_CRITICAL, _('Error al crear el cliente'));
        }

        $this->itemData->setCustomerId(DB::$lastId);

        $Log = new Log(_('Nuevo Cliente'));
        $Log->addDetails(Html::strongText(_('Cliente')), $this->itemData->getCustomerName());
        $Log->writeLog();

        Email::sendEmail($Log);

        return $this;
    }

    /**
     * @return bool
     */
    public function checkDuplicatedOnAdd()
    {
        $query = /** @lang SQL */
            'SELECT customer_id FROM customers WHERE customer_hash = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->mkCustomerHash());

        return (DB::getQuery($Data) === false || DB::$lastNumRows >= 1);
    }

    /**
     * Crear un hash con el nombre del cliente.
     * Esta función crear un hash para detectar clientes duplicados mediante
     * la eliminación de carácteres especiales y capitalización
     *
     * @return string con el hash generado
     */
    private function mkCustomerHash()
    {
        $charsSrc = array(
            ".", " ", "_", ", ", "-", ";",
            "'", "\"", ":", "(", ")", "|", "/");
        $newValue = strtolower(str_replace($charsSrc, '', DBUtil::escape($this->itemData->getCustomerName())));

        return md5($newValue);
    }

    /**
     * @param $id int
     * @return mixed
     * @throws SPException
     */
    public function delete($id)
    {
        if ($this->checkInUse($id)) {
            // FIXME
            throw new SPException(
                SPException::SP_WARNING,
                _('No es posible eliminar') . ';;' . _('Cliente en uso por')
            );
        }

        $oldCustomer = $this->getById($id)->getItemData();

        $query = /** @lang SQL */
            'DELETE FROM customers WHERE customer_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getCustomerId());

        if (DB::getQuery($Data) === false) {
            throw new SPException(SPException::SP_CRITICAL, _('Error al eliminar el cliente'));
        }

        $Log = new Log(_('Eliminar Cliente'));
        $Log->addDetails(Html::strongText(_('Cliente')), sprintf('%s (%d)', $oldCustomer->getCustomerName(), $id));
        $Log->writeLog();

        Email::sendEmail($Log);

        return $this;
    }

    /**
     * @param $id int
     * @return mixed
     */
    public function checkInUse($id)
    {
        $query = /** @lang SQL */
            'SELECT account_id FROM accounts WHERE account_customerId = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($id);

        DB::getQuery($Data);

        return DB::$lastNumRows > 0;
    }

    /**
     * @param $id int
     * @return $this
     */
    public function getById($id)
    {
        $query = /** @lang SQL */
            'SELECT customer_id, customer_name, customer_description FROM customers WHERE customer_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setMapClassName('SP\DataModel\CustomerData');
        $Data->setQuery($query);
        $Data->addParam($id);

        $this->itemData = DB::getResults($Data);

        return $this;
    }

    /**
     * @return $this
     * @throws SPException
     */
    public function update()
    {
        if ($this->checkDuplicatedOnUpdate()) {
            throw new SPException(SPException::SP_WARNING, _('Cliente duplicado'));
        }

        $oldCustomer = $this->getById($this->itemData->getCustomerId())->getItemData();

        $query = /** @lang SQL */
            'UPDATE customers
            SET customer_name = ?,
            customer_description = ?,
            customer_hash = ?
            WHERE customer_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getCustomerName());
        $Data->addParam($this->itemData->getCustomerDescription());
        $Data->addParam($this->mkCustomerHash());
        $Data->addParam($this->itemData->getCustomerId());

        if (DB::getQuery($Data) === false) {
            throw new SPException(SPException::SP_CRITICAL, _('Error al actualizar el cliente'));
        }

        $Log = new Log(_('Actualizar Cliente'));
        $Log->addDetails(Html::strongText(_('Nombre')), sprintf('%s > %s', $oldCustomer->getCustomerName(), $this->itemData->getCustomerName()));
        $Log->addDetails(Html::strongText(_('Descripción')), sprintf('%s > %s', $oldCustomer->getCustomerDescription(), $this->itemData->getCustomerDescription()));
        $Log->writeLog();

        Email::sendEmail($Log);

        return $this;
    }

    /**
     * @return bool
     */
    public function checkDuplicatedOnUpdate()
    {
        $query = /** @lang SQL */
            'SELECT customer_id FROM customers WHERE customer_hash = ? AND customer_id <> ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->mkCustomerHash());
        $Data->addParam($this->itemData->getCustomerId());

        return (DB::getQuery($Data) === false || DB::$lastNumRows >= 1);
    }

    /**
     * @return CustomerData[]
     */
    public function getAll()
    {
        $query = /** @lang SQL */
            'SELECT customer_id, customer_name, customer_description FROM customers ORDER BY customer_name';

        $Data = new QueryData();
        $Data->setMapClassName('SP\DataModel\CustomerData');
        $Data->setQuery($query);

        DB::setReturnArray();

        return DB::getResults($Data);
    }
}
