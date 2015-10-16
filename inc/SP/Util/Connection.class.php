<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2015 Rubén Domínguez nuxsmin@$syspass.org
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

namespace SP\Util;

use SP\Core\SPException;

/**
 * Class Connection para crear conexiones TCP o UDP
 *
 * @package SP\Util
 */
class Connection implements ConnectionInterface
{
    /**
     * @var resource
     */
    protected $_socket;

    /**
     * @var string
     */
    protected $_host = '';

    /**
     * @var int
     */
    protected $_port = 0;

    /**
     * @param $host string El host a conectar
     * @param $port string El puerto a conectar
     */
    public function __construct($host, $port)
    {
        $this->_host = gethostbyname($host);
        $this->_port = $port;
    }

    /**
     * Obtener un socket
     *
     * @param $type int EL tipo de socket TCP/UDP
     * @return mixed
     * @throws SPException
     */
    public function getSocket($type)
    {
        switch ($type){
            case self::TYPE_TCP:
                $this->_socket = $this->getTCPSocket();
                break;
            case self::TYPE_UDP:
                $this->_socket = $this->getUDPSocket();
                break;
            default:
                $this->_socket = $this->getTCPSocket();
                break;
        }

        if ($this->_socket === false) {
            throw new SPException(SPException::SP_WARNING, $this->getSocketError());
        }
    }

    /**
     * Cerrar el socket
     */
    public function closeSocket()
    {
        fclose($this->_socket);
//        @socket_close($this->_socket);
    }

    /**
     * Enviar un mensaje al socket
     *
     * @param $message string El mensaje a enviar
     * @return int|bool
     * @throws SPException
     */
    public function send($message)
    {
        if (!is_resource($this->_socket)) {
            throw new SPException(SPException::SP_WARNING, _('Socket no inicializado'));
        }

        $nBytes = @fwrite($this->_socket, $message);
//        $nBytes = @socket_sendto($this->_socket, $message, strlen($message), 0, $this->_host, $this->_port);

        if ($nBytes === false) {
            throw new SPException(SPException::SP_WARNING, _('Error al enviar datos'), $this->getSocketError());
        }

        return $nBytes;
    }

    /**
     * Obtener el último error del socket
     *
     * @return string
     */
    public function getSocketError()
    {
        return socket_strerror(socket_last_error($this->_socket));
    }

    /**
     * Obtener un socket del tipo UDP
     *
     * @return resource
     */
    private function getUDPSocket()
    {
        return stream_socket_client('udp://' . $this->_host . ':' . $this->_port, $errno, $errstr, 30);
//        return @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    /**
     * Obtener un socket del tipo TCP
     *
     * @return resource
     */
    private function getTCPSocket()
    {
        return stream_socket_client('tcp://' . $this->_host . ':' . $this->_port, $errno, $errstr, 30);
//        return @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }
}