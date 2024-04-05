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
			<li><a class="active" href="games.php">Games</a></li>
			<li><a href="account_test.php">Users</a></li>
			<li><a href="#about">Dev Teams</a></li>
		</ul>
	</div>
	<div class="main">
		<h1>Games</h1>

<?php
function handleViewRequest() {
    $command = "SELECT * FROM VideoGameMadeBy";

    $result = SQL::executePlainSQL($command);
    oci_commit(SQL::$db_conn);
    printResult($result);
}
?>

<h3>View All Games</h3>
<div class="outer">
    <form method="GET" action="games.php">
        <input type="submit" name="getAction" value="View"></p>
    </form>
</div>


		<?php
		function handleGameRequest() {
		    if (!$_GET['gameName']) {
        		popUp("Please enter a username!");
        		return;
        	}

            $command = "SELECT * FROM VideoGameMadeBy WHERE Name = '" . $_GET['gameName'] . "'";

            $result = SQL::executePlainSQL($command);
            oci_commit(SQL::$db_conn);

            $index = 0;
            $log = array();
            while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
                $date = date_parse($row["RELEASEDATE"]);
                $row["RELEASEDATE"] = $date["year"];
                $log[$index] = $row;
                $index += 1;
            }

            if ($index == 0) {
                popUP("No game with such a name was found in the database.");
                return;
            }

            if ($index > 1) {
                dupeGames($log);
                return;
            }

            postGame($log[0]["GID"], $log[0]["NAME"]);
        }

        function dupeGames($log) {
            echo "<h3>Did You Mean:</h3><div class=\"outer\">";
            echo "<form method=\"GET\" action=\"games.php\">";
            foreach ($log as $row) {
                echo "<input type=\"radio\" id=\"". $row["GID"] . "\" name=\"dupeID\" value=\""
                    . $row["GID"] . "\">";
                echo "<label for=\"" . $row["GID"] . "\">" . $row["NAME"]
                    . " (" . $row["RELEASEDATE"] . ") by "
                    . $row["DEVTEAMNAME"] . "</label></br></br>";
            }
            echo "</br><input type=\"submit\" name=\"getAction\" value=\"Submit\"></p></form></div>";
        }

        function handleSubmitRequest() {
            if (!$_GET['dupeID']) {
                popUp("Please select a game!");
                return;
            }

            $command = "SELECT Name FROM VideoGameMadeBy WHERE GID = '" . $_GET['dupeID'] . "'";

            $result = SQL::executePlainSQL($command);
            oci_commit(SQL::$db_conn);

            while (oci_fetch($result)) {
                postGame($_GET['dupeID'], oci_result($result, "NAME"));
            }
        }

        function postGame($GID, $Name) {
            $command = "SELECT Rating, COUNT(DISTINCT ReviewID) AS ReviewCount FROM"
                . " MakesReviewReviewing1 WHERE GID = '" . $GID . "' GROUP BY Rating";

            $result = SQL::executePlainSQL($command);
            oci_commit(SQL::$db_conn);
            echo "<h2>Ratings for <i><u>" . $Name . "</u></i>:</h2>";
            printResult($result);
        }

		?>
		<h3>View Game Ratings</h3>
		<div class="outer">
			<form method="GET" action="games.php">
				Game Name: <input type="text" name="gameName"> <br /><br />
				<input type="submit" name="getAction" value="<?= $getGame ?>"></p>
			</form>
		</div>

		<?php
		handleRequests();
		?>
	</div>
</body>

</html>