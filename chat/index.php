<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}

$messagesFile = 'messages.txt';
function parseEmojis($text){
    $emojis = [
        ':)' => '😊',
        ':(' => '😢',
        ':D' => '😃',
        ';)' => '😉',
        ':P' => '😛'
    ];
    return str_replace(array_keys($emojis), array_values($emojis), htmlspecialchars($text));
}

/// Slanje poruke i komanda /clear
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $msg = trim($_POST['message']);
    if($msg){
        if($msg === '/clear'){
            // briše sve poruke
            file_put_contents($messagesFile, '');
        } else {
            $entry = $_SESSION['username'] . '|' . $msg . '|' . date('Y-m-d H:i:s') . PHP_EOL;
            file_put_contents($messagesFile, $entry, FILE_APPEND);
        }
    }
}


// Učitavanje poruka
$messages = [];
if(file_exists($messagesFile)){
    $messages = file($messagesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <style>
        body { font-family: Arial; background:#f0f2f5; margin:0; display:flex; flex-direction:column; height:100vh; }
        header { background:#007bff; color:#fff; padding:15px; text-align:center; }
        header a { color:#fff; text-decoration:none; margin-left:20px; }
        #chatbox { flex:1; padding:10px; overflow-y:scroll; background:#fff; }
        #chatbox div { margin-bottom:5px; }
        form { display:flex; padding:10px; background:#e9ecef; }
        input[type=text] { flex:1; padding:10px; border:1px solid #ccc; border-radius:5px; }
        button { padding:10px 15px; margin-left:5px; border:none; border-radius:5px; background:#28a745; color:#fff; cursor:pointer; }
        button:hover { background:#1e7e34; }
    </style>
</head>
<body>
<header>
    Dobrodošli, <?php echo $_SESSION['username']; ?>      |     Komanda <b style="color: yellow;">/clear</b> za ciscenje ekrana   | <a href="logout.php">Logout</a>
</header>

<div id="chatbox">
<?php
foreach($messages as $m){
    $parts = explode('|',$m);
    if(count($parts) === 3){
        echo "<div><strong>{$parts[0]}</strong>: " . parseEmojis($parts[1]) . " <small>[{$parts[2]}]</small></div>";
    }
}
?>
</div>

<form method="post">
    <input type="text" name="message" id="message" placeholder="Poruka..." required autofocus>
    <button type="submit">Pošalji</button>
</form>

<script>
setInterval(() => {
    fetch('messages.txt')
    .then(res => res.text())
    .then(data => {
        const chatbox = document.getElementById('chatbox');
        chatbox.innerHTML = data.split("\n").filter(Boolean).map(line => {
            const parts = line.split("|");
            if(parts.length !== 3) return '';
            let text = parts[1].replace(/:\)/g,'😊')
                               .replace(/:\(/g,'😢')
                               .replace(/:D/g,'😃')
                               .replace(/;\)/g,'😉')
                               .replace(/:P/g,'😛');
            return `<div><strong>${parts[0]}</strong>: ${text} <small>[${parts[2]}]</small></div>`;
        }).join('');
        chatbox.scrollTop = chatbox.scrollHeight;
    });
},2000);
</script>
</body>
</html>
