<?php
class Product {
    private $productId;
    private $productName;
    private $category;
    private $description;
    private $price;
    private $quantity;
    private $rating;
    private $photo1;
    private $photo2;
    private $photo3;
    private $defaultPhoto;

    public function __construct($productId = "", $productName = "", $category = "", $description = "", $price = "", $quantity = "", $rating = "", $photo1 = "", $photo2 = "", $photo3 = "", $defaultPhoto = "") {
        if ($productId != "") {
            $this->productId = $productId;
            $this->productName = $productName;
            $this->category = $category;
            $this->description = $description;
            $this->price = $price;
            $this->quantity = $quantity;
            $this->rating = $rating;
            $this->photo1 = $photo1;
            $this->photo2 = $photo2;
            $this->photo3 = $photo3;
            $this->defaultPhoto = $defaultPhoto;
        }
    }

    public function getProductId() { return $this->productId; }
    public function getProductName() { return $this->productName; }
    public function getCategory() { return $this->category; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getQuantity() { return $this->quantity; }
    public function getRating() { return $this->rating; }
    public function getPhoto1() { return $this->photo1; }
    public function getPhoto2() { return $this->photo2; }
    public function getPhoto3() { return $this->photo3; }
    public function getDefaultPhoto() { return $this->defaultPhoto; }

    public function setProductId($value) { $this->productId = $value; }
    public function setProductName($value) { $this->productName = $value; }
    public function setCategory($value) { $this->category = $value; }
    public function setDescription($value) { $this->description = $value; }
    public function setPrice($value) { $this->price = $value; }
    public function setQuantity($value) { $this->quantity = $value; }
    public function setRating($value) { $this->rating = $value; }
    public function setPhoto1($value) { $this->photo1 = $value; }
    public function setPhoto2($value) { $this->photo2 = $value; }
    public function setPhoto3($value) { $this->photo3 = $value; }
    public function setDefaultPhoto($value) { $this->defaultPhoto = $value; }

    public function displayInTable() {
        $id = htmlText($this->productId);
        $name = htmlText($this->productName);
        $category = htmlText($this->category);
        $price = number_format($this->price, 2);
        $quantity = htmlText($this->quantity);
        $photo = htmlText($this->defaultPhoto);
        $row = "<tr>";
        $row .= "<td><img src=\"images/" . $photo . "\" alt=\"" . $name . "\" width=\"80\" height=\"80\"></td>";
        $row .= "<td><a href=\"view.php?id=" . $id . "\">" . $id . "</a></td>";
        $row .= "<td>" . $name . "</td>";
        $row .= "<td>" . $category . "</td>";
        $row .= "<td>" . $price . "</td>";
        $row .= "<td>" . $quantity . "</td>";
        $row .= "<td>";
        $row .= "<p><a href=\"view.php?id=" . $id . "\">View Details</a></p>";
        if (isEmployee()) {
            $row .= "<p><a href=\"edit.php?id=" . $id . "\">Edit</a></p>";
        } else {
            $row .= "<p><a href=\"add_to_basket.php?id=" . $id . "\">Add to Cart</a></p>";
        }
        $row .= "</td>";
        $row .= "</tr>";
        return $row;
    }

    public function displayProductPage() {
        $id = htmlText($this->productId);
        $main = "<main>";
        $main .= "<h2>" . htmlText($this->productName) . "</h2>";
        $main .= "<form>";
        $main .= "<p><label>Product ID <input type=\"text\" value=\"" . $id . "\" disabled></label></p>";
        $main .= "<p><label>Product Name <input type=\"text\" value=\"" . htmlText($this->productName) . "\" disabled></label></p>";
        $main .= "<p><label>Category <select disabled><option selected>" . htmlText($this->category) . "</option></select></label></p>";
        $main .= "<p><label>Description <textarea disabled>" . htmlText($this->description) . "</textarea></label></p>";
        $main .= "<p><label>Price <input type=\"number\" value=\"" . htmlText($this->price) . "\" disabled></label></p>";
        $main .= "<p><label>Quantity <input type=\"number\" value=\"" . htmlText($this->quantity) . "\" disabled></label></p>";
        $main .= "<p><label>Rating <input type=\"text\" value=\"" . htmlText($this->rating) . "\" disabled></label></p>";
        $main .= "<fieldset><legend>Product Photos</legend>";
        $main .= $this->photoRadio("photo1", $this->photo1, "Photo 1", "disabled");
        $main .= $this->photoRadio("photo2", $this->photo2, "Photo 2", "disabled");
        $main .= $this->photoRadio("photo3", $this->photo3, "Photo 3", "disabled");
        $main .= "</fieldset>";
        $main .= "</form>";
        if (isEmployee()) {
            $main .= "<p><a href=\"products.php\">Back to Products</a></p>";
            $main .= "<p><a href=\"edit.php?id=" . $id . "\">Edit Product</a></p>";
        } else {
            $main .= "<p><a href=\"products.php\">Back to Products</a></p>";
            $main .= "<p><a href=\"add_to_basket.php?id=" . $id . "\">Add to Cart</a></p>";
        }
        $main .= "</main>";
        return $main;
    }

    private function photoRadio($slot, $file, $label, $editable) {
        $checked = "";
        if ($this->defaultPhoto == $file) {
            $checked = "checked";
        }
        $output = "<p>";
        $output .= "<label><input type=\"radio\" name=\"default_photo\" value=\"" . htmlText($file) . "\" " . $checked . " " . $editable . "> " . htmlText($label) . "</label>";
        $output .= "<br><img src=\"images/" . htmlText($file) . "\" alt=\"" . htmlText($label) . "\" width=\"160\" height=\"120\">";
        $output .= "</p>";
        return $output;
    }
}
?>
