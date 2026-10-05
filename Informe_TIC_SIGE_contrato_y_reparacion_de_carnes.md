# Informe TIC → SIGE: contrato y reparación de carnés

**Emisor:** Equipo TIC (Sistema de Gestión Académica y Carnetización)  
**Destinatario:** Contraparte Técnica SIGE (Sistema Integrado de Gestión y Carnetización)  
**Fecha:** 5 de octubre de 2026  
**Repositorio:** `C:\xampp\htdocs\tic.scv.edu.co`  
**Estado:** Reparación Local Implementada y Verificada en Entorno de Pruebas.

---

## 1. Archivos, Líneas Revisados y Revisión Git

### 1.1 Archivos y Líneas Clave Revisados en TIC
- [`developer/Models/SigeCarnetCompat.php`](file:///C:/xampp/htdocs/tic.scv.edu.co/developer/Models/SigeCarnetCompat.php)
  - Líneas 23–154: `registrarEventoYProyectar` (Recepción de eventos, control de idempotencia y versiones monotónicas).
  - Líneas 156–181: `obtenerEstadoCarnet` (Consulta de proyección actual, orden pendiente y orden rechazada/conflicto).
  - Líneas 183–313: `crearOrdenCarnet` (Validación de reglas del dominio, verificación de versión esperada y guardado en Outbox).
  - Líneas 315–334: `bloquearProyeccionActual` (Búsqueda autoritativa con validación estricta de identidad por documento e inscripción).
- [`developer/Services/SigeOrdenDispatcher.php`](file:///C:/xampp/htdocs/tic.scv.edu.co/developer/Services/SigeOrdenDispatcher.php)
  - Líneas 17–37: `procesarPendientes` (Reclamación con bloqueo concurrente `FOR UPDATE` y lease timeout).
  - Líneas 70–117: `enviarYFinalizar` (Tratamiento de respuestas HTTP, diferenciación de códigos de error `CONFLICTO_OBSOLETO` vs `ID_OPERACION_CONFLICTIVO`, límite de reintentos e idempotencia).
- [`developer/Services/SigeWebhook.php`](file:///C:/xampp/htdocs/tic.scv.edu.co/developer/Services/SigeWebhook.php)
  - Líneas 132–163: `construirPayload` (Normalización UTF-8 mediante `mb_strtoupper`, condicionamiento de `fecha_registro` solo a inscripciones en estado entregado `3`).
- [`developer/Controller/inscripcionController.php`](file:///C:/xampp/htdocs/tic.scv.edu.co/developer/Controller/inscripcionController.php)
  - Líneas 64–121: Caso `CrearOrdenCarnet` (Control de operaciones autorizadas y despacho asíncrono).
  - Líneas 122–165: Caso `ActivarChip` (Vinculación atómica transaccional, retorno de `id_operacion` y `expected_version`, prevención de falsos éxitos).
- [`developer/Controller/sigeCarnetEventosController.php`](file:///C:/xampp/htdocs/tic.scv.edu.co/developer/Controller/sigeCarnetEventosController.php)
  - Líneas 36–44: Autenticación estricta del receptor inbound mediante `Authorization: Bearer <SIGE_INBOUND_API_KEY>`.
- [`javascripts/gestionarinscripcion.js`](file:///C:/xampp/htdocs/tic.scv.edu.co/javascripts/gestionarinscripcion.js)
  - Líneas 1763–1778: `EstadoCarnetSige` (Distinción visual en la interfaz de usuario entre orden pendiente y última orden rechazada).

### 1.2 Revisión Git
- Commit de referencia actual: `3bc5926` (`fix(acuses): corregir enrutamiento...`).
- Ramas y cambios ajenos preservados intactos.

---

## 2. Causa Demostrada frente a Hipótesis Pendientes

### 2.1 Causas Demostradas del Incidente
1. **Asunción de Versión 0 en Proyecciones Ausentes o Incompletas:**
   - **Mecanismo:** Si `sige_carnet_proyeccion` no tenía un registro local para la inscripción (o no se había vinculado `id_inscripcion`), `SigeCarnetCompat` asumía `expected_version = 0` y emitía una orden de tipo `ASIGNACION`.
   - **Resultado en Producción:** Cuando el estudiante ya existía previamente en SIGE en versión `1`, SIGE rechazó las órdenes de `ASIGNACION` con HTTP `409 CONFLICTO_OBSOLETO`.
2. **Hardcoding de Operación en `ActivarChip`:**
   - **Mecanismo:** La acción heredada `ActivarChip` invocaba fija y rígidamente `$sigeCarnetCompat->crearOrdenCarnet('ASIGNACION', ...)` sin verificar si el estudiante ya poseía un carné proyectado.
   - **Resultado:** En estudiantes con histórico de carné, la orden se rechazaba en TIC o rebotaba en SIGE.
3. **Silenciamiento de Errores de Orden en Interfaz (`ActivarChip`):**
   - **Mecanismo:** `ActivarChip` capturaba cualquier `Throwable` proveniente del Outbox/Dispatcher en un bloque `catch` silencioso y devolvía `{"success": true, "mensaje": "Chip RFID vinculado exitosamente."}`.
   - **Resultado:** La interfaz informaba éxito local al operador aun cuando la orden hacia SIGE fallaba o no se registraba.

### 2.2 Hipótesis y Auditoría Externa (SIGE DOC-695)
- **Validación del Rechazo:** La auditoría externa en SIGE (Informe DOC-695) confirmó que el rechazo `CONFLICTO_OBSOLETO` al recibir `expected_version=0` frente a la versión real `1` es un mecanismo de protección totalmente correcto y esperado por contrato.
- **Evaluación del Cuerpo de Respuesta en Resonancia de Comandos:** SIGE especificó que los reenvíos de comando pueden retornar `HTTP 200` incluso ante un rechazo original. `SigeOrdenDispatcher.php` en TIC evalúa estrictamente `status` y `codigo` del cuerpo JSON (marcando la orden como `CONFLICTO_OBSOLETO` / `FALLIDA` cuando `status != PROCESADO`), garantizando que los rechazos con HTTP 200 sean identificados correctamente sin quedar atrapados en bucles de reintento.
- **Discrepancia en el Nombre ("FELMAR" vs "FELIMAR"):** SIGE auditó internamente y corrigió un defecto local independiente que vaciaba nombres omitidos; SIGE confirmó que dicho defecto tampoco explica la diferencia entre "FELMAR" y "FELIMAR". TIC preserva y transmite el nombre autoritativo completo (`FELIMAR ANDREA PACHECO VANEGAS`) en UTF-8.

---

## 3. Cambios Implementados y Pruebas Ejecutadas

### 3.1 Cambios en el Código TIC
1. **Conciliación Dinámica y Distinción de Proyección Ausente:**
   - Se añadió `conciliarProyeccionSiAusente()` en `SigeCarnetCompat.php`. Distingue con precisión entre `carnet: null` en un estudiante registrado en SIGE (`es_nuevo = true`, versión esperada 0) y un estudiante ausente en SIGE (`no_encontrados`, `es_ausente = true`). Si el estudiante no existe en SIGE (`es_ausente = true`), se rechaza la creación de la orden con `DomainException("El estudiante no se encuentra registrado en SIGE.")`.
   - **Eliminación Absoluta del Fallback a Versión 0 sin Reconciliación (Líneas 406–415 en `SigeCarnetCompat.php`):** Se reemplazó la asignación ternaria implícita por una verificación explícita: si `$carnet` es `null` y `$esNuevo` es `false` (debido a falta de configuración de `SIGE_API_KEY` o fallo de red), TIC detiene la emisión arrojando un `DomainException("No es posible determinar la versión autoritativa del carné en SIGE")`, impidiendo de forma estricta derivar versión 0 sobre suposiciones.
2. **Validación Estricta de Identidad por Documento e Inscripción:**
   - En `bloquearProyeccionActual`, si no hay coincidencia directa por `id_inscripcion`, se verifica por `numero_documento` únicamente si la identificación coincide estrictamente con la ficha del alumno en `inscripcion` y pertenece al mismo registro. No hay fallback ambiguo entre personas distintas.
3. **Persistencia Transaccional Inmediata, Verificación Estricta del UUID Remoto y Reversión Automática en `ActivarChip`:**
   - **Persistencia Inmediata vs Entrega Asíncrona:** Toda orden generada se guarda síncrona y transaccionalmente en `sige_orden_outbox` con estado `PENDIENTE`. Su despacho hacia SIGE ocurre asíncronamente mediante `SigeOrdenDispatcher` con backoff y reintentos.
   - **Verificación Estricta del UUID Remoto (`ActivarChip` en `inscripcionController.php`):** `ActivarChip` inspecciona el estado del `id_operacion` (UUID v4) específico generado. Retorna `success: true` **únicamente** si el estado del UUID es `'ENVIADA'` (despacho y ACK `PROCESADO` confirmados por SIGE). Si el estado permanece en `'PENDIENTE'` o `'REINTENTO'`, responde `success: false` informando que la confirmación remota para ese UUID está en cola.
   - **Reversión Local Automática ante Rechazo Remoto:** Tanto en la verificación inmediata de `ActivarChip` como en el despacho asíncrono de `SigeOrdenDispatcher`, si el resultado del UUID de la orden concluye en `'FALLIDA'` o `'CONFLICTO_OBSOLETO'`, TIC ejecuta automáticamente `revertirChipInscripcion($id)` (restaurando `chip_carnet = 'NO'`, `uid_rfid = NULL` en la tabla `inscripcion`), garantizando que la transacción local no conserve un chip rechazado por SIGE.
4. **Diferenciación de Códigos de Conflicto 409 y Límites de Reintentos:**
   - `SigeOrdenDispatcher.php` distingue los códigos de conflicto HTTP 409: `CONFLICTO_OBSOLETO` marca la orden para reconciliación de versión, mientras que `ID_OPERACION_CONFLICTIVO` o `CONFLICTO_ESTADO` la marca como `FALLIDA` (terminal).
   - Se evalúa `SIGE_OUTBOX_MAX_ATTEMPTS` (máximo 12 intentos). Si se agota, marca la orden como `FALLIDA` con causa explícita.
5. **Separación de Estados en Interfaz:**
   - `obtenerEstadoCarnet` retorna `orden_rechazada` junto con `orden_pendiente`. La interfaz en `gestionarinscripcion.js` muestra las alertas de rechazo u obsoleto y permite generar una nueva orden limpia con un nuevo `UUID`.
6. **Preservación UTF-8 en Nombres y Autenticación Inbound:**
   - `SigeWebhook.php` utiliza `mb_strtoupper(..., 'UTF-8')` para evitar corromper caracteres acentuados y condiciona `fecha_registro` a `estado_inscripcion == 3`.
   - `sigeCarnetEventosController.php` exige estrictamente el encabezado `Authorization: Bearer <SIGE_INBOUND_API_KEY>` para la recepción de eventos remotos (líneas 37–44).

### 3.2 Pruebas Ejecutadas
- **`php developer/tests/contract_characterization_test.php`**: **PASS (100% OK)**
- **`php developer/tests/legacy_integration_behavior_test.php`**: **PASS (100% OK)**
- **`php developer/tests/sige_carnet_flujo_test.php`**: **PASS (100% OK)**
  - Cobertura de escenarios: Primera asignación, reemplazo autoritativo, proyección ausente/obsoleta, rechazo de identidad ambigua, conflicto concurrente `409`, orden duplicada (reuso de `id_operacion`), timeout tras aceptación (idempotencia ACK), evento duplicado o atrasado (`OBSOLETO_IGNORADO`), rechazo visible en UI y sincronización UTF-8 del nombre.

---

## 4. Contratos Utilizados

### 4.1 Comandos TIC → SIGE (`SIGE_CARNET_COMMAND_URL`)
- **Endpoint:** `POST /api/integracion/tic/carnets/comandos`
- **Headers:** `Content-Type: application/json`, `Authorization: Bearer <SIGE_OUTBOUND_API_KEY>`
- **Payload:**
  ```json
  {
    "id_operacion": "550e8400-e29b-41d4-a716-446655440000",
    "tipo": "REEMPLAZO",
    "numero_documento": "1042463034",
    "uid_rfid": "0008557921",
    "motivo": "DETERIORO",
    "expected_version": 1,
    "ocurrido_en": "2026-10-05 10:00:00",
    "usuario": {
      "id": "1",
      "nombre": "OPERADOR TIC",
      "rol": "OPERATIVO"
    }
  }
  ```
- **Respuesta Esperada (ACK):**
  - `HTTP 201 Created` o `HTTP 200 OK`
  ```json
  {
    "status": "PROCESADO",
    "id_operacion": "550e8400-e29b-41d4-a716-446655440000",
    "codigo": "OK",
    "mensaje": "Comando procesado exitosamente"
  }
  ```
- **Respuesta de Conflicto:**
  - `HTTP 409 Conflict`
  ```json
  {
    "status": "RECHAZADO",
    "id_operacion": "550e8400-e29b-41d4-a716-446655440000",
    "codigo": "CONFLICTO_OBSOLETO",
    "mensaje": "La version esperada (0) no coincide con la version en SIGE (1)"
  }
  ```

### 4.2 Reconciliación TIC → SIGE (`SIGE_CARNET_RECONCILIATION_URL`)
- **Endpoint:** `POST /api/integracion/tic/carnets/reconciliar`
- **Headers:** `Authorization: Bearer <SIGE_OUTBOUND_API_KEY>`
- **Payload:**
  ```json
  {
    "estudiantes": [
      { "numero_documento": "1042463034", "version_estado": 0 }
    ]
  }
  ```
- **Respuesta:**
  ```json
  {
    "status": "PROCESADO",
    "estudiantes": [
      {
        "numero_documento": "1042463034",
        "version_estado": 1,
        "requiere_reactivacion": false,
        "carnet": {
          "sige_carnet_id": 65,
          "estado": "ACTIVO",
          "uid_rfid": "0008308774",
          "motivo": "NO_APLICA",
          "fecha_emision": "2025-01-15 00:00:00",
          "vigencia_hasta": "2026-01-15 00:00:00"
        }
      }
    ],
    "no_encontrados": []
  }
  ```

### 4.3 Eventos Inbound SIGE → TIC (`sigeCarnetEventosController.php`)
- **Endpoint:** `POST /Marge/SigeCarnetEventos`
- **Headers:** `Authorization: Bearer <SIGE_INBOUND_API_KEY>`
- **Payload:**
  ```json
  {
    "id_evento": "660e8400-e29b-41d4-a716-44665544011",
    "tipo": "CARNET_REEMPLAZADO",
    "version_estado": 2,
    "origen": "SIGE",
    "ocurrido_en": "2026-10-05 10:05:00",
    "estudiante": {
      "tic_id_inscripcion": 22796,
      "numero_documento": "1042463034"
    },
    "carnet": {
      "sige_carnet_id": 88,
      "uid_rfid": "0008557921",
      "estado": "ACTIVO",
      "motivo": "DETERIORO",
      "fecha_emision": "2026-10-05 10:05:00",
      "vigencia_hasta": "2027-10-05 10:05:00"
    }
  }
  ```

---

## 5. Resolucción de Identidad, Proyección Ausente y Conflictos

1. **Resolución de Identidad:**
   - La correlación se realiza prioritariamente por `id_inscripcion`. Si no está en la proyección, se valida que el `numero_documento` en `inscripcion` pertenezca al mismo estudiante. Si el documento pertenece a otra persona o es ambiguo, se rechaza la asociación.
2. **Proyección Ausente:**
   - Se consulta a SIGE vía Reconciliación. Si SIGE responde `no_encontrados`, se determina con certeza `version_estado = 0` (estudiante nuevo). Si SIGE devuelve un carné autoritativo en versión `N`, TIC sincroniza la proyección local a la versión `N` antes de emitir cualquier orden.
3. **Manejo de Conflictos y Concurrencia:**
   - Si se recibe HTTP `409 CONFLICTO_OBSOLETO`, la orden se marca en Outbox como `CONFLICTO_OBSOLETO`. El worker de reconciliación actualiza la proyección local a la versión mayor existente. La interfaz visualiza el fallo y permite al operador enviar la acción correcta (`REEMPLAZO` o `REACTIVACION`) con `expected_version` actualizada y un nuevo `UUID`.

---

## 6. Garantías de Idempotencia y Tratamiento de Timeouts

- **Garantía en Outbound (TIC → SIGE):**
  - Cada orden genera un `id_operacion` único (UUID v4) que permanece inmutable durante los reintentos.
  - En caso de un timeout de red tras la recepción/procesamiento en SIGE, los reintentos de TIC envían el mismo `id_operacion`. SIGE debe retornar el ACK `PROCESADO` previamente registrado sin duplicar efectos ni incrementar la versión de nuevo.
- **Garantía en Inbound (SIGE → TIC):**
  - En `sige_carnet_evento`, la clave `id_evento` registra el hash del payload. Si SIGE reenvía un evento con el mismo `id_evento` y payload idéntico, TIC responde `DUPLICADO` sin reaplicar cambios.
  - Si la `version_estado` recibida es menor o igual a la `version_estado` proyectada actualmente en TIC, el evento se marca como `EVENTO_OBSOLETO_IGNORADO` y se descarta la modificación de estado.

---

## 7. Eventos de Retorno Necesarios y Criterios de Conciliación

1. **Emisión Inmediata de Eventos al Procesar Comandos:**
   - Cada vez que SIGE procese un comando de `ASIGNACION`, `REEMPLAZO`, `BLOQUEO` o `REACTIVACION_AUTORIZADA`, debe emitir el evento correspondiente a TIC vía `POST /Marge/SigeCarnetEventos`.
2. **Campos Requeridos en el Evento:**
   - `id_evento` (UUID v4 único), `tipo`, `version_estado` (entero estrictamente incremental), `estudiante.numero_documento`, `carnet.sige_carnet_id`, `carnet.uid_rfid`, `carnet.estado` (`ACTIVO`/`INACTIVO`).
3. **Criterios de Conciliación Asíncrona:**
   - La versión de estado (`version_estado`) en SIGE es monótona estrictamente creciente. TIC nunca decrementa la versión local al recibir eventos o respuestas de reconciliación.

---

## 8. Autoridad y Propagación del Nombre

- **Fuente Autoritativa:** TIC / Q10 es la fuente maestra para los nombres y apellidos del estudiante.
- **Formato y Codificación:** TIC envía los nombres en el webhook usando `mb_strtoupper(..., 'UTF-8')` preservando tildes, eñes y caracteres especiales.
- **Propagación:** Solicitamos a SIGE verificar que en la recepción del webhook `POST /api/webhook/tic/estudiante`, los campos `nombres` y `apellidos` actualicen la tabla de `personas`/`estudiantes` en SIGE para corregir discrepancias como "FELMAR" → "FELIMAR ANDREA PACHECO VANEGAS".

---

## 9. Solicitudes Concretas a SIGE

### Bloqueantes (Requeridas para operación continua)
1. **Verificación de Idempotencia por `id_operacion`:** Confirmar que al reintentar un comando con el mismo `id_operacion`, SIGE retorna `HTTP 200/201` con status `PROCESADO` sin rechazar ni duplicar la acción.
2. **Retorno de Evento tras Comando:** Asegurar que SIGE publique el evento de carné a TIC tan pronto se aplique el comando.

### Opciones de Mejora (Recomendadas)
1. **Actualización Automática del Nombre:** Sincronizar nombres y apellidos recibidos en el webhook TIC a las tablas de personas de SIGE.
2. **Endpoint de Consulta Individual:** Permitir la consulta ligera de versión por documento en el API de Reconciliación.

---

## 10. Ejemplo de Prueba con Datos Ficticios

```json
// Petición TIC -> SIGE (Comando Reemplazo Ficticio)
POST /api/integracion/tic/carnets/comandos
Authorization: Bearer <SIGE_OUTBOUND_API_KEY>
Content-Type: application/json

{
  "id_operacion": "a1b2c3d4-e5f6-47a8-b901-23456789abcd",
  "tipo": "REEMPLAZO",
  "numero_documento": "999888777",
  "uid_rfid": "9988776655",
  "motivo": "PERDIDA",
  "expected_version": 1,
  "ocurrido_en": "2026-10-05 10:10:00",
  "usuario": {
    "id": "100",
    "nombre": "OPERADOR PRUEBAS",
    "rol": "OPERATIVO"
  }
}

// Respuesta SIGE -> TIC
HTTP/1.1 201 Created
Content-Type: application/json

{
  "status": "PROCESADO",
  "id_operacion": "a1b2c3d4-e5f6-47a8-b901-23456789abcd",
  "codigo": "OK",
  "mensaje": "Orden de reemplazo registrada exitosamente."
}
```

---

## 11. Verificaciones Pendientes en Producción y Criterios de Aceptación

### Verificaciones Pendientes en Producción
1. Validar la respuesta HTTP del endpoint de reconciliación en producción ante estudiantes sin carné previo.
2. Comprobar la prueba física de lectura de chip y acceso en torniquetes con el carné nuevo `0008557921` (caso del documento `1042463034`).

### Criterios de Aceptación Cumplidos
- [x] Reparación local sin refactorizaciones ajenas ni modificaciones en base de datos de producción.
- [x] Eliminación de la creación automática de órdenes con `expected_version = 0` sobre estudiantes existentes.
- [x] Manejo estricto de identidad e inscripciones.
- [x] Distinción clara en la interfaz de usuario entre solicitudes pendientes, procesadas y rechazadas.
- [x] Ejecución limpia del 100% de la suite de pruebas automatizadas.
