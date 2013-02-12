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

if($_GET['view'] != '')
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
	$destination = urldecode($_GET['table']);
}

if(substr_count($destination,' ') > 0)
	$destination = ('[' . $destination . ']');

if($_POST['query'] == '')
{
	?>
		<form name="form1" method="post" action="table_select.php">
		<table width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>" cellpadding="2" cellspacing="0">
			<tr>
				<td align="center" style="background: #D0DCE0">
					<b>Run Manual Query</b>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center">
					<textarea rows="10" cols="36" name="query" wrap="off">SELECT * FROM <?php echo($destination); ?>;</textarea><br>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center" style="background: #D0DCE0">
					<input type="submit" value="Run Query">
				</td>
			</tr>
		</table>
		</form>

		<script language="javascript">
			document.form1.query.focus();
		</script>
	<?php

	include('inc/footer.php');
}

$data_query = @mssql_query($_POST['query']) or die(throwSQLError('unable to complete query'));

echo('<table width="' . $_SETTINGS['mobilescreenwidth'] . '" cellpadding="2" cellspacing="0" style="border: 1px solid">');

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

		foreach($row AS $key => $value)
		{
			if(!$toggleskip)
			{
				echo('<td align="center" style="background: #D0DCE0"><b>' . $key . '</b></td>');
				$fields[] = $key;
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

		echo('<td style="background:' . $bg . '" nowrap>' . $row[$counter] . '</td>');
	}

	echo '</tr>';
}

if($isempty)
	echo '<tr><td align="center">Table Is Empty</td></tr>';

echo '</table>';

include('inc/footer.php');

?>
