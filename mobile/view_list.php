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
?>

<form name="form3" method="post" action="view_list.php">
<table width="<?php echo($_SETTINGS['mobilescreenwidth']); ?>" cellpadding="2" cellspacing="0" style="border: 1px solid">
	<tr>
		<td align="center" colspan="5" style="background: #D0DCE0">
			<b>Views</b>
		</td>
	</tr>
	<tr>
		<td style="background: #D0DCE0">&nbsp;</td>
		<td align="center" style="background: #D0DCE0">
			<b>Name</b>
		</td>
		<td align="center" colspan="3" style="background: #D0DCE0">
			<b>Action</b>
		</td>
	</tr>
	<?php
		$toggle = true;
		$colors = array('#DDDDDD','#CCCCCC');

		$view_query = @mssql_query('sp_help') or die(throwSQLError('unable to retrieve list of stored procedures'));
		while($row = mssql_fetch_assoc($view_query))
		{
			if($row['Object_type'] == 'view' && ($row['Owner'] == 'dbo' || $_SETTINGS['showsysdata']))
			{
				if($toggle)
					$bg = $colors[0];
				else
					$bg = $colors[1];
	
				$toggle = !$toggle;
	
				echo '<tr>';
				echo('<td align="center" style="background: ' . $bg . '"><input type="checkbox" name="views[]" value="' . $row['Name'] . '"></td>');
				echo('<td style="background: ' . $bg . '" nowrap>' . $row['Name'] . '</td>');
				echo('<td align="center" style="background: ' . $bg . '"><a href="table_select.php?view=' . urlencode($row['Name']) . '">Select</a></td>');
				echo('<td align="center" style="background: ' . $bg . '"><a href="view_modify.php?view=' . urlencode($row['Name']) . '">Modify</a></td>');
				echo('<td align="center" style="background: ' . $bg . '"><a href="view_drop.php?view=' . urlencode($row['Name']) . '&returnto=view_list.php">Drop</a></td>');
				echo '</tr>';
	
				unset($row);
			}
		}
	?>
</table>
</form>

<?php include('inc/footer.php'); ?>
