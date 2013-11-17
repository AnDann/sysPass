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

$startTime = microtime();
$rowClass = "row_even";
$isDemoMode = SP_Config::getValue('demoenabled',0);
$start = ( isset($data['start']) ) ? (int)$data['start'] : 0;

$strQuery = 'SELECT SQL_CALC_FOUND_ROWS 
                log_id,
                FROM_UNIXTIME(log_date) as date,
                log_action,
                log_login,
                log_description
                FROM log ORDER BY log_id DESC LIMIT '.$start.', 50';

$resQuery = DB::getResults($strQuery, __FUNCTION__);

?>

<div id="title" class="midroundup titleNormal">
    <?php echo _('Registro de Eventos'); ?>
</div>

<?php 
if ( ! $resQuery ) {
    die('<div class="error round">'._('ERROR EN LA CONSULTA').'</div>');
}

if ( ! is_array($resQuery) ) {
    die('<div class="noRes round">'._('No se encontraron registros').'</div>');
}

$resQueryNumRows = DB::getResults("SELECT FOUND_ROWS() as numRows", __FUNCTION__);

$numRows = $resQueryNumRows[0]->numRows;
?>

<div id="resEventLog">
    <table class="data round">
        <thead>
            <tr class="header-grey">
                <th>
                    <?php echo _('ID'); ?>
                </th>
                <th>
                    <?php echo _('Fecha / Hora'); ?>
                </th>
                <th>
                    <?php echo _('Evento'); ?>
                </th>
                <th>
                    <?php echo _('Usuario'); ?>
                </th>
                <th class="cell-description">
                    <?php echo _('Descripción'); ?>
                </th>
            </tr>
        </thead>
        <tbody id="resSearch">
            <?php foreach ( $resQuery as $log ): ?>
            <?php $rowClass = ( $rowClass == "row_even" ) ? "row_odd" : "row_even"; ?>
            <?php  $description = ( $isDemoMode === 0 ) ? utf8_decode($log->log_description) : preg_replace("/\d+\.\d+\.\d+\.\d+/", "*.*.*.*", utf8_decode($log->log_description)); ?>

            <tr class="<?php echo $rowClass ?>">
                <td class="cell">
                    <?php echo $log->log_id; ?>
                </td>
                <td class="cell">
                    <?php echo $log->date; ?>
                </td>
                <td class="cell">
                    <?php echo utf8_decode($log->log_action); ?>
                </td>
                <td class="cell">
                    <?php echo strtoupper($log->log_login); ?>
                </td>
                <td class="cell-description">
                    <?php 
                    $descriptions = explode(';;', $description);
                    
                    foreach ( $descriptions as $text ){
                        if ( strlen($text) >= 300){
                            echo wordwrap($text, 300, '<br>', TRUE);
                        } else {
                            echo $text.'<br>';
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$endTime = microtime();
$totalTime = round($endTime - $startTime, 5);

SP_Html::printQueryLogNavBar($start, $numRows, $totalTime);
?>