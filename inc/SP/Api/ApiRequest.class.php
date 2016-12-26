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

namespace SP\Api;

use ReflectionClass;
use SP\Http\Request;
use SP\Core\Exceptions\SPException;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class ApiRequest encargada de atender la peticiones a la API de sysPass
 *
 * @package SP
 */
class ApiRequest extends Request
{
    /**
     * Constantes de acciones
     */
    const ACTION = 'action';
    const USER = 'user';
    const USER_PASS = 'userPass';
    const AUTH_TOKEN = 'authToken';
    const ITEM = 'itemId';
    const SEARCH = 'searchText';
    const SEARCH_COUNT = 'searchCount';

    /**
     * @var \stdClass
     */
    private $params;

    /** @var string */
    private $verb;

    /** @var ReflectionClass */
    private $ApiReflection;

    /**
     * ApiRequest constructor.
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    public function __construct()
    {
        try {
            $this->analyzeRequestMethod();
            $this->getData();
            $this->checkBasicData();
            $this->checkAction();
        } catch (SPException $e) {
            throw $e;
        }
    }

    /**
     * Analizar y establecer el método HTTP a utilizar
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    private function analyzeRequestMethod()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Sólo se permiten estos métodos
        switch ($requestMethod) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $this->verb = $requestMethod;
                break;
            default:
                throw new SPException(SPException::SP_WARNING, _('Método inválido'));
        }
    }

    /**
     * Obtener los datos de la petición
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    private function getData()
    {
        $request = file_get_contents('php://input');
        $data = self::parse($request, '', true);

        $this->params = json_decode($data);

        if (!is_object($this->params) || json_last_error() !== JSON_ERROR_NONE) {
            throw new SPException(SPException::SP_WARNING, _('Datos inválidos'));
        }
    }

    /**
     * Comprobar los datos básicos de la petición
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    private function checkBasicData()
    {
        if (!isset($this->params->authToken, $this->params->action)) {
            throw new SPException(SPException::SP_WARNING, _('Parámetros incorrectos'));
        }
    }

    /**
     * Comprobar si la API tiene implementada dicha acción
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    private function checkAction()
    {
        $this->ApiReflection = new ReflectionClass(SyspassApi::class);

        if (!$this->ApiReflection->hasMethod($this->params->action)) {
            throw new SPException(SPException::SP_WARNING, _('Acción inválida'));
        }
    }

    /**
     * Devolver un array con la ayuda de parámetros
     *
     * @return array
     */
    public static function getHelp()
    {
        return [
            self::AUTH_TOKEN => _('Token de autorización'),
            self::ACTION => _('Acción a realizar')
        ];
    }

    /**
     * Añade una nueva variable de petición al array
     *
     * @param $name  string El nombre de la variable
     * @param $value mixed El valor de la variable
     */
    public function addVar($name, $value)
    {
        $this->params->$name = $value;
    }

    /**
     * Obtiene una nueva instancia de la Api
     *
     * @return SyspassApi
     * @throws \SP\Core\Exceptions\SPException
     */
    public function runApi()
    {
        return $this->ApiReflection->getMethod($this->params->action)->invoke(new SyspassApi($this->params));
    }

    /**
     * Obtener el id de la acción
     *
     * @return int
     */
    public function getAction()
    {
        return $this->params->action;
    }
}