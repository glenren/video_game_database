<?php
// The following 3 lines allow PHP errors to be displayed along with the page content.
// Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database access configuration
$config = array();
if (file_exists("credentials.txt")) {
    $creds = fopen("credentials.txt", "r");
    $config = login($creds);
    fclose($creds);
    onPageLoad();
}

$db_conn = NULL;	// login credentials are used in connectToDB()
$success = true;	// keep track of errors so page redirects only if there are no errors
$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// Command strings for ORACLE
$postReset = "reset";
$postInsert = 'insert';
$postUpdate = 'update';
$getCount = 'count';
$getDisplay = 'display';
$getLookUp = 'Search';
$getSPJ = 'SPJ';
$getQuery = 'query';
$postDelete = 'delete';
?>



<?php
function onPageLoad()
{
    connectToDB();
    $pklist = fetch_table("
        SELECT UNIQUE cols.table_name, cols.column_name
        FROM USER_TAB_COLUMNS tab_col, user_constraints cons, user_cons_columns cols
        WHERE cols.table_name = tab_col.table_name
        AND cons.constraint_type = 'P'
        AND cons.constraint_name = cols.constraint_name
        AND cons.owner = cols.owner ");
    $columnslist = fetch_table(" SELECT tab_col.table_name, tab_col.column_name
        FROM USER_TAB_COLUMNS tab_col ");
    disconnectFromDB();
}

function login($creds)
{
    global $config;
    $config["dbuser"] = trim(fgets($creds));
    $config["dbpassword"] = trim(fgets($creds));
    $config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
    return $config;
}

function fetch_table($query)
{
    global $db_conn;
    $statement = executePlainSQL($query);
    oci_commit($db_conn);
    $table_column_pair = array();
    $nrows = oci_fetch_all($statement, $table_column_pair);
    $tables = array();
    foreach ($table_column_pair['TABLE_NAME'] as $index => $tablename) {
        if (!array_key_exists($tablename, $tables)) {
            $tables[$tablename] = array();
        }
        array_push($tables[$tablename], $table_column_pair['COLUMN_NAME'][$index]);
    }
    return $tables;
}

function debug_to_console($data)
{
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}


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
    global $postDelete;
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
            case $postDelete:
                handleDeleteRequest();
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
    global $getSPJ;
    global $getLookUp;
    if (connectToDB()) {
        switch ($_GET['getAction']) {
            case $getCount:
                handleCountRequest();
                break;
            case $getDisplay:
                handleDisplayRequest();
                break;
            case $getSPJ:
                handleSPJRequest();
                break;
            case $getQuery:
                handleQueryRequest();
                break;
            case $getLookUp:
                handleLookUpRequest();
                break;
        }
        disconnectFromDB();
    }
}

function printResult($result) { //prints results from a select statement
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
			echo $header;
			$headerPrinted = true;
		}

		echo $tuple;
	}

	echo "</table>";
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

$operators = array(
    "=",
    "<>",
    ">",
    ">=",
    "<",
    "<=",
);
function areTokensOK()
{
    global $pklist;
    global $columnslist;
    global $operators;

    $_GET['inputFrom'] = strtoupper($_GET['inputFrom']);
    trim($_GET['inputFrom']);
    $tablesFrom = preg_split("/,/", $_GET["inputFrom"]);
    foreach ($tablesFrom as $table) {
        if (!in_array($table, array_keys($pklist))) {
            popUp("Invalid table");
            return false;
        }
    }

    $_GET['inputSelect'] = strtoupper($_GET['inputSelect']);
    $inSelectedTables = false;
    if ($_GET['inputSelect'] != "*") {
        foreach ($tablesFrom as $table) {
            $column = preg_split("/\./", $_GET['inputSelect'])[1];
            var_dump($column);
            if (in_array($column, $columnslist[$table])) {
                $inSelectedTables = true;
                break;
            }
        }
    }
    if (
        !$inSelectedTables
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
?>