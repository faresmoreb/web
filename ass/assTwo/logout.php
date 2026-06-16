<?php
require_once "dbconfig.inc.php";
session_destroy();
header("Location: products.php");
exit();
?>
