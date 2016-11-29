<?php
/**
 * sysPass
 *
 * @author nuxsmin
 * @link http://syspass.org
 * @copyright 2012-2016, Rubén Domínguez nuxsmin@$syspass.org
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
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SP\Core\Plugin;

use SP\Core\Exceptions\InvalidClassException;
use SP\Core\Exceptions\SPException;
use SplObserver;
use SplSubject;

/**
 * Class PluginAwareBase
 *
 * @package SP\Core\Plugin
 */
abstract class PluginAwareBase implements SplSubject
{
    /**
     * @var SplObserver[]
     */
    protected $observers = [];

    /**
     * Attach an SplObserver
     * @link http://php.net/manual/en/splsubject.attach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to attach.
     * </p>
     * @throws InvalidClassException
     * @since 5.1.0
     */
    public function attach(SplObserver $observer)
    {
        $observerClass = get_class($observer);

        if (array_key_exists($observerClass, $this->observers)){
            throw new InvalidClassException(SPException::SP_ERROR, _('Plugin ya inicializado'));
        }

        $this->observers[$observerClass] = $observer;
    }

    /**
     * Detach an observer
     * @link http://php.net/manual/en/splsubject.detach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to detach.
     * </p>
     * @throws InvalidClassException
     * @since 5.1.0
     */
    public function detach(SplObserver $observer)
    {
        $observerClass = get_class($observer);

        if (!array_key_exists($observerClass, $this->observers)){
            throw new InvalidClassException(SPException::SP_ERROR, _('Plugin no inicializado'));
        }

        unset($this->observers[$observerClass]);
    }

    /**
     * Notify an observer
     * @link http://php.net/manual/en/splsubject.notify.php
     * @return void
     * @since 5.1.0
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}