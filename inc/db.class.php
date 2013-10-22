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
 * Esta clase es la encargada de realizar las operaciones con la BBDD de sysPass.
 */
class DB {
    private static $_db;
    
    static $last_result;
    static $affected_rows;
    static $lastId;
    static $txtError;
    static $numError;
    
    function __construct(){ }
    
    /**
     * @brief Realizar la conexión con la BBDD
     * @return bool
     * 
     * Esta función utiliza mysqli para conectar con la base de datos.
     * Guarda el objeto creado en la variable $_db de la clase
     */ 
    private static function connection(){
        if ( self::$_db ){
            return true;
        }
        
        $dbhost = SP_Config::getValue("dbhost");
        $dbuser = SP_Config::getValue("dbuser");
        $dbpass = SP_Config::getValue("dbpass");
        $dbname = SP_Config::getValue("dbname");
        
        self::$_db = @new mysqli($dbhost,$dbuser,$dbpass,$dbname);
        
        if ( self::$_db->connect_errno ){
            if ( SP_Config::getValue("installed") ){
                if (  self::$_db->connect_errno === 1049 ){
                    SP_Config::setValue('installed', '0');
                }
                
                SP_Init::initError(_('No es posible conectar con la BD'),'Error '.self::$_db->connect_errno . ': '.self::$_db->connect_error);
            } else{
                return false;
            }
        }
        return true;        
    }

    /**
     * @brief Escapar una cadena de texto
     * @param string $str con la cadena a escapar
     * @return string con la cadena escapada
     * 
     * Esta función utiliza mysqli para escapar cadenas de texto.
     */ 
    public static function escape($str) {
        if ( self::connection() ){
            return self::$_db->real_escape_string(trim($str));
        } else {
            return $str;
        }
    }

    /**
     * @brief Realizar una consulta a la BBDD
     * @param string $query con la consulta a realizar
     * @param string $querySource con el nombre de la función que realiza la consulta
     * @return bool|int devuleve bool si hay un error. Devuelve int con el número de registros
     */ 
    public static function doQuery($query,$querySource) {
        if ( ! self::connection() ){
            return false;
        }
        
        $isSelect = preg_match("/^.*(select|show)\s/i",$query);

        // Limpiar valores de caché
        self::$last_result = array();
        
        $queryRes = self::$_db->query($query);

        if ( ! $queryRes ) {
            self::$txtError = self::$_db->error;
            
            $message['action'] = $querySource;
            $message['text'][] = self::$_db->error.'('.self::$_db->errno.')';
            $message['text'][] = "SQL: ".self::escape($query);
                
            SP_Common::wrLogInfo($message);
            return FALSE;
        }

        if ( $isSelect ) {
            $num_rows = 0;
            
            while ( $row = @$queryRes->fetch_object() ) {
                self::$last_result[$num_rows] = $row;
                $num_rows++;
            }
            
            $queryRes->close();
        }

        self::$lastId = self::$_db->insert_id;
        $numRows = self::$_db->affected_rows;
        
        return $numRows;
    }
    
    /**
     * @brief Obtener los resultados de una consulta
     * @param string $query con la consulta a realizar
     * @param string $querySource con el nombre de la función que realiza la consulta
     * @return bool|array devuleve bool si hay un error. Devuelve array con el array de registros devueltos
     */ 
    public static function getResults($query, $querySource) {
        if ( $query ) self::doQuery($query,$querySource);
        
        if ( self::$numError ) {
            return FALSE;
        }
        
        if ( is_null(self::$numError) && count(self::$last_result) === 0 ){
            return TRUE;
        }
            
        return self::$last_result;
    }

    /**
     * @brief Comprobar que la base de datos existe
     * @return bool
     */ 
    public static function checkDatabaseExist(){
        if ( ! self::connection() ){
            return false;
        }
        //fill the database if needed
        $query='SELECT COUNT(*) '
                . 'FROM information_schema.tables'
                ." WHERE table_schema='".SP_Config::getValue("dbname")."' "
                . "AND table_name = 'usrData';";
        
        $resquery = self::$_db->query($query);
        
        if( $resquery ) {
            $row = $resquery->fetch_row();
        }
        
        if( ! $resquery || $row[0] == 0) {
            return false;
        }
        
        return true;
    }    
}