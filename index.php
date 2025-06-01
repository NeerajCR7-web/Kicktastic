<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Kicktastic</title>
    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 180px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropdown-button {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <h1>Welcome to Kicktastic</h1>

    <div class="dropdown">
        <button class="dropdown-button">Login</button>
        <div class="dropdown-content">
            <a href="login.php?role=team_manager">Sign in as Manager</a>
            <a href="login.php?role=league_admin">Sign in as Admin</a>
        </div>
    </div>

    <p style="margin-top: 20px;">
        Don't have an account? <a href="register.php">Register as Manager</a>
    </p>

</body>
</html>
