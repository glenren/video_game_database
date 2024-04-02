<hr />
<?php
function handleCountRequest()
{
    global $db_conn;
    $result = executePlainSQL("SELECT Count(*) FROM VideoGameMadeBy");

    global $success;
    if (
        $success
        && ($row = oci_fetch_row($result)) != false
    ) {
        // echo "<br> The number of video games in database: " . $row[0] . "<br>";
        popUp("The number of video games in database: " . $row[0]);
    } else {
        popUp("Database Error");
    }
}
?>
<h2>Number of Video Games in Database</h2>
<form method="GET" action="index.php">
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
    $query = "SELECT " . $_GET['inputSelect'];
    $selectedTables = preg_split("/,/", $_GET["inputFrom"]);
    global $pklist;
    $sharedColumns = array_intersect($pklist[$selectedTables[0]], $pklist[$selectedTables[1]]);
    $query .= " FROM " . $selectedTables[0] . " INNER JOIN " .
        $selectedTables[1] . " ON (";
    foreach ($sharedColumns as $key => $column) {
        $query .= $selectedTables[0] . "." . $column .
            "=" . $selectedTables[1] . "." . $column;
    }
    $query .= ")";

    if (!empty($_GET["inputWhereCon"])) {
        $query .= " WHERE (";
        $inputWhereConCounter = 1;
        function createCondition($inputWhereConCounter)
        {
            $condition = "";
            if (!empty($_GET['inputWhereVal2' . $inputWhereConCounter])) {
                $val2 = $_GET['inputWhereVal2' . $inputWhereConCounter];
                if (!ctype_digit($_GET['inputWhereOp' . $inputWhereConCounter])) {
                    $val2 = "'" . $val2 . "'";
                }
                $condition = $_GET['inputWhereVal1' . $inputWhereConCounter] .
                    $_GET['inputWhereOp' . $inputWhereConCounter] .
                    $val2;
            }
            return $condition;
        }
        while (isset($_GET["inputWhereCon" . $inputWhereConCounter])) {
            $query .= createCondition($inputWhereConCounter);
            $query .= " " . $_GET["inputWhereCon" . $inputWhereConCounter] . " ";
            $inputWhereConCounter++;
        }
        $query .= createCondition("");
        $query .= ")";
    }
    $results = executePlainSQL($query);

    global $success;
    if ($success) {
        popUp("Success");
        printResult($results);
    } else {
        popUp("Database Error");
    }
}
?>
<hr />
<h2>SELECT PROJECT JOIN Query</h2>
<form method="GET" action="index.php">
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
        function switchSelect(htmlFrom) {
            elements = document.getElementsByClassName("selectOption");
            for (let option of elements) {
                option.style.display = "none";
            }

            let tables = htmlFrom.value.split(",");
            tables.forEach(table => {
                elements = document.getElementsByClassName("selectOption" + table);
                for (let option of elements) {
                    option.style.display = "block";
                }
            });
        }
    </script>
    <br />
    SELECT Column:
    <select name="inputSelect">
        <option value="*">*</option>
        <?php
        foreach ($columnslist as $table => $columns) {
            foreach ($columns as $column) {
                $class1 = "selectOption" . $table;
                $class2 = "selectOption";
                echo "<option " .
                    "style=\"display:none\" " .
                    "class=\"" . $class1 . " " . $class2 . "\" " .
                    "value=\"" . $table . "." . $column . "\"" .
                    ">" . $table . "." . $column . "</option>";
            }
        }
        ?>
    </select>
    <br />
    WHERE Column:
    <div>
        <select name="inputWhereVal1">
            <?php
            foreach ($columnslist as $table => $columns) {
                foreach ($columns as $column) {
                    $class1 = "selectOption" . $table;
                    $class2 = "selectOption";
                    echo "<option " .
                        "style=\"display:none\" " .
                        "class=\"" . $class1 . " " . $class2 . "\" " .
                        "value=\"" . $table . "." . $column . "\"" .
                        ">" . $table . "." . $column . "</option>";
                }
            }
            ?>
        </select>
        <select name="inputWhereOp">
            <?php
            foreach ($operators as $operator) {
                echo "<option " .
                    "value=\"" . $operator . "\"" .
                    ">" . $operator . "</option>";
            }
            ?>
        </select>
        <input name="inputWhereVal2">
        <select name="inputWhereCon" onChange="changeWhere(this)">
            <option value=""></option>
            <option value="AND">AND</option>
            <option value="OR">OR</option>
        </select>
        <script>
            var inputWhereConCounter = 1;
            function changeWhere(menu) {
                menu2 = menu.parentElement.cloneNode(true);
                for (const child of menu.parentElement.children) {
                    child.setAttribute("name", child.getAttribute("name") + inputWhereConCounter);
                }
                menu.parentElement.appendChild(menu2);
            }
        </script>
    </div>
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

    global $success;
    if ($success) {
        popUp("Success");
        printResult($results);
    } else {
        popUp("Database Error");
    }
}
?>
<hr />
<h2>General Query</h2>
<form method="GET" action="index.php">
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
<form method="GET" action="index.php">
    <input type="submit" name="getAction" value="display"></p>
</form>