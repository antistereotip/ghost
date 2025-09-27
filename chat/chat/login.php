<?php
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        $file = 'users.txt';
        if (file_exists($file)) {
            $users = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $found = false;
            foreach ($users as $line) {
                list($u,$p) = explode('|', $line);
                if ($u === $username && password_verify($password,$p)) {
                    $_SESSION['username'] = $username;
                    header("Location: index.php");
                    exit;
                }
            }
        }
        $message = "Pogrešan username ili lozinka!";
    } else {
        $message = "Popunite oba polja!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; }
        .container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; }
        h2 { text-align:center; margin-bottom:20px; }
        input { width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; }
        button { width:100%; padding:10px; background:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer; }
        button:hover { background:#1e7e34; }
        a { display:block; text-align:center; margin-top:10px; color:#007bff; text-decoration:none; }
        a:hover { text-decoration:underline; }
        p { text-align:center; color:red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if($message) echo "<p>$message</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Korisničko ime" required>
        <input type="password" name="password" placeholder="Lozinka" required>
        <button type="submit">Login</button>
    </form>
    <a href="register.php">Nemate nalog? Registracija</a>
</div>
</body>
</html>
