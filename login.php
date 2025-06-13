<?php
session_start();
require_once 'db.php'; // Ensure this file connects to your database via MySQLi

$error = ""; // Default

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, user_name, password, role FROM user WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect to dashboard or desired page
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>🌱Plant Inventory System - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #D2D0A0, #D2D0A0);
      color: #537D5D;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    header {
      background-color: #537D5D;
      color: #fbfbfb;
      padding: 12px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: 600;
      font-size: 1.2rem;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    header .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    header .logo img {
      width: 32px;
      height: 32px;
    }

    footer {
      background-color: #4a7c59;
      color: #73946B;
      text-align: center;
      padding: 16px 24px;
      margin-top: auto;
      font-size: 0.9rem;
      user-select: none;
    }

    main {
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 24px;
    }

    .login-card {
      background-color: #73946B;
      padding: 40px 32px;
      border-radius: 16px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .login-card img {
      width: 64px;
      height: 64px;
      margin-bottom: 16px;
    }

    .login-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 16px;
      color: #ffffff;
      letter-spacing: 0.06em;
    }

    form {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .input-group {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-group .material-icons {
      position: absolute;
      left: 12px;
      color: #98ad86;
      pointer-events: none;
      font-size: 20px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px 12px 12px 40px;
      border: none;
      border-radius: 12px;
      background: #ffffff;
      font-size: 1rem;
      color: #000000;
      font-style: italic;
      transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      outline: none;
      background: #ffffff;
      box-shadow: 0 0 6px 2px #7ea84fcc;
      font-style: normal;
      color: #1c2d0a;
    }

    input::placeholder {
      color: #9ebc8a;
      font-style: italic;
    }

    button[type="submit"] {
      background-color: #4a7c59;
      color: #d7e4c9;
      font-weight: 600;
      font-size: 1.1rem;
      padding: 12px 0;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
      user-select: none;
    }

    button[type="submit"]:hover,
    button[type="submit"]:focus {
      background-color: #3f6d4c;
      transform: scale(1.05);
      outline: none;
    }

    @media (max-width: 767px) {
      header {
        padding: 12px 20px;
        font-size: 1rem;
      }
      .login-card {
        padding: 32px 24px;
        max-width: 100%;
      }
    }

    @media (min-width: 768px) and (max-width: 1439px) {
      header {
        padding: 12px 48px;
        font-size: 1.3rem;
      }
    }

    @media (min-width: 1440px) {
      main {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
      }
    }

    .error-message {
      background: #842029;
      color: #ffdddd;
      padding: 12px;
      border-radius: 8px;
      text-align: center;
      font-weight: bold;
      margin-bottom: -16px;
      width: 100%;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo" aria-label="Plant Inventory System Logo">
      <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/9b36869e-7da6-42b7-8540-09f5d342c901.png" alt="Green plant seedling icon decorative" />
      <span>Plant Inventory System</span>
    </div>
  </header>

  <main>
    <section class="login-card" role="region" aria-labelledby="login-heading">
      <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/81712c3f-7631-4498-a643-99016b3caa6a.png" alt="Plant seedling illustration icon" />
      <h1 id="login-heading" class="login-title">Plant Inventory System</h1>
      <h3 align="center" class="login-title">Administrator Log in</h3>

      <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="" aria-describedby="login-desc" aria-label="Login Form">
        <div class="input-group">
          <span class="material-icons" aria-hidden="true">person</span>
          <input type="text" id="username" name="username" placeholder="Username" autocomplete="username" required aria-required="true" />
        </div>
        <div class="input-group">
          <span class="material-icons" aria-hidden="true">lock</span>
          <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required aria-required="true" />
        </div>
        <button type="submit" aria-label="Log in to Plant Inventory System">Log In</button>
      </form>
    </section>
  </main>

  <footer>
    &copy; <?php echo date("Y"); ?> Plant Inventory System. All rights reserved.
  </footer>
</body>
</html>
