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

use JsonSerializable;
use SP\Util\Json;

/**
 * Class AccountData
 *
 * @package SP\Account
 */
class AccountData extends DataModelBase implements JsonSerializable, DataModelInterface
{
    /**
     * @var int Id de la cuenta.
     */
    private $accountId = 0;
    /**
     * @var int Id del usuario principal de la cuenta.
     */
    private $accountUserId = 0;
    /**
     * @var array Los Ids de los usuarios secundarios de la cuenta.
     */
    private $accountUsersId = [];
    /**
     * @var int Id del grupo principal de la cuenta.
     */
    private $accountUserGroupId = 0;
    /**
     * @var array Los Ids de los grupos secundarios de la cuenta.
     */
    private $accountUserGroupsId = [];
    /**
     * @var int Id del usuario que editó la cuenta.
     */
    private $accountUserEditId = 0;
    /**
     * @var string El nombre de la cuenta.
     */
    private $accountName = '';
    /**
     * @var int Id del cliente de la cuenta.
     */
    private $accountCustomerId = 0;
    /**
     * @var int Id de la categoría de la cuenta.
     */
    private $accountCategoryId = 0;
    /**
     * @var string El nombre de usuario de la cuenta.
     */
    private $accountLogin = '';
    /**
     * @var string La URL de la cuenta.
     */
    private $accountUrl = '';
    /**
     * @var string La clave de la cuenta.
     */
    private $accountPass = '';
    /**
     * @var string El vector de inicialización de la cuenta.
     */
    private $accountIV = '';
    /**
     * @var string Las nosta de la cuenta.
     */
    private $accountNotes = '';
    /**
     * @var bool Si se permite la edición por los usuarios secundarios.
     */
    private $accountOtherUserEdit = false;
    /**
     * @var bool Si se permita la edición por los grupos secundarios.
     */
    private $accountOtherGroupEdit = false;
    /**
     * @var int
     */
    private $dateAdd = 0;
    /**
     * @var int
     */
    private $dateEdit = 0;
    /**
     * @var bool
     */
    private $isModify = false;
    /**
     * @var bool
     */
    private $isDeleted = false;
    /**
     * @var array
     */
    private $tags = [];

    /**
     * AccountData constructor.
     *
     * @param int $accountId
     */
    public function __construct($accountId = 0)
    {
        $this->accountId = (int)$accountId;
    }

    /**
     * @return int
     */
    public function getDateAdd()
    {
        return $this->dateAdd;
    }

    /**
     * @param int $dateAdd
     */
    public function setDateAdd($dateAdd)
    {
        $this->dateAdd = (int)$dateAdd;
    }

    /**
     * @return int
     */
    public function getDateEdit()
    {
        return $this->dateEdit;
    }

    /**
     * @param int $dateEdit
     */
    public function setDateEdit($dateEdit)
    {
        $this->dateEdit = (int)$dateEdit;
    }

    /**
     * @return boolean
     */
    public function isIsModify()
    {
        return $this->isModify;
    }

    /**
     * @param boolean $isModify
     */
    public function setIsModify($isModify)
    {
        $this->isModify = (int)$isModify;
    }

    /**
     * @return boolean
     */
    public function isIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param boolean $isDeleted
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = (int)$isDeleted;
    }

    /**
     * @return int
     */
    public function getAccountUserEditId()
    {
        return $this->accountUserEditId;
    }

    /**
     * @param int $accountUserEditId
     */
    public function setAccountUserEditId($accountUserEditId)
    {
        $this->accountUserEditId = (int)$accountUserEditId;
    }

    /**
     * @return string
     */
    public function getAccountPass()
    {
        return $this->accountPass;
    }

    /**
     * @param string $accountPass
     */
    public function setAccountPass($accountPass)
    {
        $this->accountPass = $accountPass;
    }

    /**
     * @return string
     */
    public function getAccountIV()
    {
        return $this->accountIV;
    }

    /**
     * @param string $accountIV
     */
    public function setAccountIV($accountIV)
    {
        $this->accountIV = $accountIV;
    }

    /**
     * @return int|null
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param int $accountId
     */
    public function setAccountId($accountId)
    {
        $this->accountId = (int)$accountId;
    }

    /**
     * @return int
     */
    public function getAccountUserId()
    {
        return $this->accountUserId;
    }

    /**
     * @param int $accountUserId
     */
    public function setAccountUserId($accountUserId)
    {
        $this->accountUserId = (int)$accountUserId;
    }

    /**
     * @return int
     */
    public function getAccountUserGroupId()
    {
        return $this->accountUserGroupId;
    }

    /**
     * @param int $accountUserGroupId
     */
    public function setAccountUserGroupId($accountUserGroupId)
    {
        $this->accountUserGroupId = (int)$accountUserGroupId;
    }

    /**
     * @return bool
     */
    public function getAccountOtherUserEdit()
    {
        return $this->accountOtherUserEdit;
    }

    /**
     * @param bool $accountOtherUserEdit
     */
    public function setAccountOtherUserEdit($accountOtherUserEdit)
    {
        $this->accountOtherUserEdit = (int)$accountOtherUserEdit;
    }

    /**
     * @return bool
     */
    public function getAccountOtherGroupEdit()
    {
        return $this->accountOtherGroupEdit;
    }

    /**
     * @param bool $accountOtherGroupEdit
     */
    public function setAccountOtherGroupEdit($accountOtherGroupEdit)
    {
        $this->accountOtherGroupEdit = (int)$accountOtherGroupEdit;
    }

    /**
     * @return array
     */
    public function getAccountUserGroupsId()
    {
        return is_array($this->accountUserGroupsId) ? $this->accountUserGroupsId : [];
    }

    /**
     * @param array $accountUserGroupsId
     */
    public function setAccountUserGroupsId($accountUserGroupsId)
    {
        $this->accountUserGroupsId = $accountUserGroupsId;
    }

    /**
     * @return array
     */
    public function getAccountUsersId()
    {
        return $this->accountUsersId;
    }

    /**
     * @param array $accountUsersId
     */
    public function setAccountUsersId($accountUsersId)
    {
        $this->accountUsersId = $accountUsersId;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @param string $accountName
     */
    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
    }

    /**
     * @return int
     */
    public function getAccountCategoryId()
    {
        return $this->accountCategoryId;
    }

    /**
     * @param int $accountCategoryId
     */
    public function setAccountCategoryId($accountCategoryId)
    {
        $this->accountCategoryId = (int)$accountCategoryId;
    }

    /**
     * @return int
     */
    public function getAccountCustomerId()
    {
        return $this->accountCustomerId;
    }

    /**
     * @param int $accountCustomerId
     */
    public function setAccountCustomerId($accountCustomerId)
    {
        $this->accountCustomerId = (int)$accountCustomerId;
    }

    /**
     * @return string
     */
    public function getAccountLogin()
    {
        return $this->accountLogin;
    }

    /**
     * @param string $accountLogin
     */
    public function setAccountLogin($accountLogin)
    {
        $this->accountLogin = $accountLogin;
    }

    /**
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->accountUrl;
    }

    /**
     * @param string $accountUrl
     */
    public function setAccountUrl($accountUrl)
    {
        $this->accountUrl = $accountUrl;
    }

    /**
     * @return string
     */
    public function getAccountNotes()
    {
        return $this->accountNotes;
    }

    /**
     * @param string $accountNotes
     */
    public function setAccountNotes($accountNotes)
    {
        $this->accountNotes = $accountNotes;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *        which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $data = get_object_vars($this);

        unset($data['accountPass'], $data['accountIV']);

        return Json::safeJson($data);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->accountName;
    }
}