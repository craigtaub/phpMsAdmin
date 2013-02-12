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

$texttypes = array('binary','char','nchar','varchar','nvarchar');

if($_POST['table'] != '')
{
	@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

	$totalcount = count($_POST['field']);
	$query = 'INSERT INTO ' . $_POST['table'];

	$fields = array();
	$values = array();

	for($counter = 0; $counter < $totalcount; $counter++)
	{
		if(substr_count($_POST['type'][$counter],'identity') == 0)
		{
			$fields[] = $_POST['field'][$counter];

			if(!in_array($_POST['type'][$counter],$texttypes) && $_POST['value'][$counter] != '')
				$values[] = $_POST['value'][$counter];
			else
			{
			     if($_POST['function'][$counter] == 'md5')
			            $_POST['value'][$counter] = md5($_POST['value'][$counter]);
			     else if($_POST['function'][$counter] == 'sha1')
			            $_POST['value'][$counter] = sha1($_POST['value'][$counter]);

                    $_POST['value'][$counter] = str_replace('\'','\'\'',$_POST['value'][$counter]);
				$values[] = '\'' . $_POST['value'][$counter] . '\'';
			}
		}
	}

	$query .= ' (' . implode(',',$fields) . ') VALUES (' . implode(',',$values) . ');';

	@mssql_query($query) or die(throwSQLError('unable to complete insert'));

	echo('<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . urlencode($_SESSION['database']) . '">');

	include('inc/footer.php');
}

?>

<form name="form1" method="post" action="table_insert.php">
<input type="hidden" name="table" value="<?php echo "$_GET[table]"; ?>">
<table width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>" cellpadding="2" cellspacing="0" style="border: 1px solid">
	<tr>
		<td align="center" style="background: #D0DCE0">
			<b>Field</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Type</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Function</b>
		</td>
		<td align="center" style="background: #D0DCE0">
			<b>Value</b>
		</td>
	</tr>
	<?php
		mssql_select_db($_SESSION['database']);

          $_GET['table'] = urldecode($_GET['table']);

		if(substr_count($_GET['table'],'.') > 0)
		{
			$tablesep = explode('.',$_GET['table']);
			$query = ('sp_columns @table_name = N\'' . $tablesep[1] . '\'');
			$query .= (', @table_owner = N\'' . $tablesep[0] . '\'');
		}
		else
		{
			$query = ('sp_columns @table_name = N\'' . $_GET['table'] . '\'');
		}

		$column_query = @mssql_query($query) or die(throwSQLError('unable to retrieve column data'));

		$counter = 0;
		$toggle = true;
		$colors = array('#CCCCCC','#DDDDDD');

		while($row = mssql_fetch_array($column_query))
		{
			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			if(substr_count($row['TYPE_NAME'],'identity') > 0)
				$status = 'disabled readonly';
			else
				$status = '';

			echo '<tr>';

			echo('<input type="hidden" name="field[' . $counter . ']" value="' . $row['COLUMN_NAME'] . '">');
			echo('<input type="hidden" name="type[' . $counter . ']" value="' . $row['TYPE_NAME'] . '">');

			echo("<td style=\"background: $bg\">" . $row['COLUMN_NAME'] . '</td>');
			echo("<td align=\"center\" style=\"background: $bg\" nowrap>" . $row['TYPE_NAME'] . '(' . $row['PRECISION'] . ')</td>');

			echo "<td align=\"center\" style=\"background: $bg\">";
			echo "<select name=\"function[$counter]\" $status>";
			echo '<option value="" selected>&nbsp;</option>';
			echo '<option value="md5">MD5</option>';
			echo '<option value="sha1">SHA1</option>';
			echo '</select>';
			echo '</td>';

			if(in_array($row['COLUMN_TYPE'],$texttypes))
				echo("<td align=\"center\" style=\"background: $bg\"><input name=\"value[$counter]\" size=\"20\" maxlength=\"" . $row['PRECISION'] . "\" $status></td>");
			else
				echo("<td align=\"center\" style=\"background: $bg\"><input name=\"value[$counter]\" size=\"20\" $status></td>");

			echo '</tr>';

			$counter++;
		}
	?>
	<tr>
		<td align="center" colspan="4" style="background: #D0DCE0">
			<input type="submit" value="Insert Row">
		</td>
	</tr>
</table>
</form>

<?php include('inc/footer.php'); ?>
