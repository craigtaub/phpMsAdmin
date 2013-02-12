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

include('inc/header.php');

if(empty($_POST['mdf']))
	$result = @mssql_query('CREATE DATABASE ' . $_POST['dbname']) or die(throwSQLError('unable to create database'));
else
{
	if(substr_count($_POST['mdf'],'\\') == 0 || (substr_count($_POST['ldf'],'\\') == 0 && $_POST['ldf'] != ''))
	{
		mssql_select_db('master');
		$path_query = @mssql_query('sp_helpfile') or die(throwSQLError('unable to retrieve file path'));
		$path_array = mssql_fetch_array($path_query);

		if(substr_count($_POST['mdf'],'\\') == 0)
			$_POST['mdf'] = substr($path_array['filename'],0,strrpos($path_array['filename'],'\\')) . $_POST['mdf'];

		if(substr_count($_POST['ldf'],'\\') == 0)
			$_POST['ldf'] = substr($path_array['filename'],0,strrpos($path_array['filename'],'\\')) . $_POST['ldf'];
	}

	if($_POST['ldf'] == '')
		$result = @mssql_query('sp_attach_single_file_db \'' . $_POST['dbname'] . '\',\'' . $_POST['mdf'] . '\'') or die(throwSQLError('unable to attach database'));
	else
		$result = @mssql_query('sp_attach_db \'' . $_POST['dbname'] . '\',\'' . $_POST['mdf'] . '\',\'' . $_POST['ldf'] . '\'') or die(throwSQLError('unable to attach database'));
}

if($result)
{
	echo '<script language="javascript">parent.left.location.reload();</script>';
	echo('<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . $_POST['dbname'] . '">');
}

include('inc/footer.php');

?>
