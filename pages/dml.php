<?php
function handleInsertRequest()
{
    global $db_conn;
    //Getting the values from user and insert data into the table
    $tuple = array(
        ":bind1" => rand(),
        ":bind2" => $_POST['gameTitle'],
        ":bind3" => $_POST['releaseDate'], //has to be in form dd-MMMM-yyyy
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
        && oci_commit($db_conn)
    ) {
        popUp("Successfully added video game!");
    } else {
        popUp("Please add the developer for this game first!");
    }
}
?>
<h3>Add Video Game</h3>
<form method="POST" action="index.php">
<div class="outer"><form method="POST" action="index.php">
    Video Game Title: <input type="text" name="gameTitle"> <br /><br />
    Release Date: <input type="text" name="releaseDate"> <br /><br />
    Price: <input type="text" name="price"> <br /><br />
    Category: <input type="text" name="category"> <br /><br />
    Development Team: <input type="text" name="devteamName"> <br /><br />
    <input type="submit" name="postAction" value="<?= $postInsert ?>"></p>
</form></div>



<?php
function handleDeleteRequest()
{
    global $db_conn;
    //getting value from user and delete data from table
	$name = $_POST['gameTitle'];

	SQL::executePlainSQL("DELETE FROM VideoGameMadeBy WHERE Name LIKE'" . $name . "' collate binary_ci");

    if (oci_commit($db_conn)) {
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
    global $db_conn;
    $username = $_POST['username'];
	$displayname = $_POST['displayname'] ?? 'default';
	$email = $_POST['email'] ?? 'email';
	
	SQL::executePlainSQL("UPDATE Account SET email='" . $email . "', displayname='" . $displayname . "' WHERE username='" . $username . "'");

    global $success;
    if (
        $success
        && oci_commit($db_conn)
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