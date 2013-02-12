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
		$_GET['table'] = urlencode($_POST['table']);
?>

<form name="form1" method="post" action="table_querybuilder.php">
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
			<b>Comparison</b>
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

		$cols = array();
		$counter = 0;
		$toggle = true;
		$colors = array('#CCCCCC','#DDDDDD');

		while($row = mssql_fetch_array($column_query))
		{
			$cols[] = $row['COLUMN_NAME'];

			if($toggle)
				$bg = $colors[0];
			else
				$bg = $colors[1];

			$toggle = !$toggle;

			echo '<tr>';

			echo('<input type="hidden" name="field[' . $counter . ']" value="' . $row['COLUMN_NAME'] . '">');
			echo('<input type="hidden" name="type[' . $counter . ']" value="' . $row['TYPE_NAME'] . '">');

			echo('<td style="background: ' . $bg . '">' . $row['COLUMN_NAME'] . '</td>');
			echo('<td align="center" style="background: ' . $bg . '" nowrap>' . $row['TYPE_NAME'] . '(' . $row['PRECISION'] . ')</td>');

			echo('<td align="center" style="background: ' . $bg . '">');

			if(!empty($_POST['comparison'][$counter]))
				$comparison = $_POST['comparison'][$counter];
			else
				$comparison = '';

			echo('<select name="comparison[' . $counter . ']">');

			switch($comparison)
			{
				case('='):$equal = 'selected';break;
				case('>='):$gequal = 'selected';break;
				case('<='):$lequal = 'selected';break;
				case('LIKE'):$like = 'selected';break;
				default:$blank = 'selected';
			}

			echo('<option value="" ' . $blank . '>&nbsp;</option>');

			echo('<option value="=" ' . $equal . '>=</option>');
			echo('<option value=">=" ' . $gequal . '>>=</option>');
			echo('<option value="<=" ' . $lequal . '><=</option>');
			echo('<option value="LIKE" ' . $like . '>LIKE</option>');
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

			unset($equal,$gequal,$lequal,$like,$blank);
		}
	?>
	<tr>
		<td align="center" colspan="4" style="background: #D0DCE0">
			<b>Options</b>
		</td>
	</tr>
	<tr>
		<td align="right" style="font-weight: bold; background: #CCCCCC">
			Order By:
		</td>
		<td colspan="3" align="center" style="background: #CCCCCC">
			<select name="orderbyfield">
				<option value=""></option>
				<?php
					if(!empty($_POST['orderbyfield']))
						$orderfield = $_POST['orderbyfield'];
					else
						$orderfield = '';

					if(!empty($_POST['orderbyorder']))
						$orderbyorder = $_POST['orderbyorder'];
					else
						$orderbyorder = '';

					foreach($cols AS $value)
					{
						if($orderfield != $value)
							echo('<option value="' . $value . '">' . $value . '</option>');
						else
							echo('<option value="' . $value . '" selected>' . $value . '</option>');
					}
				?>
			</select>
			&nbsp;
			<select name="orderbyorder">
				<option value="ASC">Ascending</option>
				<option value="DESC" <?php if($orderbyorder == 'DESC') echo 'selected'; ?>>Descending</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="4" style="background: #D0DCE0">
			<input type="submit" value="Build/Run Query">
		</td>
	</tr>
	<tr>
		<td colspan="4" style="background: #D0DCE0">
			You must select a comparison for each field. Any field that does not have a comparison selected will be omitted from the query. If you choose to use the "LIKE" comparison, you may use either "*" or "%" (both without quotes) as your wildcard operator. Note that a "LIKE" without a wildcard operator in the search value is equivalent to an "=" comparison.
		</td>
	</tr>
</table>
</form>

<br><br>

<?php
	if(!empty($_POST['table']))
	{
		@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

		$totalcount = count($_POST['field']);
		$query = ('SELECT * FROM ' . $_POST['table'] . ' WHERE');

		for($counter = 0; $counter < $totalcount; $counter++)
		{
				if(!empty($_POST['comparison'][$counter]))
					if($_POST['comparison'][$counter] == '=')
						$query .= (' AND ' . $_POST['field'][$counter] . ' = \'' . $_POST['value'][$counter] . '\'');
					else if($_POST['comparison'][$counter] == 'LIKE')
						$query .= (' AND ' . $_POST['field'][$counter] . ' LIKE \'' . str_replace('*','%',$_POST['value'][$counter]) . '\'');
					else
						$query .= (' AND ' . $_POST['field'][$counter] . ' ' . $_POST['comparison'][$counter] . ' ' . $_POST['value'][$counter]);
		}

		$query = str_replace('WHERE AND','WHERE',$query);

		// BEGIN IDENTITY ISOLATION CODE
		$table = $_POST['table'];

		$tablesep = explode('.',$table);

		if(count($tablesep) > 1)
		{
			$colquery = ('sp_columns @table_name = N\'' . $tablesep[1] . '\'');
			$colquery .= (', @table_owner = N\'' . $tablesep[0] . '\'');
		}
		else
			$colquery = ('sp_columns @table_name = N\'' . $tablesep[0] . '\'');

		$column_query = @mssql_query($colquery) or throwSQLError('unable to retrieve column data');
		while($row = mssql_fetch_array($column_query))
		{
			if(substr_count($row['TYPE_NAME'],'identity') > 0)
				$idcol = $row['COLUMN_NAME'];
		}
		// END IDENTITY ISOLATION CODE

		if(substr($query,-6) == ' WHERE')
			$query = substr($query,0,-6);

		if(!empty($_POST['orderbyfield']))
			$query .= (' ORDER BY ' . $_POST['orderbyfield'] . ' ' . $_POST['orderbyorder']);

		$data_query = @mssql_query($query) or throwSQLError('unable to complete query',$query);

		if($data_query)
		{
			echo '<table width="300" cellpadding="3" cellspacing="3" style="border: 1px solid">';

			$toggle = true;
			$colors = array('#DDDDDD','#CCCCCC');

			$isempty = true;
			$fields = array();

			while($row = mssql_fetch_array($data_query))
			{
				if($isempty)
					$isempty = false;

				if($toggle)
					$bg = $colors[0];
				else
					$bg = $colors[1];

				$toggle = !$toggle;

				$fieldcount = count($fields);

				if($fieldcount == 0)
				{
					$toggleskip = true;

					echo '<tr>';

					$colcounter = 0;
					$numericidcol = -1;

					foreach($row AS $key => $value)
					{
						if(!$toggleskip)
						{
							echo('<td align="center" style="background: #D0DCE0"><b>' . $key . '</b></td>');
							$fields[] = $key;

							if(!empty($idcol))
								if($key == $idcol)
									$numericidcol = $colcounter;

							$colcounter++;
						}

						$toggleskip = !$toggleskip;
					}

					$fieldcount = count($fields);

					echo '</tr>';
				}

				echo '<tr>';

				for($counter = 0; $counter < $fieldcount; $counter++)
				{
					if($row[$counter] == '')
						$row[$counter] = '&nbsp;';
					else
					{
						$row[$counter] = str_replace('<','&#60;',$row[$counter]);
						$row[$counter] = str_replace('>','&#62;',$row[$counter]);
					}

					if($counter != $numericidcol)
						echo('<td style="background:' . $bg . '" nowrap>' . $row[$counter] . '</td>');
					else
						echo('<td style="background:' . $bg . '" nowrap><a href="table_row_modify.php?table=' . urlencode($table) . '&col=' . urlencode($idcol) . '&id=' . urlencode($row[$counter]) . '">' . $row[$counter] . '</a></td>');
				}

				echo '</tr>';
			}

			if($isempty)
				echo '<tr><td align="center">Table Is Empty</td></tr>';

			echo '</table>';
		}
	}
?>

<?php include('inc/footer.php'); ?>