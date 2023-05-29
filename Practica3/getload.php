<html>
<body>
<?php
// Uso de CPU
$cpuload = sys_getloadavg()[0]*100;

// Uso de RAM
$mem_usage = memory_get_usage();

// IP del servidor
$servidor = $_SERVER['SERVER_ADDR'];

if ($mem_usage < 1024)
	$memory_usage = $mem_usage." bytes";
elseif ($mem_usage < 1048576)
	$memory_usage = round($mem_usage/1024,2)." KB";
else
	$memory_usage = round($mem_usage/1048576,2)." MB";

$cpuload = number_format((float)$cpuload, 2, '.', '');  // mostrar 2 decimales
echo "CPU: ".$cpuload."  ";
echo nl2br ("\n");
echo "RAM: ".$memory_usage."  ";
echo nl2br ("\n");
echo "IP: ".$servidor;
?>
</body>
</html>
