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
<?php include('inc/header.php'); ?>

<table cellpadding="3" cellspacing="3" style="border: 1px solid">
	<tr>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Username</b>&nbsp;
		</td>
		<td align="center" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Default DB</b>&nbsp;
		</td>
		<td align="center" colspan="2" style="background: #D0DCE0" nowrap>
			&nbsp;<b>Actions</b>&nbsp;
		</td>
	</tr>

<?php

$toggle = true;
$colors = array('#DDDDDD','#CCCCCC');
$skiplist = array('##MS_AgentSigningCertificate##','NT AUTHORITY\NETWORK SERVICE','NT AUTHORITY\SYSTEM','sa');

$login_query = @mssql_query('sp_helplogins;') or die(throwSQLError('unable to retrieve user list'));

while($row = mssql_fetch_array($login_query))
{
    if($row['AUser'] == 'yes' && substr_count($row['LoginName'],'$') == 0 && !in_array($row['LoginName'],$skiplist))
    {
	   if($toggle)
		  $bg = $colors[0];
	   else
		  $bg = $colors[1];

	   $toggle = !$toggle;

	   echo '<tr>';
	   echo('<td style="background: ' . $bg . '" nowrap>&nbsp;' . $row['LoginName'] . '&nbsp;</td>');
	   echo('<td style="background: ' . $bg . '" nowrap>&nbsp;' . $row['DefDBName'] . '&nbsp;</td>');
	   echo('<td style="background: ' . $bg . '" nowrap>&nbsp;<a href="users_modify.php?user=' . urlencode($row['LoginName']) . '">Modify</a>&nbsp;</td>');
	   echo('<td style="background: ' . $bg . '" nowrap>&nbsp;<a href="users_drop.php?user=' . urlencode($row['LoginName']) . '">Drop</a>&nbsp;</td>');
	   echo '</tr>';
    }
}

?>
	<tr>
		<td align="center" colspan="4" style="background: #D0DCE0">
			<a href="users_create.php">Create User</a>
		</td>
	</tr>
</table>

<?php include('inc/footer.php'); ?>
