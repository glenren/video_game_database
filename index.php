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
require ("pages/library.php");

function handleRequests()
{
    if (!connectToDB()) {
        popUp("Could not connect to database when handling request.");
    }
    if (isset($_POST['postAction'])) {
        global $postReset;
        global $postUpdate;
        global $postInsert;
        global $postDelete;
        ("handle" . $_GET['postAction'] . "Request")();
    }
    if (isset($_GET['getAction'])) {
        global $getCount;
        global $getDisplay;
        global $getQuery;
        global $getSPJ;
        global $getLookUp;
        ("handle" . $_GET['getAction'] . "Request")();
    }
    disconnectFromDB();
}
?>

<html>

<head>
    <script>
        var pklist = <?php echo json_encode($pklist) ?>;
        var columnslist = <?php echo json_encode($columnslist) ?>;
    </script>
    <link rel="stylesheet" href="style.css">
</head>



<body>
    <div class="nav">
        <ul>
            <li class="spacer">|</li>
            <li class="text">VIDEO GAME DATABASE</li>
            <li><a class="active" href="index.php">Home</a></li>
            <li><a href="#news">Games</a></li>
            <li><a href="pages/account_test.php">Users</a></li>
            <li><a href="#about">Dev Teams</a></li>
        </ul>
    </div>

    <div class="main">
        <h1>Video Game Database</h1>
        <h3>Reset</h3>
        <div class="outer">
            <p>Reset the table. If this is the <b>first time</b> you're running the website, you <b>must</b> reset.</p>
            <form method="POST" action="index.php">
                <p><input type="submit" name="postAction" value="<?= $postReset ?>"></p>
            </form>
        </div>

        <?php
        function handleResetRequest()
        {
            // Drop old table and create new ones
            run_sql_file("database.sql");
            popUp("Reset successful!");
        }
        ?>

        <?php
        include ("pages/dml.php");
        include ("pages/query.php");

        handleRequests();
        ?>
</body>

</html>