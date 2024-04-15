<?php
require ("pages/library.php");
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
            <li><a href="pages/games.php">Games</a></li>
            <li><a href="pages/account_test.php">Users</a></li>
            <li><a href="pages/devteam.php">Dev Teams</a></li>
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
            SQL::run_sql_file("database.sql");
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