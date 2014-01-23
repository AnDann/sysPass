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

defined('APP_ROOT') || die(_('No es posible acceder directamente a este archivo'));

/**
 * Esta clase es encargada de ejecutar acciones comunes para las funciones
 */
class SP_Common {

    /**
     * @brief Crear un nuevo registro en el registro de eventos
     * @param array $message con el nombre de la accióm y el texto del mensaje
     * @return bool
     */ 
    public static function wrLogInfo($message) {
        if (SP_Config::getValue('logenabled', 0) === 0 || !is_array($message)) {
            return FALSE;
        }

        $login = ( isset($_SESSION["ulogin"]) ) ? $_SESSION["ulogin"] : "-";
        $userId = ( isset($_SESSION['uid']) ) ? $_SESSION['uid'] : 0;
        $action = utf8_encode($message['action']);
        $description = utf8_encode(implode(';;', $message['text']));

        $query = "INSERT INTO log SET
                    log_date = UNIX_TIMESTAMP(),
                    log_login = '" . DB::escape($login) . "',
                    log_userId = " . $userId . ",
                    log_action = '" . DB::escape($action) . "',
                    log_description = '" . DB::escape($description) . "'";

        if (DB::doQuery($query, __FUNCTION__) === FALSE) {
            return FALSE;
        }
    }

    /**
     * @brief Enviar un email
     * @param array $message con el nombre de la accióm y el texto del mensaje
     * @param string $mailTo con el destinatario
     * @return bool
     * @todo Autentificación
     * @todo Permitir HTML
     */ 
    public static function sendEmail($message, $mailTo = "") {

        if (SP_Config::getValue('mailenabled', 0) === 0) {
            return FALSE;
        }

        if (!is_array($message)) {
            return FALSE;
        }

        $info = SP_Html::getAppInfo();
        $replyTo = SP_Config::getValue('mailfrom');
        
        $strFrom = SP_Config::getValue('mailfrom');
        $mailSubject = _('Aviso') . ' ' . $info['appname'] . ' - ' . $message['action'];

        // Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
        $headers[] = 'MIME-Version: 1.0';
        // HTML Version
        //$strHead .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers[] = 'Content-Type: text/plain;charset=utf-8';

        // Cabeceras adicionales
        //$strHead .= "To: $strDestinatario \r\n";
        $headers[] = "From: " . $info['appname'] . " <$strFrom>";
        $headers[] = "Reply-To: $replyTo";
        $headers[] = "Cc: $strFrom";

        $mailbody = _('Acción') . ": " . $message['action'] . "\r\n";
        $mailbody .= _('Realizado por') . ": " . $_SESSION["ulogin"] . "\r\n";
        $mailbody .= (is_array($message['text'])) ? implode("\r\n",$message['text']) : '';

        $mailHeader = implode("\r\n", $headers);
        
        $log['action'] = _('Enviar Email');
                
        // Enviar correo
        if ( $sendMail ){
            $log['text'][]= _('Correo enviado');
        } else{
            $log['text'][] = _('Error al enviar correo');
        }

        $log['text'][] = _('Destinatario').": $mailTo"; 
	    $log['text'][] = _('CC').": $strFrom";

        self::wrLogInfo($log);
		return $sendMail;
    }

    /**
     * @brief Devuelve una respuesta en formato XML con el estado y el mensaje
     * @param string $description mensaje a devolver
     * @param int $status devuelve el estado
     * @return string documento XML
     */
    public static function printXML($description, $status = 1) {
        if (!is_string($description)) {
            return FALSE;
        }

        $arrStrFrom = array("&", "<", ">", "\"", "\'");
        $arrStrTo = array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;");

        $cleanDescription = str_replace($arrStrFrom, $arrStrTo, $description);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<root>\n<status>" . $status . "</status>\n <description>" . $cleanDescription . "</description>\n</root>";
        
        header("Content-Type: application/xml");
        exit($xml);
    }

    /**
     * @brief Devuelve una respuesta en formato JSON con el estado y el mensaje
     * @param string $description mensaje a devolver
     * @param int $status devuelve el estado
     * @return string respuesta JSON
     */
    public static function printJSON($description, $status = 1) {
        if (!is_string($description)) {
            return FALSE;
        }

        $arrStrFrom = array("&", "<", ">", "\"", "\'");
        $arrStrTo = array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;");

        $cleanDescription = str_replace($arrStrFrom, $arrStrTo, $description);

        $json = array('status' => $status, 'description' => $cleanDescription);
        
        header('Content-type: application/json');
        exit(json_encode($json));
    }
    
    /**
     * @brief Devuelve un icono de ayuda con el mensaje
     * @param int $type tipo de mensaje
     * @param int $id id del mensaje
     * @return string con la etiqueta html <img>
     */
    public static function printHelpButton($type, $id) {
        $msgHelp[0] = _('Indicar el usuario de conexión a la base de datos de phpPMS');
        $msgHelp[1] = _('Indicar el nombre de la base de datos de phpPMS');
        $msgHelp[2] = _('Indicar el servidor de la base de datos de phpPMS');
        $msgHelp[3] = _('Habilita el nombre de la cuenta de la búsqueda, como enlace a los detalles de la cuenta');
        $msgHelp[4] = _('Número de resultados por página a mostrar, al realizar una búsqueda');
        $msgHelp[5] = _('Habilita la subida/descarga de archivos para las cuentas');
        $msgHelp[6] = _('Establece el tamaño máximo para subir archivos') . "<br><br>" . _('El máximo absuluto es de 16MB');
        $msgHelp[7] = _('Habilita la opción de añadir un enlace a Wiki externa para los resultados de la búsqueda');
        $msgHelp[8] = _('URL que utiliza la wiki para realizar una búsqueda de una página en esta') . "<br><br>" . _('Como parámetro se utiliza el nombre del cliente') . "<br><br>" . _('Ejemplo') . ":<br><br>https://wiki.cygnux.org/search.php?phrase=";
        $msgHelp[9] = _('URL que utiliza la wiki para acceder a los detalles de una página de ésta') . "<br><br>" . _('El nombre de la cuenta se utiliza como parámetro de la variable de búsqueda de la Wiki') . "<br><br>" . _('Ejemplo') . ":<br><br>https://wiki.cygnux.org/show.php?name=";
        $msgHelp[10] = _('Prefijo para determinar qué cuentas tienen un enlace a una página de la Wiki') . "<br><br>" . _('Ejemplos') . ": serv- | srv- | vm-";
        $msgHelp[11] = _('Habilita de autentificación mediante servidor LDAP') . "<br><br>" . _('Este método utilizará MySQL en caso de fallo');
        $msgHelp[12] = _('Usuario para conectar con el servicio de LDAP') . "<br><br>" . _('Ejemplo') . ":<br><br>cn=syspass,ou=Users,dc=cygnux,o=org";
        $msgHelp[13] = _('Base en la que realizar la búsqueda de usuarios de LDAP') . "<br><br>" . _('Ejemplo') . ":<br><br>dc=cygnux,o=org";
        $msgHelp[14] = _('Grupo de LDAP al que debe de pertenecer el usuario para permitir el acceso') . "<br><br>" . _('Ejemplo') . ":<br><br>cn=GRP_SPUSERS,ou=USERS | GRP_SPUSERS";
        $msgHelp[15] = _('Nombre o dirección IP del servidor de LDAP');
        $msgHelp[16] = _('Establece una nueva clave maestra sin re-encriptar las cuentas');
        $msgHelp[17] = _('Clave del usuario de conexión a LDAP');
        $msgHelp[18] = _('En este modo no se puede acceder a la aplicación. Para deshabilitarlo es necesario modificar el archivo de configuración');
        $msgHelp[19] = _('Muestra información relativa a la configuración de la aplicación y rendimiento');
        $msgHelp[20] = _('Guarda las acciones realizadas en la aplicación');
        $msgHelp[21] = _('Comprobar actualizaciones de la aplicación (sólo para los usuarios administradores)');
        $msgHelp[22] = _('Extensiones de máximo 4 caracteres.') . "<br><br>" . _('Escribir extensión y pulsar intro para añadir.');
        $msgHelp[23] = _('Importar desde un archivo CSV con el formato') . ":<br><br>" . _('nombre_de_cuenta;cliente;categoría;url;usuario;clave;notas') . "<br><br>" . _('Si el cliente o la categoría no están creados, se crean automáticamente.');

        if (array_key_exists($id, $msgHelp)) {
            echo '<img src="imgs/help.png" title="' . $msgHelp[$id] . '" class="inputImgMini" />';
        }
    }

    /**
     * @brief Devuelve un hash para verificación de formularios
     * @param bool $new si es necesrio regenerar el hash
     * @return string con el hash de verificación
     * 
     * Esta función genera un hash que permite verificar la autenticidad de un formulario
     */
    public static function getSessionKey($new = FALSE) {
        $hash = sha1(time());

        if (!isset($_SESSION["sk"]) || $new === TRUE) {
            $_SESSION["sk"] = $hash;
            return $hash;
        }

        return $_SESSION["sk"];
    }

    /**
     * @brief Comprobar el hash de verificación de formularios
     * @param string $key con el hash a comprobar
     * @return boo|string si no es correcto el hash devuelve bool. Si lo es, devuelve el hash actual.
     */
    public static function checkSessionKey($key) {
        if (!isset($_SESSION["sk"]) || $_SESSION["sk"] == "" || !$key){
            return FALSE;
        }

        return ( $_SESSION["sk"] == $key );
    }

    /**
     * @brief Obtener los valores de peticiones GET o POST y devolver limpios
     * @param string $method con el método a utilizar
     * @param string $param con el parámetro a consultar
     * @param mixed $default opcional, valor por defecto a devolver
     * @param bool $onlyCHeck opcional, comprobar si el parámetro está presente
     * @param mixed $force opcional, valor devuelto si el parámeto está definido
     * @return boo|string si está presente el parámeto en la petición devuelve bool. Si lo está, devuelve el valor.
     */
    public static function parseParams($method, $param, $default = '', $onlyCHeck = FALSE, $force = FALSE){
        $out = '';
        
        switch ($method){
            case 'g':
                if ( !isset($_GET[$param]) ){
                    return $default;
                }
                $out = $_GET[$param];
                break;
            case 'p':
                if ( !isset($_POST[$param]) ){
                    return $default;
                }
                $out = $_POST[$param];
                break;
            case 's':
                if ( !isset($_SESSION[$param]) ){
                    return $default;
                }
                $out = $_SESSION[$param];
                break;
            default :
                return FALSE;
        }

        if ( $onlyCHeck ){
            return TRUE;
        }
        
        if ($force){
            return $force;
        }
        
        if (is_numeric($out) && is_numeric($default)){
            return (int)$out;
        }

        if (is_string($out)){
            return ( $method != 's' ) ? SP_Html::sanitize($out) : $out;
        }
        
        if (is_array($out)){
            return $out;
        }
    }
}
