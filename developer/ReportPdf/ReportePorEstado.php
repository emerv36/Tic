<?php
include '../Config/config.php';
include '../../class/fpdf/fpdf.php';
$db = new PDO(connstring, user, pass);
if(isset($_POST['lote'])){  $lote=$_POST['lote'];}
if(isset($_POST['chk'])){  $chk=$_POST['chk'];}

$archivo= basename($_SERVER['PHP_SELF'],'.php');

$query=$db->prepare("SELECT id_reporte,
                    codigo_reporte,
                    nombre_reporte,
                    nombre_archivo,
                    id_version_reportefk,
                    nombre_version_reporte,
                    fecha_version_reporte,
                    id_plantelfk,
                    nombre_plantel
                    FROM reporte r
                    INNER JOIN version_reporte vr ON r.id_version_reportefk=vr.id_version_reporte
                    INNER JOIN plantel  p ON r.id_plantelfk=p.id_plantel
                    WHERE nombre_archivo =:nombre_archivo");
                    $query->bindValue(':nombre_archivo', $archivo, PDO::PARAM_STR);
 $query->execute();
 $fila = $query->fetch();
 $titulo=$fila['nombre_plantel'];
 $tituloReporte=$fila['nombre_reporte'];
 $CodigoReporte=$fila['codigo_reporte'];
 $FechaVigencia=$fila['fecha_version_reporte'];
 $VersionReporte=$fila['nombre_version_reporte'];

if ($chk==1){

    $query = $db->prepare($selec="SELECT COUNT(*) AS cantidad,
    id_lote_inscripcionfk,
    CONCAT(YEAR(fecha_inscripcion),'-',MONTH(fecha_inscripcion)) AS periodo,
    etapa,
    estado_inscripcion AS valor_registro,
    CASE  
        WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
        WHEN estado_inscripcion='2' THEN 'RECIBIDO'
        WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
    END AS estado_inscripcion
    FROM inscripcion i
    INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote 
    GROUP BY  MONTH(fecha_inscripcion), estado_inscripcion");
    $query->execute();

}else{
    $query = $db->prepare($selec="SELECT COUNT(*) AS cantidad,
    id_lote_inscripcionfk,
    CONCAT(YEAR(fecha_inscripcion),'-',MONTH(fecha_inscripcion)) AS periodo,
    etapa,
    estado_inscripcion AS valor_registro,
    CASE  
        WHEN estado_inscripcion='1' THEN 'EN PROCESO' 
        WHEN estado_inscripcion='2' THEN 'RECIBIDO'
        WHEN estado_inscripcion='3' THEN 'ENTREGADO'                                  
    END AS estado_inscripcion
    FROM inscripcion i
    INNER JOIN lotecarnet l ON i.id_lote_inscripcionfk=l.id_lote 
    WHERE id_lote_inscripcionfk=:lote
    GROUP BY  MONTH(fecha_inscripcion), estado_inscripcion");
     $query->bindValue(':lote',$lote, PDO::PARAM_INT);
     $query->execute();
}
   
    
    class PDF extends FPDF
    {
    // Cabecera de página
    function Header()
    {
          $this->Cell(40,24, $this->Image('../../assets/img/logo-system.png',20,11,25),1,0,'C');
          $this->SetFont('Arial','B',10);  
          $this->Cell(160,7, $GLOBALS['titulo'], 1,0,'C');
          $this->Cell(40,24, $this->Image('../../assets/img/logo-system.png', 218,11,25),1,0,'C');
          $this->Ln(7);
          $this->Cell(40);
          $this->Cell(160,17, utf8_decode($GLOBALS['tituloReporte']) ,1,0,'C');
          $this->Ln(-7);
          $this->Cell(240);
          $this->SetFont('Arial','B',8);
          $this->Cell(35,7,utf8_decode('Código:'.$GLOBALS['CodigoReporte']),1,0,'C');
          $this->Ln(7);
          $this->Cell(240);
          $this->SetFont('Arial','B',7);
          $this->Cell(35,10,utf8_decode($GLOBALS['FechaVigencia']),1,0,'L');
          $this->Ln(10);
          $this->Cell(240);
          $this->SetFont('Arial','B',7);
          $this->Cell(35,7,utf8_decode('Versión:'.$GLOBALS['VersionReporte']),1,0,'C'); 
          $this->Ln(10);
    
    }
    
    // Pie de página
    function Footer()
    {
          // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        $this->Cell(30,12,  date('m-d-Y h:i:s a', time()),'T',0,'C');
        $this->Cell(230,12, utf8_decode('Sistema control caranetización'),'T',0,'C');
        $this->Cell(0,12,'Page '.$this->PageNo().'/{nb}','T',0,'C');
      
    }
    }
    
    // Creación del objeto de la clase heredada
    $pdf = new PDF("L");
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',8);
    $pdf->SetFillColor(192,192,192); 
    $pdf->Ln(5);
    $pdf->Cell(30,5, 'No', 1,0, 'L', 'true');
    $pdf->Cell(60,5, 'CANTIDAD', 1,0,'C', 'true');
    $pdf->Cell(60,5, utf8_decode('PÉRIODO'), 1,0,'C', 'true');
    $pdf->Cell(60,5, utf8_decode('ETAPA'), 1,0,'C', 'true');
    $pdf->Cell(60,5, 'ESTADO',1,0,'L', 'true','C');
      
    
    $pdf->Ln(5);
    
    $j=1; $total=0;
    if (!$query->rowCount() == 0){
        while ($fila = $query->fetch()){
               $pdf->Cell(30,5,($j), 1,0,'C');
               $pdf->Cell(60,5,utf8_decode((trim($fila['cantidad']))),1,0,'C');
               $pdf->Cell(60,5,utf8_decode((trim($fila['periodo']))), 1,0,'C');
               $pdf->Cell(60,5,utf8_decode((trim($fila['etapa']))), 1,0,'C');
               $pdf->Cell(60,5,utf8_decode((trim($fila['estado_inscripcion']))), 1,0,'C');
               $pdf->Ln(5);
               $total+=$fila['cantidad'];
              
          $j++;
        }
        $pdf->Cell(30,5, 'TOTALES:', 1,0,'C', 'true');
        $pdf->Cell(60,5, $total, 1,0,'C');
    
    }
    $pdf->Output('ReporteAgrupadoLote'.'_'. date('m-d-Y h:i:s a', time()).'.pdf','D');
      
?>