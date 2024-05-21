<?php
$dir = dirname(__FILE__);
require_once $dir.'/PHPRtfLite/lib/PHPRtfLite.php';
//autoloder
PHPRtfLite::registerAutoloader();
require "clases/MySQL.php";

$db = new MySQL();
$libro = "PHP7";
$capitulo = 32;
$clase = 1;
//
$sql = "SELECT * FROM li_libros WHERE clave='".$libro."'";
$data = $db->query($sql);
$libroTitulo = $data[0]["titulo"];
//
$sql = "SELECT * FROM li_capitulos WHERE libro='".$libro."' AND numero='".$capitulo."'";
$data = $db->query($sql);
$capituloTitulo = $data[0]["titulo"];
$capituloIntro = html_entity_decode($data[0]["introduccion"]);
$capituloObj = html_entity_decode($data[0]["objetivo"]);
//
$sql = "SELECT id,libro,capitulo,clase,indice,titulo FROM li_clases WHERE capitulo=32";
$data = $db->query($sql);
//
if (isset($_POST["descarga"])) {
	try {
		$rtf = new PHPRtfLite();
		//formato
		$rtf->setMargins(2.54, 2.54, 2.54, 2.54 );
		$rtf->setPaperFormat(PHPRtfLite::PAPER_LETTER);
		//Creamos el pie de pagina
		$footer = $rtf->addFooter();
		$footerFont = new PHPRtfLite_Font(8,"Arial", "#000000");
		$footerAlign = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
		//
		$footer->writeText("Indice capítulo ".$capitulo." - <pagenum>",
			$footerFont,
			$footerAlign);

		//Definimos fuentes
		$fuenteH1 = new PHPRtfLite_Font(16,"Arial","#4e9c93");
		$fuenteH2 = new PHPRtfLite_Font(14,"Arial","#4e9c93");
		$fuenteP = new PHPRtfLite_Font(12,"Helvetica","#000000");

		//Espacios verticales
		$formatoH1 = new PHPRtfLite_ParFormat();
		$formatoH2 = new PHPRtfLite_ParFormat();
		$formatoP = new PHPRtfLite_ParFormat();
		$formatoH1->setSpaceAfter(8);
		$formatoH2->setSpaceAfter(6);
		$formatoP->setSpaceAfter(3);

		//Crear sección
		$seccion = $rtf->addSection();
		$seccion->writeText($libroTitulo, 
			$fuenteH1,
			$formatoH1);
		$seccion->writeText("Capítulo ".$capitulo.": ".strip_tags($capituloTitulo),
			$fuenteH2,
			$formatoH2);
		$seccion->writeText("<b>Objetivo</b>: ".strip_tags($capituloObj)."<br>",
			$fuenteP,
			$formatoP);
		$seccion->writeText(strip_tags($capituloIntro)."<br>",
			$fuenteP,
			$formatoP);
		//
		//Creamos la lista
		//
		$lista = new PHPRtfLite_List_Numbering($rtf);
		foreach ($data as $ren) {
			$lista->addItem($ren["titulo"],
			$fuenteP,
			$formatoP);
		}
		$seccion->addNumbering($lista);
		$seccion->writeText("<hr>");
		//
		$rtf->sendRtf("indice".$libro."-".$capitulo.".rtf");
	} catch (Exception $e) {
		
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
print "<h3>Objetivo ".$capituloObj."</h3>";
print "<p>".$capituloIntro."</p>";
print "<ol>";
foreach ($data as $renglon) {
	print "<li>".$renglon["titulo"]."</li>";
}
print "</ol>";
?>
<form method="post">
	<input type="submit" name="descarga" id="descarga" value="Descarga a archivo RTF">
</form>
</body>
</html>