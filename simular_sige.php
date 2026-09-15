<?php
require 'developer/Config/config.php';
try {
    $pdo = new PDO(connstring, user, pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Obtener pendientes y fallidas
    $stmt = $pdo->query("SELECT * FROM sige_personal_outbox WHERE estado IN ('PENDIENTE', 'FALLIDA')");
    $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pendientes as $p) {
        $payload = json_decode($p['payload'], true);
        
        $uuid = $payload['persona_uuid'] ?? '';
        if (empty($uuid) && $payload['comando'] === 'PERSONA_CREAR') {
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
        }
        
        if ($payload['comando'] === 'PERSONA_CREAR' || $payload['comando'] === 'VINCULO_ACTUALIZAR') {
            $vinculosJson = json_encode([$payload['vinculo']]);
            $stmtInsert = $pdo->prepare("
                INSERT INTO sige_personal_proyeccion 
                (persona_uuid, tipo_documento, numero_documento, nombres, apellidos, estado_persona, vinculos_json, tiene_foto, version_estado)
                VALUES (?, ?, ?, ?, ?, 'ACTIVA', ?, 1, 1)
                ON DUPLICATE KEY UPDATE estado_persona = 'ACTIVA'
            ");
            $stmtInsert->execute([
                $uuid,
                $payload['identidad']['tipo_documento'],
                $payload['identidad']['numero_documento'],
                $payload['identidad']['nombres'],
                $payload['identidad']['apellidos'],
                $vinculosJson
            ]);
            
            // Marcar outbox como exitoso
            $pdo->query("UPDATE sige_personal_outbox SET estado = 'EXITOSO' WHERE id = " . $p['id']);
            echo "Persona creada simulada: $uuid <br>";
        } else if ($p['comando'] === 'CARNET') {
            if ($p['tipo'] === 'ASIGNACION') {
                $rfid = $payload['uid_rfid'];
                $stmtUpdate = $pdo->prepare("
                    UPDATE sige_personal_proyeccion 
                    SET uid_rfid = ?, carnet_estado = 'EMITIDO' 
                    WHERE persona_uuid = ?
                ");
                $stmtUpdate->execute([$rfid, $uuid]);
                echo "Carnet asignado simulado para: $uuid (RFID: $rfid) <br>";
            } else if ($p['tipo'] === 'ENTREGA_CONFIRMADA') {
                $stmtUpdate = $pdo->prepare("
                    UPDATE sige_personal_proyeccion 
                    SET carnet_estado = 'ENTREGADO' 
                    WHERE persona_uuid = ?
                ");
                $stmtUpdate->execute([$uuid]);
                echo "Carnet entregado simulado para: $uuid <br>";
            } else if ($p['tipo'] === 'REEMPLAZO') {
                $rfid = $payload['uid_rfid'];
                $stmtUpdate = $pdo->prepare("
                    UPDATE sige_personal_proyeccion 
                    SET uid_rfid = ?, carnet_estado = 'EMITIDO' 
                    WHERE persona_uuid = ?
                ");
                $stmtUpdate->execute([$rfid, $uuid]);
                echo "Carnet reemplazado simulado para: $uuid (RFID: $rfid) <br>";
            } else if ($p['tipo'] === 'BLOQUEO') {
                $stmtUpdate = $pdo->prepare("
                    UPDATE sige_personal_proyeccion 
                    SET carnet_estado = 'BLOQUEADO' 
                    WHERE persona_uuid = ?
                ");
                $stmtUpdate->execute([$uuid]);
                echo "Carnet bloqueado simulado para: $uuid <br>";
            }
            
            // Marcar outbox como exitoso
            $pdo->query("UPDATE sige_personal_outbox SET estado = 'EXITOSO' WHERE id = " . $p['id']);
        }
    }
    
    echo "Simulación completada.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
