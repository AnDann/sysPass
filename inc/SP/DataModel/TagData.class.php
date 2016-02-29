<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2016 Rubén Domínguez nuxsmin@$syspass.org
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

namespace SP\DataModel;

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Class TagData
 *
 * @package SP\Mgmt\Tags
 */
class TagData
{
    /**
     * @var int
     */
    public $tag_id = 0;
    /**
     * @var string
     */
    public $tag_name = '';
    /**
     * @var string
     */
    public $tag_hash = '';

    /**
     * TagData constructor.
     *
     * @param int    $tag_id
     * @param string $tag_name
     */
    public function __construct($tag_id = 0, $tag_name = '')
    {
        $this->tag_id = $tag_id;
        $this->tag_name = $tag_name;
    }

    /**
     * @return int
     */
    public function getTagId()
    {
        return $this->tag_id;
    }

    /**
     * @param int $tag_id
     */
    public function setTagId($tag_id)
    {
        $this->tag_id = $tag_id;
    }

    /**
     * @return string
     */
    public function getTagName()
    {
        return $this->tag_name;
    }

    /**
     * @param string $tag_name
     */
    public function setTagName($tag_name)
    {
        $this->tag_name = $tag_name;
    }

    /**
     * @return string
     */
    public function getTagHash()
    {
        return $this->tag_hash;
    }

    /**
     * @param string $tag_hash
     */
    public function setTagHash($tag_hash)
    {
        $this->tag_hash = $tag_hash;
    }
}