<?php
session_start();
require_once 'db.php';

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Securely hash the password

    // Check if email already exists
    $query = "SELECT email FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $error = "Email is already registered.";
    } else {
        // Insert new user into the database
        $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);

        // Automatically log in the user after registration
        $userId = $db->lastInsertId();
        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $name,
            'email' => $email
        ];

        header('Location: index.php'); // Redirect to homepage
        exit();
    }
}
?>


<?php include 'includes/header.php'; ?>
<main>
    <div class="common-container">
        <h1>Welcome to DesiMart</h1>
        <h2>Register</h2>
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
