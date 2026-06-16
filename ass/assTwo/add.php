<?php
require_once "dbconfig.inc.php";

if (!isEmployee()) {
    redirectToLogin("add.php");
}

$errors = array();
$message = "";
$categories = array("Fresh Grapes", "Grape Vine Leaves", "Grape Pantry", "Food Gifts");
$productName = "";
$category = "";
$price = "";
$quantity = "";
$rating = "";
$description = "";
$defaultPhoto = "1";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = trim($_POST["product_name"]);
    $category = trim($_POST["category"]);
    $price = trim($_POST["price"]);
    $quantity = trim($_POST["quantity"]);
    $rating = trim($_POST["rating"]);
    $description = trim($_POST["description"]);
    $defaultPhoto = $_POST["default_photo"];

    if ($productName == "") { $errors[] = "Product name is required."; }
    if ($category == "") { $errors[] = "Category is required."; }
    if ($price == "" || !is_numeric($price) || floatval($price) <= 0) { $errors[] = "Price must be a positive value."; }
    if ($quantity == "" || !ctype_digit($quantity)) { $errors[] = "Quantity must be a non-negative integer."; }
    if ($rating == "" || !is_numeric($rating) || floatval($rating) < 1 || floatval($rating) > 5) { $errors[] = "Rating must be from 1 to 5."; }
    if ($description == "") { $errors[] = "Description is required."; }
    if ($defaultPhoto != "1" && $defaultPhoto != "2" && $defaultPhoto != "3") { $defaultPhoto = "1"; }

    for ($i = 1; $i <= 3; $i++) {
        $key = "photo" . $i;
        if (!isset($_FILES[$key]) || $_FILES[$key]["error"] != 0) {
            $errors[] = "Photo " . $i . " is required.";
        } elseif ($_FILES[$key]["type"] != "image/jpeg") {
            $errors[] = "Photo " . $i . " must be a JPEG image.";
        }
    }

    if (count($errors) == 0) {
        $insert = $pdo->prepare("INSERT INTO products (product_name, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo) VALUES (:product_name, :category, :description, :price, :quantity, :rating, :photo1, :photo2, :photo3, :default_photo)");
        $insert->execute(array(":product_name" => $productName, ":category" => $category, ":description" => $description, ":price" => $price, ":quantity" => $quantity, ":rating" => $rating, ":photo1" => "", ":photo2" => "", ":photo3" => "", ":default_photo" => ""));
        $productId = $pdo->lastInsertId();
        $photo1 = $productId . "_1.jpeg";
        $photo2 = $productId . "_2.jpeg";
        $photo3 = $productId . "_3.jpeg";
        move_uploaded_file($_FILES["photo1"]["tmp_name"], "images/" . $photo1);
        move_uploaded_file($_FILES["photo2"]["tmp_name"], "images/" . $photo2);
        move_uploaded_file($_FILES["photo3"]["tmp_name"], "images/" . $photo3);
        $file = $photo1;
        if ($defaultPhoto == "2") { $file = $photo2; }
        if ($defaultPhoto == "3") { $file = $photo3; }
        $update = $pdo->prepare("UPDATE products SET photo1 = :photo1, photo2 = :photo2, photo3 = :photo3, default_photo = :default_photo WHERE product_id = :product_id");
        $update->execute(array(":photo1" => $photo1, ":photo2" => $photo2, ":photo3" => $photo3, ":default_photo" => $file, ":product_id" => $productId));
        header("Location: products.php");
        exit();
    }
}

require_once "header.inc.php";
?>
<main>
<h2>Add New Product</h2>
<?php if ($message != "") { ?>
<p><?php echo htmlText($message); ?></p>
<?php } ?>
<?php if (count($errors) > 0) { ?>
<section><h3>Errors</h3><ul><?php foreach ($errors as $error) { ?><li><?php echo htmlText($error); ?></li><?php } ?></ul></section>
<?php } ?>
<form method="post" action="add.php" enctype="multipart/form-data">
<p><label>Product Name <input type="text" name="product_name" placeholder="Enter product name" value="<?php echo htmlText($productName); ?>" required></label></p>
<p><label>Category <select name="category" required>
<option value="">Select Category</option>
<?php foreach ($categories as $cat) { ?><option value="<?php echo htmlText($cat); ?>" <?php if ($category == $cat) { echo "selected"; } ?>><?php echo htmlText($cat); ?></option><?php } ?>
</select></label></p>
<p><label>Price <input type="number" name="price" min="0.01" step="0.01" placeholder="Positive price" value="<?php echo htmlText($price); ?>" required></label></p>
<p><label>Quantity <input type="number" name="quantity" min="0" step="1" placeholder="Non-negative quantity" value="<?php echo htmlText($quantity); ?>" required></label></p>
<p><label>Rating (1.0 - 5.0) <input type="number" name="rating" min="1" max="5" step="0.1" value="<?php echo htmlText($rating); ?>" required></label></p>
<p><label>Description <textarea name="description" placeholder="Write product description" required><?php echo htmlText($description); ?></textarea></label></p>
<p><label>Photo 1 <input type="file" name="photo1" accept="image/jpeg" required></label></p>
<p><label>Photo 2 <input type="file" name="photo2" accept="image/jpeg" required></label></p>
<p><label>Photo 3 <input type="file" name="photo3" accept="image/jpeg" required></label></p>
<fieldset>
<legend>Default Photo</legend>
<p><label><input type="radio" name="default_photo" value="1" <?php if ($defaultPhoto == "1") { echo "checked"; } ?>> Photo 1</label></p>
<p><label><input type="radio" name="default_photo" value="2" <?php if ($defaultPhoto == "2") { echo "checked"; } ?>> Photo 2</label></p>
<p><label><input type="radio" name="default_photo" value="3" <?php if ($defaultPhoto == "3") { echo "checked"; } ?>> Photo 3</label></p>
</fieldset>
<p><input type="submit" value="Add New Product"></p>
</form>
</main>
<?php require_once "footer.inc.php"; ?>
