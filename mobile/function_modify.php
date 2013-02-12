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

if($_POST['query'] != '')
{
	$data_query = @mssql_query($_POST['query']) or die(throwSQLError('unable to save function'));

	if($data_query)
		throwSuccess('function saved');
}
else
{
	mssql_select_db($_SESSION['database']);

	$lines = array();

	$data_query = @mssql_query('sp_helptext \'' . urldecode($_GET['function']) . '\'') or die(throwSQLError('unable to retrieve function'));
	if($data_query)
	{
		while($row = mssql_fetch_array($data_query))
			$lines[] = str_replace('CREATE','ALTER',$row['Text']);
	}
	else
		$lines[] = 'I am unable to read this stored procedure.';
}

?>
		<form name="form1" method="post" action="function_modify.php?function=<?php echo($_GET['function']); ?>">
		<table width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>" cellpadding="2" cellspacing="0" style="border: 1px solid">
			<tr>
				<td align="center" style="background: #D0DCE0">
					<b>Modify Function</b>
				</td>
			</tr>
			<tr>
				<td>
					<textarea rows="10" cols="36" name="query" wrap="off"><?php echo(implode('',$lines)); ?></textarea><br>
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
<?php include('inc/footer.php'); ?>