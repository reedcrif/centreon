<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

class CentreonCommand
{
    protected $_db;
    
    /**
     * Constructor
     * 
     * @param CentreonDB $db
     */
    public function __construct($db) {
        $this->_db = $db;
    }
    
    /**
     * Get command list
     * 
     * @parma int $commandType
     * @return array
     */
    protected function getCommandList($commandType) {
        $sql = "SELECT command_id, command_name
            FROM command
            WHERE command_type = ?
            ORDER BY command_name";
        $res = $this->_db->query($sql, array($commandType));
        $arr = array();
        while ($row = $res->fetchRow()) {
            $arr[$row['command_id']] = $row['command_name'];
        }
        return $arr;
    }
    
    /**
     * Get list of check commands
     * 
     * @return array
     */
    public function getCheckCommands() {
        return $this->getCommandList(2);
    }
    
    /**
     * Get list of notification commands
     * 
     * @return array
     */
    public function getNotificationCommands() {
        return $this->getCommandList(1);
    }
    
    /**
     * Get list of misc commands
     * 
     * @return array
     */
    public function getMiscCommands() {
        return $this->getCommandList(3);
    }

    /**
     * Returns array of locked commands
     *
     * @return array
     */
    public function getLockedCommands() {
        static $arr = null;

        if (is_null($arr)) {
            $arr = array();
            $res = $this->_db->query("SELECT command_id
               FROM command
               WHERE command_locked = 1");
            while ($row = $res->fetchRow()) {
                $arr[$row['command_id']] = true;
            }
        }
        return $arr;
    }
    
    /**
     * This method gat the list of command containt a specific macro 
     * @param int $iIdCommand
     * @param string $sType
     * @param int $iWithFormatData
     * 
     * @return array
     */
    public function getMacroByIdAndType($iIdCommand, $sType, $iWithFormatData = 1)
    {
        $aTypeCommand = array(
            'host'    => array(
                'key' => '$_HOST', 
                'preg' => '/\$_HOS(\w+)\$/'
            ),
            'service' => array(
                'key' => '$_SERVICE', 
                'preg' => '/\$_SERVICE(\w+)\$/'
            ),
        );
         
        if (empty($iIdCommand) || !array_key_exists($sType, $aTypeCommand)) {
            return array();
        }
        
        $sql = "SELECT command_id, command_name, command_line
            FROM command
            WHERE command_type = 2
            AND command_id = ?
            AND command_line like '%".$aTypeCommand[$sType]['key']."%'
            ORDER BY command_name";       
        
        $res = $this->_db->query($sql, array($iIdCommand));
        $arr = array();
        $i = 0;
        if ($iWithFormatData == 1) {
             while ($row = $res->fetchRow()) {
                 if ($sType ==  'service') {
                    preg_match_all('$_SERVICE(\w+)$/', $row['command_line'], $matches, PREG_SET_ORDER);
                    var_dump($matches);
                 } else {
                     preg_match_all('/\$_HOS(\w+)\$/', $row['command_line'], $matches, PREG_SET_ORDER);
                 }
                
                foreach ($matches as $match) {
                    $arr[$i]['macroInput_#index#'] = $match[1];
                    $arr[$i]['macroValue_#index#'] = "";
                    $arr[$i]['macroPassword_#index#'] = NULL;
                    $i++;
                }
            }
        } else {
            while ($row = $res->fetchRow()) {
                $arr[$row['command_id']] = $row['command_name'];
            }
        }
        return $arr;
        
    }
}
