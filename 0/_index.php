<html><head><style>body {border: 5px double #444; margin: 0 auto; padding: 5px;}</style></head><body> 
<h1>Namerno drugaciji</h1><p><img src="logo.png" alt="logotip" width="150"></p><p><a href="#forum">Forum</a> | 
<a href="statut.html">Statut</a> | <a href="osnivacki-akt.html">Osnivacki akt</a> | 
<a href="https://antistereotip.net/antistereotip_konfekcija.pdf">Antistereotip konfekcija</a></p> <p><marquee>Budite u toku 
sa najnovijim informacijama iz sveta Informacionih Tehnologija. Pratite mali forum i rss vesti iz sveta.Sve sto 
vidite na sajtu je samo cist podatak bez dizajna.</marquee></p>
<form class="d-flex" method="post" action="notify.php">
		<input class="form-control me-2" name="email" type="search" placeholder="Upišite e-mail adresu" aria-label="Search">
		<button class="btn btn-outline-success" type="submit">Enter</button>
</form>


<?php
define('MyConst', TRUE);require_once("db.php");






$data = $pdo_conn->query('SELECT link FROM posts')->fetchAll(PDO::FETCH_ASSOC);
var_export($data);
?>
<hr />
<?php
$sve = $pdo_conn->query('SELECT * FROM posts')->fetchAll(PDO::FETCH_UNIQUE);
var_export($sve);
?>
<hr />
<?php
$link = $pdo_conn->query("SELECT * FROM posts WHERE link='https://antistereotip.net/'")->fetchAll(PDO::FETCH_ASSOC);
var_export($link);
?>
<hr />
<?php
$id = $pdo_conn->query("SELECT * FROM posts WHERE id > 100")->fetchAll(PDO::FETCH_ASSOC);
var_export($id);
?>
<hr />
<?php
$oid = $pdo_conn->query("SELECT * FROM posts ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
var_export($oid);
?>
<hr />
<?php
$lnk = $pdo_conn->query("SELECT link FROM posts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
var_export($lnk);
?>
<hr/><h1 id="forum">Forum</h1>
<?php
$rss = simplexml_load_file('https://antistereotip.net/forum/index.php?mode=rss&items=thread_starts');
echo '<h2>'. $rss->channel->title . '</h2>';	
foreach ($rss->channel->item as $item) 
{
 echo '<p class="title"><a href="'. $item->link .'">' . $item->title . "</a></p>";
} 
?><hr />
<?php
$rss = simplexml_load_file('https://feeds.skynews.com/feeds/rss/world.xml');
echo '<h2>'. $rss->channel->title . '</h2>';	
foreach ($rss->channel->item as $item) 
{
 echo '<p class="title"><a href="'. $item->link .'">' . $item->title . "</a></p>";
 echo "<p class='desc'>" . $item->description . "</p>";
} 
?></body></html>