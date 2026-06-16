<?php
require_once "dbconfig.inc.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"]) && isset($_POST["action"])) {
    $productId = $_POST["product_id"];
    $action = $_POST["action"];
    if (ctype_digit($productId) && isset($_SESSION["basket"]) && isset($_SESSION["basket"][$productId])) {
        $quantity = intval($_SESSION["basket"][$productId]["quantity"]);
        if ($action == "decrease") {
            $quantity = $quantity - 1;
            if ($quantity > 0) {
                $_SESSION["basket"][$productId]["quantity"] = $quantity;
            } else {
                unset($_SESSION["basket"][$productId]);
            }
        } elseif ($action == "increase") {
            $_SESSION["basket"][$productId]["quantity"] = $quantity + 1;
        }
    }
    header("Location: basket.php");
    exit();
}

require_once "header.inc.php";
?>
<main>
<h2>My Cart</h2>
<?php if (!isset($_SESSION["basket"]) || count($_SESSION["basket"]) == 0) { ?>
<p>Your cart is empty.</p>
<p><a href="products.php">Continue Shopping</a></p>
<?php } else { ?>
<table border="1">
<thead>
<tr>
<th>Product Image</th>
<th>Product Name</th>
<th>Unit Price</th>
<th>Quantity</th>
<th>Line Total</th>
</tr>
</thead>
<tbody>
<?php
$total = 0;
foreach ($_SESSION["basket"] as $item) {
    $lineTotal = floatval($item["unit_price"]) * intval($item["quantity"]);
    $total = $total + $lineTotal;
?>
<tr>
<td><img src="images/<?php echo htmlText($item["default_photo"]); ?>" alt="<?php echo htmlText($item["product_name"]); ?>" width="80" height="80"></td>
<td><?php echo htmlText($item["product_name"]); ?></td>
<td><?php echo htmlText(number_format($item["unit_price"], 2)); ?></td>
<td>
<table>
<tr>
<td>
<form method="post" action="basket.php">
<input type="hidden" name="action" value="decrease">
<input type="hidden" name="product_id" value="<?php echo htmlText($item["product_id"]); ?>">
<input type="submit" value="-">
</form>
</td>
<td>
<?php echo htmlText($item["quantity"]); ?>
</td>
<td>
<form method="post" action="basket.php">
<input type="hidden" name="action" value="increase">
<input type="hidden" name="product_id" value="<?php echo htmlText($item["product_id"]); ?>">
<input type="submit" value="+">
</form>
</td>
</tr>
</table>
</td>
<td><?php echo htmlText(number_format($lineTotal, 2)); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<p>Cart Total: <?php echo htmlText(number_format($total, 2)); ?></p>
<p><a href="products.php">Continue Shopping</a></p>
<p><a href="checkout.php">Place Order</a></p>
<?php } ?>
</main>
<?php require_once "footer.inc.php"; ?>
