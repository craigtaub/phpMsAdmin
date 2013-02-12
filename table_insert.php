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

if(!empty($_POST['table']))
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

	$result = @mssql_query($query);

	if($result)
	{
		echo('<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . urlencode($_SESSION['database']) . '">');
		include('inc/footer.php');
	}
	else
	{
		throwSQLError('unable to complete insert',$query);
		$_GET['table'] = urlencode($_POST['table']);
	}
}

?>

<form name="form1" method="post" action="table_insert.php">
<input type="hidden" name="table" value="<?php echo(urldecode($_GET['table'])); ?>">
<table width="350" cellpadding="3" cellspacing="3" style="border: 1px solid">
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

			echo('<td style="background: ' . $bg . '">' . $row['COLUMN_NAME'] . '</td>');
			echo('<td align="center" style="background: ' . $bg . '" nowrap>' . $row['TYPE_NAME'] . '(' . $row['PRECISION'] . ')</td>');

			echo('<td align="center" style="background: ' . $bg . '">');

			if(!empty($_POST['function'][$counter]))
				$func = $_POST['function'][$counter];
			else
				$func = '';

			echo('<select name="function[' . $counter . ']" ' . $status . '>');

			switch($func)
			{
				case('md5'):$md5 = 'selected';break;
				case('sha1'):$sha1 = 'selected';break;
				default:$blank = 'selected';break;
			}

			echo('<option value="" ' . $blank . '>&nbsp;</option>');

			echo('<option value="md5" ' . $md5 . '>MD5</option>');
			echo('<option value="sha1" ' . $sha1 . '>SHA1</option>');
			echo '</select>';
			echo '</td>';

			if(!empty($_POST['value'][$counter]))
				$val = $_POST['value'][$counter];
			else
				$val = '';

			if(in_array($row['TYPE_NAME'],$texttypes))
				echo('<td align="center" style="background: ' . $bg . '"><input name="value[' . $counter . ']" size="20" value="' . $val . '" maxlength="' . $row['PRECISION'] . '" ' . $status . '></td>');
			else
				echo('<td align="center" style="background: ' . $bg . '"><input name="value[' . $counter . ']" size="20" value="' . $val . '" ' . $status . '></td>');

			echo '</tr>';

			$counter++;

			unset($md5,$sha1,$blank);
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
