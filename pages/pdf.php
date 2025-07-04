<?php
// require '../../vendor/autoload.php';
require '../vendor/autoload.php';
use Dompdf\Dompdf; //para incluir el namespace de la librería
 
$dompdf = new Dompdf(); //crear el objeto de la clase Dompdf
       
// Componer el HTML
// $html = '<h1>Hola</h1>'; //el html que necesites en formato texto, puedes incluirlo desde una vista de tu MVC

$html = file_get_contents("dashboard.php");
// Añadir el HTML a dompdf
$dompdf->loadHtml($html);
        
//Establecer el tamaño de hoja en DOMPDF
$dompdf->setPaper('A4', 'portrait');
 
// Renderizar el PDF
$dompdf->render();
 
// Forzar descarga del PDF
$dompdf->stream("mypdf.pdf", [ "Attachment" => true]);
?>