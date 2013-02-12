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
<html>
<head>
<title>phpMSAdmin-DEV DB</title>
</head>
<body bgcolor="#D0DCE0" style="font-family: <?php echo($_SETTINGS['fontfamily']); ?>">
<center>
<font style="font-size: 24pt; font-weight: bold"><font style="color: #2A00A6">php</font><font style="color: #B90000">MS</font><font style="color: #006300">Admin---DEV DB</font></font>

<br><br>

<?php

//die(phpinfo());
// Connect to MSSQL

include('inc/funclib.php');
if(!empty($_POST['datasource']))
{

$_POST['username'] = 'username';
$_POST['password'] = 'password';

	$c = @mssql_connect($_POST['datasource'],$_POST['username'],$_POST['password']) or die(throwSQLError('unable to establish a connection'));
     mssql_close($c);

	session_start();

	$_SESSION['updatetime'] = date('U');
	$_SESSION['datasource_name'] = $_POST['datasource'];
	$_SESSION['datasource_username'] = $_POST['username'];
	$_SESSION['datasource_password'] = $_POST['password'];
	$_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
	$_SESSION['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

	echo '<meta http-equiv="refresh" content="0;url=frameset.php">';
	exit;
}

$sources = array();

if(!$_SETTINGS['detectionoff'])
{
	if(!isset($_SERVER['WINDIR']) && file_exists($_SETTINGS['freetdspath']))
	{
	     $sourcefile = array();
	     $row = '';

		$sourcefile = @file($_SETTINGS['freetdspath']) or die(throwGeneralError('unable to parse FreeTDS configuration file, check path<br>in config.php, and permissions on the freetds.conf file'));

		foreach($sourcefile AS $row)
		{
			if($row[0] == '[' && trim($row) != '[global]')
				$sources[] = substr(str_replace(']','',trim($row)),1);
		}

		if(count($sources) == 0)
			throwGeneralError('unable to find any datasources in your freetds.conf,<br>check your FreeTDS configuration');
	}
	else if(file_exists($_SETTINGS['odbcinipath']))
	{
		$sourcefile = array();
	     $row = '';

		$skipds = array('[ODBC 32 bit Data Sources]','[MS Access Database]','[Excel Files]','[dBASE Files]');

		$sourcefile = @file($_SETTINGS['odbcinipath']) or die(throwGeneralError('unable to parse ODBC configuration file, check path<br>in config.php, and permissions on the odbc.ini file'));

		foreach($sourcefile AS $row)
		{
			if($row[0] == '[' && !in_array(trim($row),$skipds))
				$sources[] = substr(str_replace(']','',trim($row)),1);
		}
	}

	if(count($_SETTINGS['connections']) > 0)
		$sources = array_merge($sources,$_SETTINGS['connections']);
}

?>

<form name="form1" method="post" action="index.php">
<table width="300" style="border: 1px solid; border-color: black">
	<tr>
		<td align="right">
			<b>Datasource:</b>
		</td>
		<td align="left">
			<?php
				if(!empty($sources))
				{
					?>
						<select name="datasource">
							<?php
								foreach($sources AS $row)
								{
									echo('<option value="' . $row . '">' . $row . '</option>' . "\n");
								}
							?>
						</select>
					<?php
				}
				else
				{
					?>
						<input name="datasource" size="15" maxlength="50">
					<?php
				}
			?>
		</td>
	</tr>
	<tr>
		<td align="right">
			<b>Username:</b>
		</td>
		<td align="left">
			<input name="username" size="15" maxlength="50" style="display:none;">
		</td>
	</tr>
	<tr>
		<td align="right">
			<b>Password:</b>
		</td>
		<td align="left">
			<input type="password" name="password" size="15" maxlength="50" style="display:none;">
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type="submit" value="Login">
		</td>
	</tr>
</table>
</form>
</center>
</body>

<script language="javascript">
	document.form1.username.focus();
</script>

</html>
