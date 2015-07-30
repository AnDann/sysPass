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

namespace Controller;


interface ActionsI {
    const ACTION__ACC_SEARCH = 1;
    const ACTION_ACC_VIEW = 2;
    const ACTION_ACC_VIEW_PASS = 3;
    const ACTION_ACC_VIEW_HISTORY = 4;
    const ACTION_ACC_EDIT = 10;
    const ACTION_ACC_EDIT_PASS = 11;
    const ACTION_ACC_NEW = 20;
    const ACTION_ACC_COPY = 30;
    const ACTION_ACC_DELETE = 40;
    const ACTION_ACC_FILES = 50;
    const ACTION_ACC_REQUEST = 51;
    const ACTION_MGM = 60;
    const ACTION_MGM_CATEGORIES = 61;
    const ACTION_MGM_CUSTOMERS = 62;
    const ACTION_USR = 70;
    const ACTION_USR_USERS = 71;
    const ACTION_USR_USERS_NEW = 711;
    const ACTION_USR_USERS_EDIT = 712;
    const ACTION_USR_USERS_EDITPASS = 713;
    const ACTION_USR_GROUPS = 72;
    const ACTION_USR_GROUPS_NEW = 721;
    const ACTION_USR_GROUPS_EDIT = 722;
    const ACTION_USR_PROFILES = 73;
    const ACTION_USR_PROFILES_NEW = 731;
    const ACTION_USR_PROFILES_EDIT = 732;
    const ACTION_CFG = 80;
    const ACTION_CFG_GENERAL = 81;
    const ACTION_CFG_ENCRYPTION = 82;
    const ACTION_CFG_ENCRYPTION_TEMPPASS = 83;
    const ACTION_CFG_BACKUP = 84;
    const ACTION_CFG_IMPORT = 85;
    const ACTION_EVL = 90;
}