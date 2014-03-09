<?php
/**
 * sysPass
 *
 * @author nuxsmin
 * @link http://syspass.org
 * @copyright 2012-2014 Rubén Domínguez nuxsmin@syspass.org
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

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Esta clase es la encargada de realizar las operaciones sobre las cuentas de sysPass.
 */
class SP_Account
{
    static $accountSearchTxt;
    static $accountSearchCustomer;
    static $accountSearchCategory;
    static $accountSearchOrder;
    static $accountSearchKey;

    var $accountId;
    var $accountParentId;
    var $accountUserId;
    var $accountUsersId;
    var $accountUserGroupId;
    var $accountUserGroupsId;
    var $accountUserEditId;
    var $accountName;
    var $accountCustomerId;
    var $accountCategoryId;
    var $accountLogin;
    var $accountUrl;
    var $accountPass;
    var $accountIV;
    var $accountNotes;
    var $accountOtherUserEdit;
    var $accountOtherGroupEdit;
    var $accountModHash;

    var $lastAction;
    var $lastId;
    var $query; // Variable de consulta
    var $queryNumRows;
    var $accountIsHistory = 0; // Variable para indicar si la cuenta es desde el histórico
    var $accountCacheUserGroupsId; // Cache para grupos de usuarios de las cuentas
    var $accountCacheUsersId; // Cache para usuarios de las cuentas

    // Variable para la caché de parámetros
    var $cacheParams;

    /**
     * @brief Obtener los datos de usuario y modificador de una cuenta
     * @param int $accountId con el Id de la cuenta
     * @return object con el id de usuario y modificador.
     */
    public static function getAccountRequestData($accountId)
    {
        $query = "SELECT account_userId,"
            . "account_userEditId,"
            . "account_name,"
            . "customer_name "
            . "FROM accounts "
            . "LEFT JOIN customers ON account_customerId = customer_id "
            . "WHERE account_id = " . (int)$accountId . " LIMIT 1";
        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        return $queryRes;
    }

    /**
     * @brief Obtiene el listado con el nombre de los usuaios de una cuenta
     * @param int $accountId con el Id de la cuenta
     * @return array con los nombres de los usuarios ordenados
     */
    public static function getAccountUsersName($accountId)
    {
        $query = "SELECT user_name "
            . "FROM accUsers "
            . "JOIN usrData ON accuser_userId = user_id "
            . "WHERE accuser_accountId = " . (int)$accountId;

        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        if (!is_array($queryRes)) {
            return false;
        }

        foreach ($queryRes as $users) {
            $usersName[] = $users->user_name;
        }

        sort($usersName, SORT_STRING);

        return $usersName;
    }

    /**
     * @brief Obtener las cuentas de una búsqueda
     * @param array $searchFilter filtros de búsqueda
     * @return array resultado de la consulta
     */
    public function getAccounts($searchFilter)
    {
        $isAdmin = ($_SESSION["uisadminapp"] || $_SESSION["uisadminacc"]);
        $globalSearch = (SP_Config::getValue('globalsearch', 0) && $searchFilter["globalSearch"] === 1);

        $arrFilterCommon = array();
        $arrFilterSelect = array();
        $arrFilterUser = array();
        $arrQueryWhere = array();

        switch ($searchFilter["keyId"]) {
            case 1:
                $orderKey = "account_name";
                break;
            case 2:
                $orderKey = "category_name";
                break;
            case 3:
                $orderKey = "account_login";
                break;
            case 4:
                $orderKey = "account_url";
                break;
            case 5:
                $orderKey = "account_customerId";
                break;
            default :
                $orderKey = "customer_name, account_name";
                break;
        }

        $querySelect = "SELECT SQL_CALC_FOUND_ROWS DISTINCT "
            . "account_id,"
            . "account_customerId,"
            . "category_name,"
            . "account_name,"
            . "account_login,"
            . "account_url,"
            . "account_notes,"
            . "account_userId,"
            . "account_userGroupId,"
            . "account_otherUserEdit,"
            . "account_otherGroupEdit,"
            . "usergroup_name,"
            . "customer_name "
            . "FROM accounts "
            . "LEFT JOIN categories ON account_categoryId = category_id "
            . "LEFT JOIN usrGroups ug ON account_userGroupId = usergroup_id "
            . "LEFT JOIN customers ON customer_id = account_customerId "
            . "LEFT JOIN accUsers ON accuser_accountId = account_id "
            . "LEFT JOIN accGroups ON accgroup_accountId = account_id";

        if ($searchFilter["txtSearch"]) {
            $arrFilterCommon[] = "account_name LIKE '%" . $searchFilter["txtSearch"] . "%'";
            $arrFilterCommon[] = "account_login LIKE '%" . $searchFilter["txtSearch"] . "%'";
            $arrFilterCommon[] = "account_url LIKE '%" . $searchFilter["txtSearch"] . "%'";
            $arrFilterCommon[] = "account_notes LIKE '%" . $searchFilter["txtSearch"] . "%'";
        }

        if ($searchFilter["categoryId"] != 0) {
            $arrFilterSelect[] = "category_id = " . $searchFilter["categoryId"];
        }
        if ($searchFilter["customerId"] != 0) {
            $arrFilterSelect[] = "account_customerId = " . $searchFilter["customerId"];
        }


        if (count($arrFilterCommon) > 0) {
            $arrQueryWhere[] = "(" . implode(" OR ", $arrFilterCommon) . ")";
        }

        if (count($arrFilterSelect) > 0) {
            $arrQueryWhere[] = "(" . implode(" AND ", $arrFilterSelect) . ")";
        }

        if (!$isAdmin && !$globalSearch) {
            $arrFilterUser[] = "account_userGroupId = " . $searchFilter["groupId"];
            $arrFilterUser[] = "account_userId = " . $searchFilter["userId"];
            $arrFilterUser[] = "accgroup_groupId = " . $searchFilter["groupId"];
            $arrFilterUser[] = "accuser_userId = " . $searchFilter["userId"];

            $arrQueryWhere[] = "(" . implode(" OR ", $arrFilterUser) . ")";
        }

        $order = ($searchFilter["txtOrder"] == 0) ? 'ASC' : 'DESC';

        $queryOrder = " ORDER BY $orderKey " . $order;

        if ($searchFilter["limitCount"] != 99) {
            $queryLimit = "LIMIT " . $searchFilter["limitStart"] . ", " . $searchFilter["limitCount"];
        }

        if (count($arrQueryWhere) === 1) {
            $query = $querySelect . " WHERE " . implode($arrQueryWhere) . " " . $queryOrder . " " . $queryLimit;
        } elseif (count($arrQueryWhere) > 1) {
            $query = $querySelect . " WHERE " . implode(" AND ", $arrQueryWhere) . " " . $queryOrder . " " . $queryLimit;
        } else {
            $query = $querySelect . $queryOrder . " " . $queryLimit;
        }

        $this->query = $query;

        // Consulta de la búsqueda de cuentas
        $queryRes = DB::getResults($query, __FUNCTION__, true);

        if ($queryRes === false) {
            return false;
        }

        // Obtenemos el número de registros totales de la consulta sin contar el LIMIT
        $resQueryNumRows = DB::getResults("SELECT FOUND_ROWS() as numRows", __FUNCTION__);
        $this->queryNumRows = $resQueryNumRows->numRows;

        $_SESSION["accountSearchTxt"] = $searchFilter["txtSearch"];
        $_SESSION["accountSearchCustomer"] = $searchFilter["customerId"];
        $_SESSION["accountSearchCategory"] = $searchFilter["categoryId"];
        $_SESSION["accountSearchOrder"] = $searchFilter["txtOrder"];
        $_SESSION["accountSearchKey"] = $searchFilter["keyId"];
        $_SESSION["accountSearchStart"] = $searchFilter["limitStart"];
        $_SESSION["accountSearchLimit"] = $searchFilter["limitCount"];
        $_SESSION["accountGlobalSearch"] = $searchFilter["globalSearch"];

        return $queryRes;
    }

    /**
     * @brief Obtener los datos del histórico de una cuenta
     * @return none
     *
     * Esta funcion realiza la consulta a la BBDD y guarda los datos del histórico en las variables de la clase.
     */
    public function getAccountHistory()
    {
        $query = "SELECT acchistory_accountId as account_id,"
            . "acchistory_customerId as account_customerId,"
            . "acchistory_categoryId as account_categoryId,"
            . "acchistory_name as account_name,"
            . "acchistory_login as account_login,"
            . "acchistory_url as account_url,"
            . "acchistory_pass as account_pass,"
            . "acchistory_IV as account_IV,"
            . "acchistory_notes as account_notes,"
            . "acchistory_countView as account_countView,"
            . "acchistory_countDecrypt as account_countDecrypt,"
            . "acchistory_dateAdd as account_dateAdd,"
            . "acchistory_dateEdit as account_dateEdit,"
            . "acchistory_userId as account_userId,"
            . "acchistory_userGroupId as account_userGroupId,"
            . "acchistory_userEditId as account_userEditId,"
            . "acchistory_isModify,"
            . "acchistory_isDeleted,"
            . "acchistory_otherUserEdit as account_otherUserEdit,"
            . "acchistory_otherGroupEdit as account_otherGroupEdit,"
            . "u1.user_name,"
            . "u1.user_login,"
            . "usergroup_name,"
            . "u2.user_name as user_editName,"
            . "u2.user_login as user_editLogin,"
            . "category_name, customer_name "
            . "FROM accHistory "
            . "LEFT JOIN categories ON acchistory_categoryId = category_id "
            . "LEFT JOIN usrGroups ON acchistory_userGroupId = usergroup_id "
            . "LEFT JOIN usrData u1 ON acchistory_userId = u1.user_id "
            . "LEFT JOIN usrData u2 ON acchistory_userEditId = u2.user_id "
            . "LEFT JOIN customers ON acchistory_customerId = customer_id "
            . "WHERE acchistory_id = " . (int)$this->accountId . " LIMIT 1";

        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        $this->accountUserId = $queryRes->account_userId;
        $this->accountUserGroupId = $queryRes->account_userGroupId;
        $this->accountOtherUserEdit = $queryRes->account_otherUserEdit;
        $this->accountOtherGroupEdit = $queryRes->account_otherGroupEdit;

        return $queryRes;
    }

    /**
     * @brief Actualiza los datos de una cuenta en la BBDD
     * @param bool $isRestore si es una restauración de cuenta
     * @return bool
     */
    public function updateAccount($isRestore = false)
    {
        $message['action'][] = __FUNCTION__;

        // Guardamos una copia de la cuenta en el histórico
        if (!$this->addHistory($this->accountId, $this->accountUserEditId, false)) {
            $message['text'][] = _('Error al actualizar el historial');
            SP_Log::wrLogInfo($message);
            return false;
        }

        if ( ! $isRestore ){
            $message['action'] = _('Actualizar Cuenta');

            if (!SP_Groups::updateGroupsForAccount($this->accountId, $this->accountUserGroupsId)) {
                $message['text'][] = _('Error al actualizar los grupos secundarios');
                SP_Log::wrLogInfo($message);
                $message['text'] = array();
            }

            if (!SP_Users::updateUsersForAccount($this->accountId, $this->accountUsersId)) {
                $message['text'][] = _('Error al actualizar los usuarios de la cuenta');
                SP_Log::wrLogInfo($message);
                $message['text'] = array();
            }
        } else {
            $message['action'] = _('Restaurar Cuenta');
        }

        $query = "UPDATE accounts SET "
            . "account_customerId = " . (int)$this->accountCustomerId . ","
            . "account_categoryId = " . (int)$this->accountCategoryId . ","
            . "account_name = '" . DB::escape($this->accountName) . "',"
            . "account_login = '" . DB::escape($this->accountLogin) . "',"
            . "account_url = '" . DB::escape($this->accountUrl) . "',"
            . "account_notes = '" . DB::escape($this->accountNotes) . "',"
            . "account_userEditId = " . (int)$this->accountUserEditId . ","
            . "account_dateEdit = NOW(), "
            . "account_otherUserEdit = " . (int)$this->accountOtherUserEdit . ","
            . "account_otherGroupEdit = " . (int)$this->accountOtherGroupEdit . " "
            . "WHERE account_id = " . (int)$this->accountId;


        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        $accountInfo = array('customer_name');
        $this->getAccountInfoById($accountInfo);

        $message['action'] = _('Cuenta actualizada');
        $message['text'][] = SP_Html::strongText(_('Cliente') . ": ") . $this->cacheParams['customer_name'];
        $message['text'][] = SP_Html::strongText(_('Cuenta') . ": ") . "$this->accountName ($this->accountId)";

        SP_Log::wrLogInfo($message);
        SP_Common::sendEmail($message);

        return true;
    }

    /**
     * @brief Crear un nuevo registro de histório de cuenta en la BBDD
     * @param bool $isDelete indica que la cuenta es eliminada
     * @return bool
     */
    private function addHistory($isDelete = false)
    {
        $objAccountHist = new SP_Account;

        $objAccountHist->accountId = $this->accountId;
        $accountData = $objAccountHist->getAccount();

        $isModify = ($isDelete === false) ? 1 : 0;
        $isDelete = ($isDelete === false) ? 0 : 1;

        $query = "INSERT INTO accHistory SET "
            . "acchistory_accountId = " . $objAccountHist->accountId . ","
            . "acchistory_categoryId = " . $accountData->account_categoryId . ","
            . "acchistory_customerId = " . $accountData->account_customerId . ","
            . "acchistory_name = '" . DB::escape($accountData->account_name) . "',"
            . "acchistory_login = '" . DB::escape($accountData->account_login) . "',"
            . "acchistory_url = '" . DB::escape($accountData->account_url) . "',"
            . "acchistory_pass = '" . DB::escape($accountData->account_pass) . "',"
            . "acchistory_IV = '" . DB::escape($accountData->account_IV) . "',"
            . "acchistory_notes = '" . DB::escape($accountData->account_notes) . "',"
            . "acchistory_countView = " . $accountData->account_countView . ","
            . "acchistory_countDecrypt = " . $accountData->account_countDecrypt . ","
            . "acchistory_dateAdd = '" . $accountData->account_dateAdd . "',"
            . "acchistory_dateEdit = '" . $accountData->account_dateEdit . "',"
            . "acchistory_userId = " . $accountData->account_userId . ","
            . "acchistory_userGroupId = " . $accountData->account_userGroupId . ","
            . "acchistory_userEditId = " . $accountData->account_userEditId . ","
            . "acchistory_isModify = " . $isModify . ","
            . "acchistory_isDeleted = " . $isDelete . ","
            . "acchistory_otherUserEdit = " . $accountData->account_otherUserEdit . ","
            . "acchistory_otherGroupEdit = " . $accountData->account_otherGroupEdit . ","
            . "acchistory_mPassHash = '" . DB::escape(SP_Config::getConfigValue('masterPwd')) . "'";

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        return true;
    }

    /**
     * @brief Obtener los datos de una cuenta
     * @return none
     *
     * Esta funcion realiza la consulta a la BBDD y guarda los datos en las variables de la clase.
     */
    public function getAccount()
    {
        $query = "SELECT account_id,"
            . "account_name,"
            . "account_categoryId,"
            . "account_userId,"
            . "account_customerId,"
            . "account_userGroupId,"
            . "account_userEditId,"
            . "category_name,"
            . "account_login,"
            . "account_url,"
            . "account_pass,"
            . "account_IV,"
            . "account_notes,"
            . "account_countView,"
            . "account_countDecrypt,"
            . "account_dateAdd,"
            . "account_dateEdit,"
            . "account_otherUserEdit,"
            . "account_otherGroupEdit,"
            . "u1.user_name,"
            . "u1.user_login,"
            . "u2.user_name as user_editName,"
            . "u2.user_login as user_editLogin,"
            . "usergroup_name,"
            . "customer_name, "
            . "CONCAT(account_name,account_categoryId,account_customerId,account_login,account_url,account_notes,BIN(account_otherUserEdit),BIN(account_otherGroupEdit)) as modHash "
            . "FROM accounts "
            . "LEFT JOIN categories ON account_categoryId = category_id "
            . "LEFT JOIN usrGroups ug ON account_userGroupId = usergroup_id "
            . "LEFT JOIN usrData u1 ON account_userId = u1.user_id "
            . "LEFT JOIN usrData u2 ON account_userEditId = u2.user_id "
            . "LEFT JOIN customers ON account_customerId = customer_id "
            . "WHERE account_id = " . (int)$this->accountId . " LIMIT 1";

        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        $this->accountUserId = $queryRes->account_userId;
        $this->accountUserGroupId = $queryRes->account_userGroupId;
        $this->accountOtherUserEdit = $queryRes->account_otherUserEdit;
        $this->accountOtherGroupEdit = $queryRes->account_otherGroupEdit;
        $this->accountModHash = $queryRes->modHash;

        return $queryRes;
    }

    /**
     * @brief Obtener los datos de una cuenta con el id
     * @param array $params con los campos de la BBDD a obtener
     * @return bool
     *
     * Se guardan los datos en la variable $cacheParams de la clase para consultarlos
     * posteriormente.
     */
    private function getAccountInfoById($params)
    {
        if (!is_array($params)) {
            return false;
        }

        if (is_array($this->cacheParams)) {
            $cache = true;

            foreach ($params as $param) {
                if (!array_key_exists($param, $this->cacheParams)) {
                    $cache = false;
                }
            }

            if ($cache) {
                return true;
            }
        }

        $query = "SELECT " . implode(',', $params) . " "
            . "FROM accounts "
            . "LEFT JOIN usrGroups ug ON account_userGroupId = usergroup_id "
            . "LEFT JOIN usrData u1 ON account_userId = u1.user_id "
            . "LEFT JOIN usrData u2 ON account_userEditId = u2.user_id "
            . "LEFT JOIN customers ON account_customerId = customer_id "
            . "WHERE account_id = " . (int)$this->accountId . " LIMIT 1";
        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        foreach ($queryRes as $param => $value) {
            $this->cacheParams[$param] = $value;
        }

        return true;
    }

    /**
     * @brief Crea una nueva cuenta en la BBDD
     * @return bool
     */
    public function createAccount()
    {
        $query = "INSERT INTO accounts SET "
            . "account_customerId = " . (int)$this->accountCustomerId . ","
            . "account_categoryId = " . (int)$this->accountCategoryId . ","
            . "account_name = '" . DB::escape($this->accountName) . "',"
            . "account_login = '" . DB::escape($this->accountLogin) . "',"
            . "account_url = '" . DB::escape($this->accountUrl) . "',"
            . "account_pass = '$this->accountPass',"
            . "account_IV = '" . DB::escape($this->accountIV) . "',"
            . "account_notes = '" . DB::escape($this->accountNotes) . "',"
            . "account_dateAdd = NOW(),"
            . "account_userId = " . (int)$this->accountUserId . ","
            . "account_userGroupId = " . (int)$this->accountUserGroupId . ","
            . "account_otherUserEdit = " . (int)$this->accountOtherUserEdit . ","
            . "account_otherGroupEdit = " . (int)$this->accountOtherGroupEdit;

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        $this->accountId = DB::$lastId;

        $message['action'] = __FUNCTION__;

        if (!SP_Groups::addGroupsForAccount($this->accountId, $this->accountUserGroupsId)) {
            $message['text'][] = _('Error al actualizar los grupos secundarios');
            SP_Log::wrLogInfo($message);
            $message['text'] = array();
        }

        if (!SP_Users::addUsersForAccount($this->accountId, $this->accountUsersId)) {
            $message['text'][] = _('Error al actualizar los usuarios de la cuenta');
            SP_Log::wrLogInfo($message);
            $message['text'] = array();
        }

        $accountInfo = array('customer_name');
        $this->getAccountInfoById($accountInfo);

        $message['action'] = _('Nueva Cuenta');
        $message['text'][] = SP_Html::strongText(_('Cliente') . ": ") . $this->cacheParams['customer_name'];
        $message['text'][] = SP_Html::strongText(_('Cuenta') . ": ") . "$this->accountName ($this->accountId)";

        SP_Log::wrLogInfo($message);
        SP_Common::sendEmail($message);

        return true;
    }

    /**
     * @brief Elimina los datos de una cuenta en la BBDD
     * @return bool
     */
    public function deleteAccount()
    {
        // Guardamos una copia de la cuenta en el histórico
        $this->addHistory(true) || die (_('ERROR: Error en la operación.'));

        $accountInfo = array('account_name,customer_name');
        $this->getAccountInfoById($accountInfo);

        $message['action'] = _('Eliminar Cuenta');
        $message['text'][] = SP_Html::strongText(_('Cliente') . ": ") . $this->cacheParams['customer_name'];
        $message['text'][] = SP_Html::strongText(_('Cuenta') . ": ") . $this->cacheParams['account_name'] . " ($this->accountId)";

        $query = "DELETE FROM accounts "
            . "WHERE account_id = " . (int)$this->accountId . " LIMIT 1";

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        if (!SP_Groups::deleteGroupsForAccount($this->accountId)) {
            $message['text'][] = _('Error al eliminar grupos asociados a la cuenta');
        }

        if (!SP_Users::deleteUsersForAccount($this->accountId)) {
            $message['text'][] = _('Error al eliminar usuarios asociados a la cuenta');
        }

        if (!SP_Files::deleteAccountFiles($this->accountId)) {
            $message['text'][] = _('Error al eliminar archivos asociados a la cuenta');
        }

        SP_Log::wrLogInfo($message);
        SP_Common::sendEmail($message);

        return true;
    }

    /**
     * @brief Obtiene el listado del histórico de una cuenta
     * @return array con los registros con id como clave y fecha - usuario como valor
     */
    public function getAccountHistoryList()
    {
        $query = "SELECT acchistory_id,"
            . "acchistory_dateEdit,"
            . "u1.user_login as user_edit,"
            . "u2.user_login as user_add,"
            . "acchistory_dateAdd "
            . "FROM accHistory "
            . "LEFT JOIN usrData u1 ON acchistory_userEditId = u1.user_id "
            . "LEFT JOIN usrData u2 ON acchistory_userId = u2.user_id "
            . "WHERE acchistory_accountId = " . $_SESSION["accParentId"] . " "
            . "ORDER BY acchistory_id DESC";

        $queryRes = DB::getResults($query, __FUNCTION__, true);

        if ($queryRes === false) {
            return false;
        }

        $arrHistory = array();

        foreach ($queryRes as $history) {
            if ($history->acchistory_dateEdit == '0000-00-00 00:00:00') {
                $arrHistory[$history->acchistory_id] = $history->acchistory_dateAdd . " - " . $history->user_add;
            } else {
                $arrHistory[$history->acchistory_id] = $history->acchistory_dateEdit . " - " . $history->user_edit;
            }
        }

        return $arrHistory;
    }

    /**
     * @brief Incrementa el contador de visitas de una cuenta en la BBDD
     * @return bool
     */
    public function incrementViewCounter()
    {
        $query = "UPDATE accounts "
            . "SET account_countView = (account_countView + 1) "
            . "WHERE account_id = " . (int)$this->accountId;

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        return true;
    }

    /**
     * @brief Incrementa el contador de vista de clave de una cuenta en la BBDD
     * @return bool
     */
    public function incrementDecryptCounter()
    {
        $query = "UPDATE accounts SET account_countDecrypt = (account_countDecrypt + 1) "
            . "WHERE account_id = " . (int)$this->accountId;

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        return true;
    }

    /**
     * @brief Obtiene el número de cuentas que un usuario puede ver
     * @return int con el número de registros
     */
    public function getAccountMax()
    {
        $userGroupId = $_SESSION["ugroup"];
        $userId = $_SESSION["uid"];
        $userIsAdminApp = $_SESSION['uisadminapp'];
        $userIsAdminAcc = $_SESSION['uisadminacc'];

        if (!$userIsAdminApp && !$userIsAdminAcc) {
            $query = "SELECT COUNT(DISTINCT account_id) as numacc "
                . "FROM accounts "
                . "LEFT JOIN accGroups ON account_id = accgroup_accountId "
                . "WHERE account_userGroupId = " . (int)$userGroupId . " "
                . "OR account_userId = " . (int)$userId . " "
                . "OR accgroup_groupId = " . (int)$userGroupId;
        } else {
            $query = "SELECT COUNT(account_id) as numacc FROM accounts";
        }

        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        return $queryRes->numacc;
    }

    /**
     * @brief Actualiza las claves de todas las cuentas con la nueva clave maestra
     * @param string $currentMasterPass con la clave maestra actual
     * @param string $newMasterPass con la nueva clave maestra
     * @return bool
     */
    public function updateAllAccountsMPass($currentMasterPass, $newMasterPass)
    {
        $accountsOk = array();
        $userId = $_SESSION["uid"];
        $errorCount = 0;
        $demoEnabled = SP_Util::demoIsEnabled();

        $message['action'] = _('Actualizar Clave Maestra');
        $message['text'][] = _('Inicio');

        SP_Log::wrLogInfo($message);

        // Limpiar 'text' para los próximos mensajes
        $message['text'] = array();

        $crypt = new SP_Crypt();

        if (!SP_Crypt::checkCryptModule()) {
            $message['text'][] = _('Error en el módulo de encriptación');
            SP_Log::wrLogInfo($message);
            return false;
        }

        $accountsPass = $this->getAccountsPassData();

        if (!$accountsPass) {
            $message['text'][] = _('Error al obtener las claves de las cuentas');
            SP_Log::wrLogInfo($message);
            return false;
        }

        foreach ($accountsPass as $account) {
            $this->accountId = $account->account_id;
            $this->accountUserEditId = $userId;

            // No realizar cambios si está en modo demo
            if ($demoEnabled) {
                $accountsOk[] = $this->accountId;
                continue;
            }

            $decryptedPass = $crypt->decrypt($account->account_pass, $currentMasterPass, $account->account_IV);
            $this->accountPass = $crypt->mkEncrypt($decryptedPass, $newMasterPass);
            $this->accountIV = $crypt->strInitialVector;

            if ($this->accountPass === false) {
                $errorCount++;
                continue;
            }

            if (!$this->updateAccountPass(true)) {
                $errorCount++;
                $message['text'][] = _('Fallo al actualizar la clave de la cuenta') . "(" . $this->accountId . ")";
            }
            $accountsOk[] = $this->accountId;
        }

        // Vaciar el array de mensaje de log
        if (count($message['text']) > 0) {
            SP_Log::wrLogInfo($message);
            $message['text'] = array();
        }

        if ($accountsOk) {
            $message['text'][] = _('Cuentas actualizadas:') . ": " . implode(',', $accountsOk);
            SP_Log::wrLogInfo($message);
            $message['text'] = array();
        }

        $message['text'][] = _('Fin');
        SP_Log::wrLogInfo($message);
        SP_Common::sendEmail($message);

        if ($errorCount > 0) {
            return false;
        }

        return true;
    }

    /**
     * @brief Obtener los datos relativos a la clave de todas las cuentas
     * @return array con los datos de la clave
     */
    private function getAccountsPassData()
    {
        $query = "SELECT account_id,"
            . "account_pass,"
            . "account_IV "
            . "FROM accounts";
        $queryRes = DB::getResults($query, __FUNCTION__, true);

        if ($queryRes === false) {
            return false;
        }

        return $queryRes;
    }

    /**
     * @brief Actualiza la clave de una cuenta en la BBDD
     * @param bool $isMassive para no actualizar el histórico ni enviar mensajes
     * @param bool $isRestore indica si es una restauración
     * @return bool
     */
    public function updateAccountPass($isMassive = false, $isRestore = false)
    {
        $message['action'] = __FUNCTION__;

        // No actualizar el histórico si es por cambio de clave maestra o restauración
        if (!$isMassive && !$isRestore) {
            // Guardamos una copia de la cuenta en el histórico
            if (!$this->addHistory($this->accountId, $this->accountUserEditId, false)) {
                $message['text'][] = _('Error al actualizar el historial');
                SP_Log::wrLogInfo($message);
                return false;
            }
        }

        $query = "UPDATE accounts SET "
            . "account_pass = '" . DB::escape($this->accountPass) . "',"
            . "account_IV = '" . DB::escape($this->accountIV) . "',"
            . "account_userEditId = " . (int)$this->accountUserEditId . ","
            . "account_dateEdit = NOW() "
            . "WHERE account_id = " . (int)$this->accountId;

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        // No escribir en el log ni enviar correos si la actualización es
        // por cambio de clave maestra o restauración
        if (!$isMassive && !$isRestore) {
            $accountInfo = array('customer_name', 'account_name');
            $this->getAccountInfoById($accountInfo);

            $message['action'] = _('Modificar Clave');
            $message['text'][] = SP_Html::strongText(_('Cliente') . ": ") . $this->cacheParams['customer_name'];
            $message['text'][] = SP_Html::strongText(_('Cuenta') . ": ") . $this->cacheParams['account_name'] . " ($this->accountId)";

            SP_Log::wrLogInfo($message);
            SP_Common::sendEmail($message);
        }

        return true;
    }

    /**
     * @brief Actualiza las claves de todas las cuentas en el histórico con la nueva clave maestra
     * @param string $currentMasterPass con la clave maestra actual
     * @param string $newMasterPass con la nueva clave maestra
     * @param string $newHash con el nuevo hash de la clave maestra
     * @return bool
     */
    public function updateAllAccountsHistoryMPass($currentMasterPass, $newMasterPass, $newHash)
    {
        $idOk = array();
        $errorCount = 0;
        $demoEnabled = SP_Util::demoIsEnabled();

        $message['action'] = _('Actualizar Clave Maestra (H)');
        $message['text'][] = _('Inicio');

        SP_Log::wrLogInfo($message);

        // Limpiar 'text' para los próximos mensajes
        $message['text'] = array();

        $crypt = new SP_Crypt();

        if (!SP_Crypt::checkCryptModule()) {
            $message['text'][] = _('Error en el módulo de encriptación');
            SP_Log::wrLogInfo($message);
            return false;
        }

        $accountsPass = $this->getAccountsHistoryPassData();

        if (!$accountsPass) {
            $message['text'][] = _('Error al obtener las claves de las cuentas');
            SP_Log::wrLogInfo($message);
            return false;
        }

        foreach ($accountsPass as $account) {
            // No realizar cambios si está en modo demo
            if ($demoEnabled) {
                $idOk[] = $account->acchistory_id;
                continue;
            }

            if (!$this->checkAccountMPass($account->acchistory_id)) {
                $errorCount++;
                $message['text'][] = _('La clave maestra del registro no coincide') . " (" . $account->acchistory_id . ")";
                continue;
            }

            $decryptedPass = $crypt->decrypt($account->acchistory_pass, $currentMasterPass, $account->acchistory_IV);

            $this->accountPass = $crypt->mkEncrypt($decryptedPass, $newMasterPass);
            $this->accountIV = $crypt->strInitialVector;

            if ($this->accountPass === false) {
                $errorCount++;
                continue;
            }

            if (!$this->updateAccountHistoryPass($account->acchistory_id, $newHash)) {
                $errorCount++;
                $message['text'][] = _('Fallo al actualizar la clave del histórico') . " (" . $account->acchistory_id . ")";
            }

            $idOk[] = $account->acchistory_id;
        }

        // Vaciar el array de mensaje de log
        if (count($message['text']) > 0) {
            SP_Log::wrLogInfo($message);
            $message['text'] = array();
        }

        if ($idOk) {
            $message['text'][] = _('Registros actualizados:') . ": " . implode(',', $idOk);
            SP_Log::wrLogInfo($message);
            $message['text'] = array();
        }

        $message['text'][] = _('Fin');
        SP_Log::wrLogInfo($message);

        if ($errorCount > 0) {
            return false;
        }

        return true;
    }

    /**
     * @brief Obtener los datos relativo a la clave de todas las cuentas del histórico
     * @return array con los datos de la clave
     */
    private function getAccountsHistoryPassData()
    {
        $query = "SELECT acchistory_id,"
            . "acchistory_pass,"
            . "acchistory_IV "
            . "FROM accHistory";
        $queryRes = DB::getResults($query, __FUNCTION__, true);

        if ($queryRes === false) {
            return false;
        }

        return $queryRes;
    }

    /**
     * @brief Comprueba el hash de la clave maestra del registro de histórico de una cuenta
     * @param int $id opcional, con el Id del registro a comprobar
     * @return bool
     */
    public function checkAccountMPass($id = NULL)
    {
        if (is_null($id)) {
            $id = $this->accountId;
        }

        $query = "SELECT acchistory_mPassHash "
            . "FROM accHistory "
            . "WHERE acchistory_id = " . (int)$id;
        $queryRes = DB::getResults($query, __FUNCTION__);

        if ($queryRes === false) {
            return false;
        }

        if ($queryRes->acchistory_mPassHash != SP_Config::getConfigValue('masterPwd')) {
            return false;
        }

        return true;
    }

    /**
     * @brief Actualiza la clave del histórico de una cuenta en la BBDD
     * @param int $id con el id del registro a actualizar
     * @param string $newHash con el hash de la clave maestra
     * @return bool
     */
    public function updateAccountHistoryPass($id, $newHash)
    {
        $query = "UPDATE accHistory SET "
            . "acchistory_pass = '" . DB::escape($this->accountPass) . "',"
            . "acchistory_IV = '" . DB::escape($this->accountIV) . "',"
            . "acchistory_mPassHash = '" . DB::escape($newHash) . "' "
            . "WHERE acchistory_id = " . (int)$id;

        if (DB::doQuery($query, __FUNCTION__) === false) {
            return false;
        }

        return true;
    }

    /**
     * @brief Calcular el hash de los datos de una cuenta
     * @return string con el hash
     *
     * Esta función se utiliza para verificar si los datos de un formulario han sido cambiados
     * con respecto a los guardados
     */
    public function calcChangesHash()
    {
        $groups = 0;
        $users = 0;

        if (is_array($this->accountUserGroupsId)) {
            $groups = implode($this->accountUserGroupsId);
        } elseif (is_array($this->accountCacheUserGroupsId)) {
            foreach ($this->accountCacheUserGroupsId as $group) {
                if (is_array($group)) {
                    // Ordenar el array para que el hash sea igual
                    sort($group, SORT_NUMERIC);
                    $groups = implode($group);
                }
            }
        }

        if (is_array($this->accountUsersId)) {
            $users = implode($this->accountUsersId);
        } elseif (is_array($this->accountCacheUsersId)) {
            foreach ($this->accountCacheUsersId as $user) {
                if (is_array($user)) {
                    // Ordenar el array para que el hash sea igual
                    sort($user, SORT_NUMERIC);
                    $users = implode($user);
                }
            }
        }

        if ( ! empty($this->accountModHash) ){
            $hashItems = $this->accountModHash.(int)$users.(int)$groups;
            //error_log("HASH MySQL: ".$hashItems);
        } else{
            $hashItems = $this->accountName.
                $this->accountCategoryId.
                $this->accountCustomerId.
                $this->accountLogin.
                $this->accountUrl.
                $this->accountNotes.
                $this->accountOtherUserEdit.
                $this->accountOtherGroupEdit.
                (int)$users.
                (int)$groups;
            //error_log("HASH PHP: ".$hashItems);
        }

        return md5($hashItems);
    }

    /**
     * @brief Devolver datos de la cuenta para comprobación de accesos
     * @param int $accountId con el id de la cuenta
     * @return array con los datos de la cuenta
     */
    public function getAccountDataForACL($accountId = null)
    {
        $accId = (!is_null($accountId)) ? $accountId : $this->accountId;

        return array(
            'id' => $accId,
            'user_id' => $this->accountUserId,
            'group_id' => $this->accountUserGroupId,
            'users_id' => $this->getUsersAccount(),
            'groups_id' => $this->getGroupsAccount(),
            'otheruser_edit' => $this->accountOtherUserEdit,
            'othergroup_edit' => $this->accountOtherGroupEdit
        );
    }

    /**
     * @brief Obtiene el listado usuarios con acceso a una cuenta
     * @return array con los registros con id de cuenta como clave e id de usuario como valor
     */
    public function getUsersAccount()
    {
        $accId = ($this->accountIsHistory && $this->accountParentId) ? $this->accountParentId : $this->accountId;

        if (!is_array($this->accountCacheUsersId)) {
            //error_log('Users cache MISS');
            $this->accountCacheUsersId = array($accId => array());
        } else {
            if (array_key_exists($accId, $this->accountCacheUsersId)) {
                //error_log('Users cache HIT');
                return $this->accountCacheUsersId[$accId];
            }
        }

        //error_log('Users cache MISS '.$accId);

        $users = SP_Users::getUsersForAccount($accId);

        if (!is_array($users)) {
            return array();
        }

        foreach ($users as $user) {
            $this->accountCacheUsersId[$accId][] = $user->accuser_userId;
        }

        return $this->accountCacheUsersId[$accId];
    }

    /**
     * @brief Obtiene el listado de grupos secundarios de una cuenta
     * @return array con los registros con id de cuenta como clave e id de grupo como valor
     */
    public function getGroupsAccount()
    {
        $accId = ($this->accountIsHistory && $this->accountParentId) ? $this->accountParentId : $this->accountId;

        if (!is_array($this->accountCacheUserGroupsId)) {
            //error_log('Groups cache NO_INIT');
            $this->accountCacheUserGroupsId = array($accId => array());
        } else {
            if (array_key_exists($accId, $this->accountCacheUserGroupsId)) {
                //error_log('Groups cache HIT');
                return $this->accountCacheUserGroupsId[$accId];
            }
        }

        //error_log('Groups cache MISS');

        $groups = SP_Groups::getGroupsForAccount($accId);

        if (!is_array($groups)) {
            return array();
        }

        foreach ($groups as $group) {
            $this->accountCacheUserGroupsId[$accId][] = $group->accgroup_groupId;
        }

        return $this->accountCacheUserGroupsId[$accId];
    }
}