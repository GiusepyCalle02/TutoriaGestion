<?php
/**
 * 
 */
class MySQL {
	private $mysql = "mysql:host=localhost;dbname=archivos";
	private $usuario = "root";
	private $clave = "";
	private $conn;
	
	/**
 	* Función constructora, no recibe parámetros 
 	*/
	function __construct(){
		$this->conn = new PDO($this->mysql,$this->usuario,$this->clave);
		$errorInfo = $this->conn->errorInfo();
		if (isset($errorInfo[2])) {
			$error = $errorInfo[2];
			exit($error);
		} 
	}

	/**
 	* @param $q query de selección
 	* @return un arreglo asociativo 
 	*/
	public function query($q)
	{
		$data = array();
		$sql = $this->conn->prepare($q);
		$sql->execute();
		while($row = $sql->fetch(PDO::FETCH_ASSOC)){
			$data[] = $row;
		}
		return $data;
	}
}
?>