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

if($_GET['mode'] == 'add')
{
	if($_GET['step'] == '2')
	{
		@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

		$query = 'ALTER TABLE ' . $_POST['table'] . ' ADD ';

		$col = $_POST['name'] . ' ' . $_POST['type'];

		if(!empty($_POST['length']))
			$col .= '(' . $_POST['length'] . ')';

		if(empty($_POST['null']))
			$col .= ' NOT NULL';

		if(!empty($_POST['default']))
			$col .= ' DEFAULT ' . $_POST['default'];

		if(!empty($_POST['identity']))
			$col .= ' IDENTITY(' . $_POST['idstart'] . ',' . $_POST['idincrement'] . ')';

		if(!empty($_POST['referencetable']))
		{
			$col .= (' REFERENCES ' . $_POST['referencetable'] . '(' . $_POST['referencecolumn'] . ')');

			$reference_query = @mssql_query('sp_helpindex ' . $_POST['referencetable']) or die(throwSQLError('unable to retrive index information'));

			if(mssql_num_rows($reference_query) == 0)
				@mssql_query('ALTER TABLE ' . $_POST['referencetable'][$counter] . ' ADD PRIMARY KEY (' . $_POST['referencecolumn'][$counter] . ');') or die(throwSQLError('unable to add primary key to satisfy relationship dependency'));
			else
			{
				$reference_array = mssql_fetch_array($reference_query);

				if($reference_array['index_keys'] != $_POST['referencecolumn'][$counter])
					die(throwSQLError('unable to complete table creation, primary key already exists on table targeted for relationship'));
			}
		}

		$query .= $col . ';';

		if(!@mssql_query($query))
		{
			throwSQLError('unable to alter table',$query);
			$_GET['step'] = 1;
		}
		else
		{
			if($_SESSION['expanded'] != '')
				echo '<script language="javascript">parent.left.location.reload();</script>';

			echo '<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . urlencode($_SESSION['database']) . '">';
		}
	}

	if($_GET['step'] == '1')
	{
		@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

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

		$column_query = @mssql_query('sp_columns ' . $row) or die(throwSQLError('unable to retrieve columns'));
		while($row2 = mssql_fetch_array($column_query))
		{
			$columns[] = '\'' . $row2['COLUMN_NAME'] . '\'';
		}

		echo('data[\'' . $row . '\'] = Array(' . implode(',',$columns) . ');' . "\n");

		unset($columns);
	}
?>

	function updateTable(tablenum)
	{
		document.form1.elements["referencecolumn"].length = 0;

		for(counter = 0; counter < data[tablenum].length; counter++)
		{
			opt = new Option;

			opt.value = data[tablenum][counter];
			opt.text = data[tablenum][counter];

			document.form1.elements["referencecolumn"].options[counter] = opt;
		}
	}
</script>

<form name="form1" method="post" action="table_modify.php?mode=add&step=2">
<input type="hidden" name="table" value="<?php echo(urldecode($_GET['table'])); ?>">
<input type="hidden" name="column" value="<?php echo(urldecode($_GET['column'])); ?>">
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
		echo '<tr>';

		if(empty($_POST['name']))
			$_POST['name'] = '';

		echo ('<td style="background: #DDDDDD" nowrap><input name="name" size="15" maxlength="50" value="' . $_POST['name'] . '"></td>');

		echo '<td style="background: #DDDDDD" nowrap><select name="type">';

		$datatypes = array('bigint','binary','bit','char','datetime','decimal','float','image','int','money','nchar','ntext','numeric','nvarchar','real','smalldatetime','smallint','smallmoney','sql_variant','text','timestamp','tinyint','uniqueidentifier','varbinary','varchar');

		foreach($datatypes AS $row)
			if($_POST['type'] != $row)
				echo('<option value="' . $row . '">' . $row . '</option>');
			else
				echo('<option value="' . $row . '" selected>' . $row . '</option>');

		echo '</select></td>';

		if(empty($_POST['length']))
			$_POST['length'] = '';

		echo ('<td style="background: #DDDDDD" nowrap><input name="length" size="5" maxlength="5" value="' . $_POST['length'] . '"></td>');

		if(!empty($_POST['null']))
			$checked = 'checked';
		else
			$checked = '';

		echo('<td align="center" style="background: #DDDDDD" nowrap><input type="checkbox" name="null" value="yes" ' . $checked . '></td>');

		if(empty($_POST['default']))
			$_POST['default'] = '';

		echo '<td style="background: #DDDDDD" nowrap><input name="default" size="5" maxlength="50" value="' . $_POST['default'] . '"></td>';
		echo '<td style="background: #DDDDDD" nowrap>';

		if(empty($_POST['identity']))
			echo '<input type="radio" name="identity" value="yes">';
		else
			echo '<input type="radio" name="identity" value="yes" checked>';

		echo '&nbsp;&nbsp;';

		if(!empty($_POST['idstart']))
			echo ('<input name="idstart" size="3" maxlength="10" value="' . $_POST['idstart'] . '">');
		else
			echo '<input name="idstart" size="3" maxlength="10" value="1">';

		echo '&nbsp;&nbsp;';

		if(!empty($_POST['idincrement']))
			echo('<input name="idincrement" size="3" maxlength="10" value="' . $_POST['idincrement'] . '">');
		else
			echo '<input name="idincrement" size="3" maxlength="10" value="1">';

		echo '<td style="background: #DDDDDD" nowrap>';

		if(empty($_POST['primarykey']))
			echo '<input type="radio" name="primarykey" value="yes">';
		else
			echo '<input type="radio" name="primarykey" value="yes" checked>';

		echo '</td>';

		echo '<td style="background: #DDDDDD" nowrap><select name="referencetable" onchange=\'javascript:updateTable(document.form1.elements["referencetable"].value);\'>';
		echo '<option value="">No Constraint</option>';

		foreach($tables AS $row)
		{
			echo "<option value=\"$row\">$row</option>";
		}

		echo '</select>&nbsp;<select name="referencecolumn"></select></td>';

		echo '</tr>';
	?>
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<input type="submit" value="Modify">
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
document.form1.elements["name"].focus();
</script>

<?php

	}
}
else if($_GET['mode'] == 'change')
{
	if($_GET['step'] == '2')
	{
		@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

		$query = 'ALTER TABLE ' . $_POST['table'] . ' ALTER COLUMN ';

		$col = ($_POST['column'] . ' ' . $_POST['type']);

		if(!empty($_POST['length']))
			$col .= '(' . $_POST['length'] . ')';

		if(empty($_POST['null']))
			$col .= ' NOT NULL';

		if(!empty($_POST['default']))
			$col .= ' DEFAULT ' . $_POST['default'];

		if(!empty($_POST['identity']))
			$col .= ' IDENTITY(' . $_POST['idstart'] . ',' . $_POST['idincrement'] . ')';

		if(!empty($_POST['referencetable']))
		{
			$col .= ' REFERENCES ' . $_POST['referencetable'] . '(' . $_POST['referencecolumn'] . ')';

			$reference_query = @mssql_query('sp_helpindex ' . $_POST['referencetable']) or die(throwSQLError('unable to retrieve index information'));

			if(mssql_num_rows($reference_query) == 0)
				@mssql_query('ALTER TABLE ' . $_POST['referencetable'][$counter] . ' ADD PRIMARY KEY (' . $_POST['referencecolumn'][$counter] . ');') or die(throwSQLError('unable to add primary key to satisfy relationship dependency'));
			else
			{
				$reference_array = mssql_fetch_array($reference_query);

				if($reference_array['index_keys'] != $_POST['referencecolumn'][$counter])
					die(throwSQLError('unable to complete table creation, primary key already exists on table targeted for relationship'));
			}
		}

		$query .= ($col . ';');

		$res = @mssql_query($query) or throwSQLError('unable to alter column',$query);

		if($_POST['column'] != $_POST['name'])
		{
			$query = ('sp_rename \'' . $_POST['table'] . '.' . $_POST['column'] . '\', \'' . $_POST['name'] . '\', \'COLUMN\'');
			$res2 = @mssql_query($query) or throwSQLError('unable to rename column',$query);
		}
		else
			$res2 = true;

		if($res && $res2)
		{
			if($_SESSION['expanded'] != '')
				echo '<script language="javascript">parent.left.location.reload();</script>';

			echo '<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . urlencode($_SESSION['database']) . '">';
		}
		else
			$_GET['step'] = '1';
	}

	if($_GET['step'] == '1')
	{
		@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

		$db_info_query = @mssql_query('sp_tables') or die(throwSQLError('unable to retrieve tables'));

		$tables = array();
		while($row = mssql_fetch_array($db_info_query))
			if($row['TABLE_TYPE'] == 'TABLE' && $row['TABLE_NAME'] != 'dtproperties')
				$tables[] = $row['TABLE_NAME'];

		$coldata = array();
		$cur_query = @mssql_query('sp_columns ' . urldecode($_GET['table']) . ';') or die(throwSQLError('unable to retrieve column information'));
		while($row = mssql_fetch_array($cur_query))
			if($row['COLUMN_NAME'] == urldecode($_GET['column']))
				$coldata = $row;

		$primary_query = @mssql_query('sp_helpindex ' . urldecode($_GET['table']) . ';') or die(throwSQLError('unable to retrieve primary key status'));
		$primary_array = @mssql_fetch_array($primary_query);

		foreach($tables AS $row)
		{
			$reference_query = @mssql_query('sp_fkeys ' . $row . ';') or die(throwSQLError('unable to retrieve foreign key data'));

			while($row = mssql_fetch_array($reference_query))
			{
				if(($row['FKTABLE_NAME'] == urldecode($_GET['table'])) && ($row['FKCOLUMN_NAME'] == urldecode($_GET['column'])))
				{
					$ftable = $row['PKTABLE_NAME'];
					$fcol = $row['PKCOLUMN_NAME'];
				}
			}
		}
?>

<script language= "javascript">
	var data = new Array();
	data['none'] = Array();

<?php
	foreach($tables AS $row)
	{
		$columns = array();

		$column_query = @mssql_query('sp_columns ' . $row) or die(throwSQLError('unable to retrieve columns'));
		while($row2 = mssql_fetch_array($column_query))
		{
			$columns[] = '\'' . $row2['COLUMN_NAME'] . '\'';
		}

		echo('data[\'' . $row . '\'] = Array(' . implode(',',$columns) . ');' . "\n");

		unset($columns);
	}
?>

	function updateTable(tablenum,col)
	{
		document.form1.elements["referencecolumn"].length = 0;

		for(counter = 0; counter < data[tablenum].length; counter++)
		{
			opt = new Option;

			opt.value = data[tablenum][counter];
			opt.text = data[tablenum][counter];

			document.form1.elements["referencecolumn"].options[counter] = opt;

			if(opt.text == col)
				document.form1.elements["referencecolumn"].options[counter].selected = true;
		}
	}
</script>

<form name="form1" method="post" action="table_modify.php?mode=change&step=2&table=<?php echo($_GET['table']); ?>&column=<?php echo($_GET['column']); ?>">
<input type="hidden" name="table" value="<?php echo(urldecode($_GET['table'])); ?>">
<input type="hidden" name="column" value="<?php echo(urldecode($_GET['column'])); ?>">
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
		echo '<tr>';
		echo '<td style="background: #DDDDDD" nowrap><input name="name" size="15" maxlength="50" value="' . urldecode($_GET['column']) . '"></td>';
		echo '<td style="background: #DDDDDD" nowrap><select name="type">';

		$datatypes = array('bigint','binary','bit','char','datetime','decimal','float','image','int','money','nchar','ntext','numeric','nvarchar','real','smalldatetime','smallint','smallmoney','sql_variant','text','timestamp','tinyint','uniqueidentifier','varbinary','varchar');
		$texttypes = array('binary','char','nchar','varchar','nvarchar');

		foreach($datatypes AS $row)
			if(str_replace(' identity','',$coldata['TYPE_NAME']) != $row)
				echo('<option value="' . $row . '">' . $row . '</option>');
			else
				echo('<option value="' . $row . '" selected>' . $row . '</option>');

		echo '</select></td>';

		if(in_array($coldata['TYPE_NAME'],$texttypes))
			echo('<td style="background: #DDDDDD" nowrap><input name="length" size="5" maxlength="5" value="' . $coldata['PRECISION'] . '"></td>');
		else
			echo '<td style="background: #DDDDDD" nowrap><input name="length" size="5" maxlength="5"></td>';

		if($coldata['NULLABLE'] == '0')
			echo '<td align="center" style="background: #DDDDDD" nowrap><input type="checkbox" name="null" value="yes"></td>';
		else
			echo '<td align="center" style="background: #DDDDDD" nowrap><input type="checkbox" name="null" value="yes" checked></td>';

		echo('<td style="background: #DDDDDD" nowrap><input name="default" size="5" maxlength="50" value="' . str_replace('(','',str_replace(')','',$coldata['COLUMN_DEF'])) . '"></td>');

		if(substr_count($coldata['TYPE_NAME'],'identity') > 0)
			echo '<td style="background: #DDDDDD" nowrap><input type="radio" name="identity" value="yes" checked>&nbsp;&nbsp;<input name="idstart" size="3" maxlength="10" value="1">&nbsp;&nbsp;<input name="idincrement" size="3" maxlength="10" value="1">';
		else
			echo '<td style="background: #DDDDDD" nowrap><input type="radio" name="identity" value="yes">&nbsp;&nbsp;<input name="idstart" size="3" maxlength="10" value="1">&nbsp;&nbsp;<input name="idincrement" size="3" maxlength="10" value="1">';

		if($primary_array['index_keys'] != urldecode($_GET['column']))
			echo '<td style="background: #DDDDDD" nowrap><input type="radio" name="primarykey" value="yes"></td>';
		else
			echo '<td style="background: #DDDDDD" nowrap><input type="radio" name="primarykey" value="yes" checked></td>';

		echo '<td style="background: #DDDDDD" nowrap><select name="referencetable" onchange=\'javascript:updateTable(document.form1.elements["referencetable"].value);\'>';
		echo '<option value="">No Constraint</option>';

		foreach($tables AS $row)
		{
			if(empty($ftable))
				$ftable = '';

			if($row != $ftable)
				echo "<option value=\"$row\">$row</option>";
			else
				echo "<option value=\"$row\" selected>$row</option>";
		}

		echo '</select>&nbsp;<select name="referencecolumn"></select></td>';

		if(!empty($fcol))
			echo('<script language="javascript">updateTable(document.form1.elements["referencetable"].value,\'' . $fcol . '\');</script>');

		echo '</tr>';
	?>
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<input type="submit" value="Modify">
		</td>
		<td style="background: #D0DCE0">
			<input type="radio" name="identity" value="" <?php if(substr_count($coldata['TYPE_NAME'],'identity') == 0) echo 'checked'; ?>> No Identity
		</td>
		<td colspan="2" style="background: #D0DCE0">
			<input type="radio" name="primarykey" value="" <?php if($primary_array['index_keys'] != urldecode($_GET['column'])) echo 'checked'; ?>> No Primary Key
		</td>
	</tr>
</table>
</form>

<script language="javascript">
document.form1.elements["name"].focus();
</script>

<?php
	}
}
else if($_GET['mode'] == 'drop')
{
	@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

	$query = 'ALTER TABLE ' . urldecode($_GET['table']) . ' DROP COLUMN ' . urldecode($_GET['column']) . ';';
	@mssql_query($query) or die(throwSQLError('unable to drop column',$query));

	echo('<meta http-equiv="refresh" content="0;url=table_properties.php?table=' . $_GET['table'] . '">');
}

?>

<?php include('inc/footer.php'); ?>
