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
<?php include('../inc/config.php'); ?>
<?php include('../inc/funclib.php'); ?>
<?php include('../inc/session.php'); ?>
<?php include('../inc/dbconnect.php'); ?>
<html>
<head>
<title>phpMSAdmin</title>
</head>
<body bgcolor="#D0DCE0" style="font-family: <?php echo($_SETTINGS['fontfamily']); ?>">
<center>
<?php
     if($skipdrawing != true)
     {
?>
		<font style="font-size: 28pt; font-weight: bold"><font style="color: #2A00A6">php</font><font style="color: #B90000">MS</font><font style="color: #006300">Admin</font></font><br>
		<font style="font-size: 8pt"><a href="menu.php">Menu</a> - <a href="logout.php">Logout</a></font>
		<br><br>
<?php
     }
?>