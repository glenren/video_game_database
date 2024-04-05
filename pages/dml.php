<?php
function handleInsertRequest()
{
    //Getting the values from user and insert data into the table
    $tuple = array(
        ":bind1" => rand(),
        ":bind2" => $_POST['gameTitle'],
        ":bind3" => strtoupper(date('d-M-y', strtotime($_POST['releaseDate']))), //has to be in form dd-MMMM-yyyy
        ":bind4" => $_POST['price'],
        ":bind5" => $_POST['category'],
        ":bind6" => $_POST['devteamName'] //case sensitive
    );
    $alltuples = array(
        $tuple
    );
    SQL::executeBoundSQL("INSERT INTO VideoGameMadeBy VALUES (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6)", $alltuples);

    global $success;
    if (
        $success
        && oci_commit(SQL::$db_conn)
    ) {
        popUp("Successfully added video game!");
    } else {
        popUp("Please add the developer for this game first!");
    }
}
?>
<h3>Add Video Game</h3>
<div class="outer"><form method="POST" action="index.php">
    Video Game Title: <input type="text" name="gameTitle"> <br /><br />
    Release Date: <input type="date" name="releaseDate"> <br /><br />
    Price: <input type="text" name="price"> <br /><br />
    Category: <input type="text" name="category"> <br /><br />
    Development Team: <input type="text" name="devteamName"> <br /><br />
    <input type="submit" name="postAction" value="<?= $postInsert ?>"></p>
</form></div>



<?php

function handleDeleteRequest() {
    if (!$_POST['gameTitle']) {
        popUp("Please enter a game name!");
        return;
    }

    $command = "SELECT * FROM VideoGameMadeBy WHERE Name LIKE '" . $_POST['gameTitle'] . "'  collate binary_ci";

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

    $command = "DELETE FROM VideoGameMadeBy WHERE GID = '" . $log[0]["GID"] . "'";

    $result = SQL::executePlainSQL($command);

    if (oci_commit(SQL::$db_conn)) {
        popUp("Successfully deleted video game!");
    } else {
        popUp("Couldn't add video game!");
    }
}

function dupeGames($log) {
    echo "<h3>Did You Mean:</h3><div class=\"outer\">";
    echo "<form method=\"POST\" action=\"index.php\">";

    foreach ($log as $row) {
        echo "<input type=\"radio\" id=\"". $row["GID"] . "\" name=\"dupeID\" value=\""
            . $row["GID"] . "\">";
        echo "<label for=\"" . $row["GID"] . "\">" . $row["NAME"]
            . " (" . $row["RELEASEDATE"] . ") by "
            . $row["DEVTEAMNAME"] . "</label></br></br>";
    }

    echo "</br><input type=\"submit\" name=\"postAction\" value=\"Submit\"></p></form></div>";

}

function handleSubmitRequest() {
    if (!$_POST['dupeID']) {
        popUp("Please select a game!");
        return;
    }

    $command = "DELETE FROM VideoGameMadeBy WHERE GID = '" . $_POST['dupeID'] . "'";

    $result = SQL::executePlainSQL($command);

    if (oci_commit(SQL::$db_conn)) {
        popUp("Successfully deleted video game!");
    } else {
        popUp("Couldn't add video game!");
    }
}

?>
<h3> Delete Video Game</h3>
<div class="outer"><form method="POST" action="index.php">
    Video Game Title: <input type="text" name="gameTitle"><br /><br />
    <input type="submit" name="postAction" value="<?= $postDelete ?>"></p>
</form></div>

<?php
function handleUpdateRequest()
{
    $username = $_POST['username'];
	$displayname = $_POST['displayname'] ?? 'default';
	$email = $_POST['email'] ?? 'email';
	
	SQL::executePlainSQL("UPDATE Account SET email='" . $email . "', displayname='" . $displayname . "' WHERE username='" . $username . "'");

    global $success;
    if (
        $success
        && oci_commit(SQL::$db_conn)
    ) {
        popUp("Successfully updated account!");
    } else {
        popUp("Database Error");
    }
}
?>

<h3>Update Account Information</h3>
<div class="outer"><p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything. Include old value if not updating with 
a new value</p>
<br/>
<form method="POST" action="index.php">
    Enter Username: <input type="text" name="username"> <br/><br/>
	Update display name: <input type="text" name="displayname"> <br /><br />
	Update email: <input type="text" name="email"> <br /><br />
    <input type="submit" name="postAction" value="<?= $postUpdate ?>"></p>
</form></div>