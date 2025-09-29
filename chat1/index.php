<?php
session_start();

$messagesFile = __DIR__ . "/messages.txt";

// Funkcija za random nick
function generateRandomNick() {
    $rand = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 4);
    return "anon" . $rand;
}

// Ako nema nickname u sesiji, kreiraj random
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = generateRandomNick();
}

// Setovanje nickname preko forme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'setnick') {
    $nick = trim($_POST['nickname'] ?? '');
    $nickLower = strtolower($nick);
    
    // Ako neko pokuša da promeni master ili admin, ignoriši
    if ($nickLower === "master" || $nickLower === "admin") {
        $_SESSION['user'] = $nickLower; // dozvoljeno ako je neko već admin/master
    } elseif ($nick === '') {
        $_SESSION['user'] = generateRandomNick();
    } else {
        $_SESSION['user'] = htmlspecialchars($nick);
    }
    echo json_encode(['ok'=>true,'user'=>$_SESSION['user']]);
    exit;
}

// Slanje, fetch, export poruka
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send') {
        $msg = trim($_POST['message'] ?? '');
        $user = $_SESSION['user'];
        if ($msg !== '') {
            if ($msg === '/clear') {
                $timestamp = date('[Y-m-d H:i:s]');
                $systemMsg = $timestamp." --- CHAT CLEARED BY ".$user." ---\n";
                file_put_contents($messagesFile, $systemMsg);
            } else {
                $timestamp = date('[Y-m-d H:i:s]');
                $line = $timestamp." ".$user.": ".$msg."\n";
                file_put_contents($messagesFile, $line, FILE_APPEND);
            }
        }
        echo json_encode(['ok'=>true]);
        exit;
    }
    
    if ($action === 'fetch') {
        if (file_exists($messagesFile)) {
            echo htmlspecialchars(file_get_contents($messagesFile));
        }
        exit;
    }
    
    if ($action === 'export') {
        $secureDir = __DIR__ . "/secure";
        if (!is_dir($secureDir)) mkdir($secureDir, 0777, true);
        $dest = $secureDir."/messages-".date("Ymd-His").".txt";
        copy($messagesFile, $dest);
        echo json_encode(['ok'=>true,'file'=>$dest]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fascinantan Chat</title>
<style>
body{
  margin:0;
  padding:0;
  background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
  height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  font-family:Arial, sans-serif;
  color:#fff;
}
.chat-container{
  width:80%;
  height:80%;
  background:rgba(255,255,255,0.05);
  backdrop-filter:blur(10px);
  border-radius:16px;
  box-shadow:0 8px 32px rgba(0,0,0,0.4);
  display:flex;
  flex-direction:column;
  padding:20px;
}
#messages{
  flex:1;
  overflow-y:auto;
  background:rgba(0,0,0,0.35);
  border:1px solid rgba(255,255,255,0.04);
  border-radius:12px;
  padding:2px 6px;
  font-family:Consolas, monospace;
  font-size:14px;
  line-height:1;
  margin-bottom:6px;
  white-space:pre-wrap;
}
.input-area{
  display:flex;
  gap:8px;
  margin-top:6px;
  align-items:center;
}
#nickname{
  flex:0 0 13%;       /* 13% širine */
  max-width:130px;
  padding:10px;
  border:none;
  border-radius:8px;
  outline:none;
}
#message{
  flex:0 0 70%;       /* 70% širine */
  padding:10px;
  border:none;
  border-radius:8px;
  outline:none;
}
#clear-btn{
  flex:0 0 8%;        /* 8% širine */
  padding:10px;
  border:none;
  border-radius:8px;
  background:#ff4d4d;
  color:#000;
  cursor:pointer;
  font-weight:bold;
}
#export-btn{
  flex:0 0 8%;        /* 8% širine */
  padding:10px;
  border:none;
  border-radius:8px;
  background:#4dff88;
  color:#000;
  cursor:pointer;
  font-weight:bold;
}
button:hover{opacity:0.85;}
.system-msg{
  color:#ff6b6b;
  font-style:italic;
}
</style>
</head>
<body>
<div class="chat-container">
  <div id="messages"></div>
  <div class="input-area">
    <input type="text" id="nickname" placeholder="Nick">
    <input type="text" id="message" placeholder="Type a message..." autofocus>
    <button id="clear-btn" onclick="clearChat()">Clear</button>
    <button id="export-btn" onclick="exportMessages()">Export</button>
  </div>
</div>

<script>
function fetchMessages(){
  fetch("", {method:"POST", headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:"action=fetch"})
    .then(r=>r.text())
    .then(txt=>{
      // označi sistemske poruke
      let html = txt.replace(/\n/g,"<br>").replace(/(--- CHAT CLEARED BY .*? ---)/g,'<span class="system-msg">$1</span>');
      document.getElementById("messages").innerHTML = html;
      let box=document.getElementById("messages");
      box.scrollTop=box.scrollHeight;
    });
}
function sendMessage(){
  let msg=document.getElementById("message").value.trim();
  if(!msg) return;
  setNickFromInput();
  fetch("", {method:"POST", headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:"action=send&message="+encodeURIComponent(msg)})
    .then(()=>{document.getElementById("message").value=""; document.getElementById("message").focus(); fetchMessages();});
}
function clearChat(){
  setNickFromInput();
  fetch("", {method:"POST", headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:"action=send&message=/clear"})
    .then(()=>fetchMessages());
}
function exportMessages(){
  fetch("", {method:"POST", headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:"action=export"})
    .then(r=>r.json())
    .then(js=>alert("Exportovano u: "+js.file));
}
function setNickFromInput(){
  let nick=document.getElementById("nickname").value.trim();
  fetch("", {method:"POST", headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:"action=setnick&nickname="+encodeURIComponent(nick)})
    .then(()=>{document.getElementById("nickname").value="";});
}
// Enter za poruku
document.getElementById("message").addEventListener("keydown",function(e){
  if(e.key==="Enter"){e.preventDefault();sendMessage();}
});
// Enter za nickname
document.getElementById("nickname").addEventListener("keydown",function(e){
  if(e.key==="Enter"){e.preventDefault();setNickFromInput();}
});
setInterval(fetchMessages,2000);
fetchMessages();
</script>
</body>
</html>
