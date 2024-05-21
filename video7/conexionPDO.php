<?php
require "clases/MySQL.php";

$db = new MySQL();
$sql = "SELECT * FROM li_clases WHERE capitulo=32 AND clase=1";
$data = $db->query($sql);
var_dump($data);
?>