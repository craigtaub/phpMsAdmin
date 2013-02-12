<?php
     /*
     Copyright (C)

     This program is free software; you can redistribute it and/or modify it
     under the terms of the GNU General Public License as published by the Free
     Software Foundation; either version 2 of the License, or (at your option)
     any later version.

     This program is distributed in the hope that it will be useful, but WITHOUT
     ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
     FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
     more details.

     You should have received a copy of the GNU General Public License along
     with this program; if not, write to the Free Software Foundation, Inc.,
     59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
     */
?>
<?php

//$_SETTINGS['freetdspath'] = '/usr/local/freetds_0.63/etc/freetds.conf';
//$_SETTINGS['odbcinipath'] = 'C:/WINDOWS/ODBC.INI';
$_SETTINGS['freetdspath'] = '/etc/freetds/freetds.conf';
$_SETTINGS['odbcinipath'] = '/etc/odbc.ini';
$_SETTINGS['detectionoff'] = false;

$_SETTINGS['sessiontime'] = 1200;
$_SETTINGS['dbexclude'] = array('master','model','msdb','tempdb');

$_SETTINGS['fontfamily'] = 'Arial';
$_SETTINGS['mobilescreenwidth'] = 320;
$_SETTINGS['dangerous_table_action_prompts'] = true;

$_SETTINGS['showsysdata'] = false;
$_SETTINGS['disablenotices'] = true;

// Example: array('127.0.0.1:1433','10.1.1.1','192.168.1.1');
$_SETTINGS['connections'] = array();

?>
