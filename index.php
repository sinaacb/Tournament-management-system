<!DOCTYPE html>
<html>
<head>
    <title>Tournament Management System - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            background-image: url('uploads/image_test2.jpeg');
            margin: 0;
            padding: 0;
        }

        .title {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #fff;
        }

        form {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        button {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 10px 20px;
        }

        button:hover {
            background-color: #45a049;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #4CAF50;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: #ff0000;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="title">
        <h1>Tournament Management System</h1>
    </div>
    <h2>Login</h2>
    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
        <p class="error">Invalid username or password.</p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
        <a href="signup.php">Sign Up</a>
    </form>
</body>
</html>
