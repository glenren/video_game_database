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
<h3>Number of Games in Database</h3>
<div class="outer"><form method="GET" action="index.php">
    <input type="submit" name="getAction" value="<?= $getCount ?>"></p>
</form></div>



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
        $inputWhereConCounter = "";
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
            $inputWhereConCounter .= "_";
        }
        $query .= ")";
    }
    $results = executePlainSQL($query);

    global $success;
    if ($success) {
        // popUp("Success");
        printResult($results);
    } else {
        popUp("Database Error");
    }
}
?>

<h3>SELECT PROJECT JOIN Query</h3>
<div class="outer"><form method="GET" action="index.php">
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
    </select><br/><br/>
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
    SELECT:
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
    </select><br/><br/>
    WHERE:
        <span><select name="inputWhereVal1">
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
        </select><br/><br/></span>
    <script>
        var inputWhereConCounter = "_";
        function changeWhere(menu) {
            if (menu.value == "") {
                let divElements = menu.parentElement.getElementsByTagName("span");
                if (divElements.length == 0) {
                    return;
                }
                divElements[0].remove();
            } else {
                if (menu.parentElement.getElementsByTagName("div").length != 0) {
                    return;
                }
                menu2 = menu.parentElement.cloneNode(true);
                for (const child of menu2.children) {
                    child.setAttribute("name", child.getAttribute("name") + inputWhereConCounter);
                }
                menu.parentElement.appendChild(menu2);
            }
        }
    </script>
    <input type="submit" name="getAction" value="<?= $getSPJ ?>"></p>
</form></div>

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
        //popUp("Success");
        printResult($results);
    } else {
        popUp("Database Error");
    }
}
?>
<h3>General Query</h3>
<div class="outer"><form method="GET" action="index.php">
    FROM:
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
    </select><br/><br/>
    SELECT Column: <input type="text" name="inputSelect">
    <br/><br/>
    WHERE Column: <input type="text" name="inputWhere">
    <br/><br/>
    GROUP BY: <input type="text" name="inputGroupBy">
    <br/><br/>
    HAVING: <input type="text" name="inputHaving">
    <br/><br/>
    <input type="submit" name="getAction" value="<?= $getQuery ?>"></p>
</form></div>



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
?>

<h3>Display All Tables in Database</h3>
<div class="outer"><form method="GET" action="index.php">
    <input type="submit" name="getAction" value="display"></p>
</form></div>