<?php
session_start();

define("DB_HOST", "localhost");
define("DB_NAME", "web1231854_souvenirStore");
define("DB_USER", "web1231854_dbuser");
define("DB_PASS", "Fares1-3-5");

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

function htmlText($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function isLoggedIn() {
    return isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true;
}

function isEmployee() {
    return isLoggedIn() && isset($_SESSION["role"]) && $_SESSION["role"] == "employee";
}

function isCustomer() {
    return isLoggedIn() && isset($_SESSION["role"]) && $_SESSION["role"] == "customer";
}

function generateTenDigitId() {
    return strval(rand(1000000000, 2147483647));
}

function redirectToLogin($returnUrl) {
    $_SESSION["return_url"] = $returnUrl;
    header("Location: login.php");
    exit();
}
?>
