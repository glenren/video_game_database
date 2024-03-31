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
// The following 3 lines allow PHP errors to be displayed along with the page content.
// Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database access configuration
$creds = fopen("credentials.txt", "r") or die("Unable to open file!");
$config["dbuser"] = trim(fgets($creds));
$config["dbpassword"] = trim(fgets($creds));
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
fclose($creds);
$db_conn = NULL;	// login credentials are used in connectToDB()
$success = true;	// keep track of errors so page redirects only if there are no errors
$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

connectToDB();
$statement = executePlainSQL(
	"SELECT table_name, column_name
	FROM USER_TAB_COLUMNS"
);
oci_commit($db_conn);
$nrows = oci_fetch_all($statement, $table_column_pair);
$tables = array();
foreach ($table_column_pair['TABLE_NAME'] as $index => $tablename) {
	if (!array_key_exists($tablename, $tables)) {
		$tables[$tablename] = array();
	}
	array_push($tables[$tablename], $table_column_pair['COLUMN_NAME'][$index]);
}
disconnectFromDB();

function debug_to_console($data)
{
	$output = $data;
	if (is_array($output))
		$output = implode(',', $output);

	echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

// Command strings for ORACLE
$postReset = "reset";
$postInsert = 'insert';
$postUpdate = 'update';
$getCount = 'count';
$getDisplay = 'display';
$getQuery = 'query';

// Other functions
function debugAlertMessage($message)
{
	global $show_debug_alert_messages;
	if ($show_debug_alert_messages) {
		echo "<script type='text/javascript'>alert('$message');</script>";
	}
}

function popUp($message)
{
	echo "<script>alert('$message');</script>";
}

function executePlainSQL($cmdstr)
{ //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $success;
	$statement = oci_parse($db_conn, $cmdstr);
	//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
		echo htmlentities($e['message']);
		$success = False;
	}
	$r = oci_execute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For oci_execute errors pass the statementhandle
		echo htmlentities($e['message']);
		$success = False;
	}
	return $statement;
}

function executeBoundSQL($cmdstr, $list)
{
	/*
	 * Sometimes the same statement will be executed several times with different values for the variables involved in the query.
	 * In this case you don't need to create the statement several times.
	 * Bound variables cause a statement to only be parsed once and you can reuse the statement.
	 * This is also very useful in protecting against SQL injection.
	 * See the sample code below for how this function is used
	 */
	global $db_conn, $success;
	$statement = oci_parse($db_conn, $cmdstr);
	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}
	// $ret = array();
	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			// echo $val;
			// echo "<br>" . $bind . "<br>";
			oci_bind_by_name($statement, $bind, $val);
			unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
		}
		$r = oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$success = False;
		}
		// array_push($ret, $statement);
	}
	$ret = $statement;
	return $ret;
}


function connectToDB()
{
	global $db_conn;
	global $config;
	// Your username is ora_(CWL_ID) and the password is a(student number). For example,
	// ora_platypus is the username and a12345678 is the password.
	// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
	$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);
	if ($db_conn) {
		debugAlertMessage("Database is Connected");
		return true;
	} else {
		debugAlertMessage("Cannot connect to Database");
		$e = OCI_Error(); // For oci_connect errors pass no handle
		echo htmlentities($e['message']);
		return false;
	}
}

function disconnectFromDB()
{
	global $db_conn;
	debugAlertMessage("Disconnect from Database");
	oci_close($db_conn);
}

// HANDLE ALL POST ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
function handlePOSTRequest()
{
	global $postReset;
	global $postUpdate;
	global $postInsert;
	if (connectToDB()) {
		switch ($_POST['postAction']) {
			case $postReset:
				handleResetRequest();
				break;
			case $postUpdate:
				handleUpdateRequest();
				break;
			case $postInsert:
				handleInsertRequest();
				break;
		}
		disconnectFromDB();
	}
}

// HANDLE ALL GET ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly.
// It will make it easier to add/remove functionality.
function handleGETRequest()
{
	global $getCount;
	global $getDisplay;
	global $getQuery;
	if (connectToDB()) {
		switch ($_GET['getAction']) {
			case $getCount:
				handleCountRequest();
				break;
			case $getDisplay:
				handleDisplayRequest();
				break;
			case $getQuery:
				handleQueryRequest();
				break;
		}
		disconnectFromDB();
	}
}

function sql_file_to_array($location)
{
	//load file
	$commands = file_get_contents($location);

	//delete comments
	$lines = explode("\n", $commands);
	$commands = '';
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line && !str_starts_with($line, '--')) {
			$commands .= $line . "\n";
		}
	}

	//convert to array
	$commands = explode(";", $commands);
	return $commands;
}

function run_sql_file($location)
{
	global $db_conn;
	$commands = sql_file_to_array($location);
	//run commands
	$total = $success = 0;
	foreach ($commands as $command) {
		if (trim($command)) {
			$success += (@executePlainSQL($command) == false ? 0 : 1);
			oci_commit($db_conn);
			$total += 1;
		}
	}
	//return number of successful queries and total number of queries found
	return array(
		"success" => $success,
		"total" => $total
	);
}
?>

<html>

<head>
	<link rel="stylesheet" href="style.css">
	<title>CPSC 304 PHP/Oracle Demonstration</title>
	<!-- setting the style for the nav bar. borrowed from https://www.w3schools.com/css/css_navbar_vertical.asp -->
	<style>
		ul {
			list-style-type: none;
			margin: 0;
			padding: 0;
			width: 200px;
			background-color: #f1f1f1;
		}

		li a {
			display: block;
			color: #000;
			padding: 8px 16px;
			text-decoration: none;
		}

		/* Change the link color on hover */
		li a:hover {
			background-color: #555;
			color: white;
		}
	</style>

</head>
<h1>Video Game Database</h1>
<h2> CPSC 304 2023w2 project by Kat Duangkham, Glen Ren and Chanaldy Soenarjo</h2>

<body>
	<!-- navigation bar to go to different pages -->

	<ul>
		<li><a href="#home">Home</a></li>
		<li><a href="#news">Video Games</a></li>
		<li><a href="account_test.php">Users</a></li>
		<li><a href="#about">Dev Teams</a></li>
	</ul>

	<hr />
	<h2>Reset</h2>
	<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you
		MUST use reset</p>
	<form method="POST" action="website.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" name="postAction" value="<?= $postReset ?>"></p>
	</form>
	<?php
	function handleResetRequest()
	{
		// Drop old table and create new ones
		run_sql_file("database.sql");
	}
	?>

	<hr />
	<h2>Add A Video Game</h2>
	<form method="POST" action="website.php">
		GID: <input type="text" name="GID"> <br /><br />
		Video Game Title: <input type="text" name="gameTitle"> <br /><br />
		Release Date: <input type="text" name="releaseDate"> <br /><br />
		Price: <input type="text" name="price"> <br /><br />
		Category: <input type="text" name="category"> <br /><br />
		Development Team: <input type="text" name="devteamName"> <br /><br />
		<input type="submit" name="postAction" value="<?= $postInsert ?>"></p>
	</form>
	
	<?php
	function handleInsertRequest()
	{
		global $db_conn;
		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind1" => $_POST['GID'],
			":bind2" => $_POST['gameTitle'],
			":bind3" => $_POST['releaseDate'], //has to be in form dd-MMMM-yyyy
			":bind4" => $_POST['price'],
			":bind5" => $_POST['category'],
			":bind6" => $_POST['devteamName'] //case sensitive
		);
		$alltuples = array(
			$tuple
		);
		executeBoundSQL("INSERT INTO VideoGameMadeBy VALUES (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6)", $alltuples);
		// oci_commit($db_conn);
	
		if (oci_commit($db_conn)) {
			popUp("Successfully inserted your values into the table!");
		}
	}
	?>

	<hr />
	<h2> Delete Video Game</h2>
	<form method="POST" action="website.php">
		Video Game Title: <input type="text" name="gameTitle"><br /><br />
		<input type="submit" name="postAction" value="<?=$getCount ?>"></p>
	</form>
	
	<?php
function handleDeleteRequest()
	{
		global $db_conn;
		//getting value from user and delete data from table
		$tuple = array(
			":bind1" => $_POST['gameTitle']
		);
		$alltuples = array(
			$tuple
		);
		executeBoundSQL("DELETE FROM VideoGameMadeBy VALUES (:bind1", $alltuples);
		
		if (oci_commit($db_conn)) {
			popUp("Successfully delete value from table!");
		}
	}
	?>
	
	<hr />
	<h2>Update Name in DemoTable</h2>
	<p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>
	<form method="POST" action="website.php">
		Old Name: <input type="text" name="oldName"> <br /><br />
		New Name: <input type="text" name="newName"> <br /><br />
		<input type="submit" name="postAction" value="<?= $postUpdate ?>"></p>
	</form>
	<?php
	function handleUpdateRequest()
	{
		global $db_conn;
		$old_name = $_POST['oldName'];
		$new_name = $_POST['newName'];
		// you need the wrap the old name and new name values with single quotations
		executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
		// oci_commit($db_conn);
	
		if (oci_commit($db_conn)) {
			popUp("Successfully updated value!");
		}
	}
	?>

	<hr />
	<h2>Number of Video Games in Database</h2>
	<form method="GET" action="website.php">
		<input type="submit" name="getAction" value="<?= $getCount ?>"></p>
	</form>
	<?php
	function handleCountRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT Count(*) FROM VideoGameMadeBy");
		if (($row = oci_fetch_row($result)) != false) {
			// echo "<br> The number of video games in database: " . $row[0] . "<br>";
			popUp("The number of video games in database: " . $row[0]);
		}
	}
	?>

	<hr />
	<h2>General Query</h2>
	<form method="GET" action="website.php">
		FROM: <input type="text" name="inputFrom">
		<!-- <select name="inputFrom">
			<?php
			// foreach ($tables as $table => $columns) {
			// 	echo "<option value=\"" . $table . "\">" . $table . "</option>";
			// }
			?>
		</select> -->
		<br />
		SELECT Column: <input type="text" name="inputSelect">
		<br />
		WHERE Column: <input type="text" name="inputWhere">
		<br />
		GROUP BY: <input type="text" name="inputGroupBy">
		<br />
		HAVING: <input type="text" name="inputHaving">
		<br />
		<input type="submit" name="getAction" value="<?= $getQuery ?>"></p>
	</form>
	<?php
	// $operators = array(
	// 	"=",
	// 	"<>",
	// 	">",
	// 	">=",
	// 	"<",
	// 	"<=",
	// );
	function areTokensOK()
	{
		global $tables;
		global $operators;

		$_GET['inputFrom'] = strtoupper($_GET['inputFrom']);
		trim($_GET['inputFrom']);
		$tablesFrom = preg_split("/,/", $_GET["inputFrom"]);
		foreach ($tablesFrom as $table) {
			if (!in_array($table, array_keys($tables))) {
				popUp("Invalid table");
				return false;
			}
		}

		$_GET['inputSelect'] = strtoupper($_GET['inputSelect']);
		$inSelectedTables = false;
		foreach ($tablesFrom as $table){
			if (in_array($_GET['inputSelect'], $tables[$table])){
				$inSelectedTables = true;
				break;
			}
		}
		if (!$inSelectedTables
			&& $_GET['inputSelect'] != "*"
		) {
			popUp("Invalid Column");
			return false;
		}

		// $conditionList = preg_split("/(and)|(or)/i", $_GET['inputWhere']);
		// foreach ($conditionList as $condition) {
		// 	$pieces = preg_split("/\s*/", $condition);
		// 	foreach ($pieces as $piece) {
		// 		if (!in_array($piece, $tablesFrom)
		// 			&& !in_array($piece, $operators)) {
		// 				return false;
		// 		}
		// 	}
		// }
		return true;
	}
	function handleQueryRequest()
	{
		global $db_conn;
		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind2" => $_GET['inputGroupBy'],
			":bind3" => $_GET['inputHaving']
		);
		$alltuples = array(
			$tuple
		);
		// Sanitize table and column names
		if (!areTokensOK()) {
			return;
		}
		$query = "SELECT " . $_GET['inputSelect'] . " FROM " . $_GET['inputFrom'];
		if (!empty($_GET['inputWhere'])) {
			$query .= " WHERE (" . $_GET['inputWhere'] . ")";
		}
		if (!empty($_GET['inputGroupBy'])) {
			$query .= " GROUP BY (" . $_GET['inputGroupBy'] . ")";
		}
		if (!empty($_GET['inputHaving'])) {
			$query .= " HAVING (" . $_GET['inputHaving'] . ")";
		}
		$results = executeBoundSQL($query, $alltuples);

		if (oci_commit($db_conn)) {
			// foreach ($results as $result) { }
			printResult($results);
			popUp("Success!");
		}
	}
	?>


	<hr />
	<h2>Display Tuples in DemoTable</h2>
	<form method="GET" action="website.php">
		<input type="submit" name="getAction" value="display"></p>
	</form>
	<?php
	function handleDisplayRequest()
	{
		global $db_conn;
		$commands = sql_file_to_array("select.sql");
		foreach ($commands as $command) {
			if (trim($command)) {
				debug_to_console($command);
				$result = executePlainSQL($command);
				oci_commit($db_conn);
				printResult($result);
			}
		}
	}
	function printResult($result)
	{ //prints results from a select statement
		echo "<table>";
		$headerPrinted = false;
		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			$tuple = "<tr>";
			$header = "<tr>";
			foreach ($row as $key => $value) {
				if (!$headerPrinted) {
					$header = $header . "<th>" . $key . "</th>";
				}
				debug_to_console($tuple);
				$tuple = $tuple . "<td>" . $value . "</td>";
			}
			$tuple = $tuple . "</tr>";
			if (!$headerPrinted) {
				$header = $header . "</tr>";
				echo "<thead>" . $header . "</thead>" . "<tbody>";
			}
			$headerPrinted = true;
			echo $tuple;
		}
		echo "</tbody>";
		echo "</table>";
	}
	?>

	<?php
	if (isset($_POST['postAction'])) {
		handlePOSTRequest();
	} else if (
		isset($_GET['getAction'])
	) {
		handleGETRequest();
	}
	?>
</body>

</html>