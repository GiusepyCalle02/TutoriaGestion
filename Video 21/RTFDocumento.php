<?php
/**
 * 
 */
class RTFDocumento
{
	private $rtf;
	private $seccion;
	private $footer;
	private $fuenteH1;
	private $fuenteH2;
	private $fuenteP;
	private $formatoH1;
	private $formatoH2;
	private $formatoP;
	private $borde;

	function __construct($data)
	{
		$this->rtf = new PHPRtfLite();
		//formato
		$this->rtf->setMargins($data["margenes"][0],
			$data["margenes"][1],
			$data["margenes"][2],
			$data["margenes"][3]);
		//
		if($data['formatoPagina']=="PAPER_LETTER"){
			$this->rtf->setPaperFormat(PHPRtfLite::PAPER_LETTER);
		} else {
			$this->rtf->setPaperFormat(PHPRtfLite::PAPER_LANDSCAPE);
		}
		//Creamos el pie de pagina
		$footer = $this->rtf->addFooter();
		$footerFont = new PHPRtfLite_Font(
			$data['piePaginaFormato'][0],
			$data['piePaginaFormato'][1],
			$data['piePaginaFormato'][2]);
		//
		if ($data['piePaginaFormato'][3]=="center") {
			$footerAlign = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
		}
		//
		$footer->writeText($data['piePagina'],
			$footerFont,
			$footerAlign);
		//
		//Definimos fuentes
		$this->fuenteH1 = new PHPRtfLite_Font(
			$data['h1'][0],
			$data['h1'][1],
			$data['h1'][2]);
		$this->fuenteH2 = new PHPRtfLite_Font(
			$data['h2'][0],
			$data['h2'][1],
			$data['h2'][2]);
		$this->fuenteP = new PHPRtfLite_Font(
			$data['p'][0],
			$data['p'][1],
			$data['p'][2]);
		//Espacios verticales
		$this->formatoH1 = new PHPRtfLite_ParFormat();
		$this->formatoH2 = new PHPRtfLite_ParFormat();
		$this->formatoP = new PHPRtfLite_ParFormat();
		//
		$this->formatoH1->setSpaceAfter($data['h1'][3]);
		$this->formatoH2->setSpaceAfter($data['h2'][3]);
		$this->formatoP->setSpaceAfter($data['p'][3]);
		//
		$this->seccion = $this->rtf->addSection();
	}

	public function encabezados($data)
	{
		$this->seccion->writeText($data[0], 
			$this->fuenteH1,
			$this->formatoH1);
		$this->seccion->writeText($data[1],
			$this->fuenteH2,
			$this->formatoH2);
		$this->seccion->writeText($data[2],
			$this->fuenteH2,
			$this->formatoH2);
		$this->seccion->writeText("<hr>");
		$this->seccion->writeText("<br>");
	}

	public function borde($ancho, $color)
	{
		//Crear los bordes
		$this->borde = new PHPRtfLite_Border(
		    $this->rtf,
		    new PHPRtfLite_Border_Format($ancho, $color),
		    new PHPRtfLite_Border_Format($ancho, $color),
		    new PHPRtfLite_Border_Format($ancho, $color),
		    new PHPRtfLite_Border_Format($ancho, $color)
		);
	}

	public function imprimirImagen($archivo, $tit)
	{
		$this->seccion->writeText("<br>");
		//Añadimos la imagen a la sección
		$imagen = $this->seccion->addImage($archivo);
		// Archo en centímetros
		$imagen->setWidth(16);
		// Altura en centímetros
		$imagen->setHeight(8);
		//
		$imagen->setBorder($this->borde);
		// 
		$this->seccion->writeText($tit,
		$this->fuenteP,
		$this->formatoP);
		//
		$this->seccion->writeText("<br>");
	}

	public function imprimirListado($archivo, $listado)
	{
		//
		$archivoID = fopen($archivo, "r");
		//
		//Lee el archivo para ser listado
		//
		$c = array();
		while(!feof($archivoID)){
			//1024 es 1 k o hasta encontrar \n o \r
			$linea = fgets($archivoID, 1024);
			$linea = htmlspecialchars($linea);
			$linea = str_replace(" ", "&nbsp;",$linea);
			$linea = str_replace("\t"," ",$linea);
	        $linea = str_replace("\n","",$linea);
	        $linea = str_replace("\r","",$linea);
			//
			array_push($c,$linea);
		}
		//
		fclose($archivoID);
		//
		$renglonNum = count($c);
		$renglonAlto = 0.3;
		$columnaNum = 2;
		$columnaAlto = 4;

		//Añadir la tabla
		$tabla = $this->seccion->addTable();
		//Añadir renglones
		$tabla->addRows($renglonNum, $renglonAlto);
		//Añadir colimnas
		$tabla->addColumnsList(array(1,15));
		//Relacionar los dordes y la tabla
		$tabla->setBorderForCellRange($this->borde, 1, 1, count($c), 2);
		
		for ($indice = 1; $indice <= $renglonNum; $indice++) {
			//Seleccionas la celda
	        $celda = $tabla->getCell($indice, 1);
	        //Escribir en la celda
	        $celda->writeText($indice);
	        //Alineación horizontal
	        $celda->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_CENTER);
	        //Alineación vertical
	        $celda->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
	        //La segunda columna
	        $celda = $tabla->getCell($indice, 2);
	        $celda->writeText($c[$indice-1]);
	        $celda->setTextAlignment(PHPRtfLite_Table_Cell::TEXT_ALIGN_LEFT);
	        $celda->setVerticalAlignment(PHPRtfLite_Table_Cell::VERTICAL_ALIGN_CENTER);
		}
		//
		$this->seccion->writeText($listado,
			$this->fuenteP,
			$this->formatoP);
	}

	public function cadenaHTML($cadena)
	{
		//$cadena = html_entity_decode($data[0]["texto"]);
		$parrafo = false;
		$listaUL = false;
		$listaOL = false;
		$listaIT = false;
		$p=$li="";
		$lon = strlen($cadena);
		for($i=0; $i<$lon; $i++){
			$c = $cadena[$i];
			if ($c=="<") {
				if($cadena[$i+1]=="p" && $cadena[$i+2]==">"){
					$parrafo = true;
					$i=$this->incrementa($i, 3, $lon);
					$c = $cadena[$i];
				} else if($cadena[$i+1]=="/" && $cadena[$i+2]=="p"){
					$parrafo = false;
					$i=$this->incrementa($i, 3, $lon);
					$c = $cadena[$i];
					$this->seccion->writeText($p."<br>",
					$this->fuenteP,
					$this->formatoP);
					$p="";
					continue;
				} else if($cadena[$i+1]=="l" && $cadena[$i+2]=="i"){
					$listaIT = true;
					$i=$this->incrementa($i, 4, $lon);
					$c = $cadena[$i];
				} else if($cadena[$i+1]=="/" && $cadena[$i+2]=="l"){
					$listaIT = false;
					$i=$this->incrementa($i, 4, $lon);
					$c = $cadena[$i];
					//
					$lista->addItem($li,
						$this->fuenteP,
						$this->formatoP);
					$li="";
					continue;
				} else if($cadena[$i+1]=="u" && $cadena[$i+3]==">"){
					$i=$this->incrementa($i, 3, $lon);
					$lista = new PHPRtfLite_List_Numbering($this->rtf);
					continue;
				} else if($cadena[$i+1]=="/" && $cadena[$i+2]=="u"){
					$i=$this->incrementa($i, 4, $lon);
					$this->seccion->addNumbering($lista);
					$this->seccion->writeText("<br>");
					continue;
				}
			}
			if($parrafo){ 
				$p.=$c;
			} else if($listaIT){
				$li.=$c;
			}
		}		
	}

	private function incrementa($i, $in, $lon)
	{
		if ($i+$in>=$lon) {
			return $lon-1;
		} else {
			return $i+$in;
		}
	}

	public function enviarDocumento($archivo)
	{
		$this->rtf->sendRtf($archivo);
	}
}
?>