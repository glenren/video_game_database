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
<h2>Add A Video Game</h2>
<form method="POST" action="dml.php">
    GID: <input type="text" name="GID"> <br /><br />
    Video Game Title: <input type="text" name="gameTitle"> <br /><br />
    Release Date: <input type="text" name="releaseDate"> <br /><br />
    Price: <input type="text" name="price"> <br /><br />
    Category: <input type="text" name="category"> <br /><br />
    Development Team: <input type="text" name="devteamName"> <br /><br />
    <input type="submit" name="postAction" value="<?= $postInsert ?>"></p>
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
<h2> Delete Video Game</h2>
<form method="POST" action="dml.php">
    Video Game Title: <input type="text" name="gameTitle"><br /><br />
    <input type="submit" name="postAction" value="<?= $getCount ?>"></p>
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
<h2>Update Name in DemoTable</h2>
<p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>
<form method="POST" action="dml.php">
    Old Name: <input type="text" name="oldName"> <br /><br />
    New Name: <input type="text" name="newName"> <br /><br />
    <input type="submit" name="postAction" value="<?= $postUpdate ?>"></p>
</form>
