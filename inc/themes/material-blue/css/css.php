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

use SP\Themes;

$cssFilesTheme = array(
    array('href' => Themes::$themePath . '/css/fonts.css', 'min' => true),
    array('href' => Themes::$themePath . '/css/material.min.css', 'min' => false),
    array('href' => Themes::$themePath . '/css/material-custom.css', 'min' => true),
    array('href' => Themes::$themePath . '/css/jquery-ui.theme.min.css', 'min' => false),
    array('href' => Themes::$themePath . '/css/styles.css', 'min' => true),
    array('href' => Themes::$themePath . '/css/search-grid.css', 'min' => true),
    array('href' => Themes::$themePath . '/css/alertify.custom.css', 'min' => true)
);