<?php
require_once "dbconfig.inc.php";

$errors = array();
$success = false;
$userId = "";

$firstName = "";
$lastName = "";
$email = "";
$mobile = "";
$dob = "";
$flat = "";
$street = "";
$city = "";
$country = "";
$postalCode = "";
$role = "customer";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["first_name"]);
    $lastName = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $dob = trim($_POST["dob"]);
    $flat = trim($_POST["flat"]);
    $street = trim($_POST["street"]);
    $city = trim($_POST["city"]);
    $country = trim($_POST["country"]);
    $postalCode = trim($_POST["postal_code"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $role = $_POST["role"];

    if ($firstName == "") { $errors[] = "First name is required."; }
    if ($lastName == "") { $errors[] = "Last name is required."; }
    if ($email == "") { $errors[] = "Email address is required."; }
    if ($mobile == "") { $errors[] = "Mobile number is required."; }
    if ($dob == "") { $errors[] = "Date of birth is required."; }
    if ($street == "") { $errors[] = "Street name and number is required."; }
    if ($city == "") { $errors[] = "City is required."; }
    if ($country == "") { $errors[] = "Country is required."; }
    if ($postalCode == "") { $errors[] = "Postal code is required."; }
    if ($password == "") { $errors[] = "Password is required."; }

    if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email format is invalid.";
    }

    if ($email != "") {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->execute(array(":email" => $email));
        if ($stmt->fetch()) {
            $errors[] = "Email already exists.";
        }
    }

    if ($dob != "") {
        $todayText = date("Y-m-d");
        $dobTime = strtotime($dob);
        if ($dobTime === false || $dob >= $todayText) {
            $errors[] = "Date of birth must be a valid date in the past.";
        } else {
            $age = intval(date("Y")) - intval(date("Y", $dobTime));
            if (date("md", $dobTime) > date("md")) {
                $age = $age - 1;
            }
            if ($age < 18) {
                $errors[] = "User must be at least 18 years old.";
            }
        }
    }

    if (strlen($postalCode) != 6 || !ctype_digit($postalCode)) {
        $errors[] = "Postal code must contain exactly 6 digits.";
    }

    if ($password != $confirmPassword) {
        $errors[] = "Password confirmation must match.";
    }

    if ($role != "customer" && $role != "employee") {
        $role = "customer";
    }

    if (count($errors) == 0) {
        $userId = generateTenDigitId();
        $check = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :user_id");
        $check->execute(array(":user_id" => $userId));
        while ($check->fetch()) {
            $userId = generateTenDigitId();
            $check->execute(array(":user_id" => $userId));
        }
        $password = md5($password);
        $insert = $pdo->prepare("INSERT INTO users (user_id, first_name, last_name, email, mobile, dob, flat, street, city, country, postal_code, password_hash, role) VALUES (:user_id, :first_name, :last_name, :email, :mobile, :dob, :flat, :street, :city, :country, :postal_code, :password_hash, :role)");
        $insert->execute(array(":user_id" => $userId, ":first_name" => $firstName, ":last_name" => $lastName, ":email" => $email, ":mobile" => $mobile, ":dob" => $dob, ":flat" => $flat, ":street" => $street, ":city" => $city, ":country" => $country, ":postal_code" => $postalCode, ":password_hash" => $password, ":role" => $role));
        $success = true;
    }
}

require_once "header.inc.php";
?>
<main>
<h2>Register</h2>
<?php if ($success) { ?>
<section>
<h3>Registration Complete</h3>
<p>Your generated User ID is <?php echo htmlText($userId); ?>.</p>
<p><a href="login.php">Login</a></p>
</section>
<?php } else { ?>
<?php if (count($errors) > 0) { ?>
<section>
<h3>Errors</h3>
<ul>
<?php foreach ($errors as $error) { ?>
<li><?php echo htmlText($error); ?></li>
<?php } ?>
</ul>
</section>
<?php } ?>
<form method="post" action="register.php">
<fieldset>
<legend>Personal Information</legend>
<p><label>First Name <input type="text" name="first_name" placeholder="Enter your first name" value="<?php echo htmlText($firstName); ?>" required></label></p>
<p><label>Last Name <input type="text" name="last_name" placeholder="Enter your last name" value="<?php echo htmlText($lastName); ?>" required></label></p>
<p><label>Email Address <input type="email" name="email" placeholder="example@domain.com" value="<?php echo htmlText($email); ?>" required></label></p>
<p><label>Mobile Number <input type="text" name="mobile" placeholder="Enter your mobile number" value="<?php echo htmlText($mobile); ?>" required></label></p>
<p><label>Date of Birth <input type="date" name="dob" value="<?php echo htmlText($dob); ?>" required></label></p>
</fieldset>
<fieldset>
<legend>Address</legend>
<p><label>Flat / Unit No (optional) <input type="text" name="flat" placeholder="Enter flat or unit number" value="<?php echo htmlText($flat); ?>"></label></p>
<p><label>Street Name &amp; No <input type="text" name="street" placeholder="Enter street name and number" value="<?php echo htmlText($street); ?>" required></label></p>
<p><label>City <input type="text" name="city" placeholder="Enter your city" value="<?php echo htmlText($city); ?>" required></label></p>
<p><label>Country <input type="text" name="country" placeholder="Enter your country" value="<?php echo htmlText($country); ?>" required></label></p>
<p><label>Postal Code <input type="text" name="postal_code" placeholder="Enter 6-digit postal code" value="<?php echo htmlText($postalCode); ?>" required></label></p>
</fieldset>
<fieldset>
<legend>Account</legend>
<p><label>Password <input type="password" name="password" placeholder="Enter your password" required></label></p>
<p><label>Confirm Password <input type="password" name="confirm_password" placeholder="Re-enter your password" required></label></p>
<p><label><input type="radio" name="role" value="customer" <?php if ($role == "customer") { echo "checked"; } ?>> Customer</label></p>
<p><label><input type="radio" name="role" value="employee" <?php if ($role == "employee") { echo "checked"; } ?>> Employee</label></p>
</fieldset>
<p><input type="submit" value="Register"></p>
</form>
<?php } ?>
</main>
<?php require_once "footer.inc.php"; ?>
