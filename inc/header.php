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
<?php include('inc/config.php'); ?>
<?php
	if($_SETTINGS['disablenotices'])
		error_reporting(E_ALL ^ E_NOTICE);
?>
<?php include('inc/funclib.php'); ?>
<?php include('inc/session.php'); ?>
<?php include('inc/dbconnect.php'); ?>
<?php
	if(empty($bgcolor))
		$bgcolor = '#F5F5F5';

	if(!isset($skipheader))
	{
?>
<html>
<head>
<title>phpMSAdmin</title>
<script type="text/javascript" src="inc/overlib/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body bgcolor="<?php echo($bgcolor); ?>" style="font-family: <?php echo($_SETTINGS['fontfamily']); ?>">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<center>
<?php
	}

     if(!isset($skipdrawing) && !isset($skipheader))
     {
?>
		<font style="font-size: 24pt; font-weight: bold"><font style="color: #2A00A6">php</font><font style="color: #B90000">MS</font><font style="color: #006300">Admin</font></font>
		<br><br>
<?php
     }
?>