<?php
require_once "dbconfig.inc.php";

if (!isLoggedIn()) {
    $returnUrl = "add_to_basket.php";
    if (isset($_GET["id"])) {
        $returnUrl = "add_to_basket.php?id=" . $_GET["id"];
    }
    redirectToLogin($returnUrl);
}

$errors = array();
$product = false;
$productId = "";

if (!isCustomer()) {
    require_once "header.inc.php";
?>
<main>
<h2>Add to Cart</h2>
<p>Only customers can add products to the cart.</p>
<p><a href="products.php">Back to Products</a></p>
</main>
<?php
    require_once "footer.inc.php";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = trim($_POST["product_id"]);
} else {
    if (isset($_GET["id"])) {
        $productId = trim($_GET["id"]);
    }
}

if ($productId == "" || !ctype_digit($productId)) {
    $errors[] = "Invalid product ID.";
} else {
    $stmt = $pdo->prepare("SELECT product_id, product_name, price, quantity, default_photo FROM products WHERE product_id = :product_id");
    $stmt->execute(array(":product_id" => $productId));
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        $errors[] = "Product was not found.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $product) {
    $quantity = trim($_POST["quantity"]);
    if ($quantity == "" || !ctype_digit($quantity) || intval($quantity) <= 0) {
        $errors[] = "Quantity must be a positive integer.";
    } else {
        $requested = intval($quantity);
        $available = intval($product["quantity"]);
        $existing = 0;
        if (isset($_SESSION["basket"]) && isset($_SESSION["basket"][$productId])) {
            $existing = intval($_SESSION["basket"][$productId]["quantity"]);
        }
        if ($requested + $existing > $available) {
            $errors[] = "Requested quantity exceeds available stock.";
        } else {
            if (!isset($_SESSION["basket"])) {
                $_SESSION["basket"] = array();
            }
            if (isset($_SESSION["basket"][$productId])) {
                $_SESSION["basket"][$productId]["quantity"] = $_SESSION["basket"][$productId]["quantity"] + $requested;
            } else {
                $_SESSION["basket"][$productId] = array("product_id" => $product["product_id"], "product_name" => $product["product_name"], "unit_price" => $product["price"], "quantity" => $requested, "default_photo" => $product["default_photo"]);
            }
            header("Location: basket.php");
            exit();
        }
    }
}

require_once "header.inc.php";
?>
<main>
<h2>Add to Cart</h2>
<?php if (count($errors) > 0) { ?>
<section><h3>Errors</h3><ul><?php foreach ($errors as $error) { ?><li><?php echo htmlText($error); ?></li><?php } ?></ul></section>
<?php } ?>
<?php if ($product) { ?>
<form method="post" action="add_to_basket.php">
<input type="hidden" name="product_id" value="<?php echo htmlText($product["product_id"]); ?>">
<p><label>Product ID <input type="text" value="<?php echo htmlText($product["product_id"]); ?>" disabled></label></p>
<p><label>Product Name <input type="text" value="<?php echo htmlText($product["product_name"]); ?>" disabled></label></p>
<p><label>Unit Price <input type="text" value="<?php echo htmlText(number_format($product["price"], 2)); ?>" disabled></label></p>
<p><label>Quantity <input type="number" name="quantity" value="1" required></label></p>
<p><input type="submit" value="Add to Cart"></p>
</form>
<?php } ?>
<p><a href="products.php">Back to Products</a></p>
</main>
<?php require_once "footer.inc.php"; ?>
