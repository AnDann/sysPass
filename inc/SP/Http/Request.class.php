<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://${PROJECT_LINK}
 * @copyright 2012-2015 Rubén Domínguez nuxsmin@${PROJECT_LINK}
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

namespace SP\Http;

use SP\Core\CryptPKI;
use SP\Html\Html;
use SP\Core\Init;

/**
 * Clase Request para la gestión de peticiones HTTP
 *
 * @package SP
 */
class Request
{
    /**
     * Obtener los valores de variables $_GET y $_POST
     * y devolverlos limpios con el tipo correcto o esperado.
     *
     * @param string $param     con el parámetro a consultar
     * @param mixed  $default   valor por defecto a devolver
     * @param bool   $check     comprobar si el parámetro está presente
     * @param mixed  $force     valor devuelto si el parámeto está definido
     * @param bool   $sanitize  escapar/eliminar carácteres especiales
     * @return mixed si está presente el parámeto en la petición devuelve bool. Si lo está, devuelve el valor.
     */
    public static function analyze($param, $default = '', $check = false, $force = false, $sanitize = true)
    {
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET':
                if (!isset($_GET[$param])) {
                    return ($force) ? !$force : $default;
                }
                $value = &$_GET[$param];
                break;
            case 'POST':
                if (!isset($_POST[$param])) {
                    return ($force) ? !$force : $default;
                }
                $value = &$_POST[$param];
                break;
        }

        if ($check) {
            return true;
        } elseif ($force) {
            return $force;
        }

        return self::parse($value, $default, $sanitize);
    }

    /**
     * Devolver el valor con el tipo correcto o requerido.
     *
     * @param $value     mixed  valor a analizar
     * @param $default   mixed  tipo por defecto a devolver
     * @param $sanitize  bool   limpiar una cadena de caracteres
     * @return mixed
     */
    private static function parse($value, $default, $sanitize)
    {
        if (is_array($value)){
            foreach($value as &$data){
                self::parse($data, $default, $sanitize);
            }

            return $value;
        }

        if ((is_numeric($value) && !is_string($default)) || is_numeric($default)) {
            return intval($value);
        }

        if (is_string($value)) {
            return ($sanitize === true) ? Html::sanitize($value) : $value;
        }
    }

    /**
     * Comprobar el método utilizado para enviar un formulario.
     *
     * @param string $method con el método utilizado.
     */
    public static function checkReferer($method)
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)
            || !isset($_SERVER['HTTP_REFERER'])
            || !preg_match('#' . Init::$WEBROOT . '/.*$#', $_SERVER['HTTP_REFERER'])
        ) {
            Init::initError(_('No es posible acceder directamente a este archivo'));
            exit();
        }
    }

    /**
     * Analizar un valor encriptado y devolverlo desencriptado
     *
     * @param $param
     * @return string
     */
    public static function analyzeEncrypted($param)
    {
        $encryptedData = self::analyze($param, '', false, false, false);

        if ($encryptedData === ''){
            return '';
        }

        try {
            // Desencriptar con la clave RSA
            $CryptPKI = new CryptPKI();
            $clearData = $CryptPKI->decryptRSA(base64_decode($encryptedData));
        } catch (\Exception $e) {
            return '';
        }

        return $clearData;
    }

    /**
     * Devolver las cabeceras enviadas desde el cliente.
     *
     * @param string $header nombre de la cabecera a devolver
     * @return array
     */
    public static function getRequestHeaders($header = '')
    {
        if (!function_exists('\apache_request_headers')) {
            function apache_request_headers()
            {
                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) == "HTTP_") {
                        $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                        $headers[$key] = $value;
                    } else {
                        $headers[$key] = $value;
                    }
                }
            }
        } else {
            $headers = apache_request_headers();
        }

        if (!empty($header) && array_key_exists($header, $headers)) {
            return $headers[$header];
        } elseif (!empty($header)) {
            return false;
        }

        return $headers;
    }

    /**
     * Comprobar si se realiza una recarga de la página
     * 
     * @return bool
     */
    public static function checkReload()
    {
        return (self::getRequestHeaders('Cache-Control') == 'max-age=0');
    }
}