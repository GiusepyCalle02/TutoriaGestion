<?php
require "clases/MySQL.php";

$db = new MySQL();
$libro = "PHP7";
$capitulo = 32;
$clase = 1;
$sql = "SELECT * FROM li_clases WHERE capitulo=32 AND clase=1";
$data = $db->query($sql);
if (isset($_POST["descarga"])) {
	$salida = $libro."-".$capitulo."-".$clase.".txt";
	header('Content-Type: text/plain');
	header('Content-Disposition: attachment;filename='.$salida);
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	header('Expires: 0');
	$output = fopen('php://output', 'w');
	foreach ($data as $renglon) {
		fwrite($output, $renglon["libro"].", capítulo ".$renglon["capitulo"]. "\r\n");
		fwrite($output, "Clase ".$renglon["clase"].": ".$renglon["titulo"]."\r\n");
		fwrite($output, html_entity_decode($renglon["texto"])."\r\n"); 
		fwrite($output, "\r\n");
	}
	fclose($output);
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Clase</title>
	<meta charset="utf-8">
</head>
<body>
<?php foreach ($data as $renglon) {
	print "<h2>".$renglon["libro"].", capítulo ";
	print $renglon["capitulo"]."</h2>";
	print "<h3>Clase ".$renglon["clase"].": ";
	print $renglon["titulo"]."</h3>";
	print html_entity_decode($renglon["texto"])."<br>";
	print "<hr>";
}
?>
<form method="post">
	<input type="submit" name="descarga" id="descarga" value="Descarga a archivo">
</form>
</body>
</html>