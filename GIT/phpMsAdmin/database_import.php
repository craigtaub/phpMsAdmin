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

if(!empty($_FILES['sql']['tmp_name']))
{
	$data = file($_FILES['sql']['tmp_name']);
	$datacount = count($data);
	$errorcount = 0;
	$successcount = 0;
	//$querys = array();

	for($counter = 0; $counter < $datacount; $counter++)
	{
		$query = $data[$counter];

		if(!empty($_POST['base64']) && substr_count($query,'VALUES') > 0)
		{
			$sep = explode('VALUES',$query);
			$sep = explode(',',substr($sep[1],(strpos($sep[1],'(')+1),-3));

			foreach($sep AS $value)
			{
				$value = str_replace('\'','',$value);

				if(!is_numeric($value))
					$query = str_replace($value,base64_decode($value),$query);
			}
		}

		//$querys[] = $query;
		//$result = true;

		$result = @mssql_query($query) or throwSQLError('unable to execute query',$query);

		if(!$result)
			$errorcount++;
		else
			$successcount++;
	}

	throwSuccess('Queries executed successfully: ' . $successcount);
	throwFailure('Queries executed with failure: ' . $errorcount);

	//print_r($querys);

	include('inc/footer.php');
}

?>

<form name="form1" method="post" enctype="multipart/form-data" action="database_import.php">
<table width="400" cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0">
			<b>Export Options</b>
		</td>
	</tr>
	<tr>
		<td align="right" style="background: #CCCCCC" valign="top" nowrap>
			SQL File:
		</td>
		<td style="background: #CCCCCC" nowrap>
			<input type="file" name="sql">
		</td>
	</tr>
	<tr>
		<td align="right" style="background: #CCCCCC" valign="top" nowrap>
			Method:
		</td>
		<td style="background: #CCCCCC" nowrap>
			<input type="checkbox" name="base64" value="yes"> Base-64 Decode Strings
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2" style="background: #D0DCE0">
			<input type="submit" value="Import">
		</td>
	</tr>
</table>
</form>

<?php include('inc/footer.php'); ?>