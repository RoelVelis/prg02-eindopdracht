<?php
$host       = "localhost";
$username       = "root";
$password   = "";
$database   = "eindopdracht_prg2";
$port = "3307";

$db = mysqli_connect($host, $username, $password, $database, $port)
or die("Error: " . mysqli_connect_error());
