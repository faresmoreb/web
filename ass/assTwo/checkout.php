<?php
require_once "dbconfig.inc.php";

if (!isLoggedIn()) {
    redirectToLogin("checkout.php");
}

if (!isset($_SESSION["basket"]) || count($_SESSION["basket"]) == 0) {
    header("Location: basket.php");
    exit();
}

$errors = array();
$confirmed = false;
$orderId = "";
$total = 0;
foreach ($_SESSION["basket"] as $item) {
    $total = $total + floatval($item["unit_price"]) * intval($item["quantity"]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cardholder = trim($_POST["cardholder"]);
    $cardNumber = trim($_POST["card_number"]);
    $expiryMonth = trim($_POST["expiry_month"]);
    $expiryYear = trim($_POST["expiry_year"]);
    $cvv = trim($_POST["cvv"]);
    $currentYear = intval(date("Y"));

    if ($cardholder == "") { $errors[] = "Cardholder name is required."; }
    if (strlen($cardNumber) != 16 || !ctype_digit($cardNumber)) { $errors[] = "Card number must contain exactly 16 digits."; }
    if ($expiryMonth == "" || !ctype_digit($expiryMonth) || intval($expiryMonth) < 1 || intval($expiryMonth) > 12) { $errors[] = "Expiry month is invalid."; }
    if ($expiryYear == "" || !ctype_digit($expiryYear) || intval($expiryYear) < $currentYear || intval($expiryYear) > $currentYear + 10) { $errors[] = "Expiry year is invalid."; }
    if (strlen($cvv) != 3 || !ctype_digit($cvv)) { $errors[] = "CVV must contain exactly 3 digits."; }

    if (count($errors) == 0) {
        $orderId = generateTenDigitId();
        $check = $pdo->prepare("SELECT order_id FROM orders WHERE order_id = :order_id");
        $check->execute(array(":order_id" => $orderId));
        while ($check->fetch()) {
            $orderId = generateTenDigitId();
            $check->execute(array(":order_id" => $orderId));
        }
        try {
            $pdo->beginTransaction();
            $orderStmt = $pdo->prepare("INSERT INTO orders (order_id, user_id, order_date, total_amount) VALUES (:order_id, :user_id, NOW(), :total_amount)");
            $orderStmt->execute(array(":order_id" => $orderId, ":user_id" => $_SESSION["user_id"], ":total_amount" => $total));
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_item_id, order_id, product_id, product_name, unit_price, quantity, line_total) VALUES (:order_item_id, :order_id, :product_id, :product_name, :unit_price, :quantity, :line_total)");
            $stockStmt = $pdo->prepare("UPDATE products SET quantity = quantity - :quantity WHERE product_id = :product_id");
            foreach ($_SESSION["basket"] as $item) {
                $lineTotal = floatval($item["unit_price"]) * intval($item["quantity"]);
                $itemStmt->execute(array(":order_item_id" => generateTenDigitId(), ":order_id" => $orderId, ":product_id" => $item["product_id"], ":product_name" => $item["product_name"], ":unit_price" => $item["unit_price"], ":quantity" => $item["quantity"], ":line_total" => $lineTotal));
                $stockStmt->execute(array(":quantity" => $item["quantity"], ":product_id" => $item["product_id"]));
            }
            $pdo->commit();
            unset($_SESSION["basket"]);
            $confirmed = true;
        } catch (PDOException $e) {
            $pdo->rollback();
            $errors[] = "Order could not be placed.";
        }
    }
}

require_once "header.inc.php";
?>
<main>
<h2>Checkout</h2>
<?php if ($confirmed) { ?>
<section>
<h3>Order Confirmation</h3>
<p>Order placed successfully.</p>
<p>Order ID: <?php echo htmlText($orderId); ?></p>
<p>Order Total: <?php echo htmlText(number_format($total, 2)); ?></p>
<p><a href="products.php">Back to Products</a></p>
</section>
<?php } else { ?>
<?php if (count($errors) > 0) { ?><section><h3>Errors</h3><ul><?php foreach ($errors as $error) { ?><li><?php echo htmlText($error); ?></li><?php } ?></ul></section><?php } ?>
<section>
<h3>Cart Summary</h3>
<table border="1">
<thead><tr><th>Product Image</th><th>Product Name</th><th>Unit Price</th><th>Quantity</th><th>Line Total</th></tr></thead>
<tbody>
<?php foreach ($_SESSION["basket"] as $item) { $lineTotal = floatval($item["unit_price"]) * intval($item["quantity"]); ?>
<tr>
<td><img src="images/<?php echo htmlText($item["default_photo"]); ?>" alt="<?php echo htmlText($item["product_name"]); ?>" width="80" height="80"></td>
<td><?php echo htmlText($item["product_name"]); ?></td>
<td><?php echo htmlText(number_format($item["unit_price"], 2)); ?></td>
<td><?php echo htmlText($item["quantity"]); ?></td>
<td><?php echo htmlText(number_format($lineTotal, 2)); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<p>Total: <?php echo htmlText(number_format($total, 2)); ?></p>
</section>
<form method="post" action="checkout.php">
<fieldset>
<legend>Credit Card Information</legend>
<p><label>Cardholder Name <input type="text" name="cardholder" placeholder="Name as written on the card" required></label></p>
<p><label>Card Number <input type="text" name="card_number" maxlength="16" pattern="[0-9]{16}" placeholder="16 digits" required></label></p>
<p><label>Expiry Month <select name="expiry_month" required>
<?php for ($m = 1; $m <= 12; $m++) { $monthText = str_pad($m, 2, "0", STR_PAD_LEFT); ?>
<option value="<?php echo htmlText($monthText); ?>"><?php echo htmlText($monthText); ?></option>
<?php } ?>
</select></label></p>
<p><label>Expiry Year <select name="expiry_year" required>
<?php $year = intval(date("Y")); for ($y = $year; $y <= $year + 10; $y++) { ?>
<option value="<?php echo htmlText($y); ?>"><?php echo htmlText($y); ?></option>
<?php } ?>
</select></label></p>
<p><label>CVV <input type="text" name="cvv" maxlength="3" pattern="[0-9]{3}" placeholder="3 digits" required></label></p>
</fieldset>
<p><input type="submit" value="Place Order"></p>
</form>
<?php } ?>
</main>
<?php require_once "footer.inc.php"; ?>
