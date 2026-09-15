<?php 
require_once '../developer/PDOConn.php';

class ProgramaModulo extends db
{
  
	public function loadProgramaModulo($whe)
	{
	      $sql = "SELECT prm.codigo, pro.codigo AS codpro, pro.nombre AS programa, mod.codigo AS codmod, mod.nombre AS modulo, sed.codigo_sede AS codsed, sed.nombre_sede AS sede, prm.fechainicio, prm.fechafin, ani.codigo AS codani, ani.nombre AS ano, prm.estado, prm.valor 
				FROM pro_programa_modulo prm
				INNER JOIN pro_sedes sed ON codsede = codigo_sede
				INNER JOIN pro_programa pro ON codprograma = pro.codigo 
				INNER JOIN pro_modulo mod ON prm.codmodulo = mod.codigo
				INNER JOIN pro_aniolectivo ani ON prm.codanio = ani.codigo ".$whe;
	      $res = $this->table($sql);
	      return $res;
	}

	public function loadPrograma()
	{
	  $sql = "SELECT codigo as cod, nombre as nombre, estado FROM pro_programa WHERE estado = 'on'";
	  $res = $this->table($sql);
	  return $res;
	}

	public function loadModulo($codprograma)
	{
	  $sql = "SELECT codigo as cod, nombre as nombre, estado FROM pro_modulo WHERE estado = 'on' AND codprograma = :codprograma";
	  $params = array(':codprograma' => $codprograma);
	  $res = $this->table($sql, $params);
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
	  $sql = "SELECT codigo as cod, nombre as nombre, estado FROM pro_aniolectivo WHERE estado = 'on'";
	  $res = $this->table($sql);
	  return $res;
	}
	public function editEstadoProgrModulo($codigo, $estado)
  	{
	    $update = "UPDATE pro_programa_modulo SET estado = :estado WHERE codigo = :codigo";
	    $params = array(':codigo' => $codigo, ':estado' => $estado);
	    $res = $this->query($update, $params);
	    return $res;
  	}

	public function editItemProgrModulo($codigo, $programa, $modulo, $sede, $fechaini, $fechafin, $anolectivo, $estado, $valor)
  	{
	    $update = "UPDATE pro_programa_modulo SET codprograma = :codprograma, codmodulo = :codmodulo, codsede = :codsede, fechainicio = :fechainicio, fechafin = :fechafin, codanio = :codanio, estado = :estado, valor = :valor WHERE codigo = :codigo";
	    $params = array(':codigo' => $codigo, ':codprograma' => $programa, ':codmodulo' => $modulo, ':codsede' => $sede, ':fechainicio' => $fechaini, ':fechafin' => $fechafin, ':codanio' => $anolectivo, ':estado' => $estado, ':valor' => $valor);
	    $res = $this->query($update, $params);
	    return $res;
  	}
	public function insertPrograModulo($programa, $modulo, $sede, $fechaini, $fechafin, $anolectivo, $estado, $valor)
  	{
  		$select = "SELECT * FROM pro_programa_modulo WHERE codprograma = :codprograma AND codmodulo = :codmodulo AND codsede = :codsede";
  		$params = array(':codprograma'=>$programa, ':codmodulo'=>$modulo, ':codsede'=>$sede);
  		$res = $this->row($select,$params);

  		if($res != ""){
  			$orden = $res["orden"] + 1;
  		}
  		else{
  			$orden = 1;
  		}

  		$insert = "INSERT INTO pro_programa_modulo (codprograma, codmodulo, codsede, fechainicio, fechafin, codanio, estado, orden,  valor) VALUES (:codprograma, :codmodulo, :codsede, :fechainicio, :fechafin, :codanio, :estado, :orden,  :valor)";
      	$params = array(':codprograma' => $programa, ':codmodulo' => $modulo, ':codsede' => $sede, ':fechainicio' => $fechaini, ':fechafin' => $fechafin, ':codanio' => $anolectivo, ':estado' => $estado, ':orden' => $orden, ':valor' => $valor);
      	$res = $this->query($insert,$params);
     	return $res;  	
 	}
  	

}