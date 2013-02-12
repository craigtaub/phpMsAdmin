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
     session_start();

     $date = date('U');

     if($_SESSION['updatetime'] == '' || ($date - $_SESSION['updatetime']) > $_SETTINGS['sessiontime'])
     {
	    session_destroy();
	    echo '<script language="javascript">parent.document.location = \'index.php\';</script>';
	    exit;
     }

	if($_SERVER['REMOTE_ADDR'] != $_SESSION['REMOTE_ADDR'] || $_SERVER['HTTP_USER_AGENT'] != $_SESSION['HTTP_USER_AGENT'])
	{
		header('Location: index.php');
		exit;
	}

     $_SESSION['updatetime'] = $date;
?>
