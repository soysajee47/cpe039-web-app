<?php
$servername = "altaria.proxy.rlwy.net";
$username = "root";
$password = "VujIQdtUhxQFggLnchLxmiHPeHmsqlRI";
$dbname = "db_northwind";
$port = "55792";

try {

  $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
} catch(PDOException $e) {
  throw $e;
}
?>