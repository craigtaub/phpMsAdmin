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
<?php include('inc/header.php'); ?>

<table cellpadding="0" cellspacing="0" border="1" width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>">
	<tr>
		<td align="center" style="font-weight: bold">Databases</td>
	</tr>
	<?php
		if($_GET['expand'] != '')
			$_SESSION['expanded'] = 'yes';
		else
			$_SESSION['expanded'] = 'no';
	
		$db_query = @mssql_query('sp_databases') or die(throwSQLError('unable to retrieve a list of databases'));
	
		while($row = mssql_fetch_array($db_query))
		{
			if(!in_array($row['DATABASE_NAME'],$_SETTINGS['dbexclude']))
			{
				$encoded = urlencode($row['DATABASE_NAME']);
	
				if($_GET['expand'] != $encoded)
					echo('<tr><td><a href="menu.php?expand=' . $encoded .'">+</a>&nbsp;<a href="database_properties.php?dbname=' . $encoded . '">' . $row['DATABASE_NAME'] . '</a></td></tr>');
				else
				{
					echo('<tr><td><a href="menu.php">+</a>&nbsp;<a href="database_properties.php?dbname=' . $encoded . '">' . $row['DATABASE_NAME'] . '</a></td></tr>');
	
					$_SESSION['database'] = $row['DATABASE_NAME'];
	
					@mssql_select_db($row['DATABASE_NAME']) or die(throwSQLError('unable to select database'));
	
					$table_query = @mssql_query('sp_tables') or die(throwSQLError('unable to retrieve a list of tables'));
					while($row2 = mssql_fetch_array($table_query))
					{
						if(($row2['TABLE_TYPE'] == 'TABLE' && $row2['TABLE_NAME'] != 'dtproperties') || $_SETTINGS['showsysdata'])
						{
							if($row2['TABLE_OWNER'] != 'dbo')
								$row2['TABLE_NAME'] = ($row2['TABLE_OWNER'] . '.' . $row2['TABLE_NAME']);
							
							echo('<tr><td>&nbsp;&nbsp;&nbsp;-&nbsp;<font size="-1"><a href="table_properties.php?table=' . urlencode($row2['TABLE_NAME']) . '">' . $row2['TABLE_NAME'] . '</a></font></td></tr>');
						}
					}
				}
			}
		}
	?>
</table>
</body>
</html>
<?php mssql_close(); ?>