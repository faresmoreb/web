<?php
require_once "dbconfig.inc.php";

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    if ($email == "" || $password == "") {
        $error = "Email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, email, password_hash, role FROM users WHERE email = :email");
        $stmt->execute(array(":email" => $email));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $inputPassword = md5($password);
        $dbPassword = "";
        if ($user) {
            $dbPassword = $user["password_hash"];
        }
        if ($user && $inputPassword == $dbPassword) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["first_name"] = $user["first_name"];
            $_SESSION["last_name"] = $user["last_name"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["logged_in"] = true;
            if (isset($_SESSION["return_url"]) && $_SESSION["return_url"] != "") {
                $returnUrl = $_SESSION["return_url"];
                unset($_SESSION["return_url"]);
                header("Location: " . $returnUrl);
                exit();
            }
            header("Location: products.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once "header.inc.php";
?>
<main>
<h2>Login</h2>
<?php if ($error != "") { ?>
<p><?php echo htmlText($error); ?></p>
<?php } ?>
<form method="post" action="login.php">
<fieldset>
<legend>Login Information</legend>
<p><label>Email <input type="email" name="email" value="<?php echo htmlText($email); ?>" required></label></p>
<p><label>Password <input type="password" name="password" required></label></p>
</fieldset>
<p><input type="submit" value="Login"></p>
</form>
</main>
<?php require_once "footer.inc.php"; ?>
