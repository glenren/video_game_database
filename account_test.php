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
// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$creds = fopen("credentials.txt", "r") or die("Unable to open file!");
$config["dbuser"] = trim(fgets($creds));
$config["dbpassword"] = trim(fgets($creds));
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
fclose($creds);
$db_conn = NULL;	// login credentials are used in connectToDB()
$success = true;	// keep track of errors so page redirects only if there are no errors
$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// Command strings for ORACLE
$postReset = "reset";
$postInsert = 'insert';
$getLookUp = 'lookup';
$postUpdate = 'update';
$getCount = 'count';
$getDisplay = 'display';

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
	/* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
													  In this case you don't need to create the statement several times. Bound variables cause a statement to only be
													  parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
													  See the sample code below for how this function is used */
	global $db_conn, $success;
	$statement = oci_parse($db_conn, $cmdstr);
	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$success = False;
	}
	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			//echo $val;
			//echo "<br>".$bind."<br>";
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
	}
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
	global $getLookUp;
	if (connectToDB()) {
		switch ($_GET['getAction']) {
			case $getCount:
				handleCountRequest();
				break;
			case $getDisplay:
				handleDisplayRequest();
				break;
			case $getLookUp:
			    handleLookUpRequest();
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
</head>
<h1>Video Game Database</h1>
<h2>CPSC 304 2023w2 project by Kat Duangkham, Glen Ren and Chanaldy Soenarjo</h2>

<body>
	<hr />
	<h2>Reset</h2>
	<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you
		MUST use reset</p>
	<form method="POST" action="account_test.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" name="postAction" value="<?= $postReset ?>"></p>
	</form>
	<?php
	function handleResetRequest()
	{
		// Drop old table and create new ones
		run_sql_file("database.sql");
	}
	function handleInsertRequest()
	{
		global $db_conn;
		//Getting the values from user and insert data into the table
		$tuple = array(
			":bind1" => $_POST['insNo'],
			":bind2" => $_POST['insName']
		);
		$alltuples = array(
			$tuple
		);
		executeBoundSQL("insert into demoTable values (:bind1, :bind2)", $alltuples);
		// oci_commit($db_conn);

		if (oci_commit($db_conn)) {
		    popUp("Successfully inserted your values into the table!");
		}
	}
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
	function handleCountRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT Count(*) FROM demoTable");
		if (($row = oci_fetch_row($result)) != false) {
			// echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
			popUp("The number of tuples in demoTable: " . $row[0]);
		}
	}
	function debug_to_console($data)
	{
		$output = $data;
		if (is_array($output))
			$output = implode(',', $output);

		echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
	}
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
	function handleLookUpRequest() {
	    global $db_conn;

	    $command = "SELECT g.Name, g.DevTeamName, g.Category, a.Status FROM VideoGameMadeBy g, Adds a "
	    . "WHERE a.Username = '" . $_GET['insName'] . "' AND a.GID = g.GID";

	    debug_to_console($command);
	    $result = executePlainSQL($command);
	    oci_commit($db_conn);
	    echo "<hr/><h2>" . $_GET['insName'] . "'s Added Games:</h2>";
	    printResult($result);
	}
    ?>
    <hr />
    <h2>Select User</h2>
    <form method="GET" action="account_test.php">
        Username: <input type="text" name="insName"> <br /><br />
        <input type="submit" name="getAction" value="<?= $getLookUp ?>"></p>
    </form>
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