<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded credentials (replace with your own)
    $validUsername = "username";
    $validPassword = "password";

    // Check if the provided credentials are valid
    if ($username === $validUsername && $password === $validPassword) {
        // Set a session variable to indicate successful login
        $_SESSION['authenticated'] = true;

        // Redirect to the index.php page
        header("Location: index.php");
        exit;
    } else {
        // Display an error message (you can customize this part)
        $error = "Invalid username or password. Please try again.";
    }
}
?>

<!DOCTYPE HTML>
<html>
    <head>
        <title>Backup Database v2</title>
        <meta charset="utf-8" />
        <meta owner="thomas vincent">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="assets/css/main.css" />
        <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    </head>
    <body class="is-preload">

        <!-- Wrapper-->
        <div id="wrapper">

            <!-- Nav -->
            <nav id="nav">
                <a href="#" class="icon solid fa-user"><span>Login</span></a>
            </nav>

            <!-- Main -->
            <div id="main">
                <!-- Me -->
                <article id="home" class="panel intro">
                    <header>
                        <p>
<center><h2>Login</h2></center>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <center><label for="username">Username:</label>
        <input type="text" id="username" name="username" style="color: black;" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" style="color: black;" required>
        <input type="submit" value="Login"></center>
    </form>
                        </p>
                    </header>
                    <a href="#" class="jumplink pic">
                        <img src="assets/css/images/backup.jpg" alt="" />
                    </a>
                </article>
            </div>

            <!-- Footer -->
            <div id="footer">
                <ul class="copyright">
                    <li>&copy; 2023.</li><li>♥ Coded: <a href="https://www.facebook.com/ThomsVnct/">Thomas Vincent</a></li>
                </ul>
            </div>
        </div>

        <!-- Scripts -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/browser.min.js"></script>
        <script src="assets/js/breakpoints.min.js"></script>
        <script src="assets/js/util.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
</html>
