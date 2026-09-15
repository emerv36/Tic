<?php 
require_once '../developer/PDOConn.php';

class cancelarClass extends db
{
  public function listarProgramacion($fecha, $hora){
    $sql = "SELECT pmp.codigo AS codigo, pmp.cuposminimo, cuposmaximo, (SELECT count(pma.codigo) AS asistentes FROM pro_modulo_asistencia   pma WHERE pma.estado = 'on' AND pmp.codigo = pma.codmodulo_programacion)
                    FROM  pro_modulo_programacion pmp 
                                WHERE pmp.fecha = :fecha AND pmp.horainicio = :hora";
    $params = array(':fecha' => $fecha, ':hora' => $hora);
    $res = $this->table($sql, $params);
      return $res;
  }

	public function cancelarClase($codigo){
	  $sql = "UPDATE pro_modulo_programacion SET motivo_cancelacion = 'La clase fue cancelada porque no cumpio con el cupo minímo.',  estado = 'off' WHERE codigo = :codigo";
	  $params = array(':codigo' => $codigo);
	  $res = $this->query($sql, $params);
	    return $res;
	}


}