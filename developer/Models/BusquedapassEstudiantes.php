<?php 
require_once('../Config/PDOconn.php');
$db= new PDO(connstring,user,pass);
$valor=strtoupper($_POST['b']);

function resaltar($string, $frase, $taga = '<span style="background-color:#ffcc00">', $tagb = '</span>')
{
return ($string !== '' && $frase !== '')
? preg_replace('/('.preg_quote($frase, '/').')/i'.('true' ? 'u' : ''), $taga.'\\1'.$tagb, $string)
: $string;
}


///consulta de estudiantes inscripiciones ////
$query = $db->prepare("SELECT id_inscripcion, 
                              identificacion,
                              tipo_identificacionfk,
                              nombre_identidad,
                              nombre_estudiante,
                              apellido_estudiante,
                              email_estudiante,
                              celular_estudiante,
                              nombre_sede,
                              nombre_programa,
                              nombre_horario,
                              dia_horario,
                              estado_inscripcion,
                              tipo_inscripcion,
                              password_estudiante,
                              estado_registro AS valor_registro,
                              CASE  
                                 WHEN estado_registro='1' THEN 'INSCRITO' 
                                 WHEN estado_registro='2' THEN 'MATRICULADO'                                    
                              END AS estado_registro      
       FROM inscripcion i
       INNER JOIN tipo_identidad          ti    ON i.tipo_identificacionfk=ti.id_tipo
       INNER JOIN sede                    s     ON i.id_sede_inscripcionfk=s.id_sede
       INNER JOIN programa                pt    ON i.id_programa_inscripcionfk=pt.id_programa
       INNER JOIN horario                 h     ON i.id_horario_inscripcionfk=h.id_horario

       WHERE (estado_inscripcion='on' AND estado_registro='2') AND (nombre_estudiante LIKE :valor  OR apellido_estudiante LIKE :valor OR identificacion  LIKE :valor)  limit 10");

        $query->bindValue(':valor', '%'.$valor.'%', PDO::PARAM_STR);
        $query->execute();

      ?>
 <div class="row">
 <div class="col-md-12">
<?php 
$i=1; 
if (!$query->rowCount() == 0){?>


  <table class="table table-hover table-striped table-condesed">
   <thead class="bg-primary">
       <th>No</th>
       <th>Identidad</th>
       <th>Tipo Identidad</th>
       <th>Apellido</th>
       <th>Nombre</th>
       <th>Email</th>
       <th>Celular</th>
       <th>Estado</th>
       <th>Password</th>
   </thead>
    <?php while ($row = $query->fetch()){?> 
   <tbody>
      <td><?=$i?></td> 
      <td><?=$row['identificacion']?></td> 
      <td><?=resaltar($row['nombre_identidad'],$valor) ?></td> 
      <td><?=resaltar($row['apellido_estudiante'],$valor)  ?></td> 
      <td><?=resaltar($row['nombre_estudiante'],$valor)  ?></td>       
      <td><?=$row['email_estudiante']  ?></td> 
      <td><?=$row['celular_estudiante']  ?></td>
      <td><?=$row['estado_registro']  ?></td>  
      <td> 

      <a href="#"  data-toggle="tooltip" title="Estudiante Inscrito"
                            onclick="Cargarinfopass(<?=$row['id_inscripcion'].','
                                                            ."'".$row['identificacion']."'".','
                                                            ."'".$row['nombre_estudiante']."'".','
                                                            ."'".$row['apellido_estudiante']."'".','
                                                            ."'".$row['email_estudiante']."'".','
                                                            ."'".$row['password_estudiante']."'"
                                                             ?>)">

      <button class="label label label-success" title="Restaurar Password">Resturar Password</button>
      </a>
      </td>
   </tbody>

   <?php 
   $i++;
   }
   }else{
    echo '<div style="top:150px" class="col-md-12 text-center">¡No hay resultados!</div>';
}
?>
        </table>
    </div>
</div>
</div>

