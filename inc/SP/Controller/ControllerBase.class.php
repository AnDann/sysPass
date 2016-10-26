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

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

use SP\Core\Acl;
use SP\Core\Exceptions\FileNotFoundException;
use SP\Core\Init;
use SP\Core\Session;
use SP\Core\Exceptions\SPException;
use SP\Core\DiFactory;
use SP\Core\Template;
use SP\Core\UI\ThemeIconsBase;

/**
 * Clase base para los controladores
 */
abstract class ControllerBase
{
    /**
     * Constantes de errores
     */
    const ERR_UNAVAILABLE = 0;
    const ERR_ACCOUNT_NO_PERMISSION = 1;
    const ERR_PAGE_NO_PERMISSION = 2;
    const ERR_UPDATE_MPASS = 3;
    const ERR_OPERATION_NO_PERMISSION = 4;

    /**
     * Instancia del motor de plantillas a utilizar
     *
     * @var Template
     */
    public $view;
    /**
     * Módulo a usar
     *
     * @var int
     */
    protected $action;
    /**
     * Instancia de los iconos del tema visual
     *
     * @var ThemeIconsBase
     */
    protected $icons;
    /**
     * Nombre del controlador
     *
     * @var string
     */
    protected $controllerName;

    /**
     * Constructor
     *
     * @param $template Template con instancia de plantilla
     */
    public function __construct(Template $template = null)
    {
        global $timeStart;

        $class = get_called_class();
        $this->controllerName = substr($class, strrpos($class, '\\') + 1,  -strlen('Controller'));

        $this->view = null === $template ? $this->getTemplate() : $template;
        $this->view->setBase(strtolower($this->controllerName));

        $this->icons = DiFactory::getTheme()->getIcons();

        $this->view->assign('timeStart', $timeStart);
        $this->view->assign('icons', $this->icons);
    }

    /**
     * Obtener una nueva instancia del motor de plantillas.
     *
     * @param null $template string con el nombre de la plantilla
     * @return Template
     */
    protected function getTemplate($template = null)
    {
        return new Template($template);
    }

    /**
     * @return int El id de la acción
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Establecer el módulo a presentar.
     *
     * @param int $action El id de la acción
     */
    public function setAction($action)
    {
        $this->action = (int)$action;
    }

    /**
     * Renderizar los datos de la plantilla y mostrarlos
     *
     * @throws FileNotFoundException
     */
    public function view()
    {
        echo $this->view->render();
    }

    /**
     * Renderizar los datos de la plantilla y devolverlos
     *
     * @throws FileNotFoundException
     */
    public function render()
    {
        return $this->view->render();
    }

    /**
     * Obtener los datos para la vista de depuración
     */
    public function getDebug()
    {
        global $memInit;

        $this->view->addTemplate('debug', 'common');

        $this->view->assign('time', Init::microtime_float() - $this->view->timeStart);
        $this->view->assign('memInit', $memInit / 1000);
        $this->view->assign('memEnd', memory_get_usage() / 1000);
    }

    /**
     * Establecer la instancia del motor de plantillas a utilizar.
     *
     * @param Template $template
     */
    protected function setTemplate(Template $template)
    {
        $this->view = $template;
    }

    /**
     * Comprobar si está permitido el acceso al módulo/página.
     *
     * @param null $action La acción a comprobar
     * @return bool
     */
    protected function checkAccess($action = null)
    {
        $checkAction = $this->action;

        if (null !== $action) {
            $checkAction = $action;
        }

        return Session::getUserIsAdminApp() || Acl::checkUserAccess($checkAction);
    }

    /**
     * Establecer la plantilla de error con el código indicado.
     *
     * @param int  $type int con el tipo de error
     * @param bool $reset
     * @param bool $fancy
     */
    protected function showError($type, $reset = true, $fancy = false)
    {
        $errorsTypes = array(
            self::ERR_UNAVAILABLE => array('txt' => _('Opción no disponible'), 'hint' => _('Consulte con el administrador')),
            self::ERR_ACCOUNT_NO_PERMISSION => array('txt' => _('No tiene permisos para acceder a esta cuenta'), 'hint' => _('Consulte con el administrador')),
            self::ERR_PAGE_NO_PERMISSION => array('txt' => _('No tiene permisos para acceder a esta página'), 'hint' => _('Consulte con el administrador')),
            self::ERR_OPERATION_NO_PERMISSION => array('txt' => _('No tiene permisos para realizar esta operación'), 'hint' => _('Consulte con el administrador')),
            self::ERR_UPDATE_MPASS => array('txt' => _('Clave maestra actualizada'), 'hint' => _('Reinicie la sesión para cambiarla'))
        );

        if ($reset) {
            $this->view->resetTemplates();
        }

        if ($fancy) {
            $this->view->addTemplate('errorfancy');
        } else {
            $this->view->addTemplate('error');
        }

        $this->view->append('errors',
            array(
                'type' => SPException::SP_WARNING,
                'description' => $errorsTypes[$type]['txt'],
                'hint' => $errorsTypes[$type]['hint'])
        );
    }
}