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

if(!empty($_GET['view']))
{
	$schema_query = @mssql_query('SELECT TABLE_SCHEMA FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = \'' . urldecode($_GET['view']) . '\';');
	if($schema_query)
	{
		$schema_array = mssql_fetch_array($schema_query);

		if($schema_array['TABLE_SCHEMA'] != 'dbo')
			$destination = ($schema_array['TABLE_SCHEMA'] . '.' . urldecode($_GET['view']));
		else
			$destination = urldecode($_GET['view']);
	}
	else
		$destination = urldecode($_GET['view']);
}
else
{
	if(!empty($_GET['table']))
		$destination = urldecode($_GET['table']);
	else
		$destination = '';
}

if(substr_count($destination,' ') > 0 || substr_count($destination,'.') > 0)
	$destination = ('[' . $destination . ']');

if(empty($_POST['query']))
{
	$_POST['query'] = ('SELECT * FROM ' . $destination . ';');
	$default = true;
}
else
	$default = false;

?>
		<form name="form1" method="post" action="table_select.php">
		<table cellpadding="3" cellspacing="3" style="border: 1px solid">
			<tr>
				<td align="center" style="background: #D0DCE0">
					<b>Run Manual Query</b>
				</td>
			</tr>
			<tr>
				<td>
					<textarea rows="10" cols="60" name="query" wrap="off"><?php echo($_POST['query']); ?></textarea><br>
				</td>
			</tr>
			<tr>
				<td align="center" style="background: #D0DCE0">
					<input type="submit" value="Run Query">
				</td>
			</tr>
		</table>
		</form>
		<br>

		<script language="javascript">
			document.form1.query.focus();
		</script>
<?php
	if(!$default)
	{
		// BEGIN IDENTITY ISOLATION CODE
		if(substr_count($_POST['query'],'FROM') > 0)
		{
			if(substr($_POST['query'],-1) == ';')
				$table = substr($_POST['query'],0,-1);
			else
				$table = $_POST['query'];
	
			$table = substr($table,(strpos($table,'FROM ')+5));
	
			if(substr_count($table,' ') > 0)
				$table = substr($table,0,strpos($table,' '));

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
		}
		// END IDENTITY ISOLATION CODE

		$data_query = @mssql_query($_POST['query']) or throwSQLError('unable to complete query');

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

	include('inc/footer.php');
?>
