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

	mssql_select_db($_SESSION['database']);

	$doit = false;

	$data_query = @mssql_query('sp_helptext \'' . urldecode($_GET['view']) . '\'') or die(throwSQLError('unable to retrieve procedure'));
	if(!@mssql_num_rows($data_query))
	{
		$schema_query = @mssql_query('SELECT TABLE_SCHEMA FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = \'' . urldecode($_GET['view']) . '\';');
		if($schema_query)
		{
			$schema_array = mssql_fetch_array($schema_query);

			if($schema_array['TABLE_SCHEMA'] != 'dbo')
				$_GET['view'] = ($schema_array['TABLE_SCHEMA'] . '.' . urldecode($_GET['view']));
		}
	}

	@mssql_query('DROP VIEW ' . urldecode($_GET['view']) . ';') or die(throwSQLError('unable to delete procedure'));

	if(empty($_GET['returnto']))
		$_GET['returnto'] = 'database_properties.php';

	echo('<meta http-equiv="refresh" content="0;url=' . $_GET['returnto'] . '">');

	include('inc/footer.php');
?>
