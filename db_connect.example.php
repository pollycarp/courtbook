<?php
// Copy this file to db_connect.php and fill in your credentials.
// db_connect.php is excluded from version control (.gitignore).

$host     = 'localhost';        // e.g. sql309.infinityfree.com for InfinityFree
$dbname   = 'courtbook_db';     // your database name
$username = 'root';             // your database username
$password = '';                 // your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}
?>
