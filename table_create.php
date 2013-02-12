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

if(empty($_GET['step']))
	$_GET['step'] = '2';

if($_GET['step'] == '3')
{
	@mssql_select_db($_POST['database']) or die(throwSQLError('unable to select database'));

	$query = 'CREATE TABLE ' . $_POST['table'];

	$colcount = count($_POST['name']);

	$cols = array();

	for($counter = 0; $counter < $colcount; $counter++)
	{
		if($_POST['name'][$counter] != '')
		{
			$cols[$counter] = $_POST['name'][$counter] . ' ' . $_POST['type'][$counter];

			if($_POST['length'][$counter] != '')
				$cols[$counter] .= '(' . $_POST['length'][$counter] . ')';

			if(empty($_POST['null'][$counter]))
				$cols[$counter] .= ' NOT NULL';

			if(!empty($_POST['default'][$counter]))
				$cols[$counter] .= ' DEFAULT ' . $_POST['default'][$counter];

			if($_POST['primarykey'] === $counter)
				$cols[$counter] .= ' PRIMARY KEY';

			if($_POST['identity'] === $counter)
				$cols[$counter] .= ' IDENTITY(' . $_POST['idstart'][$counter] . ',' . $_POST['idincrement'][$counter] . ')';

			if(!empty($_POST['referencetable'][$counter]))
			{
				$cols[$counter] .= ' REFERENCES ' . $_POST['referencetable'][$counter] . '(' . $_POST['referencecolumn'][$counter] . ')';

				$reference_query = @mssql_query('sp_helpindex ' . $_POST['referencetable'][$counter]) or die(throwSQLError('unable to retrieve index'));

				if(mssql_num_rows($reference_query) == 0)
					@mssql_query('ALTER TABLE ' . $_POST['referencetable'][$counter] . ' ADD PRIMARY KEY (' . $_POST['referencecolumn'][$counter] . ');') or die(throwSQLError('unable to add primary key to satisfy relationship dependency'));
				else
				{
					$reference_array = mssql_fetch_array($reference_query);

					if($reference_array['index_keys'] != $_POST['referencecolumn'][$counter])
						die(throwSQLError('unable to complete table creation, primary key already exists on table targeted for relationship'));
				}
			}
		}
	}

	$query .= '(' . implode(', ',$cols) . ');';

	if(!@mssql_query($query))
	{
		throwSQLError('unable to create table',$query);
		$_GET['step'] = '2';
	}
	else
	{
		if($_SESSION['expanded'] != '')
			echo '<script language="javascript">parent.left.location.reload();</script>';

		echo '<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . urlencode($_POST['database']) . '">';
		include('inc/footer.php');
	}
}

if($_GET['step'] == '2')
{
	mssql_select_db($_POST['database']);

	$db_info_query = @mssql_query('sp_tables') or die(throwSQLError('unable to retrieve tables'));

	$tables = array();
	while($row = mssql_fetch_array($db_info_query))
		if($row['TABLE_TYPE'] == 'TABLE' && $row['TABLE_NAME'] != 'dtproperties')
			$tables[] = $row['TABLE_NAME'];
?>

<script language= "javascript">
	var data = new Array();
	data['none'] = Array();

<?php
	foreach($tables AS $row)
	{
		$columns = array();

		$column_query = @mssql_query('sp_columns [' . $row . ']') or die(throwSQLError('unable to retrieve columns'));
		while($row2 = mssql_fetch_array($column_query))
		{
			$columns[] = '\'' . $row2['COLUMN_NAME'] . '\'';
		}

		echo('data[\'' . $row . '\'] = Array(' . implode(',',$columns) . ');' . "\n");

		unset($columns);
	}
?>

	function updateTable(tablenum,row)
	{
		document.form1.elements["referencecolumn[" + row + "]"].length = 0;

		for(counter = 0; counter < data[tablenum].length; counter++)
		{
			opt = new Option;

			opt.value = data[tablenum][counter];
			opt.text = data[tablenum][counter];

			document.form1.elements["referencecolumn[" + row + "]"].options[counter] = opt;
		}
	}
</script>

<form name="form1" method="post" action="table_create.php?step=3">
<input type="hidden" name="table" value="<?php echo($_POST['table']); ?>">
<input type="hidden" name="database" value="<?php echo($_POST['database']); ?>">
<input type="hidden" name="columns" value="<?php echo($_POST['columns']); ?>">
<table width="300" cellpadding="3" cellspacing="3">
	<tr>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Field</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Type</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Length</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Null</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Default</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Identity (<font size="-2">Start</font>, <font size="-2">Increment</font>)</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>P. Key</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Constraint (<font size="-2">Table</font>, <font size="-2">Column</font>)&nbsp;
		</td>
	</tr>
	<?php
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		for($counter = 0; $counter < $_POST['columns']; $counter++)
		{
			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			echo '<tr>';

			if(!empty($_POST['name'][$counter]))
				echo('<td style="background: ' . $bg . '" nowrap><input name="name[' . $counter . ']" size="15" maxlength="50" value="' . $_POST['name'][$counter] . '"></td>');
			else
				echo('<td style="background: ' . $bg . '" nowrap><input name="name[' . $counter . ']" size="15" maxlength="50"></td>');

			echo('<td style="background: ' . $bg . '" nowrap><select name="type[' . $counter . ']">');

			$datatypes = array('bigint','binary','bit','char','datetime','decimal','float','image','int','money','nchar','ntext','numeric','nvarchar','real','smalldatetime','smallint','smallmoney','sql_variant','text','timestamp','tinyint','uniqueidentifier','varbinary','varchar');

			foreach($datatypes AS $row)
				if($_POST['type'][$counter] != $row)
					echo('<option value="' . $row . '">' . $row . '</option>');
				else
					echo('<option value="' . $row . '" selected>' . $row . '</option>');

			echo '</select></td>';

			if(!empty($_POST['length'][$counter]))
				echo('<td style="background: ' . $bg . '" nowrap><input name="length[' . $counter . ']" size="5" maxlength="5" value="' . $_POST['length'][$counter] . '"></td>');
			else
				echo('<td style="background: ' . $bg . '" nowrap><input name="length[' . $counter . ']" size="5" maxlength="5"></td>');

			echo('<td align="center" style="background: ' . $bg . '" nowrap>');

			if(empty($_POST['null'][$counter]))
				echo('<input type="checkbox" name="null[' . $counter . ']" value="yes">');
			else
				echo('<input type="checkbox" name="null[' . $counter . ']" value="yes" checked>');

			echo '</td>';

			if(!empty($_POST['default'][$counter]))
				echo('<td style="background: ' . $bg . '" nowrap><input name="default[' . $counter . ']" size="5" maxlength="50" value="' . $_POST['default'][$counter] . '"></td>');
			else
				echo('<td style="background: ' . $bg . '" nowrap><input name="default[' . $counter . ']" size="5" maxlength="50"></td>');

			echo('<td style="background: ' . $bg . '" nowrap>');

			if(!empty($_POST['identity']))
				$identity = $_POST['identity'];
			else
				$identity = '';

			if($identity != $counter)
				echo('<input type="radio" name="identity" value="' . $counter . '">');
			else
				echo('<input type="radio" name="identity" value="' . $counter . '" checked>');

			echo '&nbsp;&nbsp;';

			if(empty($_POST['idstart'][$counter]))
				echo('<input name="idstart[' . $counter . ']" size="3" maxlength="10" value="1">');
			else
				echo('<input name="idstart[' . $counter . ']" size="3" maxlength="10" value="' . $_POST['idstart'][$counter] . '">');

			echo '&nbsp;&nbsp;' ;

			if(empty($_POST['idincrement'][$counter]))
				echo('<input name="idincrement[' . $counter . ']" size="3" maxlength="10" value="1">');
			else
				echo('<input name="idincrement[' . $counter . ']" size="3" maxlength="10" value="' . $_POST['idincrement'][$counter] . '">');

			echo '</td>';

			echo('<td style="background: ' . $bg . '" nowrap>');

			if(!empty($_POST['primarykey']))
				$primarykey = $_POST['primarykey'];
			else
				$primarykey = '';

			if($primarykey != $counter)
				echo('<input type="radio" name="primarykey" value="' . $counter . '">');
			else
				echo('<input type="radio" name="primarykey" value="' . $counter . '" checked>');

			echo '</td>';

			echo('<td style="background: ' . $bg . '" nowrap><select name="referencetable[' . $counter . ']" onchange=\'javascript:updateTable(document.form1.elements["referencetable[' . $counter . ']"].value,' . $counter . ');\'>');
			echo '<option value="">No Constraint</option>';

			foreach($tables AS $row)
				echo('<option value="' . $row . '">' . $row . '</option>');

			echo('</select>&nbsp;<select name="referencecolumn[' . $counter . ']"></select></td>');

			echo '</tr>';
		}
	?>
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<input type="submit" value="Create">
		</td>
		<td style="background: #D0DCE0">
			<input type="radio" name="identity" value="" <?php if(empty($_POST['identity'])) echo 'checked'; ?>> No Identity
		</td>
		<td colspan="2" style="background: #D0DCE0">
			<input type="radio" name="primarykey" value="" <?php if(empty($_POST['primarykey'])) echo 'checked'; ?>> No Primary Key
		</td>
	</tr>
</table>
</form>

<script language="javascript">
document.form1.elements["name[0]"].focus();
</script>

<?php

}

if($_GET['step'] == '1')
{
	$db_info_query = @mssql_query('sp_helpdb;') or die(throwSQLError('unable to retrieve databases'));

	$dbinfo = array();
	while($row = mssql_fetch_array($db_info_query))
		if(!in_array($row['name'],$_SETTINGS['dbexclude']))
			$dbinfo[] = $row['name'];
?>

<form name="form1" method="post" action="table_create.php?step=2">
<input type="hidden" name="database" value="<?php echo($_REQUEST['database']); ?>">
<table width="300" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Create Table</b>
		</td>
	</tr>
	<tr>
		<td align="right">
			<b>Table Name:</b>
		</td>
		<td>
			<input name="table" size="15" maxlength="50">
		</td>
	</tr>
	<tr>
		<td align="right">
			<b>Columns:</b>
		</td>
		<td>
			<input name="columns" size="3" maxlength="3">
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<input type="submit" value="Next">
		</td>
	</tr>
</table>
</form>

<script language="javascript">
document.form1.table.focus();
</script>

<?php
}
?>

<?php include('inc/footer.php'); ?>
