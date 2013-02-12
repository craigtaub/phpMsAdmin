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
	$skipdrawing = true;
	$bgcolor = '#D0DCE0';

	include('inc/header.php');
?>

<script language="javascript">
function genDBLink(db)
{
	return overlib('<a href="database_detach.php?dbname=' + db + '" target="right">Detach</a><br><a href="database_drop.php?dbname=' + db + '" target="right">Drop</a><br><a href="database_mode.php?dbname=' + db + '" target="right">Change Mode</a><br><br><a href="database_import.php?dbname=' + db + '" target="right">Import</a><br><a href="database_export.php?dbname=' + db + '" target="right">Export</a><br><br><a href="table_create.php?database=' + db + '&step=1" target="right">New Table</a><br><a href="procedure_list.php?dbname=' + db + '" target="right">Procedures</a><br><a href="view_list.php?dbname=' + db + '" target="right">Views</a><br><a href="function_list.php?dbname=' + db + '" target="right">Functions</a>', STICKY, MOUSEOFF, CENTER, OFFSETY, -10, CLOSECLICK, CAPTION, "Options", WIDTH, 95);
}
function genTableLink(table,db)
{
	<?php
		if($_SETTINGS['dangerous_table_action_prompts'])
			echo 'return overlib(\'<a href="table_browse.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Browse</a><br><a href="table_select.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Select</a><br><a href="table_insert.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Insert</a><br><a href="trigger_list.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Triggers</a><br><a href="table_querybuilder.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Query Builder</a><br><br><a href="table_empty.php?table=\' + table + \'&dbname=\' + db + \'" target="right" onclick="javascript:if(confirm(\\\'Are you sure you wish to empty this table?\\\')) return(true); else return(false);">Empty</a><br><a href="table_rename.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Rename</a><br><a href="table_drop.php?table=\' + table + \'&dbname=\' + db + \'" target="right" onclick="javascript:if(confirm(\\\'Are you sure you wish to drop this table?\\\')) return(true); else return(false);">Drop</a>\', STICKY, MOUSEOFF, CENTER, OFFSETY, -10, CLOSECLICK, CAPTION, "Options", WIDTH, 95);';
		else
			echo 'return overlib(\'<a href="table_browse.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Browse</a><br><a href="table_select.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Select</a><br><a href="table_insert.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Insert</a><br><a href="trigger_list.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Triggers</a><br><a href="table_querybuilder.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Query Builder</a><br><br><a href="table_empty.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Empty</a><br><a href="table_rename.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Rename</a><br><a href="table_drop.php?table=\' + table + \'&dbname=\' + db + \'" target="right">Drop</a>\', STICKY, MOUSEOFF, CENTER, OFFSETY, -10, CLOSECLICK, CAPTION, "Options", WIDTH, 95);';
	?>
}
</script>

<div style="font-size: 8pt"><a href="home.php" target="right">Home</a> - <a href="#" onclick="javascript:document.location.reload();">Refresh</a> - <a href="logout.php" target="_top">Logout</a></div>
</center>
<br>

<?php
	if(!empty($_GET['expand']))
	{
		$expand = $_GET['expand'];
		$_SESSION['expanded'] = 'yes';
	}
	else
	{
		$expand = '';
		$_SESSION['expanded'] = 'no';
	}

	$db_query = @mssql_query('sp_databases') or die(throwSQLError('unable to retrieve a list of databases'));

	while($row = mssql_fetch_array($db_query))
	{
		if(!in_array($row['DATABASE_NAME'],$_SETTINGS['dbexclude']))
		{
			$encoded = urlencode($row['DATABASE_NAME']);

			if($expand != $encoded)
				echo('<a href="menu.php?expand=' . $encoded .'">+</a>&nbsp;<a href="database_properties.php?dbname=' . $encoded . '" oncontextmenu="javascript:genDBLink(\'' . $encoded . '\');return(false);" target="right">' . $row['DATABASE_NAME'] . '</a><br>');
			else
			{
				echo('<a href="menu.php">+</a>&nbsp;<a href="database_properties.php?dbname=' . $encoded . '" oncontextmenu="javascript:genDBLink(\'' . $encoded . '\');return(false);" target="right">' . $row['DATABASE_NAME'] . '</a><br>');

				$_SESSION['database'] = $row['DATABASE_NAME'];

				@mssql_select_db($row['DATABASE_NAME']) or die(throwSQLError('unable to select database'));

				$table_query = @mssql_query('sp_tables') or die(throwSQLError('unable to retrieve a list of tables'));
				while($row2 = mssql_fetch_array($table_query))
				{
					if(($row2['TABLE_TYPE'] == 'TABLE' && $row2['TABLE_NAME'] != 'dtproperties') || $_SETTINGS['showsysdata'])
					{
						if($row2['TABLE_OWNER'] != 'dbo')
							$row2['TABLE_NAME'] = ($row2['TABLE_OWNER'] . '.' . $row2['TABLE_NAME']);
						
						echo('&nbsp;&nbsp;&nbsp;-&nbsp;<font size="-1"><a href="table_properties.php?table=' . urlencode($row2['TABLE_NAME']) . '&dbname=' . $_GET['expand'] . '" oncontextmenu="javascript:genTableLink(\'' . urlencode($row2['TABLE_NAME']) . '\',\'' . $_GET['expand'] . '\');return(false);" target="right">' . $row2['TABLE_NAME'] . '</a></font><br>');
					}
				}
			}
		}
	}
?>
<p>Menu.php Queries:</p>
<p>1. select username,password,passwordreset from Contacts
where username ='craigtaub'</p>
<p>2. update CorpInvite
set autoRenewal = 1</p>
<p>3. SELECT top 20 c.Id
FROM content as c 
WHERE c.publicationDate <= GETDATE()
order by id desc </p>
</body>
</html>
<?php mssql_close(); ?>
