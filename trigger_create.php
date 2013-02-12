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

if(!empty($_POST['query']))
{
	if(!@mssql_query($_POST['query']))
		throwSQLError('unable to create trigger');
	else
	{
		echo '<meta http-equiv="refresh" content="0;url=database_properties.php">';
		include('inc/footer.php');
	}
}
else
	$_POST['query'] = 'CREATE TRIGGER NewTrig;';

?>
		<form name="form1" method="post" action="trigger_create.php">
		<table cellpadding="3" cellspacing="3" style="border: 1px solid">
			<tr>
				<td align="center" style="background: #D0DCE0">
					<b>Create Trigger</b>
				</td>
			</tr>
			<tr>
				<td>
					<textarea rows="10" cols="60" name="query" wrap="off"><?php echo($_POST['query']); ?></textarea><br>
				</td>
			</tr>
			<tr>
				<td align="center" style="background: #D0DCE0">
					<input type="submit" value="Create">
				</td>
			</tr>
		</table>
		</form>

		<script language="javascript">
			document.form1.query.focus();
		</script>
<?php include('inc/footer.php'); ?>