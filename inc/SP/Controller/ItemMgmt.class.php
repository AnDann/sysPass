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

namespace SP\Controller;

use SP\Core\ActionsInterface;
use SP\Core\Template;
use SP\Http\Request;
use SP\Mgmt\Category;
use SP\Mgmt\Customer;
use SP\Mgmt\CustomFieldDef;
use SP\Mgmt\CustomFields;
use SP\Core\SessionUtil;
use SP\Mgmt\Files;
use SP\Util\Checks;
use SP\Util\Util;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Clase encargada de preparar la presentación de las vistas de gestión de cuentas
 *
 * @package Controller
 */
class ItemMgmt extends Controller implements ActionsInterface
{
    /**
     * @var int
     */
    private $_module = 0;

    /**
     * Constructor
     *
     * @param $template Template con instancia de plantilla
     */
    public function __construct(Template $template = null)
    {
        parent::__construct($template);

        $this->view->assign('isDemo', Checks::demoIsEnabled());
        $this->view->assign('sk', SessionUtil::getSessionKey());
    }

    /**
     * Obtener los datos para la ficha de cliente
     */
    public function getCustomer()
    {
        $this->_module = self::ACTION_MGM_CUSTOMERS;
        $this->view->addTemplate('customers');

        $this->view->assign('customer', Customer::getCustomerData($this->view->itemId));
        $this->getCustomFieldsForItem();
    }

    /**
     * Obtener la lista de campos personalizados y sus valores
     */
    private function getCustomFieldsForItem()
    {
        // Se comprueba que hayan campos con valores para el elemento actual
        if (!$this->view->isView && CustomFields::checkCustomFieldExists($this->_module, $this->view->itemId)) {
            $this->view->assign('customFields', CustomFields::getCustomFieldsData($this->_module, $this->view->itemId));
        } else {
            $this->view->assign('customFields', CustomFields::getCustomFieldsForModule($this->_module));
        }
    }

    /**
     * Obtener los datos para la ficha de categoría
     */
    public function getCategory()
    {
        $this->_module = self::ACTION_MGM_CATEGORIES;
        $this->view->addTemplate('categories');

        $this->view->assign('category', Category::getCategoryData($this->view->itemId));
        $this->getCustomFieldsForItem();
    }

    /**
     * Obtener los datos para la vista de archivos de una cuenta
     */
    public function getAccountFiles()
    {
        $this->setAction(self::ACTION_ACC_FILES);

        $this->view->assign('accountId', Request::analyze('id', 0));
        $this->view->assign('deleteEnabled', Request::analyze('del', 0));
        $this->view->assign('files', Files::getAccountFileList($this->view->accountId));

        if (!is_array($this->view->files) || count($this->view->files) === 0) {
            return;
        }

        $this->view->addTemplate('files');

        $this->view->assign('sk', SessionUtil::getSessionKey());
    }

    /**
     * Obtener los datos para la ficha de campo personalizado
     */
    public function getCustomField()
    {
        $this->view->addTemplate('customfields');

        $customField = CustomFieldDef::getCustomFields($this->view->itemId, true);
        $field = (is_object($customField)) ? unserialize($customField->customfielddef_field) : null;

        if (is_object($field) && get_class($field) === '__PHP_Incomplete_Class') {
            $field = Util::castToClass('SP\Mgmt\CustomFieldDef', $field);
        }

        $this->view->assign('gotData', ($customField && $field instanceof CustomFieldDef));
        $this->view->assign('customField', $customField);
        $this->view->assign('field', $field);
        $this->view->assign('types', CustomFieldDef::getFieldsTypes());
        $this->view->assign('modules', CustomFieldDef::getFieldsModules());
    }
}
