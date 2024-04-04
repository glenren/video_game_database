<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up All OCI commands are
  commands to the Oracle libraries. To get the file to work, you must place it
  somewhere where your Apache server can run it, and you must rename it to have
  a ".php" extension. You must also change the username and password on the
  oci_connect below to be your ORACLE username and password
-->
<?php
require ('library.php');
$creds = fopen("../credentials.txt", "r") or die("Unable to open file!");
login($creds);
?>

<head>
	<link rel="stylesheet" href="../style.css">
</head>

<body>
	<div class="nav">
		<ul>
			<li class="spacer">|</li>
			<li class="text">VIDEO GAME DATABASE</li>
			<li><a href="../index.php">Home</a></li>
			<li><a href="#news">Games</a></li>
			<li><a class="active" href="account_test.php">Users</a></li>
			<li><a href="#about">Dev Teams</a></li>
		</ul>
	</div>
	<div class="main">
		<h1>Users</h1>


		<?php
		function handleSearchRequest()
		{
			if (!$_GET['insName']) {
				popUp("Please enter a username!");
				return;
			}

			$command = "SELECT g.Name, g.DevTeamName, g.Category, a.Status FROM VideoGameMadeBy g, Adds a "
				. "WHERE a.Username = '" . $_GET['insName'] . "' AND a.GID = g.GID";

			$result = SQL::executePlainSQL($command);
			oci_commit(SQL::$db_conn);
			echo "<h2>Games added by user <i><u>" . $_GET['insName'] . "</u></i>:</h2>";
			printResult($result);
		}
		?>
		<h3>Select User</h3>
		<div class="outer">
			<form method="GET" action="account_test.php">
				Username: <input type="text" name="insName"> <br /><br />
				<input type="submit" name="getAction" value="<?= $getLookUp ?>"></p>
			</form>
		</div>

		<?php
		handleRequests();
		?>
	</div>
</body>

</html>