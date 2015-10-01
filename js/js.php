<?php
/**
 * sysPass
 *
 * @author nuxsmin
 * @link http://syspass.org
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

use SP\Themes;

define('APP_ROOT', '..');

require APP_ROOT . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'Base.php';

$themeJsPath = VIEW_PATH . DIRECTORY_SEPARATOR . Themes::$theme . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'js.php';

$jsFilesBase = array(
    array('href' => 'js/jquery-1.11.2.min.js', 'min' => false),
//    array('href' => 'js/jquery-migrate-1.2.1.min.js', 'min' => false),
    array('href' => 'js/jquery.placeholder.js', 'min' => true),
    array('href' => 'js/jquery-ui.min.js', 'min' => false),
    array('href' => 'js/jquery.fancybox.pack.js', 'min' => false),
    array('href' => 'js/jquery.powertip.min.js', 'min' => false),
    array('href' => 'js/chosen.jquery.min.js', 'min' => false),
    array('href' => 'js/alertify.js', 'min' => false),
    array('href' => 'js/jquery.fileDownload.js', 'min' => true),
    array('href' => 'js/jquery.filedrop.js', 'min' => true),
    array('href' => 'js/jquery.tagsinput.js', 'min' => true),
    array('href' => 'js/ZeroClipboard.min.js', 'min' => false),
    array('href' => 'js/jsencrypt.min.js', 'min' => false),
    array('href' => 'js/zxcvbn-async.js', 'min' => true),
    array('href' => 'js/functions.js', 'min' => true),
);

if (file_exists($themeJsPath)){
    include $themeJsPath;

    foreach ($jsFilesTheme as $file) {
        array_push($jsFilesBase, $file);
    }
}

SP\Util::getMinified('js', $jsFilesBase, false);