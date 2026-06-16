<?php
require_once "dbconfig.inc.php";
require_once "Product.class.php";

if (isset($_GET["reset"])) {
    unset($_SESSION["product_search_state"]);
    header("Location: products.php");
    exit();
}

$state = array("name" => "", "max_price" => "", "category" => "", "sort_col" => "product_id", "sort_dir" => "ASC", "current_page" => 1, "per_page" => 5);
if (isset($_SESSION["product_search_state"])) {
    $saved = json_decode($_SESSION["product_search_state"], true);
    if (is_array($saved)) {
        foreach ($state as $key => $value) {
            if (isset($saved[$key])) {
                $state[$key] = $saved[$key];
            }
        }
    }
}

$sortCols = array("product_id" => "product_id", "price" => "price", "category" => "category");
$sortDirs = array("ASC" => "ASC", "DESC" => "DESC");
if (!isset($sortCols[$state["sort_col"]])) {
    $state["sort_col"] = "product_id";
}
if (!isset($sortDirs[$state["sort_dir"]])) {
    $state["sort_dir"] = "ASC";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $state["name"] = trim($_POST["name"]);
    $state["max_price"] = trim($_POST["max_price"]);
    $state["category"] = trim($_POST["category"]);
    $state["current_page"] = 1;
}

if (isset($_GET["sort_col"]) && isset($sortCols[$_GET["sort_col"]])) {
    $sortCol = $_GET["sort_col"];
    if ($state["sort_col"] == $sortCol && $state["sort_dir"] == "ASC") {
        $state["sort_dir"] = "DESC";
    } else {
        $state["sort_dir"] = "ASC";
    }
    $state["sort_col"] = $sortCol;
    $state["current_page"] = 1;
}

if (!isset($sortCols[$state["sort_col"]])) {
    $state["sort_col"] = "product_id";
}
if (!isset($sortDirs[$state["sort_dir"]])) {
    $state["sort_dir"] = "ASC";
}

if (isset($_GET["page"]) && ctype_digit($_GET["page"]) && intval($_GET["page"]) > 0) {
    $state["current_page"] = intval($_GET["page"]);
}

if (isset($_GET["per_page"])) {
    $pp = $_GET["per_page"];
    if ($pp == "0" || $pp == "5" || $pp == "10" || $pp == "15" || $pp == "20") {
        $state["per_page"] = intval($pp);
        $state["current_page"] = 1;
    }
}

$_SESSION["product_search_state"] = json_encode($state);

$categoryStmt = $pdo->prepare("SELECT DISTINCT category FROM products ORDER BY category ASC");
$categoryStmt->execute(array());
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

$where = " WHERE 1 = 1";
$params = array();
if ($state["name"] != "") {
    $where .= " AND product_name LIKE :name";
    $params[":name"] = "%" . $state["name"] . "%";
}
if ($state["max_price"] != "" && is_numeric($state["max_price"])) {
    $where .= " AND price <= :max_price";
    $params[":max_price"] = $state["max_price"];
}
if ($state["category"] != "") {
    $where .= " AND category = :category";
    $params[":category"] = $state["category"];
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products" . $where);
$countStmt->execute($params);
$totalRows = intval($countStmt->fetchColumn());

$perPage = intval($state["per_page"]);
$totalPages = 1;
$offset = 0;
if ($perPage > 0) {
    $totalPages = intval(ceil($totalRows / $perPage));
    if ($totalPages < 1) {
        $totalPages = 1;
    }
    if ($state["current_page"] > $totalPages) {
        $state["current_page"] = $totalPages;
    }
    $offset = ($state["current_page"] - 1) * $perPage;
}

$orderBy = $state["sort_col"];
$sortDir = $state["sort_dir"];
$sql = "SELECT product_id AS productId, product_name AS productName, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo AS defaultPhoto FROM products" . $where . " ORDER BY " . $orderBy . " " . $sortDir;
if ($perPage > 0) {
    $sql .= " LIMIT :limit_value OFFSET :offset_value";
}
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
if ($perPage > 0) {
    $stmt->bindValue(":limit_value", $perPage, PDO::PARAM_INT);
    $stmt->bindValue(":offset_value", $offset, PDO::PARAM_INT);
}
$stmt->execute();

require_once "header.inc.php";
?>
<main>
<h2>Products</h2>
<section>
<figure>
<img src="images/banner.png" alt="Baladi Store banner" width="900">
<figcaption>Palestinian grape products from Hebron.</figcaption>
</figure>
</section>
<section>
<h3>Testing Accounts</h3>
<p>Employee: fares.moreb@icloud.com</p>
<p><strong>Password:</strong> fares1234</p>
<p>Customer1: cristiano.ronaldo@icloud.com</p>
<p><strong>Password:</strong> SUII123</p>
<p>Customer2: Lionel.Messi@icloud.com</p>
<p><strong>Password:</strong> Messi123</p>
</section>
<?php if (isEmployee()) { ?>
<p><a href="add.php">Add New Product</a></p>
<?php } ?>
<section>
<h3>Products Table</h3>
<table>
<tr>
<td>
<form method="post" action="products.php">
<input type="hidden" name="form_name" value="filter">
<table>
<tr>
<td><label for="name">Product Name</label></td>
<td><input type="text" id="name" name="name" placeholder="Product Name" value="<?php echo htmlText($state["name"]); ?>"></td>
<td><label for="max_price">Max Price</label></td>
<td><input type="number" id="max_price" name="max_price" step="0.01" placeholder="Max Price" value="<?php echo htmlText($state["max_price"]); ?>"></td>
<td><label for="category">Category</label></td>
<td><select id="category" name="category">
<option value="">Select Category</option>
<?php foreach ($categories as $category) { ?>
<option value="<?php echo htmlText($category); ?>" <?php if ($state["category"] == $category) { echo "selected"; } ?>><?php echo htmlText($category); ?></option>
<?php } ?>
</select></td>
<td><input type="submit" value="Filter"></td>
<td><a href="products.php?reset=1">Reset / Show All</a></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
<table border="1">
<thead>
<tr>
<th>Product Image</th>
<th><a href="products.php?sort_col=product_id">Product ID</a><?php if ($state["sort_col"] == "product_id") { echo " " . htmlText($state["sort_dir"]); } ?></th>
<th>Product Name</th>
<th><a href="products.php?sort_col=category">Category</a><?php if ($state["sort_col"] == "category") { echo " " . htmlText($state["sort_dir"]); } ?></th>
<th><a href="products.php?sort_col=price">Price</a><?php if ($state["sort_col"] == "price") { echo " " . htmlText($state["sort_dir"]); } ?></th>
<th>Quantity</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
while ($product = $stmt->fetchObject("Product")) {
    echo $product->displayInTable();
}
?>
</tbody>
</table>
<?php if ($totalRows == 0) { ?>
<p>No products found.</p>
<?php } ?>
<?php if ($perPage > 0) { ?>
<table>
<tr>
<td>Page <?php echo htmlText($state["current_page"]); ?> of <?php echo htmlText($totalPages); ?></td>
<td>
<?php if ($state["current_page"] > 1) { ?>
<a href="products.php?page=<?php echo htmlText($state["current_page"] - 1); ?>">Previous</a>
<?php } ?>
</td>
<td>
<?php if ($state["current_page"] < $totalPages) { ?>
<a href="products.php?page=<?php echo htmlText($state["current_page"] + 1); ?>">Next</a>
<?php } ?>
</td>
</tr>
</table>
<?php } ?>
<form method="get" action="products.php">
<p><label>Products Per Page <select name="per_page">
<option value="5" <?php if ($perPage == 5) { echo "selected"; } ?>>5</option>
<option value="10" <?php if ($perPage == 10) { echo "selected"; } ?>>10</option>
<option value="15" <?php if ($perPage == 15) { echo "selected"; } ?>>15</option>
<option value="20" <?php if ($perPage == 20) { echo "selected"; } ?>>20</option>
<option value="0" <?php if ($perPage == 0) { echo "selected"; } ?>>All</option>
</select></label> <input type="submit" value="Apply"></p>
</form>
</section>
</main>
<?php require_once "footer.inc.php"; ?>
