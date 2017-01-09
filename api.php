<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2015 Rubén Domínguez nuxsmin@syspass.or
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

use SP\Api\ApiRequest;
use SP\Core\Init;
use SP\Http\Response;

define('APP_ROOT', '.');

require APP_ROOT . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'Base.php';

Init::setLogging();

header('Content-type: application/json');

try {
    $ApiRequest = new ApiRequest();
    exit($ApiRequest->runApi());
} catch (\SP\Core\Exceptions\InvalidArgumentException $e) {
    $code = $e->getCode();

    Response::printJson(
        [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $e->getMessage(),
                'data' => $e->getHint()
                ],
            'id' => ($code === -32700 || $code === -32600) ? null : $ApiRequest->getId()
        ]);
} catch (Exception $e) {
    Response::printJson(
        [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ],
            'id' => null
        ]);
}