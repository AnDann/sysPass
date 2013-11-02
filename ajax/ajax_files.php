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
// TODO: comprobar permisos para eliminar archivos

define('APP_ROOT', '..');
include_once (APP_ROOT . "/inc/init.php");

SP_Util::checkReferer('POST');

if (!SP_Init::isLoggedIn()) {
    SP_Util::logout();
}

$sk = SP_Common::parseParams('p', 'sk', FALSE);

if (!$sk || !SP_Common::checkSessionKey($sk)) {
    die(_('CONSULTA INVÁLIDA'));
}

if (SP_Config::getValue('filesenabled', 0) == 0) {
    exit(_('Gestión de archivos deshabilitada'));
}

$action = SP_Common::parseParams('p', 'action');
$accountId = SP_Common::parseParams('p', 'accountId', 0);
$fileId = SP_Common::parseParams('p', 'fileId', 0);

if ($action == 'upload') {
    if (!is_array($_FILES["inFile"]) || !$accountId === 0) {
        exit();
    }

    $allowedExts = strtoupper(SP_Config::getValue('allowed_exts'));
    $allowedSize = SP_Config::getValue('allowed_size');

    if ($allowedExts) {
        // Extensiones aceptadas
        $extsOk = explode(",", $allowedExts);
    } else {
        exit(_('No hay extensiones permitidas'));
    }

    if (is_array($_FILES) && $_FILES['inFile']['name']) {
        // Comprobamos la extensión del archivo
        $fileData['extension'] = strtoupper(pathinfo($_FILES['inFile']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileData['extension'], $extsOk)) {
            exit(_('Tipo de archivo no soportado') . " '" . $fileData['extension'] . "' ");
        }
    } else {
        exit(_('Archivo inválido') . ":<br>" . $_FILES['inFile']['name']);
    }

    // Variables con información del archivo
    $fileData['name'] = SP_Html::sanitize($_FILES['inFile']['name']);
    $tmpName = SP_Html::sanitize($_FILES['inFile']['tmp_name']);
    $fileData['size'] = $_FILES['inFile']['size'];
    $fileData['type'] = $_FILES['inFile']['type'];

    if (!file_exists($tmpName)) {
        // Registramos el máximo tamaño permitido por PHP
        SP_Files::getMaxUpload();

        exit(_('Error interno al leer el archivo'));
    }

    if ($fileData['size'] > ($allowedSize * 1000)) {
        exit(_('El archivo es mayor de ') . " " . round(($allowedSize / 1000), 1) . "MB");
    }

    // Leemos el archivo a una variable
    $fileHandle = fopen($tmpName, 'r');

    if (!$fileHandle) {
        $message['action'] = _('Subir Archivo');
        $message['text'][] = _('Error interno al leer el archivo');

        SP_Common::wrLogInfo($message);

        exit(_('Error interno al leer el archivo'));
    }

    $fileData['content'] = addslashes(fread($fileHandle, filesize($tmpName)));
    fclose($fileHandle);

    if (SP_Files::fileUpload($accountId, $fileData)) {
        exit(_('Archivo guardado'));
    } else {
        exit(_('No se pudo guardar el archivo'));
    }
}

if ($action == 'download' || $action == 'view') {
    // Verificamos que el ID sea numérico
    if (!is_numeric($fileId) || $fileId === 0) {
        exit(_('No es un ID de archivo válido'));
    }

    $isView = ( $action == 'view' ) ? TRUE : FALSE;

    $file = SP_Files::fileDownload($fileId);

    if (!$file) {
        exit(_('El archivo no existe'));
    }

    $fileSize = $file->accfile_size;
    $fileType = $file->accfile_type;
    $fileName = $file->accfile_name;
    $fileExt = $file->accfile_extension;
    $fileData = $file->accfile_content;

    $message['action'] = _('Descargar Archivo');
    $message['text'][] = _('ID') . ": " . $fileId;
    $message['text'][] = _('Archivo') . ": " . $fileName;
    $message['text'][] = _('Tipo') . ": " . $fileType;
    $message['text'][] = _('Tamaño') . ": " . round($fileSize / 1024, 2) . " KB";

    if (!$isView) {
        SP_Common::wrLogInfo($message);
        
        // Enviamos el archivo al navegador
        header("Content-length: $fileSize");
        header("Content-type: $fileType");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Description: PHP Generated Data");
        header("Content-transfer-encoding: binary");

        exit($fileData);
    } else {
        $extsOkImg = array("JPG", "GIF", "PNG");
        if (in_array(strtoupper($fileExt), $extsOkImg)) {
            SP_Common::wrLogInfo($message);
            
            $imgData = chunk_split(base64_encode($fileData));
            exit('<img src="data:' . $fileType . ';base64, ' . $imgData . '" border="0" />');
//            } elseif ( strtoupper($fileExt) == "PDF" ){
//                echo '<object data="data:application/pdf;base64, '.base64_encode($fileData).'" type="application/pdf"></object>';
        } elseif (strtoupper($fileExt) == "TXT") {
            SP_Common::wrLogInfo($message);
            
            exit('<div id="fancyView" class="backGrey"><pre>' . $fileData . '</pre></div>');
        } else {
            exit();
        }
    }
}

if ($action == "delete") {
    // Verificamos que el ID sea numérico
    if (!is_numeric($fileId) || $fileId === 0) {
        exit(_('No es un ID de archivo válido'));
    }

    if (SP_Files::fileDelete($fileId)) {
        exit(_('Archivo eliminado'));
    } else {
        exit(_('Error al eliminar el archivo'));
    }
}