<?php 
require_once '../developer/PDOConn.php';

class Temas extends db
{
  	public function loadPrograma()
	{
	  $sql = "SELECT codigo as cod, nombre as nombre FROM pro_programa WHERE estado = 'on'";
	  $res = $this->table($sql);
	  return $res;
	}

	public function loadModulo()
	{
	  $sql = "SELECT codigo as cod, nombre as nombre FROM pro_modulo WHERE estado = 'on'";
	  $res = $this->table($sql);
	  return $res;
	}

	public function loadSede()
	{
	  $sql = "SELECT codigo_sede as cod, nombre_sede as nombre, estado_sede FROM pro_sedes WHERE estado_sede = 'on'";
	  $res = $this->table($sql);
	  return $res;
	}

	public function loadAnoLect()
	{
	  $sql = "SELECT codigo as cod, nombre as nombre FROM pro_aniolectivo WHERE estado = 'on'";
	  $res = $this->table($sql);
	  return $res;
	}

	public function loadGestionarTemas($whe)
	{
	    $sql = "SELECT prm.codigo, pro.nombre AS programa, mod.nombre AS modulo, sed.nombre_sede AS sede, prm.fechainicio, prm.fechafin, ani.nombre AS ano
				FROM pro_programa_modulo prm
				INNER JOIN pro_sedes sed ON codsede = codigo_sede
				INNER JOIN pro_programa pro ON codprograma = pro.codigo 
				INNER JOIN pro_modulo mod ON prm.codmodulo = mod.codigo
				INNER JOIN pro_aniolectivo ani ON prm.codanio = ani.codigo ".$whe;
	    $res = $this->table($sql);
	    return $res;
	}

	public function loadAgregarTemas($codigo)
	{
		$sql = "SELECT codigo, nombre, estado FROM pro_tema WHERE codprograma_modulo = :codprogramamodulo";
		$params = array(':codprogramamodulo'=>$codigo);
		$res = $this->table($sql,$params);
		return $res;
	}

	public function editEstadoTemas($codigo, $estado)
  	{
	    $update = "UPDATE pro_tema SET estado = :estado WHERE codigo = :codigo";
	    $params = array(':codigo' => $codigo, ':estado' => $estado);
	    $res = $this->query($update, $params);
	    return $res;
  	}

  	public function editEstadoActividad($codigo, $estado)
  	{
	    $update = "UPDATE pro_actividad SET estado = :estado WHERE codigo = :codigo";
	    $params = array(':codigo' => $codigo, ':estado' => $estado);
	    $res = $this->query($update, $params);
	    return $res;
  	}

  	public function insertTemas($nombre, $codprogramamodulo, $estado)
  	{
	    $insert = "INSERT INTO pro_tema (nombre, codprograma_modulo, estado) VALUES (:nombre, :codprogramamodulo, :estado)";
	    $params = array(':nombre' => $nombre, ':codprogramamodulo' => $codprogramamodulo, ':estado' => $estado);

	    $res = $this->query($insert, $params);
      	return $res;
  	}

  	public function insertActividad($nombre, $codigo, $estado)
  	{
  		$insert = "INSERT INTO pro_actividad (nombre, codtema, estado) VALUES (:nombre, :codtema, :estado)";
  		$params = array(':nombre' => $nombre, ':codtema' => $codigo, 'estado' => $estado);

  		$res = $this->query($insert, $params);
  		return $res;
  	}

  	public function editTema($nombre, $codigo)
  	{
	    $sql="UPDATE pro_tema SET nombre=:nombre WHERE codigo=:codigo";
	    $params = array(':nombre' => $nombre, ':codigo' => $codigo);
	    $res = $this->query($sql, $params);
		return $res;
	}

	public function editActividad($codigo, $nombre)
  	{
	    $sql="UPDATE pro_actividad SET nombre = :nombre WHERE codigo = :codigo";
	    $params = array(':nombre' => $nombre, ':codigo' => $codigo);
	    $res = $this->query($sql, $params);
		return $res;
	}

	public function loadActividades($codigo)
	{
		$sql = "SELECT codigo, nombre, estado FROM pro_actividad WHERE codtema = :codtema";
		$params = array(':codtema' => $codigo);
		$res = $this->table($sql, $params);
		return $res;
	}

}
