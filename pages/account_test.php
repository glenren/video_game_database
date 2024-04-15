<?php
require ('library.php');
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
			<li><a href="games.php">Games</a></li>
			<li><a class="active" href="account_test.php">Users</a></li>
			<li><a href="devteam.php">Dev Teams</a></li>
		</ul>
	</div>
	<div class="main">

<?php
function handleSearchRequest() {
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

<h1>Users</h1>
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