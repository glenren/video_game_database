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
			<li><a href="games.php">Games</a></li>
			<li><a href="account_test.php">Users</a></li>
			<li><a class="active" href="devteam.php">Dev Teams</a></li>
		</ul>
	</div>
	<div class="main">
		<h1>Dev Teams</h1>

<?php
function handleViewRequest() {
    $command = "SELECT * FROM DevTeam";

    $result = SQL::executePlainSQL($command);
    oci_commit(SQL::$db_conn);
    printResult($result);
}
?>

<h3>View All Dev Teams</h3>
<div class="outer">
    <form method="GET" action="devteam.php">
        <input type="submit" name="getAction" value="View">
    </form>
</div>

<?php
function handleFilterRequest() {
    $command = "WITH Temp(Team_Name, Average_Game_Rating, Total_Games) AS "
        . "(SELECT g.DevTeamName AS Team_Name, AVG(r.Rating) AS Average_Game_Rating, "
        . "COUNT(g.GID) AS Total_Games FROM VideoGameMadeBy g, MakesReviewReviewing1 r "
        . "WHERE g.GID = r.GID GROUP BY g.DevTeamName) SELECT Team_Name, Average_Game_Rating, Total_Games "
        . "FROM Temp WHERE Average_Game_Rating > (SELECT AVG(Average_Game_Rating) FROM Temp)";

    $result = SQL::executePlainSQL($command);
    oci_commit(SQL::$db_conn);
    printResult($result);
}
?>

<h3>View Top Dev Teams</h3>
<div class="outer">
<p>View the Dev Teams that have an above average rating.</p><br/>
    <form method="GET" action="devteam.php">
        <input type="submit" name="getAction" value="Filter">
    </form>
</div>

<?php
function handleAddRequest() {
    $dt = array(
        ":bind1" => $_POST['teamName'],
        ":bind2" => $_POST['numPloyees'],
        ":bind3" => $_POST['location'],
    );

    $dt = array($dt);

    SQL::executeBoundSQL("INSERT INTO DevTeam VALUES (:bind1, :bind2, :bind3)", $dt);

    global $success;

    if ($success && oci_commit(SQL::$db_conn)) {
        popUp("Successfully added the Dev Team <i>" . $_POST['teamName'] . "</i>!");
    } else {
        popUp("Unable to add the Dev Team at this time. Check that a team with this name has not already been added!");
    }
}
?>

<h3>Add Dev Team</h3>
<div class="outer"><form method="POST" action="devteam.php">
    Team Name (this is case sensitive): <input type="text" name="teamName"> <br /><br />
    Number of Employees: <input type="number" name="numPloyees"> <br /><br />
    Location: <input type="text" name="location"> <br /><br />
    <input type="submit" name="postAction" value="Add"></p>
</form></div>

<?php
		handleRequests();
		?>
	</div>
</body>

</html>