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
require("pages/library.php");
?>

<html>

<head>
    <!-- setting the style for the nav bar. borrowed from https://www.w3schools.com/css/css_navbar_vertical.asp -->
    <style>
        ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            width: 200px;
            background-color: #f1f1f1;
        }

        li a {
            display: block;
            color: #000;
            padding: 8px 16px;
            text-decoration: none;
        }

        /* Change the link color on hover */
        li a:hover {
            background-color: #555;
            color: white;
        }
    </style>
</head>

<body>
    <h1>Video Game Database</h1>
    <h2> CPSC 304 2023w2 project by Kat Duangkham, Glen Ren and Chanaldy Soenarjo</h2>
    <!-- navigation bar to go to different pages -->
    <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#news">Video Games</a></li>
        <li><a href="account_test.php">Users</a></li>
        <li><a href="#about">Dev Teams</a></li>
    </ul>
</body>
<html>

<html>

<head>
	<script>
		var pklist = <?php echo json_encode($pklist) ?>;
		var columnslist = <?php echo json_encode($columnslist) ?>;
	</script>
	<link rel="stylesheet" href="style.css">
	<title>CPSC 304 PHP/Oracle Demonstration</title>
</head>



<body>

	<hr />
	<h2>Reset</h2>
	<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you
		MUST use reset</p>
	<form method="POST" action="index.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<p><input type="submit" name="postAction" value="<?= $postReset ?>"></p>
	</form>
	<?php
	function handleResetRequest()
	{
		// Drop old table and create new ones
		run_sql_file("database.sql");
	}
	?>

	<?php
	include("pages/dml.php");
	include("pages/query.php");

	if (isset($_POST['postAction'])) {
		handlePOSTRequest();
	} else if (
		isset($_GET['getAction'])
	) {
		handleGETRequest();
	}
	?>
</body>

</html>