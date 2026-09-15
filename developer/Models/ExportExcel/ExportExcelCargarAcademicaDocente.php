<?php
header('content-type: aplication/vnd.ms-excel');
header('content-Disposition: attachment; filename=archivo.xls');

require_once('../../Config/PDOconn.php');
$db= new PDO(connstring,user,pass);
if(isset($_POST['txtselectperiodocarga'])){$Rperiodo=$_POST['txtselectperiodocarga'];} 
$i=1;

$query = $db->prepare("SELECT id_carga,
                    id_curso_cargafk,
                    COUNT(id_detalle_cargafk) AS cantidad,
                    codigo_curso,
                    id_modulo_cargafk,
                    nombre_modulo,
                    id_docente_cargafk,
                    nombre_docente,
                    apellido_docente,
                    id_periodo_cargafk,
                    periodo,
                    fecha_carga,
                    id_sede_cursofk,
                    nombre_sede,
                    id_horario_cursofk,
                    nombre_horario,
                    dia_horario,
                    id_ambiente_cargafk,
                    nombre_ambiente,
                    fecha_carga,
                    creado_por FROM carga_academica ca 
                        INNER JOIN detalle_carga 	dc 	ON ca.id_carga=dc.id_detalle_cargafk 
                        INNER JOIN curso         	c 	ON ca.id_curso_cargafk=c.id_curso
                        INNER JOIN modulo        	m	ON ca.id_modulo_cargafk=m.id_modulo
                        INNER JOIN docente       	d 	ON ca.id_docente_cargafk=d.id_docente
                        INNER JOIN periodo       	p 	ON ca.id_periodo_cargafk=p.id_periodo
                        INNER JOIN sede          	s 	ON c.id_sede_cursofk=s.id_sede
                        INNER JOIN horario       	h 	ON c.id_horario_cursofk=h.id_horario
                        INNER JOIN ambiente      	a 	ON ca.id_ambiente_cargafk=a.id_ambiente
                        WHERE   id_periodo_cargafk:valor
                        GROUP BY id_detalle_cargafk
                        ORDER BY codigo_curso ASC");
                        $query->bindValue(':valor', $Rperiodo, PDO::PARAM_INT);
                        $query->execute();

 
if (!$query->rowCount() == 0){?>
        <table class="table table-hover table-striped table-condesed">
            <thead>
                <tr class="bg-primary">
                    <th>No</th>
                    <th>Périodo</th>
                    <th>Curso</th>
                    <th>Cantidad</th>
                    <th>Salón</th>
                    <th>Módulo</th>
                    <th>Sede/Horario</th>
                    <th>Docente</th>
                    <th>Fecha-Asignacón</th>

            </tr>
            </thead>
             <?php while ($row = $query->fetch()){?>
              <tbody class="buscar">
                <tr>
                    <td><?= $i; ?></td>
                    <td><?=$row['periodo']?></td>
                    <td><?=$row['periodo']?></td>
                    <td><?=$row['cantidad']?></td>
                    <td><?=$row['nombre_ambiente']?></td>
                    <td><?=$row['nombre_modulo']?></td>
                    <td><?=$row['nombre_sede'].'/'.$row['nombre_horario'].'/'.$row['dia_horario']?></td>  
                    <td><?=$row['apellido_docente'] .' '.$row['nombre_docente']  ?></td>
                    <td><?=$row['fecha_carga']?></td>
                </tr>  
            </tbody>
            <?php 
            } 
            }
            ?>
             </table>
