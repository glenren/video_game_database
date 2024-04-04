<?php

class Query
{
    public static function areTokensOK()
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
    public static function createCondition($inputWhereConCounter)
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

    public static function joinTwoTables($selectedTables)
    {
        assert(count($selectedTables) > 1);
        global $pklist;
        $sharedColumns = array_intersect($pklist[$selectedTables[0]], $pklist[$selectedTables[1]]);
        $query = " FROM " . $selectedTables[0] . " INNER JOIN " .
            $selectedTables[1] . " ON (";
        foreach ($sharedColumns as $key => $column) {
            $query .= $selectedTables[0] . "." . $column .
                "=" . $selectedTables[1] . "." . $column;
        }
        $query .= ")";
        return $query;
    }

    public static function projectColumns()
    {
        if (!empty($_GET["inputSelect"])) {
            $query = " SELECT ";
            $inputSelectCounter = "";
            while (isset($_GET["inputSelect" . $inputSelectCounter])) {
                $query .= " " . $_GET["inputSelect" . $inputSelectCounter];
                $inputSelectCounter .= "_";
                if (empty($_GET["inputSelect" . $inputSelectCounter])) {
                    break;
                }
                $query .= ", ";
            }
            return $query;
        }
        return "";
    }

    public static function select()
    {
        if (!empty($_GET["inputWhereCon"])) {
            $query = " WHERE (";
            $inputWhereConCounter = "";
            while (isset($_GET["inputWhereCon" . $inputWhereConCounter])) {
                $query .= Query::createCondition($inputWhereConCounter);
                $query .= " " . $_GET["inputWhereCon" . $inputWhereConCounter] . " ";
                $inputWhereConCounter .= "_";
            }
            $query .= ")";
            return $query;
        }
        return "";
    }

    public static function project()
    {
        // Sanitize table and column names
        if (!Query::areTokensOK()) {
            return;
        }
        $query = Query::projectColumns();
        $selectedTables = preg_split("/,/", $_GET["inputFrom"]);
        if (count($selectedTables) > 1) {
            assert(count($selectedTables) == 2, "Two tables only supported for now.");
            $query .= Query::joinTwoTables($selectedTables);
        } else {
            $query .= $selectedTables[0];
        }

        return $query;
    }
}
?>


<?php
function handleCountRequest()
{
    $result = SQL::executePlainSQL("SELECT Count(*) FROM VideoGameMadeBy");

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
<div class="outer">
    <form method="GET" action="index.php">
        <input type="submit" name="getAction" value="<?= $getCount ?>"></p>
    </form>
</div>



<?php
function handleSPJRequest()
{
    $query = Query::project();
    $query .= Query::select();
    $results = SQL::executePlainSQL($query);

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
<div class="outer">
    <form method="GET" action="index.php">
        FROM:
        <select name="inputFrom" onChange="switchSelect(this);">
            <?php
            
            $temp = $pklist;
            foreach ($pklist as $table => $columns) {
                echo "<option value=\"" . $table . "\">" . $table . "</option>";
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
        </select><br /><br />
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
        <span> <select name="inputSelect" onChange="changeSelect(this)">
                <option value=""></option>
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
            </select><br /><br /></span>
        <script>
            var inputSelectCounter = "_";
            function changeSelect(menu) {
                if (menu.value == "") {
                    let divElements = menu.parentElement.getElementsByTagName("span");
                    if (divElements.length == 0) {
                        return;
                    }
                    divElements[0].remove();
                } else {
                    if (menu.parentElement.getElementsByTagName("span").length != 0) {
                        return;
                    }
                    menu2 = menu.parentElement.cloneNode(true);
                    for (const child of menu2.children) {
                        child.setAttribute("name", child.getAttribute("name") + inputSelectCounter);
                    }
                    menu.parentElement.appendChild(menu2);
                }
            }
        </script>
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
            </select><br /><br /></span>
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
                    if (menu.parentElement.getElementsByTagName("span").length != 0) {
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
    </form>
</div>



<?php
function handleDivideRequest()
{
    $command = "SELECT DISTINCT A.username
        FROM Adds A
        WHERE NOT EXISTS (
            SELECT GID 
            FROM VideoGameMadeBy V
            WHERE NOT EXISTS (
                SELECT A2.GID
                FROM Adds A2
                WHERE A2.GID = V.GID
                AND A.username = A2.username))";
    $result = SQL::executePlainSQL($command);
    printResult($result);
}
?>

<h3>Divide Query</h3>
<p>Finds all accounts that owns all games.</p>
<div class="outer">
    <form method="GET" action="index.php">
        <input type="submit" name="getAction" value="<?php echo $getDivide?>"></p>
    </form>
</div>




<?php
function handleQueryRequest()
{
    // Sanitize table and column names
    if (!Query::areTokensOK()) {
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
    $results = SQL::executePlainSQL($query);
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
<div class="outer">
    <form method="GET" action="index.php">
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
        </select><br /><br />
        SELECT Column: <input type="text" name="inputSelect">
        <br /><br />
        WHERE Column: <input type="text" name="inputWhere">
        <br /><br />
        GROUP BY: <input type="text" name="inputGroupBy">
        <br /><br />
        HAVING: <input type="text" name="inputHaving">
        <br /><br />
        <input type="submit" name="getAction" value="<?= $getQuery ?>"></p>
    </form>
</div>



<?php
function handleDisplayRequest()
{
    $commands = SQL::sql_file_to_array("select.sql");
    foreach ($commands as $command) {
        if (trim($command)) {
            $result = SQL::executePlainSQL($command);
            printResult($result);
        }
    }
}
?>

<h3>Display Tuples in DemoTable</h3>
<div class="outer">
    <form method="GET" action="index.php">
        <input type="submit" name="getAction" value="display"></p>
    </form>
</div>