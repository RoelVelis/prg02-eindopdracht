<?php
$host       = "localhost";
$user       = "root";
$password   = "";
$database   = "eindopdracht_prg2";

$db = mysqli_connect($host, $user, $password, $database)
or die("Error: " . mysqli_connect_error());;
