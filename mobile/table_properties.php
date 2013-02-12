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

	$_GET['table'] = urldecode($_GET['table']);

	$tablesep = explode('.',$_GET['table']);

	if(substr_count($_GET['table'],'.') > 0)
	{
		$query = ('sp_columns @table_name = N\'' . $tablesep[1] . '\'');
		$query .= (', @table_owner = N\'' . $tablesep[0] . '\'');
	}
	else
	{
		$query = ('sp_columns @table_name = N\'' . $tablesep[0] . '\'');
	}

	$column_query = @mssql_query($query) or die(throwSQLError('unable to retrieve column data'));
?>

<script language="javascript">
function doCheck(mode)
{
	for(counter = 0; counter < document.form1.columncount.value; counter++)
		document.forms['form1'].elements['columns[]'][counter].checked = mode;
}
</script>

<form name="form1" method="post" action="table_properties.php">
<table width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>" cellpadding="2" cellspacing="0" style="border: 1px solid">
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Field</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Type</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Default</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Null</b>
		</td>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Action</b>
		</td>
	</tr>
	<?php
		$texttypes = array('binary','char','nchar','varchar','nvarchar');

		$totalcolumns = 0;
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		while($row = mssql_fetch_array($column_query))
		{
			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			echo '<tr>';
			echo('<td align="center" style="background: ' . $bg . '" nowrap><input type="checkbox" name="columns[]" value="' . $row['COLUMN_NAME'] . '"></td>');
			echo('<td style="background: ' . $bg . '" nowrap>' . $row['COLUMN_NAME'] . '</td>');

			if(in_array($row['TYPE_NAME'],$texttypes))
				echo('<td align="center" style="background: ' . $bg . '" nowrap>' . $row['TYPE_NAME'] . '(' . $row['PRECISION'] . ')</td>');
			else
				echo('<td align="center" style="background: ' . $bg . '" nowrap>' . $row['TYPE_NAME'] . '</td>');

			echo('<td style="background: ' . $bg . '" nowrap>' . $row['COLUMN_DEF'] . '</td>');
			echo('<td align="center" style="background: ' . $bg . '" nowrap>' . $row['IS_NULLABLE'] . '</td>');
			echo('<td align="center" style="background: ' . $bg . '" nowrap><a href="table_modify.php?mode=change&table=' . $_GET[table] . '&column=' . urlencode($row['COLUMN_NAME']) . '&step=1">Change</a></td>');
			echo('<td align="center" style="background: ' . $bg . '" nowrap><a href="table_modify.php?mode=drop&table=' . $_GET[table] . '&column=' . urlencode($row['COLUMN_NAME']) . '">Drop</a></td>');
			echo '</tr>';

			$totalcolumns++;
		}
	?>
	
	<tr>
		<td align="right" colspan="2" nowrap>
			Select Columns:
		</td>
		<td align="left" colspan="5">
			&nbsp;&nbsp;<a href="javascript:doCheck(true);">All</a> / <a href="javascript:doCheck(false);">None</a>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="7" style="background: #D0DCE0">
			<a href="table_modify.php?mode=add&table=<?php echo($_GET[table]); ?>&step=1">Create A New Column</a>
		</td>
	</tr>
</table>
<input type="hidden" name="columncount" value="<?php echo($totalcolumns); ?>">
</form>

<?php include('inc/footer.php'); ?>
