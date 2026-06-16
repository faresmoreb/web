<?php
require_once "dbconfig.inc.php";

if (!isEmployee()) {
    $returnUrl = "edit.php";
    if (isset($_GET["id"])) {
        $returnUrl = "edit.php?id=" . $_GET["id"];
    }
    redirectToLogin($returnUrl);
}

$errors = array();
$message = "";
$product = false;
$productId = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = trim($_POST["product_id"]);
    $price = trim($_POST["price"]);
    $quantity = trim($_POST["quantity"]);
    $description = trim($_POST["description"]);
    $defaultPhoto = trim($_POST["default_photo"]);

    if ($productId == "" || !ctype_digit($productId)) { $errors[] = "Invalid product ID."; }
    if ($price == "" || !is_numeric($price) || floatval($price) <= 0) { $errors[] = "Price must be positive."; }
    if ($quantity == "" || !ctype_digit($quantity)) { $errors[] = "Quantity must be a non-negative integer."; }
    if ($description == "") { $errors[] = "Description is required."; }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->execute(array(":product_id" => $productId));
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        $errors[] = "Product was not found.";
    }

    if ($product) {
        $photo1 = $product["photo1"];
        $photo2 = $product["photo2"];
        $photo3 = $product["photo3"];
        $oldPhoto1 = $photo1;
        $oldPhoto2 = $photo2;
        $oldPhoto3 = $photo3;
        for ($i = 1; $i <= 3; $i++) {
            $key = "photo" . $i;
            if (isset($_FILES[$key]) && $_FILES[$key]["error"] == 0) {
                if ($_FILES[$key]["type"] != "image/jpeg") {
                    $errors[] = "Replacement photo " . $i . " must be JPEG.";
                } else {
                    $fileName = $productId . "_" . $i . ".jpeg";
                    move_uploaded_file($_FILES[$key]["tmp_name"], "images/" . $fileName);
                    if ($i == 1) { $photo1 = $fileName; }
                    if ($i == 2) { $photo2 = $fileName; }
                    if ($i == 3) { $photo3 = $fileName; }
                }
            }
        }
        if ($defaultPhoto == $oldPhoto1) { $defaultPhoto = $photo1; }
        if ($defaultPhoto == $oldPhoto2) { $defaultPhoto = $photo2; }
        if ($defaultPhoto == $oldPhoto3) { $defaultPhoto = $photo3; }
        if ($defaultPhoto != $photo1 && $defaultPhoto != $photo2 && $defaultPhoto != $photo3) {
            $errors[] = "Default photo selection is invalid.";
        }
    }

    if (count($errors) == 0) {
        $update = $pdo->prepare("UPDATE products SET price = :price, quantity = :quantity, description = :description, photo1 = :photo1, photo2 = :photo2, photo3 = :photo3, default_photo = :default_photo WHERE product_id = :product_id");
        $update->execute(array(":price" => $price, ":quantity" => $quantity, ":description" => $description, ":photo1" => $photo1, ":photo2" => $photo2, ":photo3" => $photo3, ":default_photo" => $defaultPhoto, ":product_id" => $productId));
        header("Location: products.php");
        exit();
    }
} else {
    if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
        $productId = $_GET["id"];
    }
}

if ($productId != "") {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->execute(array(":product_id" => $productId));
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

require_once "header.inc.php";
?>
<main>
<h2>Edit Product</h2>
<?php if ($message != "") { ?><p><?php echo htmlText($message); ?></p><?php } ?>
<?php if (count($errors) > 0) { ?><section><h3>Errors</h3><ul><?php foreach ($errors as $error) { ?><li><?php echo htmlText($error); ?></li><?php } ?></ul></section><?php } ?>
<?php if (!$product) { ?>
<p>Product was not found.</p>
<p><a href="products.php">Back to Products</a></p>
<?php } else { ?>
<form method="post" action="edit.php" enctype="multipart/form-data">
<input type="hidden" name="product_id" value="<?php echo htmlText($product["product_id"]); ?>">
<p><label>Product ID <input type="text" value="<?php echo htmlText($product["product_id"]); ?>" disabled></label></p>
<p><label>Product Name <input type="text" value="<?php echo htmlText($product["product_name"]); ?>" disabled></label></p>
<p><label>Category <select disabled><option selected><?php echo htmlText($product["category"]); ?></option></select></label></p>
<p><label>Rating <input type="text" value="<?php echo htmlText($product["rating"]); ?>" disabled></label></p>
<p><label>Price <input type="number" name="price" step="0.01" value="<?php echo htmlText($product["price"]); ?>" required></label></p>
<p><label>Quantity <input type="number" name="quantity" value="<?php echo htmlText($product["quantity"]); ?>" required></label></p>
<p><label>Description <textarea name="description" required><?php echo htmlText($product["description"]); ?></textarea></label></p>
<fieldset>
<legend>Default Photo</legend>
<p><label><input type="radio" name="default_photo" value="<?php echo htmlText($product["photo1"]); ?>" <?php if ($product["default_photo"] == $product["photo1"]) { echo "checked"; } ?>> Photo 1</label></p>
<p><img src="images/<?php echo htmlText($product["photo1"]); ?>" alt="Photo 1" width="160" height="120"></p>
<p><label><input type="radio" name="default_photo" value="<?php echo htmlText($product["photo2"]); ?>" <?php if ($product["default_photo"] == $product["photo2"]) { echo "checked"; } ?>> Photo 2</label></p>
<p><img src="images/<?php echo htmlText($product["photo2"]); ?>" alt="Photo 2" width="160" height="120"></p>
<p><label><input type="radio" name="default_photo" value="<?php echo htmlText($product["photo3"]); ?>" <?php if ($product["default_photo"] == $product["photo3"]) { echo "checked"; } ?>> Photo 3</label></p>
<p><img src="images/<?php echo htmlText($product["photo3"]); ?>" alt="Photo 3" width="160" height="120"></p>
</fieldset>
<fieldset>
<legend>Replace Photo</legend>
<p><label>Replace Photo 1 <input type="file" name="photo1" accept="image/jpeg"></label></p>
<p><label>Replace Photo 2 <input type="file" name="photo2" accept="image/jpeg"></label></p>
<p><label>Replace Photo 3 <input type="file" name="photo3" accept="image/jpeg"></label></p>
</fieldset>
<p><input type="submit" value="Save Product Changes"></p>
</form>
<?php } ?>
</main>
<?php require_once "footer.inc.php"; ?>
