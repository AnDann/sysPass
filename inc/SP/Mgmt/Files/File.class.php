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

namespace SP\Mgmt\Files;

use SP\Account\AccountUtil;
use SP\Core\Exceptions\SPException;
use SP\DataModel\FileData;
use SP\Log\Email;
use SP\Log\Log;
use SP\Mgmt\ItemInterface;
use SP\Mgmt\ItemSelectInterface;
use SP\Mgmt\ItemTrait;
use SP\Storage\DB;
use SP\Storage\QueryData;
use SP\Util\ImageUtil;

defined('APP_ROOT') || die();

/**
 * Esta clase es la encargada de realizar operaciones con archivos de las cuentas de sysPass
 */
class File extends FileBase implements ItemInterface, ItemSelectInterface
{
    use ItemTrait;

    /**
     * @return mixed
     * @throws \phpmailer\phpmailerException
     * @throws \SP\Core\Exceptions\SPException
     */
    public function add()
    {
        $query = /** @lang SQL */
            'INSERT INTO accFiles
            SET accfile_accountId = ?,
            accfile_name = ?,
            accfile_type = ?,
            accfile_size = ?,
            accfile_content = ?,
            accfile_extension = ?,
            accfile_thumb = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getAccfileAccountId());
        $Data->addParam($this->itemData->getAccfileName());
        $Data->addParam($this->itemData->getAccfileType());
        $Data->addParam($this->itemData->getAccfileSize());
        $Data->addParam($this->itemData->getAccfileContent());
        $Data->addParam($this->itemData->getAccfileExtension());

        if (FileUtil::isImage($this->itemData)) {
            $thumbnail = ImageUtil::createThumbnail($this->itemData->getAccfileContent());

            if ($thumbnail !== false) {
                $Data->addParam($thumbnail);
            } else {
                $Data->addParam('no_thumb');
            }
        } else {
            $Data->addParam('no_thumb');
        }

        $Log = new Log();
        $LogMessage = $Log->getLogMessage();
        $LogMessage->setAction(__('Subir Archivo', false));
        $LogMessage->addDetails(__('Cuenta', false), AccountUtil::getAccountNameById($this->itemData->getAccfileAccountId()));
        $LogMessage->addDetails(__('Archivo', false), $this->itemData->getAccfileName());
        $LogMessage->addDetails(__('Tipo', false), $this->itemData->getAccfileType());
        $LogMessage->addDetails(__('Tamaño', false), $this->itemData->getRoundSize() . 'KB');

        if (DB::getQuery($Data) === false) {
            $LogMessage->addDescription(__('No se pudo guardar el archivo', false));
            $Log->writeLog();

            Email::sendEmail($LogMessage);

            return false;
        }

        $LogMessage->addDescription(__('Archivo subido', false));
        $Log->writeLog();

        Email::sendEmail($LogMessage);

        return true;
    }

    /**
     * @param $id int|array
     * @return mixed
     * @throws \SP\Core\Exceptions\SPException
     */
    public function delete($id)
    {
        if (is_array($id)) {
            foreach ($id as $itemId){
                $this->delete($itemId);
            }

            return $this;
        }

        // Eliminamos el archivo de la BBDD
        $query = /** @lang SQL */
            'DELETE FROM accFiles WHERE accfile_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($id);

        if (DB::getQuery($Data) === false) {
            throw new SPException(SPException::SP_ERROR, __('Error al eliminar archivo', false));
        }

        return $this;
    }

    /**
     * @param $id
     * @return FileData
     */
    public function getInfoById($id)
    {
        $query = /** @lang SQL */
            'SELECT accfile_name,
            accfile_size,
            accfile_type,
            accfile_accountId,
            accfile_extension
            FROM accFiles
            WHERE accfile_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setQuery($query);
        $Data->addParam($id);

        return DB::getResults($Data);
    }

    /**
     * @return mixed
     */
    public function update()
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id int
     * @return FileData
     */
    public function getById($id)
    {
        $query = /** @lang SQL */
            'SELECT accfile_name,
            accfile_size,
            accfile_type,
            accfile_accountId,
            accfile_content,
            accfile_thumb,
            accfile_extension
            FROM accFiles
            WHERE accfile_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setQuery($query);
        $Data->addParam($id);

        return DB::getResults($Data);
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