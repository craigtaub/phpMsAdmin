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

$db_info_query = @mssql_query('sp_helpdb') or die(throwSQLError('unable to retrieve databases'));

$dbinfo = array();

while($row = mssql_fetch_array($db_info_query))
	if(!in_array($row['name'],$_SETTINGS['dbexclude']))
		$dbinfo[] = $row['name'] . ':' . number_format($row['db_size'],2);

?>

<table width="90%">
	<tr>
		<td align="left" valign="top" width="45%">
			<form name="form1" method="post" action="database_create.php?step=1">
			<table cellpadding="5" cellspacing="0" width="100%" style="border: 1px solid; border-color: black">
				<tr>
					<td align="center" colspan="2" style="background: #D0DCE0; border-bottom: 1px solid">
						<b>Create/Attach DB</b>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td align="right" nowrap>
						<b>Name:</b>
					</td>
					<td nowrap>
						<input name="dbname" size="15" maxlength="50">&nbsp;&nbsp;<input type="submit" value="Create/Attach">
					</td>
				</tr>
				<tr>
					<td align="center" colspan="2" style="background: #D0DCE0; border-top: 1px solid">
						<b>Optional Settings</b>
					</td>
				</tr>
				<tr>
					<td align="right" style="border-top: 1px solid">
						<b>MDF:</b>
					</td>
					<td style="border-top: 1px solid">
						<input name="mdf" size="15" maxlength="500">
					</td>
				</tr>
				<tr>
					<td align="right" style="border-top: 1px solid">
						<b>LDF:</b>
					</td>
					<td style="border-top: 1px solid">
						<input name="ldf" size="15" maxlength="500">
					</td>
				</tr>
			</table>
			</form>
		</td>
		<td width="10%">
			&nbsp;
		</td>
		<td align="right" valign="top" width="45%">
			<table cellpadding="5" cellspacing="0" width="100%" style="border: 1px solid; border-color: black">
				<tr>
					<td align="center" style="background: #D0DCE0; border-bottom: 1px solid; border-color: black">
						<b>Create User</b>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td align="center" nowrap>
						<form name="form2" method="post" action="users_create.php">
						<b>Name:</b>&nbsp;&nbsp;<input name="newusername" size="15" maxlength="50">&nbsp;&nbsp;&nbsp;
						<input type="submit" value="Create">
						</form>
					</td>
				</tr>
				<tr>
					<td align="center" style="border-top: 1px solid">
						<a href="users_list.php">Manage Existing Users</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" colspan="2" width="55%">
			<table cellpadding="5" cellspacing="0" width="100%" style="border: 1px solid; border-color: black">
				<tr>
					<td align="center" colspan="3" style="background: #D0DCE0; border-bottom: 1px solid; border-color: black">
						<b>Database Info</b>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center" style="border-bottom: 1px solid">
						<b>Name</b>
					</td>
					<td align="center" style="border-bottom: 1px solid; border-left: 1px solid">
						<b>Size</b>
					</td>
				</tr>
				<?php
					$total = 0;

					foreach($dbinfo AS $row)
					{
						$row = explode(':',$row);
						$total += str_replace(',','',$row[1]);

						if(str_replace(',','',$row[1]) >= 1000000)
						{
							$row[1] = number_format((str_replace(',','',$row[1]) / 1000000),2);
							$unit = 'TB';
						}
						else if(str_replace(',','',$row[1]) >= 1000)
						{
							$row[1] = number_format((str_replace(',','',$row[1]) / 1000),2);
							$unit = 'GB';
						}
						else
							$unit = 'MB';

						echo '<tr>';
						echo('<td style="border-bottom: 1px dashed" nowrap>' . $row[0] . '</td><td align="right" style="border-bottom: 1px dashed" nowrap><font size="-2">(<a href="database_detach.php?dbname=' . urlencode($row[0]) . '">DETACH</a>)&nbsp;&nbsp;(<a href="database_drop.php?dbname=' . urlencode($row[0]) . '">DROP</a>)</font></td>');
						echo('<td style="border-left: 1px solid" nowrap>&nbsp;' . $row[1] . ' ' . $unit . '</td>');
						echo '</tr>';
					}
				?>
				<tr>
					<td colspan="2" align="right">
						<b>Total Size:</b>
					</td>
					<td align="left" style="border-top: 1px solid">
						<?php
							if($total >= 1000000)
								echo(number_format(($total / 1000000),2) . ' TB');
							else if($total >= 1000)
								echo(number_format(($total / 1000),2) . ' GB');
							else
								echo "$total MB";
						?>
					</td>
				</tr>
			</table>
		</td>
		<td valign="top" width="45%">
			<form name="form3" method="post" action="table_create.php">
			<table cellpadding="5" cellspacing="0" width="100%" style="border: 1px solid; border-color: black">
				<tr>
					<td align="center" colspan="2" style="background: #D0DCE0; border-bottom: 1px solid; border-color: black">
						<b>Create Table</b>
					</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td align="right">
						<b>Database:</b>
					</td>
					<td align="left">
						<select name="database">
							<?php
								foreach($dbinfo AS $row)
								{
									$row = explode(':',$row);

									echo('<option value="' . $row[0] . '">' . $row[0] . '</option>');
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap>
						<b>Table Name:</b>
					</td>
					<td align="left">
						<input name="table" size="15" maxlength="50">
					</td>
				</tr>
				<tr>
					<td align="right" nowrap>
						<b>Columns:</b>
					</td>
					<td align="left">
						<input name="columns" size="3" maxlength="3">
					</td>
				</tr>
				<tr>
					<td align="center" colspan="2">
						<input type="submit" value="Create">
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
</table>

<script language="javascript">
document.form1.dbname.focus();
</script>

<?php include('inc/footer.php'); ?>
