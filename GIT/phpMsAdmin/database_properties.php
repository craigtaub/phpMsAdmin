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
</script>

<form name="form1" method="post" action="database_export.php">
<table width="450" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Table</b>
		</td>
		<td align="center" colspan="7" style="background: #D0DCE0">
			<b>Action</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Records</b>
		</td>
	</tr>
	<?php
		$totalrecords = 0;
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

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
			echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="tables[]" value="' . urlencode($row) . '"></td>');
			echo('<td style="background: ' . $bg . '">' . $row . '</td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_browse.php?table=' . urlencode($row) . '">Browse</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_select.php?table=' . urlencode($row) . '">Select</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_insert.php?table=' . urlencode($row) . '">Insert</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_properties.php?table=' . urlencode($row) . '">Properties</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="trigger_list.php?table=' . urlencode($row) . '">Triggers</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_drop.php?table=' . urlencode($row) . '">Drop</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_empty.php?table=' . urlencode($row) . '">Empty</a></td>');
			echo('<td style="background: ' . $bg . '">' . number_format($records) . '</td>');
			echo '</tr>';

			unset($record_query,$record_array,$records);
		}

		if(count($tables) == 0)
			echo '<tr><td align="center" colspan="10" style="background: #DDDDDD">No Tables Exist</td></tr>';
	?>
	<tr>
		<td colspan="6" nowrap>
			Select Tables:&nbsp;&nbsp;<a href="javascript:doCheck(true);">All</a> / <a href="javascript:doCheck(false);">None</a>
		</td>
		<td align="right" colspan="3" style="background: #EAEAEA">
			<b>Total Row Count:</b>
		</td>
		<td align="right" style="background: #EAEAEA">
			<?php echo number_format($totalrecords); ?>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="10" style="background: #D0DCE0">
			<a href="table_create.php?database=<?php echo($_GET['dbname']); ?>&step=1">Create A New Table</a>
		</td>
	</tr>
</table>

<br>

<input type="hidden" name="tablecount" value="<?php echo(count($tables)); ?>">
<table width="250" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" style="background: #D0DCE0">
			<b>Mass Operation</b>
		</td>
	</tr>
	<tr>
		<td align="left" style="background: #CCCCCC" nowrap>
			<input type="radio" name="operation" value="export" onclick="javascript:document.form1.action = 'database_export.php';" checked>Export - SQL<br>
			<input type="radio" name="operation" value="export" onclick="javascript:document.form1.action = 'table_export.php';">Export - CSV<br>
			<input type="radio" name="operation" value="empty" onclick="javascript:document.form1.action = 'table_empty.php';">Empty Selected Table's<br>
			<input type="radio" name="operation" value="drop" onclick="javascript:document.form1.action = 'table_drop.php';">Drop Selected Table's
		</td>
	</tr>
	<tr>
		<td align="center">
			<input type="submit" value="Continue">
		</td>
	</tr>
</table>
</form>
<br><br>

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

<form name="form2" method="post" action="procedure_list.php">
<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<b>Stored Procedures</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
		<td align="center" colspan="3" style="background: #D0DCE0">
			<b>Action</b>
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
			echo('<td align="center" style="background: ' . $bg . '"><a href="procedure_execute.php?procedure=' . urlencode($row['Name']) . '">Execute</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="procedure_modify.php?procedure=' . urlencode($row['Name']) . '">Modify</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="procedure_drop.php?procedure=' . urlencode($row['Name']) . '&returnto=database_properties.php">Drop</a></td>');
			echo '</tr>';

			unset($row);
		}

		if(count($procs) == 0)
			echo '<tr><td align="center" colspan="5" style="background: #DDDDDD">No Stored Procedures</td></tr>';
	?>
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<a href="procedure_create.php">Create A New Stored Procedure</a>
		</td>
	</tr>
</table>
</form>
<br><br>

<form name="form3" method="post" action="view_list.php">
<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<b>Views</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
		<td align="center" colspan="3" style="background: #D0DCE0">
			<b>Action</b>
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
			echo('<td align="center" style="background: ' . $bg . '"><a href="table_select.php?view=' . urlencode($row['Name']) . '">Select</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="view_modify.php?view=' . urlencode($row['Name']) . '">Modify</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="view_drop.php?view=' . urlencode($row['Name']) . '&returnto=database_properties.php">Drop</a></td>');
			echo '</tr>';

			unset($row);
		}

		if(count($views) == 0)
			echo '<tr><td align="center" colspan="5" style="background: #DDDDDD">No Views</td></tr>';
	?>
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<a href="view_create.php">Create A New View</a>
		</td>
	</tr>
</table>
</form>
<br><br>

<form name="form3" method="post" action="function_list.php">
<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<b>Functions</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
		<td align="center" colspan="3" style="background: #D0DCE0">
			<b>Action</b>
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
			echo('<td align="center" style="background: ' . $bg . '"><a href="function_modify.php?function=' . urlencode($row['Name']) . '">Modify</a></td>');
			echo('<td align="center" style="background: ' . $bg . '"><a href="function_drop.php?function=' . urlencode($row['Name']) . '&returnto=database_properties.php">Drop</a></td>');
			echo '</tr>';

			unset($row);
		}

		if(count($functions) == 0)
			echo '<tr><td align="center" colspan="5" style="background: #DDDDDD">No Functions</td></tr>';
	?>
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<a href="function_create.php">Create A New Function</a>
		</td>
	</tr>
</table>
</form>

<?php include('inc/footer.php'); ?>
