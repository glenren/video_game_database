<hr />
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
<h2>Number of Video Games in Database</h2>
<form method="GET" action="query.php">
    <input type="submit" name="getAction" value="<?= $getCount ?>"></p>
</form>



<?php
function handleSPJRequest()
{
    global $db_conn;
    // Sanitize table and column names
    if (!areTokensOK()) {
        return;
    }
    $query = "SELECT " . $_GET['inputSelect'] . " FROM " . $_GET['inputFrom'];
    if (!empty($_GET['inputWhere'])) {
        $query .= " WHERE (" . $_GET['inputWhere'] . ")";
    }
    $results = executePlainSQL($query);
    if (oci_commit($db_conn)) {
        printResult($results);
        popUp("Success!");
    }
}
?>
<hr />
<h2>SELECT PROJECT JOIN Query</h2>
<form method="GET" action="query.php">
    FROM:
    <select name="inputFrom" onChange="switchSelect(this);">
        <?php
        $temp = $pklist;
        foreach ($pklist as $table => $columns) {
            unset($temp[$table]);
            foreach ($temp as $table2 => $columns2) {
                if (empty(array_intersect($columns, $columns2))) {
                    continue;
                }
                $joinOption = $table . "," . $table2;
                echo "<option value=\"" . $joinOption . "\">" . $joinOption . "</option>";
            }
        }
        ?>
    </select>
    <script>
        function switchSelect(test) {
            console.log(test.value);
        }
    </script>
    <br />
    SELECT Column:
    <input type="text" name="inputSelect">
    <select name="inputSelect">

    </select>
    <br />
    WHERE Column: <input type="text" name="inputWhere">
    <br />
    <input type="submit" name="getAction" value="<?= $getSPJ ?>"></p>
</form>



<?php
function handleQueryRequest()
{
    global $db_conn;
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
    $results = executePlainSQL($query);

    if (oci_commit($db_conn)) {
        // foreach ($results as $result) { }
        printResult($results);
        popUp("Success!");
    }
}
?>
<hr />
<h2>General Query</h2>
<form method="GET" action="query.php">
    FROM:
    <!-- <input type="text" name="inputFrom"> -->
    <select name="inputFrom">
        <?php
        $temp = $pklist;
        foreach ($pklist as $table => $columns) {
            unset($temp[$table]);
            foreach ($temp as $table2 => $columns2) {
                if (empty(array_intersect($columns, $columns2))) {
                    continue;
                }
                $joinOption = $table . "," . $table2;
                echo "<option value=\"" . $joinOption . "\">" . $joinOption . "</option>";
            }
        }
        ?>
    </select>
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
function handleDisplayRequest()
{
    global $db_conn;
    $commands = sql_file_to_array("select.sql");
    foreach ($commands as $command) {
        if (trim($command)) {
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
<hr />
<h2>Display Tuples in DemoTable</h2>
<form method="GET" action="query.php">
    <input type="submit" name="getAction" value="display"></p>
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