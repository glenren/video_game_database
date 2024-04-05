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
			<li><a href="devteam.php">Dev Teams</a></li>
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
        <input type="submit" name="getAction" value="View">
    </form>
</div>

<?php
function handleFilterRequest() {
    $command;

    if (!$_GET['min']) {
        $command = "SELECT g.Name, g.DevTeamName, COUNT(DISTINCT r.ReviewID) AS Number_of_Reviews, "
            . "AVG(r.Rating) AS AVG_Rating FROM MakesReviewReviewing1 r, VideoGameMadeBy g WHERE "
            . "g.GID = r.GID GROUP BY g.GID, g.Name, g.DevTeamName ORDER BY COUNT(DISTINCT r.ReviewID) DESC";
    } else {
        $command = "SELECT g.Name, g.DevTeamName, COUNT(DISTINCT r.ReviewID) AS Number_of_Reviews, "
            . "AVG(r.Rating) AS AVG_Rating FROM MakesReviewReviewing1 r, VideoGameMadeBy g WHERE "
            . "g.GID = r.GID GROUP BY g.GID, g.Name, g.DevTeamName HAVING COUNT(DISTINCT r.ReviewID) >= '" . $_GET['min']
            . "' ORDER BY COUNT(DISTINCT r.ReviewID) DESC";
    }

    $result = SQL::executePlainSQL($command);
    oci_commit(SQL::$db_conn);
    echo "<h2>Games by Review Count:</h2>";
    printResult($result);
}
?>

<h3>Filter Games by Review Count</h3>
<div class="outer">
<form method="GET" action="games.php">
Mininum # of Reviews (leave blank to view all): <input type="number" name="min" value="Filter">
<p><input type="submit" name="getAction" value="Filter"></p>
</form>
</div>


		<?php
		function handleGameRequest() {
		    if (!$_GET['gameName']) {
        		popUp("Please enter a game name!");
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

            echo "<h3>Leave a review for " . $Name . "?</h3>";
            echo "<div class=\"outer\"><form method=\"POST\" action=\"games.php\" id=\"review\">";
            echo "<input type=\"hidden\" name=\"GID\" value=" . $GID . ">";
            echo "<input type=\"hidden\" name=\"reviewDate\" value=" . strtoupper(date("d-M-y")) . ">";
            echo "<input type=\"hidden\" name=\"length\" value=\"0\">";
            echo "<input type=\"hidden\" name=\"category\" value=\"Short\">";
            echo "Username : <input type=\"text\" name=\"username\"><br />";

            echo "<p>Rating:</p><br/>";
            echo "<label class=\"rating\">1<br/><input type=\"radio\" id=\"1\" name=\"rating\" value=1></label>";
            echo "<label class=\"rating\">2<br/><input type=\"radio\" id=\"2\" name=\"rating\" value=2></label>";
            echo "<label class=\"rating\">3<br/><input type=\"radio\" id=\"3\" name=\"rating\" value=3></label>";
            echo "<label class=\"rating\">4<br/><input type=\"radio\" id=\"4\" name=\"rating\" value=4></label>";
            echo "<label class=\"rating\">5<br/><input type=\"radio\" id=\"5\" name=\"rating\" value=5></label>";

            echo "<br/><br/><br/><input type=\"submit\" name=\"postAction\" value=\"Review\"></form></div>";
        }

        function handleReviewRequest() {
            $mrr1 = array(
                ":bind1" => rand(),
                ":bind2" => $_POST['reviewDate'],
                ":bind3" => $_POST['rating'],
                ":bind4" => $_POST['length'],
                ":bind5" => $_POST['username'],
                ":bind6" => $_POST['GID']
            );

            $mrr1 = array($mrr1);

            SQL::executeBoundSQL("INSERT INTO MakesReviewReviewing1 VALUES (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6)", $mrr1);

            global $success;

            if ($success && oci_commit(SQL::$db_conn)) {
                popUp("Successfully added review!");
            } else {
                popUp("Error: your review could not be added,"
                    . " please check that the inputted username is registered in our system.");
            }
        }

		?>
		<h3>View and Leave Game Ratings</h3>
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