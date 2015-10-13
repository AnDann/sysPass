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

namespace SP\Html\DataGrid;

/**
 * Class DataGridTab para crear una matriz de datos a usar en pestañas
 *
 * @package SP\Html\DataGrid
 */
class DataGridTab extends DataGridBase
{
    /**
     * Título de la pestaña
     *
     * @var string
     */
    private $_title = '';
    /**
     * Tiempo de ejecución
     *
     * @var int
     */
    private $_time = 0;

    /**
     * @param $title string
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * @param int $time
     */
    public function setTime($time)
    {
        $this->_time = $time;
    }
}
    
