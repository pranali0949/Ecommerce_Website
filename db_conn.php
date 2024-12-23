<?php

$sname= "localhost";
$unmae= "root";
$password = "";
$db_name = "bookshop";
$port = 3310;
$conn = mysqli_connect($sname, $unmae, $password, $db_name, $port);

if (!$conn) {
	echo "Connection failed!";
}