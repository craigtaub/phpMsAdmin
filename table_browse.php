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

@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

// BEGIN IDENTITY ISOLATION CODE
$tablesep = explode('.',urldecode($_GET['table']));

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

if(!empty($_GET['showall']))
{
	if(substr_count(urldecode($_GET['table']),'.') > 0)
		$data_query = @mssql_query('SELECT TOP 100 * FROM ' . urldecode($_GET['table'])) or die(throwSQLError('unable to complete query'));
	else
		$data_query = @mssql_query('SELECT TOP 100 * FROM [' . urldecode($_GET['table']) . ']') or die(throwSQLError('unable to complete query'));
}
else
{
	if(substr_count(urldecode($_GET['table']),'.') > 0)
		$data_query = @mssql_query('SELECT * FROM ' . urldecode($_GET['table'])) or die(throwSQLError('unable to complete query'));
	else
		$data_query = @mssql_query('SELECT * FROM [' . urldecode($_GET['table']) . ']') or die(throwSQLError('unable to complete query'));
}

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
			echo('<td style="background:' . $bg . '" nowrap><a href="table_row_modify.php?table=' . $_GET['table'] . '&col=' . urlencode($idcol) . '&id=' . urlencode($row[$counter]) . '">' . $row[$counter] . '</a></td>');
	}

	echo '</tr>';
}

if($isempty)
	echo '<tr><td align="center">Table is Empty</td></tr>';
else
{
	if(!empty($_GET['showall']))
		echo('<tr><td align="left" colspan="' . $fieldcount . '" style="background: #D0DCE0"><a href="table_browse.php?table=' . $_GET['table'] . '">Show Top 100 Rows</a></td></tr>');
	else
		echo('<tr><td align="left" colspan="' . $fieldcount . '" style="background: #D0DCE0"><a href="table_browse.php?table=' . $_GET['table'] . '&showall=yes">Show All Rows</a></td></tr>');
}

echo '</table>';

include('inc/footer.php');

?>
