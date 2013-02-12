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

if($confirm == 'readonly')
{
	$_SESSION['database'] = 'master';
	mssql_select_db('master');

	$query = ('ALTER DATABASE ' . urldecode($_GET[dbname]) . ' SET SINGLE_USER WITH ROLLBACK IMMEDIATE');
	@mssql_query($query) or die(throwSQLError('unable to lock database',$query));

	$query = ('ALTER DATABASE ' . urldecode($_GET[dbname]) . ' SET READ_ONLY');
	@mssql_query($query) or die(throwSQLError('unable to make database READ-ONLY',$query));

	$query = ('ALTER DATABASE ' . urldecode($_GET[dbname]) . ' SET MULTI_USER');
	@mssql_query($query) or die(throwSQLError('unable to re-enable mutli-user mode on database',$query));

	echo('<meta http-equiv="refresh" content="0;url=home.php">');
}
else if($confirm == 'readwrite')
{
	$_SESSION['database'] = 'master';
	mssql_select_db('master');

	$query = ('ALTER DATABASE ' . urldecode($_GET[dbname]) . ' SET SINGLE_USER');
	@mssql_query($query) or die(throwSQLError('unable to lock database',$query));

	$query = ('ALTER DATABASE ' . urldecode($_GET[dbname]) . ' SET READ_WRITE');
	@mssql_query($query) or die(throwSQLError('unable to make database READ-WRITE',$query));

	$query = ('ALTER DATABASE ' . urldecode($_GET[dbname]) . ' SET MULTI_USER');
	@mssql_query($query) or die(throwSQLError('unable to re-enable mutli-user mode on database',$query));

	echo('<meta http-equiv="refresh" content="0;url=home.php">');
}
else
{
	$status_query = @mssql_query('sp_helpdb') or die(throwError('unable to retrive database information'));
	while($row = mssql_fetch_array($status_query))
	{
		if($row['name'] == $_GET['dbname'])
		{
			$status = explode(', ',$row['status']);
			foreach($status AS $value)
			{
				$split = explode('=',$value);
				if($split[0] == 'Updateability')
				{
					if($split[1] == 'READ_WRITE')
						$newmode = 'READ_ONLY';
					else
						$newmode = 'READ_WRITE';
				}
			}
		}
	}
?>

<form name="form1" method="post" action="database_mode.php?dbname=<?php echo($_GET['dbname']); ?>">
<input type="hidden" name="dbname" value="<?php echo($_GET['dbname']); ?>">
<table cellpadding="3" cellspacing="3" width="300" style="border: 1px solid">
	<tr>
		<td align="center" colspan="2" style="background: #D0DCE0; font-weight: bold">Confirm Mode Change</td>
	</tr>
	<tr>
		<td colspan="2" style="border: 1px solid; background: #DDDDDD">
			Are you absolutely sure you wish to switch the database "<?php echo(urldecode($_GET['dbname'])); ?>" into <?php echo(str_replace('_','-',$newmode)); ?> mode? Doing so will immediately rollback all transactions and disconnect all users. If you are sure, type "<?php echo(str_replace('_','',strtolower($newmode))); ?>" (without the quotes) in the box below and click "Confirm".
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