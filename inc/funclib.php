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
	function throwSQLError($message,$query = '')
	{
		$output = (ucfirst($message) . ', the error returned was:<br><br><font color="red">' . mssql_get_last_message());

		if($query != '')
			$output .= ('<br>The query I attempted to execute was: ' . $query);

		$output .= '</font><br><br>';

		echo($output . '<br>');
	}

	function throwGeneralError($message)
	{
		echo(ucfirst($message . '.<br>'));
	}

	function throwSuccess($message)
	{
		echo('<font color="green">' . ucfirst($message) . '</font><br>');
	}

	function throwFailure($message)
	{
		echo('<font color="red">' . ucfirst($message) . '</font><br>');
	}
?>
