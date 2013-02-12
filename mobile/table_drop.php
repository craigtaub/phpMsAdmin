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

@mssql_select_db($_SESSION['database']) or die(throwSQLError('unable to select database'));

if($_POST['tables'][0] == '')
	@mssql_query('DROP TABLE ' . urldecode($_GET['table']) . ';') or die(throwSQLError('unable to complete drop'));
else
{
	for($counter = 0; $counter < count($_POST['tables']); $counter++)
		@mssql_query('DROP TABLE ' . urldecode($_POST['tables'][$counter]) . ';') or die(throwSQLError('unable to complete drop'));
}

if($_SESSION['expanded'] != '')
	echo '<script language="javascript">parent.left.location.reload();</script>';

echo('<meta http-equiv="refresh" content="0;url=database_properties.php?dbname=' . urlencode($_SESSION['database']) . '">');

include('inc/footer.php');

?>
