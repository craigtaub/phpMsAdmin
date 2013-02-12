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

if(empty($_POST['delimiter']))
{
	echo '<form name="form1" method="post" action="table_export.php">';

	foreach($_POST['tables'] AS $row)
	{
		echo('<input type="hidden" name="tables[]" value="' . $row . '">');
	}
?>

<table cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Export Table Data</b>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap>
			<b>Field Delimiter:</b>
		</td>
		<td>
			<input name="delimiter" size="5" maxlength="10" value=",">
		</td>
	</tr>
	<tr>
		<td align="right" nowrap>
			<b>Field Quoted With:</b>
		</td>
		<td>
			<select name="fieldquote">
				<option value="single" selected>'</option>
				<option value="double">"</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="2">
			<input type="submit" value="Export">
		</td>
	</tr>
</table>
</form>

<script language="javascript">
document.form1.delimiter.focus();
</script>

<?php

}
else
{
	@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

     if($_POST['fieldquote'] == 'single')
          $quote = '\'';
     else
          $quote = '"';

     echo '<form name="form1" method="post" action="table_export_download.php">';
     echo '<textarea name="data" rows="30" cols="75">';

	$tablecount = count($_POST['tables']);
	for($counter = 0; $counter < $tablecount; $counter++)
	{
		$table_query = @mssql_query('SELECT * FROM ' . $_POST['tables'][$counter] . ';') or die(throwSQLError('unable to retrieve table data'));
		while($row = mssql_fetch_array($table_query))
		{
			if(!isset($schema))
			{
				$schema = array();

				foreach($row AS $key => $value)
					if(!is_int($key))
						$schema[] = $key;
			}

			$values = array();
			foreach($schema AS $col)
				if($quote == '\'')
					$values[] = '\'' . str_replace('\'','\'\'',$row[$col]) . '\'';
				else
					$values[] = '"' . $row[$col] . '"';

			echo(implode(',',$values) . "\n");

			unset($values);
		}

		unset($table_query,$row,$schema);
	}

	echo '</textarea>';
	echo '<br><br><input type="submit" value="Save to File">';
	echo '</form>';
}

include('inc/footer.php');

?>
