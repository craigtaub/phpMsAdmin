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

if($_POST['query'] == '')
{
	mssql_select_db($_SESSION['database']);

	$params = array();
	$doit = false;

	$_GET['procedure'] = urldecode($_GET['procedure']);

	if(substr_count($_GET['procedure'],' ') > 0)
		$_GET['procedure'] = ('[' . $_GET['procedure'] . ']');

	$data_query = @mssql_query('sp_helptext ' . $_GET['procedure']) or die(throwSQLError('unable to retrieve procedure'));
	if(!@mssql_num_rows($data_query))
	{
		$schema_query = @mssql_query('SELECT SPECIFIC_SCHEMA FROM INFORMATION_SCHEMA.ROUTINES WHERE SPECIFIC_NAME = \'' . $_GET['procedure'] . '\';');
		if($schema_query)
		{
			$schema_array = mssql_fetch_array($schema_query);
			$_GET['procedure'] = ($schema_array['SPECIFIC_SCHEMA'] . '.' . $_GET['procedure']);

			unset($data_query);
			$data_query = @mssql_query('sp_helptext \'' . $_GET['procedure'] . '\'') or die(throwSQLError('unable to retrieve procedure'));

			if(@mssql_num_rows($data_query))
				$doit = true;
		}
	}
	else
		$doit = true;

	if($doit)
	{
		while($row = mssql_fetch_assoc($data_query))
		{
			$val = trim($row['Text']);
	
			if($val[0] == '@')
			{
				$split = explode(' ',$val);
				$params[] = $split[0];
			}
		}
	
		$execline = ('EXECUTE ' . $_GET['procedure']);
	
		foreach($params AS $key => $value)
		{
			$execline .= (' ' . $value . '=\'\'');
		}
	
		$execline = (str_replace('\' @', '\', @', $execline) . ';');
	}
	else
		$execline = 'I am unable to read this stored procedure.';
}
else
	$execline = $_POST['query'];

?>
		<form name="form1" method="post" action="procedure_execute.php">
		<table width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>" cellpadding="2" cellspacing="0" style="border: 1px solid">
			<tr>
				<td align="center" style="background: #D0DCE0">
					<b>Execute Procedure</b>
				</td>
			</tr>
			<tr>
				<td>
					<textarea rows="10" cols="36" name="query" wrap="off"><?php echo($execline); ?></textarea><br>
				</td>
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
	if($_POST['query'] == '')
		include('inc/footer.php');

	mssql_select_db($_SESSION['database']);

	$data_query = @mssql_query($_POST['query']) or die(throwSQLError('unable to complete query'));

	echo '<table width="' . $_SETTINGS['mobilescreenwidth'] . '" cellpadding="2" cellspacing="0" style="border: 1px solid">';

	$toggle = true;
	$colors = array('#DDDDDD','#CCCCCC');

	$isempty = true;
	$fields = array();

	if(mssql_num_rows($data_query) > 0)
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
		echo '<tr><td align="center">No Results Returned</td></tr>';

	echo '</table>';

	include('inc/footer.php');
?>
