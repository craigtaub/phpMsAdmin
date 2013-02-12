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

if(!empty($_POST['skippreview']) && !empty($_POST['doexport']))
	$skipheader = true;

include('inc/header.php');

mssql_select_db($_SESSION['database']);

if(!empty($_POST['doexport']))
{
	$texttypes = array('binary','char','nchar','varchar','nvarchar');
	$masterquery = '';

	if(!empty($_POST['dropdb']))
		$masterquery .= ('DROP DATABASE ' . $_SESSION['database'] . ';' . "\n");

	if(!empty($_POST['createdb']))
		$masterquery .= ('CREATE DATABASE ' . $_SESSION['database'] . ';' . "\n");

	if(!empty($_POST['useentry']))
		$masterquery .= ('USE ' . $_SESSION['database'] . ';' . "\n");

	$tablecount = count($_POST['tables']);

	for($counter = 0; $counter < $tablecount; $counter++)
	{
		$table = $_POST['tables'][$counter];

		if(!empty($_POST['structure']))
		{
			$tablequery = ('CREATE TABLE ' . $table);
			$columns = array();
			$tablesep = explode('.',$table);

			if(substr_count($_POST['tables'][$counter],'.') > 0)
			{
				$colquery = ('sp_columns @table_name = N\'' . $tablesep[1] . '\'');
				$colquery .= (', @table_owner = N\'' . $tablesep[0] . '\'');
			}
			else
				$colquery = ('sp_columns @table_name = N\'' . $tablesep[0] . '\'');

			$column_query = @mssql_query($colquery) or die(throwSQLError('unable to retrieve column data'));

			if(mssql_num_rows($column_query) > 0)
				$tablequery .= ' (';

			while($row = mssql_fetch_assoc($column_query))
			{
				// Start Column Conversion

				$colspec = ($row['COLUMN_NAME'] . ' ' . strtoupper($row['TYPE_NAME']));

				if(in_array($row['TYPE_NAME'],$texttypes))
					$colspec .= ('(' . $row['PRECISION'] . ')');

				if(!$row['NULLABLE'])
					$colspec .= ' NOT NULL';

				if($row['COLUMN_DEF'] != '')
					$colspec .= (' DEFAULT ' . $row['COLUMN_DEF']);

				$tablequery .= (', ' . $colspec);

				// End Column Conversion
			}

			if(mssql_num_rows($column_query) > 0)
				$tablequery .= ')';

			$tablequery = str_replace('(, ','(',$tablequery);
			$masterquery .= ($tablequery . ';' . "\n");
		}

		if(!empty($_POST['data']))
		{
			$table_query = @mssql_query('SELECT * FROM ' . $table . ';') or die(throwSQLError('unable to retrieve table data'));
			while($row = mssql_fetch_assoc($table_query))
			{
				if(!isset($schema))
				{
					$schema = array();
	
					foreach($row AS $key => $value)
						$schema[] = $key;
				}
	
				$values = array();
				foreach($schema AS $col)
					if(is_numeric($row[$col]))
						$values[] = ('\'' . str_replace('\'','\'\'',$row[$col]) . '\'');
					else if(!empty($_POST['base64']))
						$values[] = ('\'' . base64_encode(str_replace('\'','\'\'',$row[$col])) . '\'');
					else
						$values[] = ('\'' . str_replace('\'','\'\'',$row[$col]) . '\'');

				$masterquery .= ('INSERT INTO ' . $table . ' (' . implode(',',$schema) . ') VALUES (' . implode(',',$values) . ');' . "\n");
			}
		}
	}

	if(!empty($_POST['procedures']))
	{
		foreach($_POST['procedures'] AS $value)
		{
			$lines = array();
			$doit = false;

			$data_query = @mssql_query('sp_helptext \'' . $value . '\'') or die(throwSQLError('unable to retrieve procedure'));
			if(!@mssql_num_rows($data_query))
			{
				$schema_query = @mssql_query('SELECT SPECIFIC_SCHEMA FROM INFORMATION_SCHEMA.ROUTINES WHERE SPECIFIC_NAME = \'' . $value . '\';');
				if($schema_query)
				{
					$schema_array = mssql_fetch_array($schema_query);
					$value = ($schema_array['SPECIFIC_SCHEMA'] . '.' . $value);
		
					unset($data_query);
					$data_query = @mssql_query('sp_helptext \'' . $value . '\'') or die(throwSQLError('unable to retrieve procedure'));
		
					if(@mssql_num_rows($data_query))
						$doit = true;
				}
			}
			else
				$doit = true;

			if($doit)
			{
				while($row = mssql_fetch_array($data_query))
					$lines[] = $row['Text'];

				$masterquery .= (implode('',$lines) . "\n");
			}

			unset($lines,$doit);
		}
	}

	if(!empty($_POST['views']))
	{
		foreach($_POST['views'] AS $value)
		{
			$lines = array();
			$doit = false;

			$data_query = @mssql_query('sp_helptext \'' . $value . '\'') or die(throwSQLError('unable to retrieve procedure'));
			if(!@mssql_num_rows($data_query))
			{
				$schema_query = @mssql_query('SELECT TABLE_SCHEMA FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = \'' . $value . '\';');
				if($schema_query)
				{
					$schema_array = mssql_fetch_array($schema_query);
					$value = ($schema_array['TABLE_SCHEMA'] . '.' . $value);

					unset($data_query);
					$data_query = @mssql_query('sp_helptext \'' . $value . '\'') or die(throwSQLError('unable to retrieve view'));

					if(@mssql_num_rows($data_query))
						$doit = true;
				}
			}
			else
				$doit = true;

			if($doit)
			{
				while($row = mssql_fetch_array($data_query))
					$lines[] = $row['Text'];

				$masterquery .= (implode('',$lines) . "\n");
			}

			unset($lines,$doit);
		}
	}

	if(!empty($_POST['functions']))
	{
		foreach($_POST['functions'] AS $value)
		{
			$lines = array();

			$data_query = @mssql_query('sp_helptext \'' . $value . '\'') or die(throwSQLError('unable to retrieve function'));
			if($data_query)
			{
				while($row = mssql_fetch_array($data_query))
					$lines[] = $row['Text'];

				$masterquery .= (implode('',$lines) . "\n");

				unset($lines);
			}
		}
	}

	$masterquery = rtrim($masterquery);

	if(empty($_POST['skippreview']))
	{
		echo '<form name="form1" method="post" action="database_export_download.php">';
		echo '<textarea name="data" rows="30" cols="75">';

		echo($masterquery);

		echo '</textarea>';

		echo '<br><br><input type="submit" value="Save to File">';
		echo '</form>';

		include('inc/footer.php');
	}
	else
	{
		header('Content-type: application/x-download');
	     header('Content-Disposition: attachment; filename="' . $_SESSION['database'] . '.sql"');
	     header('Content-Length: ' . strlen($masterquery));
	     echo($masterquery);
		exit;
	}
}

$tables = array();

$db_query = mssql_query('sp_tables') or die(throwSQLError('unable to retrieve list of tables'));

while($row = mssql_fetch_array($db_query))
{
	if($row['TABLE_TYPE'] == 'TABLE' && $row['TABLE_NAME'] != 'dtproperties')
	{
		if($row['TABLE_OWNER'] != 'dbo')
			$tables[] = ($row['TABLE_OWNER'] . '.' . $row['TABLE_NAME']);
		else
			$tables[] = $row['TABLE_NAME'];
	}
}

?>

<script language="javascript">
function doCheck(mode)
{
	for(counter = 0; counter < document.form1.tablecount.value; counter++)
		document.forms['form1'].elements['tables[]'][counter].checked = mode;
}

function doCheckProcedures(mode)
{
	for(counter = 0; counter < document.form1.procedurecount.value; counter++)
		document.forms['form1'].elements['procedures[]'][counter].checked = mode;
}

function doCheckViews(mode)
{
	for(counter = 0; counter < document.form1.viewcount.value; counter++)
		document.forms['form1'].elements['views[]'][counter].checked = mode;
}

function doCheckFunctions(mode)
{
	for(counter = 0; counter < document.form1.functioncount.value; counter++)
		document.forms['form1'].elements['functions[]'][counter].checked = mode;
}
</script>

<form name="form1" method="post" action="database_export.php">
<input type="hidden" name="doexport" value="yes">
<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="3" style="background: #D0DCE0"><b>Tables</b></td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Table</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Records</b>
		</td>
	</tr>
	<?php
		$totalrecords = 0;
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		if(empty($_POST['tables']))
			$_POST['tables'] = array();

		foreach($tables AS $row)
		{
			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			if(substr_count($row,'.') > 0)
				$record_query = mssql_query('SELECT count(*) AS itemcount FROM ' . $row);
			else
				$record_query = mssql_query('SELECT count(*) AS itemcount FROM [' . $row . ']');

			$record_array = mssql_fetch_array($record_query);
			$records = $record_array['itemcount'];
			$totalrecords += $records;

			echo '<tr>';

			if(in_array($row,$_POST['tables']))
				echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="tables[]" value="' . $row . '" checked></td>');
			else
				echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="tables[]" value="' . $row . '"></td>');

			echo('<td style="background: ' . $bg . '">' . $row . '</td>');
			echo('<td style="background: ' . $bg . '">' . number_format($records) . '</td>');
			echo '</tr>';

			unset($record_query,$record_array,$records);
		}
	?>
	<tr>
		<td align="right" colspan="2" style="background: #EAEAEA">
			<b>Total Row Count:</b>
		</td>
		<td align="right" style="background: #EAEAEA">
			<?php echo number_format($totalrecords); ?>
		</td>
	</tr>
	<tr>
		<td colspan="3" style="background: #D0DCE0" nowrap>
			Select Tables:&nbsp;&nbsp;<a href="javascript:doCheck(true);">All</a> / <a href="javascript:doCheck(false);">None</a>
		</td>
	</tr>
</table>

<br>

<?php
	$procs = array();
	$views = array();
	$functions = array();

	$proc_query = @mssql_query('sp_help') or die(throwSQLError('unable to retrieve list of stored procedures'));
	while($row = mssql_fetch_assoc($proc_query))
	{
		if($row['Object_type'] == 'stored procedure' && ($row['Owner'] == 'dbo' || $_SETTINGS['showsysdata']))
			$procs[] = $row['Name'];
		else if($row['Object_type'] == 'view' && ($row['Owner'] == 'dbo' || $_SETTINGS['showsysdata']))
			$views[] = $row['Name'];
		else if(substr_count($row['Object_type'],'function') > 0 && ($row['Owner'] == 'dbo' || $_SETTINGS['showsysdata']))
			$functions[] = $row['Name'];
	}
?>

<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Stored Procedures</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
	</tr>
	<?php
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		foreach($procs AS $key => $value)
		{
			$row['Name'] = $value;

			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			echo '<tr>';
			echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="procedures[]" value="' . $row['Name'] . '"></td>');
			echo('<td style="background: ' . $bg . '" nowrap>' . $row['Name'] . '</td>');
			echo '</tr>';

			unset($row);
		}

		if(count($procs) == 0)
			echo '<tr><td align="center" colspan="3" style="background: #DDDDDD">No Stored Procedures</td></tr>';
	?>
	<tr>
		<td colspan="2" style="background: #D0DCE0">
			Select Procedures:&nbsp;&nbsp;<a href="javascript:doCheckProcedures(true);">All</a> / <a href="javascript:doCheckProcedures(false);">None</a>
		</td>
	</tr>
</table>

<br>

<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Views</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
	</tr>
	<?php
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		foreach($views AS $key => $value)
		{
			$row['Name'] = $value;

			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			echo '<tr>';
			echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="views[]" value="' . $row['Name'] . '"></td>');
			echo('<td style="background: ' . $bg . '" nowrap>' . $row['Name'] . '</td>');
			echo '</tr>';

			unset($row);
		}

		if(count($procs) == 0)
			echo '<tr><td align="center" colspan="3" style="background: #DDDDDD">No Views</td></tr>';
	?>
	<tr>
		<td colspan="2" style="background: #D0DCE0">
			Select Views:&nbsp;&nbsp;<a href="javascript:doCheckViews(true);">All</a> / <a href="javascript:doCheckViews(false);">None</a>
		</td>
	</tr>
</table>

<br>

<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Functions</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
	</tr>
	<?php
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		foreach($functions AS $key => $value)
		{
			$row['Name'] = $value;

			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			echo '<tr>';
			echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="functions[]" value="' . $row['Name'] . '"></td>');
			echo('<td style="background: ' . $bg . '" nowrap>' . $row['Name'] . '</td>');
			echo '</tr>';

			unset($row);
		}

		if(count($functions) == 0)
			echo '<tr><td align="center" colspan="2" style="background: #DDDDDD">No Functions</td></tr>';
	?>
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			Select Functions:&nbsp;&nbsp;<a href="javascript:doCheckFunctions(true);">All</a> / <a href="javascript:doCheckFunctions(false);">None</a>
		</td>
	</tr>
</table>

<br>

<input type="hidden" name="tablecount" value="<?php echo(count($tables)); ?>">
<input type="hidden" name="procedurecount" value="<?php echo(count($procs)); ?>">
<input type="hidden" name="viewcount" value="<?php echo(count($views)); ?>">
<input type="hidden" name="functioncount" value="<?php echo(count($functions)); ?>">

<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Export Options</b>
		</td>
	</tr>
	<tr>
		<td align="right" style="background: #CCCCCC" valign="top" nowrap>
			Selection:
		</td>
		<td style="background: #CCCCCC" nowrap>
			<input type="checkbox" name="structure" value="yes"> Structure<br>
			<input type="checkbox" name="data" value="yes"> Data
		</td>
	</tr>
	<tr>
		<td align="right" style="background: #CCCCCC" valign="top" nowrap>
			Preparation:
		</td>
		<td style="background: #CCCCCC" nowrap>
			<input type="checkbox" name="dropdb" value="yes"> Drop Database First<br>
			<input type="checkbox" name="createdb" value="yes"> Use Create Database<br>
			<input type="checkbox" name="useentry" value="yes"> Add "USE" Database Switch
		</td>
	</tr>
	<tr>
		<td align="right" style="background: #CCCCCC" valign="top" nowrap>
			Method:
		</td>
		<td style="background: #CCCCCC" nowrap>
			<input type="checkbox" name="base64" value="yes"> Base-64 Encode Strings<br>
			<input type="checkbox" name="skippreview" value="yes"> Skip Export Preview
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2" style="background: #D0DCE0">
			<input type="submit" value="Export">
		</td>
	</tr>
</table>
</form>

<?php include('inc/footer.php'); ?>
