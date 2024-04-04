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

// Operators for WHERE clause
$operators = array(
    "=",
    "<>",
    ">",
    ">=",
    "<",
    "<=",
);
?>



<?php
function onPageLoad()
{
    SQL::connectToDB();
    global $pklist;
    global $columnslist;
    $pklist = fetch_table("
        SELECT UNIQUE cols.table_name, cols.column_name
        FROM USER_TAB_COLUMNS tab_col, user_constraints cons, user_cons_columns cols
        WHERE cols.table_name = tab_col.table_name
        AND cons.constraint_type = 'P'
        AND cons.constraint_name = cols.constraint_name
        AND cons.owner = cols.owner ");
    $columnslist = fetch_table("
        SELECT tab_col.table_name, tab_col.column_name
        FROM USER_TAB_COLUMNS tab_col ");
    SQL::disconnectFromDB();
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
    $statement = SQL::executePlainSQL($query);
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
            if ($key == "WEBSITE" && $value && $value != "null") {
                $tuple = $tuple . "<td><a href =" . $value . " target='_blank'>Visit site</a></td>";
            } else {
                $tuple = $tuple . "<td>" . $value . "</td>";
            }
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

function handleRequests()
{
    if (!SQL::connectToDB()) {
        popUp("Could not connect to database when handling request.");
    }
    if (isset($_POST['postAction'])) {
        ("handle" . $_POST['postAction'] . "Request")();
    }
    if (isset($_GET['getAction'])) {
        ("handle" . $_GET['getAction'] . "Request")();
    }
    SQL::disconnectFromDB();
}
?>


<?php
class SQL
{
    public static $db_conn = NULL;	// login credentials

    public static function executePlainSQL($cmdstr)
    {
        global $db_conn, $success;
        $statement = oci_parse($db_conn, $cmdstr);

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

    public static function executeBoundSQL($cmdstr, $list)
    {
        /*
         * Sometimes the same statement will be executed several times with different values for the variables involved in the query.
         * In this case you don't need to create the statement several times.
         * Bound variables cause a statement to only be parsed once and you can reuse the statement.
         * This is also very useful in protecting against SQL injection.
         */
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
        $ret = $statement;
        return $ret;
    }

    public static function connectToDB()
    {
        global $db_conn;
        global $config;
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

    public static function disconnectFromDB()
    {
        global $db_conn;
        debugAlertMessage("Disconnect from Database");
        oci_close($db_conn);
    }
    public static function sql_file_to_array($location)
    {
        $commands = file_get_contents($location);
        function deleteComments($commands)
        {
            $lines = explode("\n", $commands);
            $commands = '';
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line && !str_starts_with($line, '--')) {
                    $commands .= $line . "\n";
                }
            }
        }
        deleteComments($commands);
        $commands = explode(";", $commands);
        return $commands;
    }

    public static function run_sql_file($location)
    {
        global $db_conn;
        $commands = SQL::sql_file_to_array($location);
        $total = $success = 0;
        foreach ($commands as $command) {
            if (trim($command)) {
                $success += (@SQL::executePlainSQL($command) == false ? 0 : 1);
                oci_commit($db_conn);
                $total += 1;
            }
        }
        return array(
            "success" => $success,
            "total" => $total
        );
    }
}
?>