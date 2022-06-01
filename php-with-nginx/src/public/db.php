<?php
$host = 'db';
$username = "arpan";
$password = "password";

// Create connection
// $conn = new \MySQLi($host, $username, $password);
// $conn = new PDO($host, $username, $password);
// $conn = new PDO("mysql:host=$host;dbname=pureeats", $username, $password);

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// } else {
//     echo "Connected to MySQL server successfully!";
// }

try {
    $conn = new PDO("mysql:host=$host;dbname=pureeats", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
  } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
?>