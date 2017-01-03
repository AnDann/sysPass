<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2016, Rubén Domínguez nuxsmin@$syspass.org
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

namespace Plugins\Authenticator;

use InvalidArgumentException;
use SP\Controller\TabControllerBase;
use SP\Core\Plugin\PluginBase;
use SP\Util\ArrayUtil;

/**
 * Class Controller
 *
 * @package Plugins\Authenticator
 */
class PreferencesController
{
    /**
     * @var TabControllerBase
     */
    protected $Controller;
    /**
     * @var PluginBase
     */
    protected $Plugin;

    /**
     * Controller constructor.
     *
     * @param TabControllerBase $Controller
     * @param PluginBase $Plugin
     */
    public function __construct(TabControllerBase $Controller, PluginBase $Plugin)
    {
        $this->Controller = $Controller;
        $this->Plugin = $Plugin;
    }

    /**
     * Obtener la pestaña de seguridad
     */
    public function getSecurityTab()
    {
        $base = $this->Plugin->getThemeDir() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'userpreferences';

        // Datos del plugin
        $pluginData = $this->Plugin->getData();

        // Datos del usuario de la sesión
        $UserData = $this->Controller->getUserData();

        // Buscar al usuario en los datos del plugin
        /** @var AuthenticatorData $AuthenticatorData */
        $AuthenticatorData = ArrayUtil::searchInObject($pluginData, 'userId', $UserData->getUserId(), new AuthenticatorData());

        $this->Controller->view->addTemplate('preferences-security', $base);

        try {
            $twoFa = new Authenticator($UserData->getUserId(), $UserData->getUserLogin());

            $this->Controller->view->assign('qrCode', !$AuthenticatorData->isTwofaEnabled() ? $twoFa->getUserQRCode() : '');
            $this->Controller->view->assign('userId', $UserData->getUserId());
            $this->Controller->view->assign('chk2FAEnabled', $AuthenticatorData->isTwofaEnabled());

            $this->Controller->view->assign('tabIndex', $this->Controller->addTab(_('Seguridad')), 'security');
            $this->Controller->view->assign('actionId', ActionController::ACTION_TWOFA_SAVE, 'security');
        } catch (InvalidArgumentException $e) {
        }
    }
}