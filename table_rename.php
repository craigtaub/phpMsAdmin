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

if(!empty($_POST['newname']))
{
	mssql_select_db($_SESSION['database']);

	$query = ('sp_rename \'' . $_POST['oldname'] . '\', \'' . $_POST['newname'] . '\'');
	$result = @mssql_query($query) or throwSQLError('unable to renable table',$query);

	if($result)
	{
		echo '<script language="javascript">parent.left.location.reload();</script>';
		echo('<meta http-equiv="refresh" content="0;url=database_properties.php">');
		exit;
	}
}
else
{
?>

<form name="form1" method="post" action="table_rename.php">
<input type="hidden" name="oldname" value="<?php echo(urldecode($_GET['table'])); ?>">
<table cellpadding="3" cellspacing="3" width="300" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0; font-weight: bold">Rename Table</td>
	</tr>
	<tr>
		<td align="right" style="background: #DDDDDD">
			<b>Old Name:</b>
		</td>
		<td style="background: #DDDDDD">
			<?php echo(urldecode($_GET['table'])); ?>
		</td>
	</tr>
	<tr>
		<td align="right" style="background: #DDDDDD">
			<b>New Name:</b>
		</td>
		<td style="background: #DDDDDD">
			<input name="newname" size="15" maxlength="255">
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2" style="background: #D0DCE0">
			<input type="submit" value="Save">
		</td>
	</tr>
</table>
</form>

<script language="javascript">
document.form1.newname.focus();
</script>

<?php
}
include('inc/footer.php');
?>