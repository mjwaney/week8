<?php

class SQL
{
	function query(String $sql)
	{
		//1. Connect with mysqli_connect
		$dbc = mysqli_connect("localhost", "root", "", "week7") or die("Error connecting to MYSQL server");
		
		//3. Execute the Queries
		$result = mysqli_query($dbc, $sql) or die("<br><br>Error querying the database");
		while($row = mysqli_fetch_assoc($result))
			return $row;

		// 4. Close the connection
		mysqli_close($dbc);	
	}
}