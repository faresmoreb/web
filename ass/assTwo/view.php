<?php
require_once "dbconfig.inc.php";
require_once "Product.class.php";

$product = false;
$error = "";
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    $error = "Missing or invalid product ID.";
} else {
    $stmt = $pdo->prepare("SELECT product_id AS productId, product_name AS productName, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo AS defaultPhoto FROM products WHERE product_id = :product_id");
    $stmt->execute(array(":product_id" => $_GET["id"]));
    $product = $stmt->fetchObject("Product");
    if (!$product) {
        $error = "The requested product was not found.";
    }
}

require_once "header.inc.php";
if ($error != "") {
?>
<main>
<h2>Product Error</h2>
<p><?php echo htmlText($error); ?></p>
<p><a href="products.php">Back to Products</a></p>
</main>
<?php
} else {
    echo $product->displayProductPage();
}
require_once "footer.inc.php";
?>
