<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Baladi Store</title>
</head>
<body>
<header>
<h1>Baladi Store</h1>
<img src="images/logo.png" alt="Baladi Store logo" width="250">
<nav>
<ul>
<li><a href="products.php">Products</a></li>
<?php if (!isLoggedIn()) { ?>
<li><a href="register.php">Register</a></li>
<li><a href="login.php">Login</a></li>
<?php } ?>
<?php if (isLoggedIn()) { ?>
<li><a href="logout.php">Logout</a></li>
<?php } ?>
<?php if (isCustomer()) { ?>
<li><a href="basket.php">My Cart</a></li>
<?php } ?>
</ul>
</nav>
<?php if (isLoggedIn()) { ?>
<p>Signed in as <?php echo htmlText($_SESSION["first_name"] . " " . $_SESSION["last_name"]); ?> (<?php echo htmlText(ucfirst($_SESSION["role"])); ?>)</p>
<?php } ?>
</header>
