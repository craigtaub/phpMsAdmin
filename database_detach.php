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

if(!empty($_POST['confirm']))
	$confirm = $_POST['confirm'];
else
	$confirm = '';

if($confirm == 'detach')
{
	$_SESSION['database'] = 'master';
	mssql_select_db('master');

	$result = @mssql_query('sp_detach_db \'' . urldecode($_POST[dbname]) . '\',\'false\'') or die(throwSQLError('unable to detach database'));

	if($result)
	{
		echo '<script language="javascript">parent.left.location.reload();</script>';
		echo('<meta http-equiv="refresh" content="0;url=home.php">');
	}
}
else
{
?>

<form name="form1" method="post" action="database_detach.php?dbname=<?php echo($_GET['dbname']); ?>">
<input type="hidden" name="dbname" value="<?php echo($_GET['dbname']); ?>">
<table cellpadding="3" cellspacing="3" width="300" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0; font-weight: bold">Confirm Detachment</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid; background: #DDDDDD">
			Are you absolutely sure you wish to detach the database "<?php echo(urldecode($_GET['dbname'])); ?>"? If you are, type "detach" (without the quotes) in the box below and click "Confirm".
		</td>
	</tr>
	<tr>
		<td align="right">
			<b>Confirm:</b>
		</td>
		<td>
			<input name="confirm" size="15" maxlength="15">
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2" style="background: #D0DCE0">
			<input type="submit" value="Confirm">
		</td>
	</tr>
</table>
</form>

<script language="javascript">
document.form1.confirm.focus();
</script>

<?php
}
include('inc/footer.php');
?>
