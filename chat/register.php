<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === '' || $password === '') {
        $message = "Popunite oba polja!";
    } else {
        $file = 'users.txt';

        // Kreiraj fajl ako ne postoji
        if (!file_exists($file)) {
            if (!touch($file)) {
                die("Ne mogu da kreiram fajl za korisnike. Proverite privilegije.");
            }
        }

        $users = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $exists = false;
        foreach ($users as $line) {
            list($u,) = explode('|', $line);
            if ($u === $username) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $message = "Korisnik već postoji!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if (file_put_contents($file, $username . '|' . $hash . PHP_EOL, FILE_APPEND) !== false) {
                $message = "Uspešno ste registrovani!";
            } else {
                $message = "Došlo je do greške. Proverite privilegije fajla!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registracija</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; display:flex; justify-content:center; align-items:center; height:100vh; }
        .container { background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; }
        h2 { text-align:center; margin-bottom:20px; }
        input { width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; }
        button { width:100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; }
        button:hover { background:#0056b3; }
        a { display:block; text-align:center; margin-top:10px; color:#007bff; text-decoration:none; }
        a:hover { text-decoration:underline; }
        p { text-align:center; color:green; }
    </style>
</head>
<body>
<div class="container">
    <h2>Registracija</h2>
    <?php if ($message) echo "<p>$message</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Korisničko ime" required>
        <input type="password" name="password" placeholder="Lozinka" required>
        <button type="submit">Registruj se</button>
    </form>
    <a href="login.php">Već imate nalog? Login</a>
</div>
</body>
</html>
