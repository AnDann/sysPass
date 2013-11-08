<?php
/** 
* sysPass
* 
* @author nuxsmin
* @link http://syspass.org
* @copyright 2012 Rubén Domínguez nuxsmin@syspass.org
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

Header("content-type: application/x-javascript");

define('APP_ROOT', '..');

if ( isset($_GET["l"]) && isset($_GET["r"]) ){
    $appLang = strtolower($_GET["l"]);
    $appRoot = base64_decode($_GET["r"]);
}else{
    return;
}

$locale= array(
    "es_es" => array('Ha ocurrido un error en la consulta',
                    'Ha ocurrido un error',
                    'Sesión finalizada',
                    'Archivo no seleccionado',
                    'Archivo no indicado',
                    'Gestión de Usuarios',
                    'Gestión de Grupos',
                    'Comprobando',
                    'Borrar la cuenta?',
                    'Borrar el usuario?',
                    'Valor no introducido',
                    'Valor duplicado',
                    'Opción no seleccionada',
                    'Opción añadida<br>Guarde la configuración para que sea efectiva',
                    'Opción eliminada<br>Guarde la configuración para que sea efectiva',
                    'Clave Generada',
                    'Nivel alto',
                    'Nivel medio',
                    'Nivel bajo',
                    'Nivel muy alto',
                    'Utilizar al menos 8 caracteres',
                    'Borrar elemento?',
                    'Página no encontrada, verifique el parámetro "siteroot"',
                    'Archivo no soportado para visualizar',
                    'Eliminar archivo?',
                    'Su navegador no soporta subir archivos con HTML5',
                    'Demasiados archivos',
                    'No es posible guardar el archivo.<br>Tamaño máximo:',
                    'Extensión no permitida',
                    'Vaciar el registro de eventos?'),
    "en_us" => array('Query error',
                    'There was an error',
                    'Session ended',
                    'File not selected',
                    'File not entered',
                    'Users Management',
                    'Groups Management',
                    'Checking',
                    'Delete account?',
                    'Delete user?',
                    'Value not entered',
                    'Duplicated value',
                    'Option not selected',
                    'Option added<br>You should save configuration in order to take effect',
                    'Option deleted<br>You should save configuration in order to take effect',
                    'Generated Password',
                    'High level',
                    'Average level',
                    'Low level',
                    'Very high level',
                    'You should use at least 8 characters',
                    'Delete item?',
                    'Page not found, please verify "siteroot" parameter',
                    'File not supported for preview',
                    'Delete file?',
                    'Your browser does not support HTML5 file uploads.',
                    'Too many files',
                    'Unable to save file.<br>Max file size:',
                    'Extension not allowed',
                    'Clear event log?'));

$arrJsLang = array();

foreach ( $locale[$appLang] as $langIndex => $langDesc ){
    $arrJsLang[] = "'".$langDesc."'";
}

echo "// i18n language array from PHP\n";
echo "var LANG = [".implode(",",$arrJsLang)."]; \n\n";
echo "var APP_ROOT = '$appRoot';\n";

include_once 'functions.js';
?>