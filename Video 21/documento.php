<?php
$dir = dirname(__FILE__);
require_once $dir.'/PHPRtfLite/lib/PHPRtfLite.php';
//autoloder
PHPRtfLite::registerAutoloader();
require "clases/MySQL.php";
require "clases/RTFDocumento.php";

$db = new MySQL();
$libro = "PHP7";
$capitulo = 32;
$clase = 2;
//
$sql = "SELECT * FROM li_libros WHERE clave='".$libro."'";
$data = $db->query($sql);
$libroTitulo = $data[0]["titulo"];
//
$sql = "SELECT * FROM li_capitulos WHERE libro='".$libro."' ";
$sql.= "AND numero='".$capitulo."'";
$data = $db->query($sql);
$capituloTitulo = $data[0]["titulo"];
//
$sql = "SELECT * FROM li_imagenes WHERE  libro='".$libro."' AND ";
$sql.= "capitulo=".$capitulo." AND clase=".$clase;
$imagenes = $db->query($sql);
//
$sql = "SELECT * FROM li_clases WHERE capitulo=".$capitulo." AND ";
$sql.= "clase=".$clase;
$data = $db->query($sql);
$claseTitulo = html_entity_decode($data[0]["titulo"]);
$claseTexto = html_entity_decode($data[0]["texto"]);
//
if (isset($_POST["descarga"])) {
	try {
		//margenes
		$data = [
			'margenes'=>[2.54, 2.54, 2.54, 2.54 ],
			'formatoPagina'=>"PAPER_LETTER",
			'piePagina'=>$libro."-".$capitulo." - <pagenum>",
			'piePaginaFormato'=>[8,"Arial","#000000","center"],
			'h1'=>[16,"Arial","#4e9c93",8],
			'h2'=>[14,"Arial","#4e9c93",8],
			'p'=>[12,"Helvetica","#000000",4]
		];
		$doc = new RTFDocumento($data);
		//
		//Crear encabezados
		//
		$data = [
			$libroTitulo,
			"Capítulo ".$capitulo.": ".strip_tags($capituloTitulo),
			"Clase ".$clase.": ".$claseTitulo];
		$doc->encabezados($data);
		//
		//Imprime texto HTML
		//
		$doc->cadenaHTML($claseTexto);
		//
		//Creamos un borde
		//
		$doc->borde(1,"#000000");
		//
		//Imprime imagen
		//
		//
		foreach ($imagenes as $imagen) {
			$img = 'img/'.$imagen["archivo"];
			//
			if (file_exists($img)) {
				$tit = "Imagen ".$clase.".".$imagen["imagen"].".";
				//Imprimir imagen
				$doc->imprimirImagen($img,$tit);
			}
		}
		//
		//Imprime listado
		//
		$listado = "Listado ".$clase.".1.<br>";
		$archivo = __FILE__;
		if (file_exists($archivo)) {
			$doc->imprimirListado($archivo,$listado);
		}
		//
		//Envia archivo
		//
		$salida = $libro."-".$capitulo."-".$clase.".rtf";
		$doc->enviarDocumento($salida);
		exit;
	} catch (Exception $e) {
		$error = $e->getMessage();
		exit($error);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Clase</title>
	<meta charset="utf-8">
</head>
<body>
<?php 
print "<h2>".$libroTitulo."</h2>";
print "<h3>Capítulo ".$capitulo.": ".$capituloTitulo."</h3>";
print "<h3>Clase ".$clase.": ".$claseTitulo."</h3>";
print $claseTexto;
?>
<form method="post">
	<input type="submit" name="descarga" id="descarga" value="Descarga a archivo RTF">
</form>
</body>
</html>