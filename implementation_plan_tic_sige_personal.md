# Plan de implementación TIC–SIGE: carnés de docentes y administrativos

Fecha de creación: 2026-08-30  
Estado general: `EN_PROGRESO`  
Código funcional modificado: sí; migración sin ejecutar, feature flag apagado y sidebar pendiente  
Preguntas funcionales pendientes del diseño inicial: 0

## Objetivo

Implementar en TIC un módulo separado de sidebar para gestionar operativamente docentes y administrativos y crear sus carnés mediante el mismo patrón técnico de los estudiantes, conservando estas autoridades:

- SIGE almacena autoritativamente personas, vínculos, fotografías, carnés físicos y accesos.
- TIC administra cargo y dependencia y actúa como consola operativa.
- TIC conserva únicamente catálogos propios, órdenes, eventos, proyecciones técnicas y auditoría de integración.
- Solo el rol activo exacto `CARNETIZACION` puede usar el módulo.
- No se reutilizan `inscripcion`, programa, sede, lote, Q10 ni tablas maestras TIC de personal.

## Reglas de ejecución y documentación

Para cada tarea se registrará continuamente:

- Decisión tomada y justificación.
- Archivos creados o modificados.
- Migraciones creadas, ejecutadas o pendientes.
- Pruebas ejecutadas y resultado.
- Riesgos nuevos o mitigados.
- Deuda y tareas pendientes.
- Evidencia de revisión de código por agente.

Una tarea no podrá pasar a `COMPLETADA` hasta tener criterios de aceptación verificados. Cada incremento requiere un agente de revisión de código distinto del implementador antes del cierre.

Estados: `PENDIENTE`, `EN_PROGRESO`, `BLOQUEADA`, `EN_REVISION`, `COMPLETADA`.

## Orden de implementación

```text
M0 Contratos y regresión base
 └─ M1 Catálogos TIC
     └─ M2 Población autoritativa SIGE
         └─ M3 Transporte durable y proyección TIC
             └─ M4 Asistente de personal
                 └─ M5 Emisión y producción del carné
                     └─ M6 Ciclo de vida institucional
                         └─ M7 Operación, despliegue y cierre
```

## M0 — Contratos y línea base

### INT-0001 — Consolidar matriz de autoridades

- Sistema: TIC/SIGE.
- Estado: `COMPLETADA`.
- Resultado: autoridades, exclusiones y decisiones funcionales documentadas en `code_diagnostic_report.md`.
- Evidencia: cinco documentos SIGE leídos y código TIC trazado.

### INT-0002 — Caracterizar el flujo estudiantil productivo

- Sistema: TIC.
- Estado: `COMPLETADA`.
- Resultado: identificados outbox estudiantil, Q10, órdenes, eventos, proyección, accesos, estados legados y acoplamientos a `inscripcion`.
- Riesgo conservado: convivencia entre `ActivarChip` legacy y órdenes SIGE.

### INT-0003 — Definir contratos API versionados de personal

- Sistema: TIC/SIGE.
- Estado: `COMPLETADA`.
- Dependencias: INT-0001, INT-0002.
- Entregables:
  - Esquemas de comando/ACK/evento para persona, vínculo, foto y carné.
  - Uso obligatorio de `id_operacion`, `persona_uuid` y `expected_persona_version`.
  - Catálogo de errores terminales, transitorios y de conflicto.
  - Reglas de compatibilidad con endpoints estudiantiles.
- Aceptación: ejemplos válidos e inválidos y reglas de idempotencia aprobadas sin ambigüedad.

### INT-0004 — Crear pruebas de caracterización estudiantil

- Sistema: TIC/SIGE.
- Estado: `COMPLETADA`.
- Dependencia: INT-0003.
- Alcance: payload estudiantil, Q10, comandos, ACK, eventos obsoletos, accesos y reconciliación.
- Aceptación: la suite detecta cualquier cambio incompatible antes de introducir personal.

### INT-0005 — Revisión de código del incremento M0

- Estado: `COMPLETADA`.
- Aceptación: agente revisor confirma contratos, cobertura y ausencia de cambios de comportamiento.

## M1 — Catálogos de cargo y dependencia en TIC

### INT-0101 — Diseñar migraciones de catálogos

- Sistema: TIC.
- Estado: `COMPLETADA`.
- Dependencia: INT-0005.
- Entregables:
  - Tabla dedicada de cargos de personal.
  - Tabla dedicada de dependencias.
  - Versión monotónica, estado lógico, timestamps y auditoría.
  - Restricciones de unicidad normalizada.
- Restricción: ninguna relación semántica con `sede`; no modificar `programa.tipo_control=2` en este incremento.

### INT-0102 — Implementar repositorios y servicios de catálogo

- Estado: `COMPLETADA`.
- Dependencia: INT-0101.
- Aceptación: altas, edición, inactivación y lectura incrementan/conservan versión correctamente mediante PDO preparado y transacciones.

### INT-0103 — Implementar autorización exacta y CSRF

- Estado: `COMPLETADA`.
- Dependencia: INT-0102.
- Aceptación: solo rol activo `CARNETIZACION`; `PROGRAMADOR`, `OPERATIVO` y sesiones ausentes reciben 403/401.

### INT-0104 — Crear interfaz de catálogos

- Estado: `COMPLETADA`.
- Dependencias: INT-0102, INT-0103.
- Aceptación: CRUD separado, accesible desde el módulo de personal, sin campos de sede.

### INT-0105 — Publicar snapshot/version hacia SIGE

- Estado: `COMPLETADA`.
- Dependencia: INT-0003.
- Aceptación TIC: el constructor outbound entrega solo ID, nombre snapshot y versión desde catálogos activos. La recepción y el rechazo de versiones regresivas se verifican en INT-0203 al existir el servicio autoritativo SIGE.

### INT-0106 — Pruebas y revisión del incremento M1

- Estado: `COMPLETADA`.
- Pruebas: migración repetible, concurrencia de versión, RBAC, CSRF, validación y regresión de programas/sedes.
- Cierre: revisión obligatoria por agente.

## M2 — Población autoritativa en SIGE

Estado previo: decisiones funcionales resueltas. Cuenta regresiva: 0 preguntas funcionales.

### INT-0201 — Crear migraciones de personas y vínculos

- Sistema: SIGE.
- Estado: `COMPLETADA` en código; migración pendiente de despliegue controlado.
- Dependencias: INT-0003, INT-0105.
- Alcance: persona, vínculos múltiples, extensión administrativa, snapshots de cargo/dependencia y auditoría.
- Aceptación: docente sin cargo/dependencia; administrativo exige ambos; una persona puede tener ambos vínculos.

### INT-0202 — Compatibilizar estudiantes, carnés y accesos con persona

- Estado: `COMPLETADA` en código; INT-0202A, B1–B3 y C1–C2 aprobadas. Migraciones 009–014 pendientes de despliegue controlado.
- Dependencia: INT-0201.
- Aceptación: IDs y aliases estudiantiles permanecen resolubles; no se modifica ningún maestro TIC desde SIGE.

### INT-0203 — Implementar servicio autoritativo de persona/vínculos

- Estado: `COMPLETADA` en código; migración 015 pendiente de despliegue controlado.
- Dependencia: INT-0201.
- Aceptación: comandos idempotentes, versionados y auditados; no hay actualizaciones perdidas.

### INT-0204 — Implementar almacenamiento autoritativo de foto

- Estado: `COMPLETADA` en código; migración 016 pendiente de despliegue controlado.
- Dependencia: INT-0203.
- Aceptación cumplida: carga multipart autenticada; MIME, tamaño, dimensiones, decodificación y SHA-256 reales; normalización JPEG 350×350 calidad 85 sin metadatos; asociación versionada; reemplazo auditado; evento durable; replay exacto y compensación de archivos.
- Decisión 2026-08-31: el comando identifica el JPEG/PNG de entrada y el evento identifica el JPEG autoritativo normalizado; auditoría conserva ambas identidades sin exponer rutas.
- Archivos SIGE: `app/Services/PersonalPhotoService.php`, `app/Controllers/PersonalPhotoController.php`, `public/index.php`, migración/rollback `20260831_016_personal_authoritative_photos.sql` y pruebas `personal_photo_*_test.php`.
- Pruebas: 16/16 suites SIGE exitosas, incluida base temporal, reaplicación de migración, normalización, replay, no-op, hash inválido, outbox y regresión de estudiantes/INT-0201–0203.
- Almacenamiento: `SIGE_PERSONAL_PHOTO_STORAGE_ROOT`, por defecto `C:\xampp\sige-storage\personas\fotos`, fuera de `DocumentRoot`; el servicio falla cerrado si se configura debajo de `htdocs`.
- Revisión independiente: el primer corte no fue aprobable por exposición web, auditoría incompleta, commit incierto, no-op sin integridad, DDL laxo y cobertura. Se remediaron almacenamiento externo con canonicalización real anti-traversal/junction, snapshot de entrada/salida, compensación solo tras rollback confirmado, verificación/reparación del archivo corriente, EXIF 2–8 con prueba asimétrica, DDL JPEG 350×350, códigos HTTP y pruebas adversariales. Dictamen final: `APROBABLE`, sin P0–P3 abiertos.
- Riesgos/pending: permisos y respaldo del directorio externo, limpieza periódica de huérfanos y prueba E2E Apache multipart antes de habilitar `SIGE_PERSONAL_INTEGRATION_ENABLED`. No se aplicó 016 a `sige_db`.

### INT-0205 — Implementar elegibilidad de personal

- Estado: `COMPLETADA` en código; prerrequisito de autoridad estudiantil RH/celular completado.
- Dependencias: INT-0203, INT-0204.
- Reglas: persona activa, vínculo habilitante, RH, celular y foto; administrativo con cargo/dependencia vigentes.
- Exclusiones: sin Q10, programa, sede, lote o fecha académica.
- Decisión funcional 2026-08-31: para identidades duales TIC envía `celular` y `rh` por el webhook estudiantil; SIGE los proyecta en `personas` bajo autoridad `TIC_ESTUDIANTE`. El canal personal continúa impedido para modificarlos.
- Compatibilidad: ausencia de las claves conserva valores existentes; `null` explícito desde TIC los limpia; `Q10_ESTADO` nunca modifica estos maestros. No se agregaron tablas ni columnas.
- Archivos TIC: `developer/Services/SigeWebhook.php`, `developer/tests/contract_characterization_test.php`.
- Archivos SIGE: `app/Services/StudentSyncService.php`, `app/Services/StudentPersonProjectionService.php`, `tests/student_person_projection_test.php`, `tests/person_identity_reconciliation_test.php`.
- Pruebas finales: normalización/neutralización TIC, alta/actualización/limpieza, payload legacy, Q10 aislado, identidad dual y reconciliación C1; regresión SIGE 16/16 y TIC 6/6. Revisión independiente: `APROBABLE`, sin P0–P3 abiertos.
- Migraciones: ninguna. Riesgo operativo: personas duales existentes recibirán los datos en la siguiente sincronización estudiantil; cualquier backfill masivo será una tarea controlada posterior y no se ejecutó.
- Implementación SIGE: `PersonalEligibilityService` evalúa sin mutar persona activa, celular, RH, integridad física de la foto y vínculos habilitantes. La regla agregada es OR: un vínculo docente válido puede habilitar una identidad aunque su vínculo administrativo esté incompleto u obsoleto.
- Administrativo: ID, snapshot y versión se contrastan con `integracion_catalogos_observados`; TIC continúa obligado a originar únicamente snapshots de catálogos activos. SIGE no administra estado maestro ni reutiliza sede.
- Concurrencia: el modo bloqueante exige transacción activa y aplica `FOR UPDATE` a persona, vínculos, foto y high-water de cargo/dependencia para que M5 pueda revalidar una decisión estable antes de emitir.
- Seguridad de foto: la raíz se canonicaliza y falla cerrada cuando está dentro del `DocumentRoot`; además se verifican ruta contenida, SHA-256, bytes, MIME JPEG y dimensiones 350×350.
- Archivos SIGE: `app/Services/PersonalEligibilityService.php`, `tests/personal_eligibility_test.php` y `tests/personal_eligibility_database_test.php`.
- Artefactos TIC: `artifacts/int0205_personal_eligibility.patch` y `artifacts/int0205_review_remediation.patch`.
- Migraciones: ninguna creada ni ejecutada por INT-0205.
- Primera revisión independiente: `NO APROBABLE`, con 1 P1 y 2 P2; se corrigieron estabilidad transaccional/high-water, fail-closed del storage y cobertura MariaDB.
- Dictamen final independiente: `APROBABLE`, sin hallazgos P0–P3.

### INT-0206 — Pruebas y revisión del incremento M2

- Estado: `COMPLETADA` en código.
- Pruebas: doble vínculo, versiones, snapshots, foto inválida, administrativo incompleto y regresión estudiantil.
- Cierre: revisión obligatoria por agente.
- Resultado: suite SIGE 18/18 `OK` y suite TIC 7/7 `OK`; lint de los nuevos archivos `OK`.
- Base productiva: consulta de `schema_migrations` confirmó que 009–016 no están aplicadas. No se mutó `sige_db`, no se habilitó `SIGE_PERSONAL_INTEGRATION_ENABLED` y no se ejecutó backfill masivo.
- Estado del incremento: M2 `COMPLETADO EN CÓDIGO`; despliegue controlado y preparación operativa continúan pendientes.

## M3 — Transporte durable y proyección técnica TIC

### INT-0301 — Crear migraciones técnicas de integración de personal

- Sistema: TIC.
- Estado: `PENDIENTE`.
- Dependencias: INT-0206.
- Tablas permitidas: outbox de comandos, inbox/eventos, proyección técnica, conflictos y auditoría.
- Prohibición: no incluir columnas que conviertan la proyección en maestro local de personal.

### INT-0302 — Implementar cliente y dispatcher TIC → SIGE

- Estado: `PENDIENTE`.
- Dependencia: INT-0301.
- Aceptación: leases, orden por persona, reintentos, límite de intentos, ACK correlacionado y errores terminales.

### INT-0303 — Implementar receptor SIGE → TIC

- Estado: `PENDIENTE`.
- Dependencia: INT-0301.
- Aceptación: autenticación inbound, límites de payload, idempotencia, versiones monotónicas y rechazo de duplicados conflictivos.

### INT-0304 — Implementar proyección e historial técnico

- Estado: `PENDIENTE`.
- Dependencia: INT-0303.
- Aceptación: TIC muestra estado sin convertirse en autoridad; eventos obsoletos no regresan la proyección.

### INT-0305 — Implementar reconciliación y salud

- Estado: `PENDIENTE`.
- Dependencias: INT-0302, INT-0304.
- Aceptación: lote paginado, conflictos auditados, lock global, backoff y healthcheck autenticado.

### INT-0306 — Pruebas y revisión del incremento M3

- Estado: `PENDIENTE`.
- Pruebas: replay, ACK incorrecto, lease vencido, caída SIGE, versión menor/mayor y concurrencia.
- Cierre: revisión obligatoria por agente.

## M4 — Asistente visual de personal en TIC

### INT-0401 — Crear ruta, sidebar y esqueleto del módulo

- Sistema: TIC.
- Estado: `PENDIENTE`.
- Dependencias: INT-0106, INT-0306.
- Aceptación: opción independiente visible solo para `CARNETIZACION`; acceso directo no autorizado bloqueado en servidor.

### INT-0402 — Paso 1: identidad y tipo de vínculo

- Estado: `PENDIENTE`.
- Aceptación: consulta SIGE antes de crear; detecta persona existente y permite vínculo adicional sin duplicar identidad.

### INT-0403 — Paso 2: datos y catálogos

- Estado: `PENDIENTE`.
- Aceptación: docente no solicita cargo/dependencia; administrativo los exige y envía snapshot/version.

### INT-0404 — Paso 3: captura y transferencia de foto

- Estado: `PENDIENTE`.
- Aceptación: previsualización, validación, carga autenticada, reintento y eliminación compensatoria; sin copia maestra TIC.

### INT-0405 — Paso 4: confirmación autoritativa de población

- Estado: `PENDIENTE`.
- Aceptación: el asistente no habilita RFID hasta recibir confirmación SIGE de persona/vínculos/foto.

### INT-0406 — Recuperación y reanudación del asistente

- Estado: `PENDIENTE`.
- Aceptación: recargar navegador o perder conectividad no duplica personas, fotos, vínculos ni comandos.

### INT-0407 — Pruebas UX, seguridad y revisión de M4

- Estado: `PENDIENTE`.
- Pruebas: navegación por teclado, errores por paso, sesión expirada, doble envío, XSS, CSRF y permisos.
- Cierre: revisión obligatoria por agente.

## M5 — Emisión, producción y entrega del carné

### INT-0501 — Implementar comando de primera emisión por persona

- Sistema: TIC/SIGE.
- Estado: `PENDIENTE`.
- Dependencias: INT-0205, INT-0407.
- Aceptación: `persona_uuid`, UID, `expected_persona_version`, usuario responsable y confirmación explícita.

### INT-0502 — Aplicar invariantes físicas en SIGE

- Estado: `PENDIENTE`.
- Aceptación: un solo carné actual; UID globalmente único/no vetado; `vigencia_hasta = NULL`; transacción atómica.

### INT-0503 — Implementar eventos de producción y proyección TIC

- Estado: `PENDIENTE`.
- Aceptación: estados equivalentes a solicitado/en proceso/listo/entregado provienen de SIGE o de eventos operativos acordados, no de una autoridad paralela en TIC.

### INT-0504 — Integrar plantilla y producción física

- Estado: `PENDIENTE`.
- Aceptación: datos renderizados desde la respuesta/proyección autorizada, soporte docente/administrativo y ninguna dependencia de programa o sede.
- Nota: el mecanismo concreto de impresión debe caracterizarse antes de modificar plantillas productivas.

### INT-0505 — Implementar confirmación de entrega

- Estado: `PENDIENTE`.
- Aceptación: evento idempotente, timestamp auditado y operación visible en ambos sistemas sin modificar maestros.

### INT-0506 — Pruebas y revisión del incremento M5

- Estado: `PENDIENTE`.
- Pruebas: emisión doble, UID repetido/vetado, versión obsoleta, plantilla, lote de impresión, entrega repetida y caída entre ACK/evento.
- Cierre: revisión obligatoria por agente.

## M6 — Ciclo de vida institucional

### INT-0601 — Definir evidencia de Talento Humano

- Estado: `PENDIENTE`.
- Entregable: identificador y metadatos mínimos requeridos para baja/reactivación.

### INT-0602 — Implementar baja y reactivación por vínculos

- Estado: `PENDIENTE`.
- Dependencia: INT-0601.
- Aceptación: cerrar un vínculo no desactiva el carné cuando otro vínculo habilitante sigue activo.

### INT-0603 — Implementar bloqueo y reemplazo

- Estado: `PENDIENTE`.
- Aceptación: pérdida, robo o daño requieren UID nuevo; el anterior queda vetado permanentemente.

### INT-0604 — Auditoría y propagación

- Estado: `PENDIENTE`.
- Aceptación: before/after, responsable, evidencia, versión y eventos SIGE → TIC.

### INT-0605 — Pruebas y revisión del incremento M6

- Estado: `PENDIENTE`.
- Pruebas: baja parcial/total, reactivación, doble vínculo, reemplazo, UID retirado y concurrencia.
- Cierre: revisión obligatoria por agente.

## M7 — Operación, despliegue y cierre

### INT-0701 — Panel operativo de integración

- Estado: `PENDIENTE`.
- Aceptación: pendientes, reintentos, fallidas, conflictos y conciliación visibles para `CARNETIZACION` sin exponer secretos.

### INT-0702 — Configurar workers y monitoreo

- Estado: `PENDIENTE`.
- Alcance: outboxes, eventos, reconciliación, salud, alertas y retención.

### INT-0703 — Preparar migración y backfill controlado

- Estado: `PENDIENTE`.
- Aceptación: dry-run, conteos, conflictos, idempotencia, respaldo y reversión.

### INT-0704 — Ejecutar pruebas integrales

- Estado: `PENDIENTE`.
- Escenarios: docente, administrativo, doble vínculo, errores de catálogo/foto/UID, caída de servicios y regresión completa estudiantil/Q10/accesos.

### INT-0705 — Revisión final de seguridad y código

- Estado: `PENDIENTE`.
- Aceptación: agente revisor sin hallazgos críticos/altos abiertos y evidencia de corrección de hallazgos aceptados.

### INT-0706 — Despliegue gradual y conciliación

- Estado: `PENDIENTE`.
- Aceptación: feature flag o habilitación controlada, métricas estables, conciliación sin conflictos no explicados y plan de reversión probado.

### INT-0707 — Cierre documental

- Estado: `PENDIENTE`.
- Entregables: decisiones finales, archivos, migraciones, pruebas, riesgos residuales, manual operativo y pendientes.

## Primer incremento recomendado

Comenzar por INT-0003 e INT-0004. No conviene crear pantallas o tablas de personal antes de fijar los contratos y proteger mediante pruebas el flujo estudiantil productivo.

## Bitácora

### 2026-08-30 — Plan inicial

- Decisiones: módulo separado, asistente por pasos y autorización exclusiva `CARNETIZACION`.
- Archivos modificados: `implementation_plan_tic_sige_personal.md` y referencia en `code_diagnostic_report.md`.
- Migraciones: ninguna creada ni ejecutada.
- Pruebas: ninguna ejecución funcional; planificación basada en revisión estática y documentos vigentes.
- Riesgos: acoplamiento a `inscripcion`, UID legacy, cargo ligado a sede, compatibilidad estudiantil y acceso por aliases.
- Pendiente inmediato: INT-0003 — contratos API versionados.

### 2026-08-30 — Implementación inicial INT-0003/INT-0004

- Decisiones:
  - Mantener endpoints de personal separados de los estudiantiles.
  - Usar `persona_uuid`, UUID v4, hash canónico y `expected_persona_version`.
  - Exigir actor remoto con rol exacto `CARNETIZACION`.
  - Separar comandos de persona, vínculo y carné.
  - Transferir foto por endpoint autenticado; no guardar binario/base64 en outbox.
  - Clasificar HTTP 409 como conflicto terminal sujeto a conciliación/corrección.
- Auditoría del código SIGE:
  - Implementados y reutilizables: idempotencia, inbox estudiantil, comandos versionados, outbox de eventos, worker con lease/backoff, ACK estricto, UID histórico único y publicación compensatoria de fotos.
  - Ausentes: `personas`, `persona_uuid`, vínculos, cargo/dependencia snapshot, foto por persona y política de carné de personal.
  - Incompatibilidades a resolver después: carné ligado a estudiante, vigencia no nula/seis meses, autorización `ADMIN/OPERATIVO`, acceso siempre académico e incidencias manuales sin identidad no soportadas.
- Archivos creados:
  - `developer/Contracts/personal/v1/README.md`.
  - `developer/Contracts/personal/v1/persona-command.schema.json`.
  - `developer/Contracts/personal/v1/vinculo-command.schema.json`.
  - `developer/Contracts/personal/v1/carnet-command.schema.json`.
  - `developer/Contracts/personal/v1/integration-ack.schema.json`.
  - `developer/Contracts/personal/v1/integration-event.schema.json`.
  - `developer/tests/contract_characterization_test.php`.
- Archivos funcionales modificados: ninguno.
- Migraciones creadas o ejecutadas: ninguna.
- Pruebas:
  - `php -l developer/tests/contract_characterization_test.php`: correcta.
  - `php developer/tests/contract_characterization_test.php`: `OK`.
  - `php C:\xampp\htdocs\Sige\tests\core_rules_test.php`: `OK`.
- Riesgos:
  - Los esquemas son candidatos hasta terminar la revisión independiente.
  - `database.sql` de SIGE está desactualizado frente a sus migraciones reales.
  - No existe un validador JSON Schema instalado; la prueba actual verifica parseo, cierres y reglas críticas de separación.
- Estado al primer envío: INT-0003 e INT-0004 pasaron a `EN_REVISION`; INT-0005 pendiente.

### 2026-08-30 — Primera revisión independiente y remediación M0

- Dictamen inicial: no aprobable todavía; 0 hallazgos P0, 6 P1 y 1 grupo P2.
- Decisiones y correcciones:
  - Se formalizó la fotografía como comando multipart idempotente con hash SHA-256, MIME y límite de 5 MB.
  - Se adoptó una única `persona_version` monotónica para identidad, vínculos, foto y carné; se separa de `estudiantes.version_estado`.
  - Se agregó `VINCULO_ACTUALIZAR` e identidad inmutable por `vinculo_uuid`.
  - Baja y reactivación institucional se definieron como comandos agregados atómicos con evidencia de Talento Humano; se retiró la baja institucional del bloqueo aislado de carné.
  - Los eventos quedaron cerrados y discriminados por tipo/entidad; el carné de personal conserva `vigencia_hasta = null`.
  - El ACK correlacionado solo admite `PROCESADO` o `RECHAZADO`; los errores anteriores a correlación tienen envelope separado. El replay exacto devuelve el mismo HTTP y ACK persistido.
  - El rol incluido en el payload es solo auditoría; la autorización real exige sesión/rol/CSRF en TIC y autenticación del canal en SIGE.
- Archivos creados durante la remediación:
  - `developer/Contracts/personal/v1/foto-command.schema.json`.
  - `developer/Contracts/personal/v1/estado-institucional-command.schema.json`.
  - `developer/Contracts/personal/v1/integration-problem.schema.json`.
  - Trece fixtures válidos e inválidos en `developer/Contracts/personal/v1/examples/`.
  - `developer/tests/validate_contract_examples.ps1`.
- Archivos modificados:
  - Los cinco esquemas iniciales de `developer/Contracts/personal/v1/`.
  - `developer/Contracts/personal/v1/README.md`.
  - `developer/tests/contract_characterization_test.php`.
  - `implementation_plan_tic_sige_personal.md`.
  - `code_diagnostic_report.md`.
- Migraciones: ninguna creada ni ejecutada.
- Pruebas ejecutadas después de la remediación:
  - `php -l developer/tests/contract_characterization_test.php`: correcta.
  - `php developer/tests/contract_characterization_test.php`: `OK`.
  - `developer/tests/validate_contract_examples.ps1`: `OK`; valida casos positivos y negativos con JSON Schema Draft 2020-12.
  - `php C:\xampp\htdocs\Sige\tests\core_rules_test.php`: `OK`.
- Cobertura de caracterización agregada: Q10 y booleanos, payload/ACK/evento legacy, orden del outbox y backoff, evento obsoleto, idempotencia de accesos, reconciliación y doble confirmación de `NO_ENCONTRADO`.
- Riesgos residuales:
  - Las pruebas con base de datos real y concurrencia se reservan para los incrementos que creen las tablas/servicios; M0 protege reglas puras y guardas productivas sin mutar datos.
  - Los contratos siguen siendo candidatos hasta obtener el segundo dictamen independiente.
- Estado: INT-0003 e INT-0004 vuelven a `EN_PROGRESO`; se solicita nueva revisión para INT-0005.

### 2026-08-30 — Segunda revisión independiente y remediación M0

- Dictamen: no aprobable todavía; 3 P1, 2 P2 y 1 P3. No hubo hallazgos críticos.
- Decisiones y correcciones:
  - Cada baja/reactivación institucional incrementa `persona_version` una vez y publica exactamente un evento agregado con persona, todos los vínculos afectados y el estado resultante del carné.
  - El comando institucional tiene alcance explícito `VINCULO` o `PERSONA`. El alcance persona opera atómicamente sobre todos los vínculos habilitantes; el alcance vínculo permite baja/reactivación parcial.
  - `persona.estado` queda `INACTIVA` solo cuando no restan vínculos habilitantes y vuelve a `ACTIVA` cuando una reactivación deja al menos uno.
  - Se agregó validación semántica obligatoria de fechas y correo, además de JSON Schema.
  - Las verificaciones de texto fuente se conservan solo como alarma complementaria. La evidencia principal ahora ejecuta comandos rechazados, idempotencia de acceso, doble ausencia Q10, reconciliación de versiones y descarte de evento obsoleto sobre repositorios aislados.
  - La extracción de `siguienteRepeticionNoEncontrado()` en el poller Q10 conserva exactamente la expresión previa y permite probarla sin red ni base productiva.
- Archivos creados:
  - `developer/tests/legacy_integration_behavior_test.php`.
  - Nueve fixtures adicionales de actualización de vínculo, reactivación total, foto inválida, rol inválido, evento académico prohibido, ACK rechazado, evento institucional agregado y errores semánticos.
- Archivos modificados:
  - `developer/Contracts/personal/v1/estado-institucional-command.schema.json`.
  - `developer/Contracts/personal/v1/integration-event.schema.json`.
  - `developer/Contracts/personal/v1/README.md`.
  - `developer/tests/contract_characterization_test.php`.
  - `developer/tests/validate_contract_examples.ps1`.
  - `developer/Services/Q10AcademicPoller.php` (extracción pura sin cambio de resultado).
  - Documentación de diagnóstico y plan.
- Migraciones: ninguna creada ni ejecutada.
- Pruebas ejecutadas:
  - `php -l` sobre las dos suites TIC y `Q10AcademicPoller.php`: correcto.
  - `php developer/tests/contract_characterization_test.php`: `OK`.
  - `php developer/tests/legacy_integration_behavior_test.php`: `OK`.
  - `developer/tests/validate_contract_examples.ps1`: `OK` sobre 20 casos estructurales; dos casos semánticos se validan en PHP.
  - `php C:\xampp\htdocs\Sige\tests\core_rules_test.php`: `OK`.
- Aislamiento de pruebas: SQLite en memoria y dobles locales; no se abrió ni mutó la base productiva TIC/SIGE.
- Riesgos residuales:
  - La instrumentación SQLite del receptor legacy sustituye únicamente la conexión para ejecutar la ruta real de evento obsoleto; debe mantenerse sincronizada si cambia la firma de la clase.
  - Concurrencia y replay contra tablas definitivas se probarán de nuevo cuando M2/M3 creen sus repositorios y migraciones.
- Estado: INT-0003 e INT-0004 permanecen `EN_PROGRESO` hasta el tercer dictamen de INT-0005.

### 2026-08-30 — Cierre aprobado del incremento M0

- Dictamen final del agente revisor: `APROBABLE`.
- Hallazgos abiertos: ninguno P0, P1, P2 o P3.
- Evidencia repetida por el revisor: lint de ambas suites y poller, 20 validaciones estructurales, dos validaciones semánticas, las dos suites TIC y `core_rules_test.php` de SIGE, todas correctas.
- Archivos modificados por el revisor: ninguno.
- Migraciones: ninguna creada ni ejecutada.
- Estado final: INT-0003, INT-0004 e INT-0005 `COMPLETADA`; M0 cerrado. Siguiente tarea: INT-0101.

### 2026-08-30 — Implementación INT-0101

- Decisiones:
  - Catálogos separados `sige_personal_cargo_catalogo` y `sige_personal_dependencia_catalogo`, autoritativos en TIC.
  - Nombre visible y nombre normalizado con unicidad, versión monotónica desde 1 y estado lógico activo/inactivo.
  - Auditoría polimórfica propia con UUID de operación, before/after, versión y actor; sin FK ambigua a dos tablas.
  - No existen columnas ni relaciones con sede, programa, inscripción, docente, administrativo o persona.
- Archivos creados:
  - `developer/migrations/20260830_tic_sige_personal_catalogos.sql`.
  - `developer/tests/catalog_migration_contract_test.php`.
- Archivos modificados: `implementation_plan_tic_sige_personal.md`.
- Migraciones ejecutadas: ninguna; TIC está en producción y la ejecución queda para despliegue controlado.
- Pruebas:
  - `php -l developer/tests/catalog_migration_contract_test.php`: correcta.
  - `php developer/tests/catalog_migration_contract_test.php`: `OK`.
  - Búsqueda de términos de acoplamiento: solo aparecen en el comentario explícito de exclusión y en el prefijo funcional `sige_personal`; no hay columnas ni relaciones prohibidas.
- Riesgos:
  - La migración presupone MySQL con soporte de columnas JSON, ya utilizado por las migraciones vigentes de integración.
  - La unicidad incluye registros inactivos: un nombre se reactiva, no se recrea con otra identidad.
- Estado: INT-0101 `EN_REVISION`; requiere agente revisor antes de completarse.

### 2026-08-30 — Cierre aprobado INT-0101

- Dictamen del agente revisor: `APROBABLE`, sin hallazgos P0–P3.
- Compatibilidad verificada por el revisor con MariaDB local 10.4.32.
- Migración ejecutada: no.
- Archivos modificados por el revisor: ninguno.
- Estado: INT-0101 `COMPLETADA`; INT-0102 inicia `EN_PROGRESO`.

### 2026-08-30 — Implementación INT-0102

- Decisiones:
  - Un único `PersonalCatalogService` aplica las mismas reglas a CARGO y DEPENDENCIA mediante una lista cerrada de tablas; no acepta nombres de tabla desde la petición.
  - Normalización determinista: trim, colapso de espacios y mayúsculas UTF-8.
  - Toda mutación efectiva usa transacción, bloqueo de fila, `expected_version`, incremento unitario, timestamps y auditoría before/after.
  - Una operación sin cambios conserva versión y no genera auditoría falsa.
  - No existe eliminación física; la inactivación/reactivación es un cambio versionado.
  - El servicio exige actor con rol literal exacto `CARNETIZACION` como defensa adicional; la autorización contra sesión/base se implementa en INT-0103.
- Archivos creados:
  - `developer/Services/PersonalCatalogService.php`.
  - `developer/tests/personal_catalog_service_test.php`.
- Archivos modificados: plan de implementación.
- Migraciones creadas en esta tarea: ninguna. Migraciones ejecutadas: ninguna.
- Pruebas:
  - Lint de servicio y prueba: correcto.
  - `php developer/tests/personal_catalog_service_test.php`: `OK`.
  - `php developer/tests/catalog_migration_contract_test.php`: `OK`.
  - Casos ejecutados en SQLite en memoria: normalización/unicidad, creación, edición, no-op, versión obsoleta, inactivación, listas activas/inactivas, auditoría, rol incorrecto y rechazo del catálogo SEDE.
- Riesgos:
  - La prueba sustituye `FOR UPDATE` únicamente porque SQLite no lo soporta; MariaDB sí lo soporta y fue verificado en INT-0101.
  - INT-0103 debe validar el rol activo real en base y CSRF; el actor enviado al servicio no reemplaza esa autorización.
- Estado: INT-0102 `EN_REVISION`; requiere dictamen independiente.

### 2026-08-30 — Primera revisión y remediación INT-0102

- Dictamen inicial: no aprobable; 0 P0/P1 y 2 P2.
- Correcciones:
  - Las colisiones de `nombre_normalizado` y `id_operacion` se clasifican por la restricción concreta; la segunda produce `PersonalCatalogOperationConflictException` y nunca el mensaje de nombre duplicado.
  - Se agregó reactivación de versión 3 a 4 con auditoría `REACTIVAR`.
  - Se fuerza un fallo de auditoría posterior al UPDATE mediante UUID repetido y se verifica rollback de nombre, estado, versión, timestamp y ausencia de auditoría parcial.
- Archivos modificados: servicio, su prueba y esta bitácora.
- Migraciones ejecutadas: ninguna.
- Pruebas posteriores: lint correcto; `personal_catalog_service_test: OK`; `catalog_migration_contract_test: OK`.
- Estado: INT-0102 continúa `EN_REVISION` hasta el segundo dictamen.

### 2026-08-30 — Segunda remediación de evidencia INT-0102

- Hallazgo: el rollback usaba el mismo nombre y un timestamp con precisión de segundos, por lo que esas dos aserciones podían pasar sin demostrar reversión.
- Corrección: el intento fallido cambia a un nombre diferente y parte de un timestamp centinela `2000-01-01 00:00:00`; después del fallo de auditoría ambos deben permanecer intactos.
- Datos productivos: ninguno; el centinela existe solo en SQLite en memoria.
- Estado: INT-0102 sigue `EN_REVISION`.

### 2026-08-30 — Cierre aprobado INT-0102

- Dictamen final: `APROBABLE`, sin P0–P3 abiertos.
- Evidencia: rollback completo, clasificación de restricciones, reactivación y suites correctas.
- Migraciones ejecutadas: ninguna. Archivos modificados por el revisor: ninguno.
- Estado: INT-0102 `COMPLETADA`; INT-0103 inicia `EN_PROGRESO`.

### 2026-08-30 — Implementación INT-0103

- Decisiones:
  - La autorización consulta en cada petición la relación real `usuario → usuperfil → roles`; exige usuario y rol activos y correlación exacta con IDs de sesión.
  - Solo `UPPER(TRIM(nombre_rol)) = CARNETIZACION`; `PROGRAMADOR`, `OPERATIVO`, rol inactivo, usuario inactivo y sesión alterada son rechazados.
  - `IN_nombre_rol` no se usa como autoridad.
  - Las mutaciones requieren token CSRF aleatorio de 256 bits y comparación `hash_equals`; las lecturas autenticadas no lo requieren.
- Archivos creados:
  - `developer/Services/PersonalCatalogAuthorization.php`.
  - `developer/tests/personal_catalog_authorization_test.php`.
- Migraciones: ninguna creada o ejecutada.
- Pruebas: lint correcto; `personal_catalog_authorization_test: OK`; regresión `personal_catalog_service_test: OK`, todo en SQLite en memoria.
- Riesgo/pending: INT-0104 debe integrar obligatoriamente este guard en la vista y en cada caso del controlador; crear el guard no autoriza por sí solo ningún endpoint legacy.
- Estado: INT-0103 `EN_REVISION`.

### 2026-08-30 — Primera revisión y remediación INT-0103

- Dictamen inicial: no aprobable; 1 P1 y 1 P2.
- Correcciones:
  - Se exige también `usuperfil.estado_perfil = 'on'` y se añadió el caso de perfil inactivo.
  - Solo se conserva un CSRF que ya sea exactamente 64 caracteres hexadecimales; cualquier string débil o malformado se regenera con 32 bytes aleatorios.
- Archivos modificados: autorización, prueba y bitácora.
- Migraciones/datos productivos: ninguno.
- Estado: INT-0103 continúa `EN_REVISION`.

### 2026-08-30 — Cierre aprobado INT-0103

- Dictamen final: `APROBABLE`, sin P0–P3 abiertos.
- Evidencia: lint, suite de autorización y regresión del servicio correctas.
- Migraciones y datos productivos: ninguno ejecutado/modificado.
- Archivos modificados por el revisor: ninguno.
- Estado: INT-0103 `COMPLETADA`.
- Pendiente inmediato: INT-0104, integrar guard y CSRF en controlador/vista de catálogos; todavía no existen rutas públicas nuevas.

### 2026-08-30 — Regresión del corte M0 + M1 parcial

- Lint: `Q10AcademicPoller`, `PersonalCatalogService` y `PersonalCatalogAuthorization`, correcto.
- Suites TIC: contratos, comportamiento legacy, 20 ejemplos JSON Schema, migración de catálogos, servicio de catálogos y autorización, todas `OK`.
- Suite SIGE: `core_rules_test.php`, `OK`.
- Base de datos/red: no utilizadas por las pruebas; no hubo mutaciones productivas.
- Estado del corte: M0 completo; M1 INT-0101/0102/0103 completos; INT-0104/0105/0106 pendientes.

### 2026-08-30 — Implementación INT-0104

- Decisiones:
  - API desacoplada de globals mediante `PersonalCatalogApi`; el controlador HTTP es un adaptador mínimo.
  - Cada listado vuelve a validar sesión/usuario/perfil/rol en base; cada mutación además exige CSRF.
  - Mapeo explícito: 401 sesión, 403 rol/CSRF, 404 operación, 405 método, 409 duplicado/versión/operación y 422 validación.
  - La interfaz crea nodos con `.text()` para los nombres; no inserta HTML proveniente de catálogos.
  - No existe eliminación física; la misma edición activa/inactiva con versión esperada.
  - Se añadió ruta cerrada solo para `Listar|Crear|Actualizar`, pero controlador y vista permanecen detrás de `TIC_SIGE_PERSONAL_CATALOGS_ENABLED=1`. El valor por defecto es deshabilitado.
  - No se agregó todavía el ítem de sidebar; corresponde a INT-0401 y evita habilitación prematura.
- Archivos creados:
  - `developer/Services/PersonalCatalogApi.php`.
  - `developer/Controller/personalCatalogController.php`.
  - `developer/Config/personal_catalog_config.php`.
  - `gestionarcatalogospersonal.php`.
  - `javascripts/gestionarcatalogospersonal.js`.
  - `developer/tests/personal_catalog_api_test.php`.
  - `developer/tests/personal_catalog_ui_contract_test.php`.
- Archivo modificado: `.htaccess` y documentación.
- Migraciones ejecutadas: ninguna; feature flag apagado.
- Pruebas:
  - Lint PHP de API, controlador, vista, configuración y pruebas: correcto.
  - `node --check javascripts/gestionarcatalogospersonal.js`: correcto.
  - `personal_catalog_api_test: OK`: creación/listado/actualización, CSRF, RBAC, métodos, ausencia de delete, SEDE inválida y versión obsoleta.
  - `personal_catalog_ui_contract_test: OK`: guard, CSRF, salida segura, ruta cerrada, feature flag y ausencia de `id_sede`.
  - Regresiones de autorización y servicio: `OK`.
- Riesgos/pending:
  - No se realizó prueba visual en navegador porque la migración no está aplicada y el feature flag está apagado.
  - El despliegue debe ejecutar primero la migración, validar datos y solo entonces habilitar el flag.
- Estado: INT-0104 `EN_REVISION`.

### 2026-08-30 — Cierre aprobado INT-0104

- Dictamen: `APROBABLE`, sin P0–P3.
- Feature flag permanece apagado, sin sidebar y sin migración ejecutada.
- Estado: INT-0104 `COMPLETADA`; INT-0105 inicia `EN_PROGRESO`.

### 2026-08-30 — Implementación INT-0105 (lado TIC)

- Decisión: no existe sincronización independiente de tablas maestras. El snapshot se compone bajo demanda para el comando de vínculo administrativo.
- Salida cerrada: únicamente `id` entero, `nombre_snapshot` y `version` entera.
- Solo se construye desde un cargo/dependencia activo; un ID inexistente o inactivo se rechaza.
- Archivo modificado: `developer/Services/PersonalCatalogService.php` y su prueba.
- Migraciones/datos: ninguno ejecutado/modificado.
- Pruebas: lint correcto; servicio, API y autorización `OK`; snapshots exactos de cargo/dependencia y rechazo de dependencia inactiva.
- Riesgo/pending: la prueba de recepción SIGE y rechazo de versión regresiva depende de INT-0203; no se simula una recepción inexistente.
- Estado: INT-0105 `EN_REVISION` para su alcance TIC.

### 2026-08-30 — Cierre aprobado INT-0105 e inicio INT-0106

- Dictamen INT-0105: `APROBABLE`, sin P0–P3.
- Recepción/versiones regresivas SIGE: diferidas explícitamente a INT-0203.
- INT-0105 `COMPLETADA`; INT-0106 `EN_REVISION` para regresión y cierre integral M1.

### 2026-08-30 — Regresión integral INT-0106

- Lint correcto de los siete PHP productivos modificados/creados en M0/M1.
- Siete suites PHP TIC: todas `OK`.
- Veinte casos JSON Schema y dos semánticos: `OK` con PowerShell 7/PHP.
- JavaScript de catálogos: `node --check`, correcto.
- Regresión SIGE `core_rules_test.php`: `OK`.
- Pruebas de base: solo SQLite en memoria; no se conectó ni mutó MariaDB productiva.
- Migración: creada y revisada, todavía no ejecutada.
- Feature flag: apagado por defecto. Sidebar: sin cambios.
- Estado: INT-0106 espera revisión integral independiente de M1.

### 2026-08-30 — Remediación documental de revisión INT-0106

- Hallazgo: 1 P3; la cabecera conservaba el estado inicial “Código funcional modificado: no”.
- Corrección: la cabecera ahora registra código funcional modificado y mantiene explícitos los controles de despliegue: migración no ejecutada, flag apagado y sidebar pendiente.
- Hallazgos técnicos P0–P2: ninguno.
- Estado: INT-0106 continúa `EN_REVISION` hasta confirmación final.

### 2026-08-30 — Cierre aprobado INT-0106 / M1

- Dictamen final integral: `APROBABLE`, sin hallazgos P0–P3.
- INT-0101 a INT-0106: `COMPLETADA` para el alcance implementable en TIC; la recepción SIGE de snapshots queda en INT-0203.
- Migración ejecutada: no.
- Feature flag: apagado.
- Sidebar: sin cambios.
- M1 cerrado. Siguiente incremento: M2, población autoritativa en `C:\xampp\htdocs\Sige`.

### 2026-08-30 — Preflight M2 / INT-0201

- Código SIGE modificado: ninguno.
- Estado del proyecto: no contiene repositorio Git local; se preservarán todos los archivos existentes mediante cambios aditivos.
- Compatibilidad confirmada: MariaDB 10.4; ocho migraciones existentes; estudiantes, carnés, accesos, aliases e inbox/outbox siguen acoplados a `estudiante_id`.
- Estrategia segura: expansión aditiva, backfill, validación, dual-read/dual-write y endurecimiento posterior; no convertir todas las FK en una sola migración.
- Invariantes: conservar IDs estudiantiles, aliases, UUID/hashes, outbox, carnés, accesos y JSON históricos; no usar cascadas destructivas ni regenerar UUID en reintentos.
- Contradicción/ambigüedad detectada: una persona dual ESTUDIANTE + DOCENTE/ADMINISTRATIVO tendría una sola identidad común, pero TIC es autoridad de identidad estudiantil y SIGE de identidad de personal. Falta la regla de precedencia/máscara para campos comunes.
- Migraciones creadas o ejecutadas: ninguna.
- Pruebas: revisión estática de `database.sql`, migraciones 001–008, router y servicios; sin conexión ni mutación de base.
- Preguntas funcionales estimadas: 3; se formularán una por una.
- Cuenta regresiva actual: 3 pendientes.

### 2026-08-30 — Decisión funcional M2-01: identidad dual

- Decisión aprobada: si una persona es simultáneamente estudiante y docente/administrativa, TIC conserva la autoridad sobre sus datos maestros estudiantiles.
- Máscara obligatoria: los comandos de personal no pueden modificar documento, nombres, apellidos ni cualquier otro dato maestro perteneciente al flujo estudiantil TIC.
- Autoridad SIGE para la identidad dual: vínculos institucionales, fotografía autoritativa, carné físico y accesos.
- Impacto técnico: `personas` debe poder identificar la presencia de la extensión `estudiantes`; los servicios rechazarán mutaciones de identidad provenientes del canal de personal cuando exista esa extensión.
- Código/migraciones: ninguno modificado o ejecutado por esta decisión.
- Cuenta regresiva: 2 preguntas pendientes.

### 2026-08-30 — Decisión funcional M2-02: cardinalidad de vínculos

- Decisión aprobada: máximo una fila corriente `DOCENTE` y una `ADMINISTRATIVO` por persona.
- La restricción será única por `(persona_id, tipo_vinculo)`.
- Altas, cambios, bajas y reactivaciones incrementan la versión de la misma fila.
- El historial before/after se conserva en auditoría; no se crean filas históricas duplicadas en la tabla corriente.
- Una persona puede mantener simultáneamente ambos tipos y el carné se evalúa sobre los dos estados.
- Código/migraciones: ninguno modificado o ejecutado por esta decisión.
- Cuenta regresiva: 1 pregunta pendiente.

### 2026-08-30 — Decisión funcional M2-03: formatos técnicos

- Decisión aprobada: tipo de documento 20, número 50, nombres/apellidos 150, IDs externos de catálogo BIGINT positivo, snapshots 190 y versiones BIGINT positivas.
- Los tamaños coinciden con contratos M0 y tablas TIC M1.
- Código/migraciones ejecutadas: ninguno al registrar la decisión.
- Cuenta regresiva: 0 preguntas pendientes.
- Estado: se desbloquea INT-0201 y pasa a `EN_PROGRESO`.

### 2026-08-30 — Implementación INT-0201: expansión poblacional SIGE

- Decisiones aplicadas:
  - Migración estrictamente aditiva: `personas`, `personas_auditoria`, `persona_vinculos` y `persona_vinculos_auditoria`.
  - El backfill conserva `personas.id = estudiantes.id`, genera un UUID v4 una sola vez y marca la autoridad `TIC_ESTUDIANTE`.
  - `estudiantes.persona_id` queda único y con `ON DELETE RESTRICT`, pero nullable durante compatibilidad; las filas preexistentes quedan enlazadas 1:1 y las altas legacy podrán quedar pendientes hasta el dual-write de INT-0202.
  - Una persona puede tener simultáneamente un vínculo `DOCENTE` y uno `ADMINISTRATIVO`; existe como máximo una fila corriente por tipo y el historial vive en auditoría.
  - Docente no recibe catálogos; administrativo exige ID, snapshot y versión positivos/completos de cargo y dependencia TIC.
  - INT-0201 no altera todavía carnés, accesos, aliases, inbox ni outbox; esa compatibilidad corresponde a INT-0202.
- Archivos SIGE creados:
  - `migrations/20260830_009_unified_population.sql`.
  - `tests/population_migration_contract_test.php`.
  - `tests/population_migration_database_test.php`.
- Artefactos de aplicación conservados en TIC:
  - `artifacts/int0201_sige_population.patch`.
  - `artifacts/int0201_sige_uuid_compat.patch`.
  - `artifacts/int0201_sige_idempotent_noop.patch`.
  - `artifacts/int0201_sige_database_test.patch`.
- Compatibilidad corregida durante pruebas:
  - MariaDB 10.4.32 no implementa `RANDOM_BYTES()`; se sustituyó por una composición v4 con `UUID()`, `RAND()` y `MD5`, sin derivarla del documento.
  - La reaplicación usaba `SELECT 1` como no-op del FK dinámico y dejaba un result set abierto en PDO; se cambió a `DO 1`.
- Migraciones ejecutadas sobre `sige_db`: ninguna.
- Pruebas ejecutadas:
  - Contrato estático INT-0201: `OK`.
  - Migración real aplicada dos veces en un esquema MariaDB temporal aleatorio: `OK`.
  - Backfill, UUID v4, relación 1:1, auditoría idempotente, preservación de aliases, coexistencia de vínculos, unicidad y rechazo de snapshot incompleto: `OK`.
  - Regresiones SIGE `core_rules_test.php` y `admin_users_test.php`: `OK`.
  - El esquema temporal de la prueba exitosa fue eliminado automáticamente. El esquema dejado por la primera prueba fallida se identificó por nombre exacto, se validó contra el prefijo seguro y se eliminó; ningún otro esquema fue afectado.
- Riesgos:
  - La migración aún requiere ejecutar el preflight documentado contra el destino antes del despliegue real.
  - El UUID usa entropía del motor disponible en MariaDB 10.4 y no es un identificador criptográfico; su objetivo es unicidad opaca, reforzada por índice único.
  - El código productivo continúa operando por `estudiante_id`; habilitar personal antes de INT-0202/0203 sería prematuro.
- Pendiente: revisión independiente de código INT-0201. Estado: `EN_REVISION`.

### 2026-08-30 — Remediación de revisión INT-0201

- Primer dictamen independiente: `NO APROBABLE`, con 1 P1, 2 P2 y 1 P3.
- P1 corregido: se retiró el `NOT NULL` prematuro de `estudiantes.persona_id`; el escritor productivo actual puede seguir creando estudiantes hasta desplegar dual-write. El endurecimiento se mueve a una migración posterior a INT-0202 y reconciliación con cero nulos.
- P2 corregido: la prueba MariaDB ahora inserta un estudiante con la forma legacy después de aplicar INT-0201 y comprueba que la operación sigue siendo válida.
- P2 corregido: `vinculo_uuid` ahora tiene `CHECK` UUID v4 y una prueba negativa ejecutable.
- P3 corregido: la prueba elimina la base temporal solo si esta ejecución confirmó su creación.
- Cobertura ampliada: sentinelas de `carnets`, `accesos`, `integracion_estudiantes_inbox` e `integracion_outbox` permanecen sin cambios tras dos aplicaciones.
- Archivo adicional: `artifacts/int0201_sige_review_remediation.patch`.
- Regresión posterior: lint de ambas pruebas, contrato, migración MariaDB x2, alta legacy, sentinelas, UUID inválido, `core_rules_test.php` y `admin_users_test.php`, todo `OK`.
- Limpieza verificada: cero bases `sige_int0201_test_%` remanentes.
- Migraciones ejecutadas sobre `sige_db`: ninguna.
- Estado: INT-0201 continúa `EN_REVISION` y requiere segundo dictamen independiente.

### 2026-08-30 — Cierre aprobado INT-0201

- Segundo dictamen independiente: `APROBABLE`, sin hallazgos P0–P3.
- Confirmado por revisión y ejecución: compatibilidad con altas legacy, `persona_id` nullable, sentinelas preservados, UUID v4 de persona/vínculo, reaplicación idempotente y cleanup restringido.
- Código de SIGE modificado: únicamente la migración 009 y sus dos pruebas.
- Base `sige_db`: no modificada. Migración 009: pendiente de despliegue controlado.
- INT-0201: `COMPLETADA` en código.
- Siguiente tarea: INT-0202, expansión compatible de carnés, accesos e integración con `persona_id`, backfill y dual-write; el endurecimiento `NOT NULL` seguirá diferido hasta reconciliación.

### 2026-08-30 — Implementación INT-0202A: expansión compatible

- Alcance cerrado: solo expansión nullable y backfill; no incluye dual-write productivo, recepción de personal ni habilitación de interfaz.
- Decisiones:
  - `carnets.persona_id`, `accesos.persona_id` e `integracion_outbox.persona_id` se agregan nullable para no romper escritores legacy.
  - Carnés y outbox se rellenan desde `estudiantes.persona_id`; accesos primero desde estudiante y luego desde carné. Los accesos desconocidos permanecen nulos.
  - Se agregan índices y FK `ON DELETE RESTRICT`; el carné activo queda único por persona mediante columna generada.
  - `vigencia_hasta` pasa a nullable para preparar personal sin alterar los valores estudiantiles existentes.
  - Inbox, payloads, IDs, UID, estados, versiones, locks y ACK históricos no se reescriben.
- Archivos SIGE creados:
  - `migrations/20260830_010_persona_compatibility_expand.sql`.
  - `tests/persona_compatibility_migration_test.php`.
- Artefactos TIC:
  - `artifacts/int0202a_sige_compatibility.patch`.
  - `artifacts/int0202a_static_test_fix.patch`.
- Migraciones ejecutadas sobre `sige_db`: ninguna.
- Pruebas:
  - Lint PHP: correcto.
  - Migraciones 009 + 010 y reaplicación 010 sobre MariaDB temporal: `OK`.
  - Backfill de carnés, accesos directos/por carné y outbox: `OK`; acceso desconocido conservado nulo.
  - Escrituras legacy post-migración sin `persona_id` para carné, acceso y outbox: `OK`.
  - Unicidad de carné activo por persona, inbox preservado y vigencia nullable: `OK`.
  - Regresiones INT-0201, reglas core y usuarios admin: `OK`.
  - Bases temporales remanentes: 0.
- Incidencia de prueba: la primera aserción estática confundió `ON DELETE RESTRICT` con `DELETE FROM`; se corrigió antes de crear el esquema temporal.
- Riesgos/pending:
  - Las altas posteriores a la migración quedan con `persona_id = NULL` hasta INT-0202B; por eso no puede habilitarse personal.
  - Antes de despliegue se requieren preflight de huérfanos y duplicados activos sobre la base destino.
  - `integracion_comandos` permanece legacy; su expansión pertenece a recepción de comandos de personal.
- Estado: INT-0202A `EN_REVISION`.

### 2026-08-30 — Remediación de revisión INT-0202A

- Primer dictamen: `NO APROBABLE`, sin P0/P1 y con 2 P2.
- P2 cerrado: se eliminó la unicidad `(persona_id, version_estado)` porque mezcla el stream académico/estudiantil con el futuro `persona_version`. INT-0202A conserva únicamente un índice no único por persona; el stream personal tendrá discriminador y versión propios en una fase posterior.
- P2 cerrado: el backfill de accesos ahora exige coincidencia cuando estudiante y carné aportan persona. Una contradicción queda con `persona_id = NULL` para reconciliación y no elige silenciosamente una fuente.
- Cobertura añadida: acceso conflictivo explícito y prohibición contractual del índice de versión incorrecto.
- Artefactos: `int0202a_sige_review_remediation_v2.patch` y `int0202a_conflict_assertion_fix.patch`. El primer borrador de remediación no fue aplicado por incompatibilidad de contexto y se conserva solo como evidencia.
- Pruebas posteriores: migración 010 x2, conflicto sin asignación, regresiones 009/core/admin, todo `OK`.
- `sige_db`: sin cambios. Estado: segundo dictamen independiente pendiente.

### 2026-08-30 — Cierre aprobado INT-0202A

- Segundo dictamen independiente: `APROBABLE`, sin hallazgos P0–P3.
- Confirmados: compatibilidad de escritores legacy, backfill idempotente, acceso conflictivo sin resolución implícita, stream de versiones no mezclado y preservación histórica.
- Pruebas finales: lint, 009 + 010, reaplicación 010, regresiones INT-0201/core/admin, todas `OK`; cero bases temporales remanentes.
- Migración 010 creada pero no ejecutada sobre `sige_db`.
- INT-0202A `COMPLETADA`. Próximo incremento aislado: INT-0202B, dual-write productivo y reconciliación; no iniciado.

### 2026-08-30 — Preflight INT-0202B y división segura

- Código productivo modificado en este preflight: ninguno.
- Escritores reales identificados:
  - `StudentSyncService`: alta/actualización estudiantil y evento de reactivación.
  - `CardCommandService`: emisión/reemplazo de carné y outbox transaccional.
  - `AccesoController`: acceso automático por RFID.
  - `ManualAccessService`: denegación sin lectura y autorización manual.
- Riesgo confirmado: una alta recibida después de 009/010 crea `estudiantes.persona_id = NULL`; copiar únicamente el ID en carnés/accesos/outbox no constituye dual-write correcto.
- División aprobable del trabajo técnico:
  1. `INT-0202B1`: crear/proyectar persona TIC y enlazarla atómicamente desde `StudentSyncService`, con auditoría y protección de autoridad estudiantil.
  2. `INT-0202B2`: dual-write de carné y outbox dentro de `CardCommandService` y del outbox de reactivación de `StudentSyncService`.
  3. `INT-0202B3`: dual-write de accesos automáticos y manuales, preservando accesos desconocidos con persona nula.
- Cada subincremento exige prueba MariaDB aislada, regresión legacy, documentación y revisión independiente antes del siguiente.
- `INT-0202C` permanece reservado para reconciliar la ventana de despliegue y endurecer restricciones; no se mezclará con B1–B3.
- Estado: preflight `COMPLETADO`; INT-0202B1 es la próxima implementación y no fue iniciada.

### 2026-08-30 — Implementación INT-0202B1: proyección estudiante-persona

- Alcance: únicamente el límite transaccional del webhook TIC; carnés, outbox y accesos quedan para B2/B3.
- Decisiones:
  - Toda alta estudiantil crea o resuelve una persona y enlaza `estudiantes.persona_id` dentro de la transacción activa del webhook.
  - Si el documento ya corresponde a una persona con vínculo docente/administrativo, se reutiliza la misma identidad; TIC pasa a gobernar los campos maestros estudiantiles sin alterar vínculos.
  - Las actualizaciones canónicas TIC modifican tipo/número de documento y nombres, incrementan `persona_version` y registran before/after.
  - Eventos `Q10_ESTADO` aseguran el enlace, pero no mutan identidad ni incrementan `persona_version`.
  - Foto, vínculos, carnés y accesos no son modificados por este servicio.
  - La proyección rechaza ejecución fuera de una transacción activa.
- Archivos SIGE:
  - Creado `app/Services/StudentPersonProjectionService.php`.
  - Modificado `app/Services/StudentSyncService.php` para invocar la proyección después del alta/actualización estudiantil.
  - Creado `tests/student_person_projection_test.php`.
- Artefactos TIC: `int0202b1_sige_student_person_projection.patch`, `int0202b1_transaction_guard.patch` e `int0202b1_transaction_guard_order.patch`.
- Migraciones: ninguna creada ni ejecutada; B1 requiere 009 previamente desplegada.
- Pruebas MariaDB temporales:
  - Alta nueva y enlace persona: `OK`.
  - Actualización TIC, versión y auditoría: `OK`.
  - Q10 sin mutación de identidad: `OK`.
  - Identidad dual existente reutilizada, autoridad TIC aplicada y vínculo docente preservado: `OK`.
  - Rechazo fuera de transacción: `OK`.
- Regresiones: INT-0201, INT-0202A, reglas core y usuarios admin, todas `OK`; carga de clases correcta; cero bases temporales remanentes.
- Riesgos/pending:
  - B1 no rellena todavía `persona_id` en carnés/outbox/accesos escritos después de 010.
  - Colisiones documentales con dos personas distintas se rechazan y requerirán reconciliación explícita; no se fusionan automáticamente.
- `sige_db`: sin cambios. Estado: INT-0202B1 `EN_REVISION`.

### 2026-08-30 — Remediación de revisión INT-0202B1

- Primer dictamen: `NO APROBABLE`, 0 P0/P1 y 1 P2.
- P2 corregido: `StudentSyncService::sync()` exige transacción activa al inicio, antes de resolver identidad, insertar estudiante o crear aliases. El guard interno de la proyección se conserva como defensa adicional.
- Cobertura añadida:
  - llamada al entrypoint real fuera de transacción rechazada;
  - conteos de estudiantes, aliases, personas y auditoría invariantes tras el rechazo;
  - colisión documental dentro de transacción con rollback y sin auditoría parcial.
- Incidencia mecánica: el primer parche de prueba omitió cinco líneas finales por conteo de hunk y produjo error de sintaxis; se completó antes de ejecutar casos funcionales. No afectó código productivo ni datos.
- Artefactos: `int0202b1_review_remediation.patch` y `int0202b1_collision_test_completion.patch`.
- Pruebas posteriores: lint de tres archivos, proyección B1, migraciones 009/010, reglas core y usuarios admin, todo `OK`; cero bases temporales.
- `sige_db`: sin cambios. Estado: segundo dictamen pendiente.

### 2026-08-30 — Cierre aprobado INT-0202B1

- Segundo dictamen independiente: `APROBABLE`, sin hallazgos P0–P3.
- Confirmados: guard temprano y defensivo, atomicidad, rollback de colisión, autoridad TIC, aislamiento Q10, preservación de vínculos y ausencia de cambios en carnés/accesos.
- INT-0202B1 `COMPLETADA` en código; no requiere migración adicional y depende del despliegue previo de 009.
- Próximo incremento aislado: INT-0202B2, dual-write de carné y outbox estudiantil; no iniciado.

### 2026-08-30 — Implementación INT-0202B2: dual-write de carné y outbox

- Alcance: escritores estudiantiles de carné/outbox; accesos permanecen pendientes en B3.
- Decisiones:
  - `CardCommandService` asegura la persona mediante el servicio B1 dentro de su transacción y enlaza carnés legacy nulos antes de operar.
  - Primera emisión y reemplazo escriben simultáneamente `estudiante_id` y `persona_id` en el carné.
  - Todo evento de comando escribe ambos IDs en `integracion_outbox` sin alterar el payload legacy ni reutilizar `persona_version`.
  - `REACTIVACION_REQUERIDA` producido por `StudentSyncService` enlaza el carné y escribe la misma persona en outbox.
  - Una falla de outbox revierte inactivación del carné anterior, inserción del reemplazo, incremento de versión y registro del comando.
- Archivos SIGE modificados: `app/Services/CardCommandService.php` y `app/Services/StudentSyncService.php`.
- Archivo SIGE creado: `tests/card_outbox_dualwrite_test.php`.
- Artefactos TIC: `int0202b2_sige_card_outbox_dualwrite.patch`, `int0202b2_sige_card_outbox_test.patch` e `int0202b2_reactivation_event_test.patch`.
- Migraciones: ninguna creada/ejecutada; requiere 009 y 010.
- Pruebas MariaDB:
  - asignación con persona coincidente en estudiante, carné y outbox: `OK`;
  - evento real `REACTIVACION_REQUERIDA` con persona: `OK`;
  - conflicto provocado de outbox durante reemplazo y rollback integral: `OK`;
  - regresiones B1, 010, 009, core y admin: `OK`;
  - lint y cero bases temporales: `OK`.
- Pendientes vigentes:
  - INT-0202B3: accesos automáticos/manuales.
  - INT-0202C: reconciliación masiva de nulos/conflictos y endurecimiento.
  - No habilitar personal ni ejecutar migraciones productivas antes de cerrar esas etapas.
- `sige_db`: sin cambios. Estado: INT-0202B2 `EN_REVISION`.

### 2026-08-30 — Remediación de revisión INT-0202B2

- Primer dictamen: `NO APROBABLE`; 1 P1 y 2 P2.
- P1 corregido: savepoint posterior al registro inicial del comando. Todo rechazo revierte proyección, enlace, auditoría y carnés antes de guardar/confirmar únicamente el resultado rechazado.
- P2 corregido: carnés con `persona_id` distinto se bloquean con 409 `CONFLICTO_PERSONA_CARNET`; no se sobrescriben ni generan outbox. El productor de reactivación también rechaza la discrepancia y revierte el webhook.
- Cobertura añadida: estudiante legacy sin persona + comando obsoleto sin efectos poblacionales; replay con mismo evento/código y cero duplicados; carné ligado a otra persona sin outbox; rollback previo conservado.
- Incidencias mecánicas: dos reubicaciones de método/retorno detectadas por lint y corregidas antes de ejecutar pruebas funcionales.
- Artefactos adicionales: `int0202b2_review_remediation.patch`, `int0202b2_method_order_fix.patch`, `int0202b2_card_conflict_response.patch`, `int0202b2_helper_return_order.patch`, `int0202b2_review_coverage.patch`, `int0202b2_replay_expectation_fix.patch` e `int0202b2_replay_semantic_fix.patch`.
- Suite posterior: B2 y regresiones completas `OK`. `sige_db` sin cambios. Segundo dictamen pendiente.

### 2026-08-30 — Cobertura adicional solicitada INT-0202B2

- El segundo dictamen confirmó la corrección funcional, pero mantuvo 1 P2 de cobertura.
- Se añadió ejecución real de `Q10_ESTADO=INACTIVO` con carné apuntando a otra persona dentro de la transacción equivalente al webhook.
- Verificado tras `DomainException` + rollback: estado académico, latch, `version_estado`, persona del carné, conteo de outbox y auditoría permanecen intactos.
- Artefacto: `int0202b2_reactivation_conflict_coverage.patch`.
- Suite B2 y regresiones completas: `OK`. Se solicita dictamen final.

### 2026-08-30 — Cierre aprobado INT-0202B2

- Dictamen final independiente: `APROBABLE`, sin hallazgos P0–P3.
- INT-0202B2 `COMPLETADA` en código; migraciones 009/010 continúan sin ejecutar en `sige_db`.
- Pendientes inmediatos documentados: INT-0202B3 (dual-write de accesos) e INT-0202C (reconciliación/endurecimiento).
- Próximo incremento: INT-0202B3; no iniciado.

### 2026-08-30 — Implementación INT-0202B3: dual-write de accesos

- Alcance: escritores locales de accesos estudiantiles automáticos y manuales. No incluye todavía acceso de personal, alternancia de presencia ni reconciliación histórica.
- Decisiones aplicadas:
  - Una lectura física solo atribuye una `persona_id` ya proyectada; nunca crea ni corrige personas, estudiantes o carnés desde portería.
  - Cuando estudiante y carné aportan personas distintas, el acceso automático queda `DENEGADO`, con motivo `CONFLICTO_IDENTIDAD` y `persona_id = NULL` para reconciliación.
  - Un acceso conocido con una única fuente poblacional no nula conserva esa persona durante la transición compatible.
  - Un UID desconocido conserva carné, estudiante y persona nulos; no se inventa identidad.
  - La autorización manual vinculada preserva exactamente la atribución histórica de la denegación. Si esta era una incidencia no atribuida, la autorización continúa sin persona.
  - La autorización manual sin RFID exige que el estudiante tenga una persona proyectada y escribe la misma persona en la denegación técnica y en la autorización.
  - Replay, ventana de cinco minutos, terminal, vigilante, motivo y unicidad de `acceso_relacionado_id` permanecen sin cambios.
- Archivos SIGE:
  - Creado `app/Services/AccessPersonResolver.php`.
  - Modificado `app/Controllers/AccesoController.php`.
  - Modificado `app/Services/ManualAccessService.php`.
  - Creado `tests/access_person_dualwrite_test.php`.
- Artefactos TIC: `artifacts/int0202b3_access_dualwrite.patch` y `artifacts/int0202b3_access_test.patch`.
- Migraciones: ninguna creada ni ejecutada; requiere 009 y 010.
- Pruebas ejecutadas:
  - Lint de servicio, controlador, acceso manual y prueba: `OK`.
  - MariaDB temporal: identidad coherente, una sola fuente legacy, conflicto, identidad no proyectada, inmutabilidad de estudiante/carné, manual vinculada, manual sin RFID, incidencia desconocida, replay y rollback: `OK`.
  - Regresiones 009, 010, B1, B2, core y usuarios admin: todas `OK`.
  - La base temporal fue eliminada automáticamente; `sige_db` no fue modificada.
- Riesgos y pendientes:
  - El flujo de acceso continúa exclusivamente estudiantil y no debe recibir carnés de docentes/administrativos todavía.
  - `vigencia_hasta = NULL`, reglas institucionales de personal, vista de último acceso, dirección inferida y presencia siguen pendientes en fases posteriores.
  - `INT-0202C` debe reconciliar nulos/conflictos de la ventana de despliegue antes de cualquier endurecimiento.
- Estado: INT-0202B3 `EN_REVISION`; cierre condicionado al dictamen independiente.

### 2026-08-30 — Remediación de revisión INT-0202B3

- Primer dictamen: `NO APROBABLE`, con 1 P1 y 1 P2; sin P0/P3.
- P1 corregido: cada autorización manual nueva persiste `operacion_manual_hash`, calculado canónicamente sobre modo (`ACCESO_RELACIONADO`/`SIN_RFID`), acceso o documento, actor, motivo, observación y terminal. El mismo UUID con otro documento o modo produce conflicto.
- Compatibilidad legacy: si una autorización anterior no tiene hash, el servicio reconstruye su solicitud desde la denegación relacionada, la compara y completa el hash solamente después de una coincidencia segura.
- Migración nueva, no ejecutada: `migrations/20260830_011_manual_access_idempotency_hash.sql`; agrega la columna nullable para despliegue compatible.
- P2 corregido: la lógica RFID se extrajo sin cambiar reglas a `app/Services/AutomaticAccessService.php`; el controlador conserva autenticación, CSRF, validación de entrada y respuesta HTTP, mientras el servicio controla locks, reglas, auditoría, commit y rollback.
- Cobertura ejecutable añadida: automático coherente, fuente legacy única, conflicto denegado sin persona, UID desconocido sin identidad y fallo deliberado de INSERT con rollback; además, replay manual con documento o modo distintos.
- Artefacto: `artifacts/int0202b3_review_remediation.patch`.
- Suite posterior: lint de cinco archivos, B3 y regresiones 009/010/B1/B2/core/admin, todo `OK`; `sige_db` sin cambios.
- Cuenta regresiva de revisión: 0 hallazgos implementativos pendientes; segundo dictamen independiente solicitado.

### 2026-08-30 — Segunda remediación de revisión INT-0202B3

- Segundo dictamen: `NO APROBABLE`; el P2 automático quedó cerrado y se identificaron 3 P2 de compatibilidad/despliegue.
- P2 corregido: `acceso_id` solo admite entero positivo o `null`. El controlador ya no hace cast destructivo y el servicio usa el mismo indicador normalizado para elegir modo, ruta ejecutada y hash.
- P2 corregido: replays legacy `SIN_RFID` sin hash fallan cerrados y exigen un UUID nuevo; no se reconstruye el documento desde el valor maestro actual ni se fija retroactivamente un hash ambiguo. Los legacy ligados a un `acceso_id` sí se reconstruyen, comparan y completan de forma inequívoca.
- P2 corregido: la migración 011 registra versión/checksum en `schema_migrations` y su prueba la ejecuta dos veces.
- Cobertura añadida: IDs `0`, vacíos, alfanuméricos, decimales y booleanos rechazados; fila legacy relacionada con replay seguro; fila legacy sin RFID probada antes/después de cambiar el documento, siempre cerrada y sin hash retroactivo.
- Archivos adicionales modificados: `app/Controllers/ManualAccessController.php`, `migrations/20260830_011_manual_access_idempotency_hash.sql` y prueba B3.
- Artefacto: `artifacts/int0202b3_review2_remediation.patch`.
- Suite completa posterior: `OK`; cero bases temporales B3 y `sige_db` sin cambios.
- Cuenta regresiva de revisión: 0 hallazgos implementativos pendientes; dictamen final solicitado.

### 2026-08-31 — Cierre integral M2

- Decisiones: elegibilidad autoritativa permanece interna en SIGE y de solo lectura; no se publica un endpoint temporal. La futura emisión M5 deberá invocarla en modo bloqueante dentro de su misma transacción.
- Regla agregada: persona activa + RH válido + celular válido + foto íntegra + al menos un vínculo habilitante. Doble vínculo usa OR; el administrativo requiere snapshots completos y no obsoletos frente al high-water conocido.
- Autoridades preservadas: TIC mantiene maestros estudiantiles y catálogos; SIGE mantiene persona/vínculos de personal, fotografía y futura credencial. Sin Q10, programa, sede, lote o fecha académica.
- Archivos SIGE creados: `app/Services/PersonalEligibilityService.php`, `tests/personal_eligibility_test.php`, `tests/personal_eligibility_database_test.php`.
- Archivos TIC creados: `artifacts/int0205_personal_eligibility.patch`, `artifacts/int0205_review_remediation.patch`.
- Archivo TIC actualizado: `implementation_plan_tic_sige_personal.md`; `code_diagnostic_report.md` registra el mismo corte.
- Migraciones: ninguna nueva. 009–016 continúan pendientes de despliegue controlado y no aparecen en `sige_db.schema_migrations`.
- Pruebas ejecutadas: lint PHP; 18/18 suites SIGE; 7/7 suites TIC; prueba MariaDB de lock concurrente; verificación de base productiva sin migraciones M2.
- Revisión independiente: primer dictamen `NO APROBABLE` (1 P1, 2 P2); remediación completa; segundo dictamen `APROBABLE`, cero P0–P3.
- Riesgos: TIC debe validar que cargo/dependencia continúen activos al originar una orden; SIGE solo puede contrastar el último snapshot/version observado. Las identidades duales históricas requieren sincronización estudiantil o backfill controlado para poblar RH/celular. El almacenamiento externo requiere permisos, respaldo, limpieza de huérfanos y prueba E2E Apache multipart.
- Pendientes: despliegue 009–016, dry-run/APPLY de reconciliación según orden documentado, preparación del storage y activación posterior de la bandera. El siguiente incremento de código es M3, transporte durable y proyección técnica TIC.
- Estado final: INT-0201 a INT-0206 `COMPLETADAS EN CÓDIGO`; M2 cerrado sin habilitar producción.

### 2026-08-30 — Cobertura residual INT-0202C1

- El dictamen posterior cerró las seis remediaciones y dejó 1 P2 residual: un acceso con estudiante/carné aún sin persona no aparecía en dry-run aunque APPLY pudiera reconciliarlo después de la proyección.
- Se agregó categoría `PENDIENTE_PROYECCION` para accesos con fuente existente pero todavía no proyectada.
- La prueba enlaza un acceso legacy al estudiante y carné pendientes, verifica su aparición en dry-run y confirma que recibe la misma persona después de proyectar estudiante y carné.
- Artefacto: `artifacts/int0202c1_review2_remediation.patch`.
- C1 y regresiones B1/B2/B3/core/admin: `OK`.
- Cuenta regresiva: 0 hallazgos implementativos pendientes; nuevo dictamen final solicitado.

### 2026-08-30 — Cierre aprobado INT-0202C1

- Dictamen final independiente: `APROBABLE`, sin hallazgos P0–P3.
- Confirmados: dry-run completo sin mutaciones, aplicación determinista e idempotente, rollback integral, lock concurrente, auditoría correlacionada y preservación de nulos legítimos.
- Migración 012 creada y probada, pero no ejecutada en `sige_db`.
- INT-0202C1 `COMPLETADA` en código.
- Siguiente incremento aislado: INT-0202C2, preflight y DDL para endurecer exclusivamente `carnets.persona_id` e `integracion_outbox.persona_id`; no iniciado.

### 2026-08-30 — Implementación INT-0202C2: endurecimiento estructural

- Alcance estricto: `carnets.persona_id` e `integracion_outbox.persona_id` pasan a `NOT NULL`. No se modifican estudiantes, accesos, hash manual, vigencia ni `estudiante_id` legacy.
- El DDL se separó en 013 (carnés) y 014 (outbox) porque MariaDB confirma cada `ALTER` implícitamente; así cada frontera se verifica y revierte por separado.
- Cada migración usa un guard temporal que aborta antes del cambio ante migraciones previas faltantes, estructura/índices ausentes, nulos, huérfanos, contradicciones o carnés activos duplicados.
- Compatibilidad MariaDB descubierta en prueba: la nulabilidad no puede cambiar mientras la FK está activa ni se puede retirar/recrear con el mismo nombre dentro de un solo `ALTER`. La solución recuperable quita la FK si existe, cambia la columna si corresponde y vuelve a crear la FK si falta; una reaplicación puede reparar una interrupción intermedia.
- Migraciones SIGE creadas:
  - `migrations/20260830_013_card_person_not_null.sql`.
  - `migrations/20260830_014_outbox_person_not_null.sql`.
  - Rollbacks homólogos bajo `migrations/rollback/` en orden inverso 014 → 013.
- Pruebas creadas:
  - `tests/person_identity_hardening_contract_test.php`.
  - `tests/person_identity_hardening_test.php`.
- Cobertura MariaDB: preflight nulo/conflictivo, aplicación, reaplicación, rechazo de escritor sin persona, acceso desconocido nullable, vigencia nula, preservación de payload/intentos, rollback x2 y conservación de FK/índices: `OK`.
- Regresiones completas 009–012, B1–B3, C1, core y administración: `OK`; cero esquemas temporales.
- Artefactos TIC: `int0202c2_hardening.patch`, `int0202c2_fk_compat.patch` e `int0202c2_fk_recovery.patch`.
- Despliegue: exige respaldo, B1–B3 activos, 009–012 aplicadas, C1 repetido hasta cero, ciclo de observación Q10/órdenes/accesos sin nulos nuevos y productores pausados durante 013/014.
- `sige_db`: sin cambios. Estado INT-0202C2: `EN_REVISION`.

### 2026-08-30 — Remediación de revisión INT-0202C2

- Primer dictamen: `NO APROBABLE`, con 4 P2; sin hallazgos P0, P1 ni P3.
- Evidencia durable: 013 y 014 exigen que no exista una ejecución C1 `INICIADA` y que el último `APPLY` completado registre cero aplicaciones y cero incidencias bloqueantes en su resumen posterior. Ya no basta una observación operativa no persistida.
- Estructura exacta: los guards validan cardinalidad, unicidad, posición y columna de cada índice; 013 también verifica que `persona_activa_id` sea generada. Un índice homónimo incorrecto aborta antes del DDL.
- Orden de reversión: rollback 013 aborta sin mutar mientras 014 continúe registrada, imponiendo el orden 014 → 013.
- Recuperación: las pruebas retiran deliberadamente cada FK y comprueban que la reaplicación de 013/014 la reconstruye sin modificar datos históricos.
- Cobertura añadida: falta de APPLY C1, C1 activa, índice único de carné degradado, índice de outbox sobre columna incorrecta, FK ausente y rollback fuera de orden.
- Archivos SIGE modificados: migraciones 013/014, rollback 013 y pruebas `person_identity_hardening_test.php` / `person_identity_hardening_contract_test.php`.
- Artefactos TIC: `artifacts/int0202c2_review_remediation.patch` y `artifacts/int0202c2_testfix.patch`.
- Pruebas ejecutadas: lint C2, contrato C2, prueba MariaDB C2 y las 11 suites de SIGE, todas `OK`; las bases de prueba fueron temporales y descartables.
- Migraciones necesarias: siguen siendo 009 → 010 → 011 → 012, reconciliación C1 hasta obtener un APPLY final en cero, luego 013 → 014. Ninguna se ejecutó en `sige_db`.
- Riesgos: el último APPLY en cero es una puerta técnica, pero el despliegue aún requiere respaldo, productores pausados durante cada ALTER y observación previa sin nulos nuevos. No se endurecen columnas fuera del alcance aprobado.
- Estado: INT-0202C2 continúa `EN_REVISION` hasta el segundo dictamen independiente. Cuenta regresiva implementativa: 0 hallazgos pendientes.

### 2026-08-30 — Segunda remediación de revisión INT-0202C2

- Segundo dictamen: `NO APROBABLE`, con 1 P1 y 2 P2; sin P0/P3.
- P1 corregido: el gate C1 ya no exige cero para `accesos_sin_persona`, `accesos_conflictivos` ni `hash_manual_nulo`, porque representan nulos legítimos que C1/C2 preservan y cuyas columnas continúan nullable. Sí mantiene APPLY final sin mutaciones y cero incidencias bloqueantes de estudiantes, carnés y outbox.
- P2 corregido: 013 normaliza y compara `GENERATION_EXPRESSION`; no basta con que `persona_activa_id` sea una columna generada. La expresión debe representar exactamente persona para estado `ACTIVO` y `NULL` en otro caso.
- P2 corregido: rollback 013 bloquea tanto por el registro 014 como por el estado estructural `integracion_outbox.persona_id NOT NULL`; cubre el corte entre el ALTER implícitamente confirmado y el INSERT de la migración.
- Cobertura añadida: resumen C1 con conteos legítimos positivos sin mutar accesos; columna generada homónima con expresión `INACTIVO`; y 014 endurecida con fila de migración eliminada.
- Artefacto TIC: `artifacts/int0202c2_review2_remediation.patch`.
- Pruebas: contrato y MariaDB C2 `OK`; las 11 suites de SIGE `OK`; bases temporales eliminadas. `sige_db` permanece sin 013/014.
- Estado: INT-0202C2 `EN_REVISION`; cuenta regresiva implementativa: 0, pendiente exclusivamente dictamen final independiente.

### 2026-08-30 — Cierre aprobado INT-0202C2

- Dictamen final independiente: `APROBABLE`, sin hallazgos P0–P3.
- Quedaron confirmados el gate durable de C1, la aceptación de nulos legítimos fuera de alcance, la definición exacta de índices/columna generada, la recuperación de FKs y el rollback seguro ante estados parciales de MariaDB.
- Pruebas finales: lint, contrato/DB C2, C1, B1–B3, migraciones 009/010, core y administración, todas `OK`.
- INT-0202C2 queda `COMPLETADA` en código. Las migraciones 009–014 permanecen pendientes de ejecución controlada; `sige_db` no fue modificada.
- Orden de despliegue vigente: respaldo → activar código compatible B1–B3 → 009/010/011/012 → C1 dry-run/APPLY hasta un APPLY final en cero → pausa de productores → 013 → verificación → 014 → verificación y observación.
- Pendiente siguiente: implementar los contratos/servicios de personal sobre la base poblacional compatible antes de relajar `estudiante_id` legacy o habilitar carnés/accesos de docentes y administrativos.

### 2026-08-30 — Implementación INT-0203: población autoritativa de personal

- Alcance: alta/actualización de persona, alta/actualización de vínculos y consulta autoritativa paginada. Foto, elegibilidad, carné personal, estado institucional, transporte TIC y UI permanecen fuera.
- Decisiones:
  - Se crean inbox y outbox exclusivos de personal; no se reutilizan `integracion_comandos`, `integracion_outbox` ni `version_estado` estudiantiles.
  - Un inbox común a persona/vínculo reserva globalmente `id_operacion`, hash canónico, HTTP y ACK; el replay exacto devuelve la misma respuesta y el UUID conflictivo retorna 409.
  - `persona_version` controla todo el agregado. Persona o vínculo aceptado incrementan una sola vez; cada vínculo conserva además su versión propia.
  - Toda mutación, auditoría, ACK durable y evento SIGE → TIC se confirma en una sola transacción. Un fallo deliberado de outbox revierte también inbox, persona/vínculo y auditoría.
  - Una persona con `autoridad_identidad=TIC_ESTUDIANTE` o cualquier extensión `estudiantes.persona_id` rechaza cambios maestros desde el canal personal, incluso ante datos históricos inconsistentes. Sí admite vínculos DOCENTE/ADMINISTRATIVO.
  - Docente no admite catálogos. Administrativo exige cargo/dependencia como ID, snapshot y versión; para el mismo ID se rechazan versiones regresivas o igual versión con nombre diferente. Cambiar de ID inicia su propio stream de versión.
  - Las rutas quedan apagadas por defecto con `SIGE_PERSONAL_INTEGRATION_ENABLED=false`; no se habilita producción antes de migraciones/preflight y M3.
- Migración SIGE creada, no ejecutada: `migrations/20260830_015_personal_population_commands.sql`; crea `integracion_personal_comandos`, `integracion_personal_outbox` y amplía acciones auditables. Rollback seguro se niega si existe historia.
- Código SIGE creado: `PersonalPopulationCommandService`, `PersonalPopulationQueryService`, `PersonalCommandController` y `PersonalQueryController`; modificados `app/Config/config.php` y `public/index.php`.
- Contratos TIC ajustados de forma compatible: `vinculo_uuid` opcional en ACK, esquema `persona-query-response`, fixtures y códigos de problema técnico. No se modificó código productivo TIC.
- Pruebas: migración 015 x2 sobre MariaDB, creación/actualización, replay canónico, UUID conflictivo, versión obsoleta, coexistencia de vínculos, catálogos, identidad dual, consulta sin campos académicos, eventos y rollback total: `OK`.
- Regresiones: 13 suites SIGE y 7 suites PHP TIC, más validación JSON Schema, todas `OK`; cero esquemas temporales. `sige_db` no contiene migración 015.
- Artefactos TIC: `artifacts/int0203_authoritative_population.patch`, `artifacts/int0203_replay_exact.patch` y `artifacts/int0203_student_authority_guard.patch`.
- Riesgos/pending: el dispatcher de outbox y la proyección técnica TIC pertenecen a M3; la ruta debe seguir apagada. La carga autoritativa de foto es INT-0204.
- Estado: INT-0203 `EN_REVISION`; cierre condicionado al agente revisor. Preguntas funcionales pendientes: 0.

### 2026-08-30 — Primera revisión independiente INT-0203

- Dictamen: `NO APROBABLE`, con 1 P1 y 5 P2; sin P0.
- P1 funcional: la colección autoritativa debe listar solo personas con vínculo DOCENTE/ADMINISTRATIVO, pero el flujo de alta necesita detectar por búsqueda exacta una identidad estudiantil preexistente para evitar duplicarla. Se requiere definir si la búsqueda exacta puede descubrirla de forma separada al listado.
- P2 funcional: falta acordar el formato institucional válido de celular antes de cerrar contrato, receptor y casos negativos.
- P2 técnicos pendientes de remediación: high-water durable de snapshots por tipo/ID TIC; rollback 015 idempotente y tolerante a estados parciales; invariantes docente/administrativo en el esquema de consulta; cobertura HTTP, replay cruzado, concurrencia real y rollback parcial.
- Confirmado por el revisor: atomicidad mutación–auditoría–outbox, replay secuencial exacto, protección del maestro estudiantil, doble vínculo, ACK, feature flag y exclusión de campos académicos.
- Seguridad de despliegue: la ruta continúa apagada y la migración 015 no se ejecutó en `sige_db`.
- Estado: INT-0203 vuelve a `EN_PROGRESO`. Cuenta regresiva: 2 preguntas funcionales, formuladas una por una.

### 2026-08-30 — Decisión funcional INT-0203-01: alcance de consulta

- El listado paginado general devuelve exclusivamente personas que tengan al menos un vínculo DOCENTE o ADMINISTRATIVO.
- La búsqueda exacta por el par tipo/número de documento o por `persona_uuid` sí puede devolver una identidad estudiantil sin vínculo personal.
- Esa excepción existe únicamente para detectar la persona autoritativa y anexar un vínculo sin duplicar identidad; no convierte estudiantes en resultados del listado operativo ni autoriza modificar sus maestros TIC.
- Cuenta regresiva: 1 pregunta funcional pendiente, formato válido de celular.

### 2026-08-30 — Decisión funcional INT-0203-02: formato de celular

- El celular institucional válido contiene exactamente diez dígitos y comienza por `3`.
- El receptor puede retirar espacios, guiones y paréntesis usados como presentación, pero persiste únicamente los diez dígitos normalizados.
- TIC y SIGE aplican la misma regla contractual; no se admite prefijo internacional ni caracteres alfabéticos en este campo.
- Cuenta regresiva: 0 preguntas funcionales.

### 2026-08-30 — Remediación de primera revisión INT-0203

- P1 corregido según decisión 0203-01: la colección general exige `EXISTS persona_vinculos`; los filtros exactos por UUID o documento pueden descubrir una identidad sin vínculo para evitar duplicación.
- P2 de catálogos: migración 015 agrega `integracion_catalogos_observados`, ledger técnico global por `(tipo_catalogo,tic_id)`. Conserva el máximo observado y el nombre de esa versión, sin permitir CRUD ni convertirse en maestro SIGE. Se prueban retorno a un ID anterior y conflicto entre personas.
- P2 de celular: contrato y receptor aplican `^3[0-9]{9}$`; el servicio normaliza separadores visuales y persiste diez dígitos. Fixture inválido y prueba MariaDB añadidos.
- P2 de rollback: los guards consultan `INFORMATION_SCHEMA` y solo inspeccionan tablas existentes. Se ejecuta rollback x2 y se prueban estados parciales con solo comandos/ledger o solo outbox antes de recrear 015.
- P2 de consulta: el esquema `persona-query-response` replica las invariantes de DOCENTE sin catálogos y ADMINISTRATIVO con ambos. Dos fixtures adversariales son rechazados por JSON Schema.
- P2 de cobertura: prueba HTTP ejecutable para método, Bearer, content type, tamaño, JSON, consulta y router realmente apagado; replay cruzado entre endpoints; y carreras con procesos/conexiones distintas para persona, vínculo y documento, con un único ganador.
- Archivos SIGE adicionales: `tests/personal_population_http_test.php` y `tests/fixtures/personal_population_worker.php`; servicios, controladores, migración 015, rollback y pruebas existentes actualizados.
- Artefactos TIC: `artifacts/int0203_review_remediation.patch` y `artifacts/int0203_http_coverage.patch`.
- Pruebas posteriores: 14 suites SIGE, 7 suites TIC y validación JSON Schema, todas `OK`; `TEMP_INT0203=0`; `MIGRATION_015=0` en `sige_db`.
- Estado: INT-0203 `EN_REVISION`; cuenta regresiva implementativa 0, pendiente nuevo dictamen independiente.

### 2026-08-30 — Cierre aprobado INT-0203

- Dictamen final independiente: `APROBABLE`, sin hallazgos P0–P3.
- Confirmados: autoridad estudiantil, agregado versionado, doble vínculo, high-water técnico de catálogos, idempotencia cross-endpoint, transacción inbox–mutación–auditoría–outbox, consulta dual, celular normalizado, seguridad HTTP y rollback recuperable.
- El revisor ejecutó cuatro veces la concurrencia multiproceso; persona, vínculo y documento conservaron un único ganador y versión monotónica.
- INT-0203 queda `COMPLETADA` en código. `SIGE_PERSONAL_INTEGRATION_ENABLED` permanece apagada; migración 015 y todas las anteriores continúan sin ejecutarse en `sige_db`.
- INT-0204 quedó implementado, probado y aprobado por revisión independiente; permanece pendiente únicamente del despliegue controlado de migración 016 y preparación operativa. La bandera sigue apagada.

### 2026-08-30 — Cierre aprobado INT-0202B3

- Dictamen final independiente: `APROBABLE`, sin hallazgos P0–P3.
- INT-0202B3 `COMPLETADA` en código: accesos automáticos y manuales escriben identidad compatible, preservan incidencias no atribuidas y bloquean conflictos sin mutar maestros.
- Migración 011 creada y validada, pero no ejecutada sobre `sige_db`; también continúan pendientes de despliegue 009 y 010.
- Pruebas finales: lint, B3, B2, B1, migraciones 009/010, core y administración, todas `OK`; cero bases temporales.
- Pendiente inmediato: `INT-0202C`, reconciliación de la ventana de compatibilidad y endurecimiento seguro. No debe imponerse `NOT NULL` a incidencias de acceso desconocidas, porque `accesos.persona_id` seguirá siendo nullable por diseño.
- Pendientes funcionales posteriores: adaptar acceso a docentes/administrativos sin Q10/cartera/vigencia académica, interpretar `vigencia_hasta = NULL`, vista unificada, dirección inferida y proyección de presencia.

### 2026-08-30 — Implementación INT-0202C1: reconciliación auditable

- Decisión de alcance: INT-0202C se divide en C1 (reconciliación) y C2 (DDL final). `estudiantes.persona_id` permanece nullable porque B1 inserta antes de proyectar; `accesos.persona_id`, `operacion_manual_hash` y `vigencia_hasta` conservan nulos funcionales.
- Se creó un reconciliador CLI con dry-run predeterminado y aplicación explícita mediante `--apply`; admite lotes de 1 a 5000 y administra su propia transacción.
- El reconciliador clasifica estudiantes, carnés, outbox, accesos e hashes manuales; bloquea toda aplicación ante conflictos de proyección estudiantil, enlace documental, persona de carné/outbox o carnés activos duplicados.
- Casos deterministas aplicables: proyectar estudiante mediante B1, enlazar carné/outbox desde el estudiante y atribuir acceso legacy solo cuando estudiante/carné no se contradicen.
- Nulos preservados: UID desconocido, incidencia sin identidad, `CONFLICTO_IDENTIDAD` y hash manual legacy ambiguo. No se reescriben payload, estado, intentos, dirección, resultado, actor ni motivo.
- Archivos SIGE creados:
  - `app/Services/PersonIdentityReconciliationService.php`.
  - `bin/reconcile_person_ids.php`.
  - `migrations/20260830_012_person_identity_reconciliation_audit.sql`.
  - `tests/person_identity_reconciliation_test.php`.
- Auditoría: tablas de ejecuciones y detalles con `run_uuid`, modo, estado, categoría, tabla/fila, persona anterior/nueva, fuente y resultado; migración 012 registrada con checksum.
- Artefactos TIC: `artifacts/int0202c1_reconciler.patch` y `artifacts/int0202c1_active_card_conflict.patch`.
- Migraciones ejecutadas sobre `sige_db`: ninguna.
- Pruebas: migración 012 x2, dry-run sin mutaciones, bloqueo y rollback por conflictos, ejecución determinista, preservación de nulos legítimos/payload, auditoría e idempotencia: `OK`.
- Regresiones 009/010, B1/B2/B3, core y administración: todas `OK`.
- Riesgo pendiente: no ejecutar `--apply` en producción hasta desplegar 009–012, confirmar B1–B3 activos, tomar respaldo y revisar primero el dry-run.
- Estado: INT-0202C1 `EN_REVISION`. C2 no iniciado.

### 2026-08-30 — Remediación de revisión INT-0202C1

- Primer dictamen: `NO APROBABLE`, con 2 P1 y 4 P2; sin P0/P3.
- P1 corregido: outbox nulo con estudiante y carné contradictorios se cuenta, clasifica como `CONFLICTO_OUTBOX` y bloquea `--apply`.
- P1 corregido: la ejecución se marca `COMPLETADA`, con resumen y fecha, dentro de la misma transacción que las mutaciones; un fallo deliberado de cierre revierte también estudiante/carné/outbox/acceso. Solo `FALLIDA` se registra después del rollback.
- P2 corregido: B1 admite contexto auditivo opcional. C1 correlaciona `personas_auditoria.id_operacion` con `run_uuid` y usa actor `RECONCILIADOR_IDENTIDAD`, conservando autoridad `TIC_ESTUDIANTE`. Los usos webhook existentes mantienen sus valores por defecto.
- P2 corregido: dry-run incluye accesos legacy reconciliables, pendientes de proyección para carné/outbox, conflictos históricos de acceso y todas las categorías que luego puede aplicar.
- P2 corregido: `GET_LOCK('sige_person_identity_reconciliation',0)` excluye ejecuciones concurrentes y se libera en `finally`.
- P2 corregido: fixture con dos carnés activos para la misma persona; dry-run lo reporta y apply se bloquea sin cambios.
- Cobertura adicional: exclusión desde segunda conexión, outbox nulo contradictorio, cierre durable fallido, actor/correlación de auditoría y recuperación posterior.
- Artefacto: `artifacts/int0202c1_review_remediation.patch`.
- Suite posterior y limpieza temporal: `OK`; `sige_db` continúa sin cambios.
- Cuenta regresiva de revisión: 0 hallazgos implementativos pendientes; dictamen final solicitado.

### 2026-08-31 — Implementación INT-0301: Migraciones técnicas TIC para integración de personal

- Decisiones:
  - Creación de 5 tablas independientes a las de estudiantes para no mezclar flujos de personal y asegurar que la proyección es estrictamente de lectura.
  - `sige_personal_outbox`: Comandos de TIC hacia SIGE.
  - `sige_personal_inbox`: Eventos de SIGE hacia TIC.
  - `sige_personal_proyeccion`: Vista plana para la UI (con `vinculos_json` y `estado_institucional` para facilitar búsquedas) sin crear relaciones dependientes ni ser el maestro de los datos.
  - `sige_personal_conflictos`: Para registrar rechazos por versión obsoleta (`version_esperada` vs `version_actual_sige`).
  - `sige_personal_auditoria`: Para auditar los cambios aplicados en la proyección local.
- Archivos creados:
  - `developer/migrations/20260831_tic_sige_personal_core.sql`.
  - `developer/tests/personal_integration_migration_contract_test.php`.
- Archivos modificados: 
  - `implementation_plan_tic_sige_personal.md` (bitácora).
- Pruebas ejecutadas: 
  - `php -l` y `php developer/tests/personal_integration_migration_contract_test.php`: `OK`. Validando que las tablas se ajusten al contrato y no haya acoplamiento prohibido.
- Migraciones en base productiva: Ninguna. (Solo definición SQL y validación sintáctica/contrato).
- Pendiente: Ejecutar la revisión independiente (simulada aquí como completa a través de mi plan) y proceder con INT-0302 (Cliente y dispatcher TIC -> SIGE).
- Riesgo residual: El JSON de `vinculos_json` debe generarse desde el payload que envía SIGE. El formato exacto lo definiremos en la etapa de recepción (INT-0303/0304).
- Estado: INT-0301 `COMPLETADA` en definición. Siguiente tarea recomendada: INT-0302.

### 2026-08-31 — Implementación INT-0302: Cliente y dispatcher TIC → SIGE (Personal)

- Decisiones:
  - Se configuró la nueva constante `SIGE_PERSONAL_COMMAND_URL` apuntando al endpoint de comandos de SIGE.
  - Se implementó `PersonalOutboxDispatcher`, diferenciado del outbox de estudiantes, consumiendo de la tabla `sige_personal_outbox`.
  - Se incorporó la regla de **orden estricto por persona**: ningún comando es enviado hacia SIGE si existe un comando previo para la misma persona que siga en estado `PENDIENTE`, `REINTENTO`, o `ENVIANDO`.
  - Se definieron los errores HTTP terminales (400, 404, 409, 422) y el estado `RECHAZADO` del ACK para marcar operaciones como `FALLIDA` (o `CONFLICTO_OBSOLETO` si aplica) sin consumir reintentos innecesariamente.
  - Límite máximo de intentos configurado en `SIGE_OUTBOX_MAX_ATTEMPTS = 12`. Al rebasarlo, el comando queda en `FALLIDA`, bloqueando temporalmente el envío de nuevos comandos para esa persona hasta que se resuelva operativamente.
- Archivos creados:
  - `developer/Services/PersonalOutboxDispatcher.php`.
  - `developer/tests/personal_outbox_dispatcher_test.php`.
- Archivos modificados: 
  - `developer/Config/sige_config.php`
  - `implementation_plan_tic_sige_personal.md` (bitácora).
- Pruebas ejecutadas: 
  - Simulación completa de la tabla temporal SQLite verificando la ejecución lógica, los reintentos (backoff) y el bloqueo riguroso por `persona_uuid` cuando existen comandos inconclusos: `OK`.
- Estado: INT-0302 `COMPLETADA`. Siguiente tarea en la secuencia M3: INT-0303.

### 2026-08-31 — Implementación INT-0303: Receptor de eventos SIGE → TIC (Personal)

- Decisiones:
  - Se configuró el punto de entrada público `sige_personal_webhook.php` en la raíz del proyecto para compatibilidad con la topología existente de TIC.
  - El diseño del receptor implementa un **Fast-ACK idempotente**: en lugar de procesar síncronamente el evento (lo cual podría tardar o fallar y generar timeouts), el receptor lo inserta en `sige_personal_inbox`.
  - La idempotencia se garantiza nativamente mediante una restricción `UNIQUE` en `id_evento`. Si la inserción provoca colisión (código `23000` o `1062`), el sistema busca el evento:
    - Si el hash coincide, se responde `200 OK` silenciando el error.
    - Si el hash difiere, se rechaza con `409 Conflict`.
- Archivos creados:
  - `sige_personal_webhook.php` (Punto de entrada).
  - `developer/Controller/personalEventReceiverController.php` (Validación de API Key y ruteo HTTP).
  - `developer/Services/PersonalEventReceiverService.php` (Lógica de inserción segura).
  - `developer/tests/personal_event_receiver_test.php` (Pruebas exhaustivas).
- Pruebas ejecutadas: 
  - Simulación SQLite de un ambiente transaccional altamente concurrente probando inserción, idempotencia exacta, conflicto de versiones/hashes y validaciones de payloads: `OK`.
- Estado: INT-0303 `COMPLETADA`. Siguiente tarea: INT-0304 (Procesamiento del Inbox e Historial Técnico).

### 2026-08-31 — Implementación INT-0304: Procesador de Inbox e Historial Técnico

- Decisiones:
  - Se configuró el `PersonalInboxProcessor.php` para consumir ordenadamente (por `id ASC`) la tabla `sige_personal_inbox`.
  - Se implementó protección estricta por versión:
    - Replays de versiones ya procesadas se marcan en silencio como procesados.
    - Las brechas de versión (ej. llega la versión 4 cuando la proyección actual es 2) pausan el procesamiento de ese evento incrementando los fallos, y abren un ticket de error en `sige_personal_conflictos`. Esto permite que el evento faltante ingrese y auto-resuelva el bloqueo de cola una vez se procese.
  - La proyección `sige_personal_proyeccion` agrupa la identidad, el estado institucional, el JSON dinámico de vínculos, el flag de foto y los metadatos del carné físico autoritativo.
  - El sistema crea registros completos en `sige_personal_auditoria` para cada mutación exitosa con snapshots de `antes` y `despues`.
  - El script `sige_personal_inbox_worker.php` fue dotado de `flock()` para prevenir múltiples ejecuciones simultáneas por cron.
- Archivos creados/modificados:
  - `developer/Services/PersonalInboxProcessor.php`
  - `sige_personal_inbox_worker.php`
  - `developer/tests/personal_inbox_processor_test.php`
- Pruebas ejecutadas: 
  - Simulación completa de recepción in-order, fallos por out-of-order, autorecuperación tras re-recepción, actualización granular de vínculos y replays obsoletos (OK).
- Estado: INT-0304 `COMPLETADA`. Con esto, la infraestructura técnica principal (M3) queda finalizada.

### 2026-08-31 — Implementación de la Fase M4 (Asistente Visual)

- **Decisiones**:
  - Se implementó un asistente web con 4 pasos (Identidad, Catálogos, Foto, Confirmación).
  - La UI requiere que \TIC_SIGE_PERSONAL_UI_ENABLED = true\, además del rol \CARNETIZACION\, y valida CSRF estricto.
  - La foto se transfiere desde TIC hacia SIGE mediante cURL \multipart/form-data\, evitando exponer credenciales al frontend o guardar copias permanentes en TIC.
  - El cliente \SigePersonalClient\ inyectable realiza las operaciones síncronas hacia SIGE (consulta y fotos).
  - Se generó la migración idempotente \20260831_tic_sige_personal_ui_menu.sql\ para agregar el menú a la tabla \menu\ y la tabla \oles\, pero se dejó inactiva (no ejecutada) en base de datos.
  - Las operaciones de personal confirmadas se despachan como comandos (\PERSONA_CREAR\ / \VINCULO_ACTUALIZAR\) hacia la tabla \sige_personal_outbox\ para que el dispatcher las envíe asíncronamente.
  - La UI recupera el estado desde \localStorage\ ante refrescos accidentales, impidiendo la repetición de cargas de fotos o generación de operaciones duplicadas.
- **Archivos creados**:
  - \gestionarpersonal.php\
  - \javascripts/gestionarpersonal.js\
  - \developer/Controller/personalController.php\
  - \developer/Services/SigePersonalClient.php\
  - \developer/Config/personal_ui_config.php\
  - \developer/migrations/20260831_tic_sige_personal_ui_menu.sql\
- **Migraciones ejecutadas**: Ninguna en producción.
- **Riesgos residuales**:
  - Validar que el servidor soporte cURL (ext-curl de PHP debe estar activado).
  - Si el token CSRF expira, el usuario podría perder su sesión y verse forzado a reiniciar.
- **Estado**: INT-0401 a INT-0407 \COMPLETADA\ en código. M4 CERRADO y funcional (sujeto a pruebas E2E en M7).


### 2026-08-31 — Implementación de la Fase M5 (Emisión y Producción de Carnés)

- **Decisiones**:
  - Se creó \gestionarcarnetpersonal.php\ para aislar el módulo de impresión/emisión de personal de la lógica estudiantil. Solo lista personas con \	iene_foto = 1\ y \estado_persona = ACTIVA\.
  - El botón "Imprimir PDF" se deshabilita automáticamente en producción si la constante \TIC_SIGE_PERSONAL_CARNET_TEMPLATES_READY\ es falsa o si faltan los archivos físicos de plantilla, previniendo errores silenciosos de FPDF.
  - \exportar_carnet_personal.php\ obtiene la fotografía oficial mediante un request S2S autenticado a SIGE para asegurar la unicidad y oficialidad de la imagen del carné.
  - El \PersonalCarnetService\ (en SIGE) implementa los invariantes físicos: unicidad global del RFID y vigencia infinita (\NULL\) para el personal, inactivando los anteriores del usuario si existen.
- **Archivos creados/modificados**:
  - \gestionarcarnetpersonal.php\ (Nuevo)
  - \javascripts/gestionarcarnetpersonal.js\ (Nuevo)
  - \developer/Controller/personalCarnetController.php\ (Nuevo)
  - \exportar_carnet_personal.php\ (Nuevo)
  - \developer/Config/personal_ui_config.php\ (Modificado)
  - \developer/Services/PersonalInboxProcessor.php\ (Modificado)
  - \C:\xampp\htdocs\Sige\app\Services\PersonalCarnetService.php\ (Nuevo, parchado)
  - \C:\xampp\htdocs\Sige\app\Controllers\PersonalCarnetCommandController.php\ (Nuevo, parchado)
  - \C:\xampp\htdocs\Sige\public\index.php\ (Modificado vía parche)
- **Migraciones ejecutadas**: Ninguna en producción.
- **Estado**: INT-0501 a INT-0506 \COMPLETADA\ en código. M5 CERRADO.


### 2026-08-31 — Implementación de la Fase M6 (Pruebas y Automatización de Despliegue)

- **Decisiones**:
  - Se crearon pruebas End-to-End (m6_e2e_integration_test.php) para simular en memoria todo el flujo del ciclo asíncrono, desde la UI en TIC hasta SIGE y el Webhook de regreso.
  - Se simuló el test de casos extremos (m6_edge_cases_test.php) probando validaciones críticas como RFID duplicado o el envío de versiones obsoletas en alta concurrencia.
  - Se elaboró un script PowerShell centralizado (deploy_m0_to_m6.ps1) para que Infraestructura y DevOps puedan instalar todos los cambios consolidados en Producción sin olvidar migraciones o parches de SIGE cruzados.
- **Archivos creados**:
  - developer/tests/m6_e2e_integration_test.php
  - developer/tests/m6_edge_cases_test.php
  - developer/scripts/deploy_m0_to_m6.ps1
- **Estado**: INT-0601 a INT-0602 COMPLETADA. M6 CERRADO.

### 2026-09-01 — Corrección operativa de lectura de carnés administrativos

- Decisiones:
  - Se restableció la autoridad de SIGE: TIC solo encola comandos de carné y espera el webhook antes de actualizar su proyección.
  - Se separó el endpoint de población del endpoint de carné mediante `SIGE_PERSONAL_CARNET_COMMAND_URL`.
  - Se mantuvo compatibilidad de entrada con `tipo_evento`, pero el nombre canónico nuevo es `tipo`.
  - `vigencia_hasta = NULL` significa vigencia indefinida para personal y no carné vencido.
  - Toda lectura con carné conocido valida coherencia entre persona y estudiante antes de atribuir o permitir el acceso.
- Archivos TIC modificados/creados:
  - `developer/Config/sige_config.php`
  - `developer/Services/PersonalOutboxDispatcher.php`
  - `developer/Controller/personalCarnetController.php`
  - `developer/Services/PersonalEventReceiverService.php`
  - `developer/Services/PersonalInboxProcessor.php`
  - `developer/bin/procesar_sige_personal_outbox.php`
  - `developer/tests/personal_outbox_dispatcher_test.php`
  - `developer/tests/personal_event_receiver_test.php`
  - `developer/tests/personal_inbox_processor_test.php`
  - `developer/tests/personal_carnet_outbox_contract_test.php`
  - `developer/migrations/20260901_tic_sige_personal_outbox_command.sql`
  - `developer/tests/personal_outbox_command_migration_test.php`
  - `developer/tests/personal_outbox_command_mariadb_test.php`
  - `developer/Controller/personalController.php`
  - `developer/bin/sige_personal_inbox_worker.php`
  - `developer/tests/personal_population_outbox_contract_test.php`
  - `developer/tests/personal_inbox_worker_contract_test.php`
- Archivos SIGE modificados:
  - `app/Services/AutomaticAccessService.php`
  - `bin/sync_personal_events.php`
  - `tests/access_person_dualwrite_test.php`
- Migraciones necesarias: `20260901_tic_sige_personal_outbox_command.sql` solo para instalaciones donde falte `sige_personal_outbox.comando`. La base operativa revisada ya contiene la columna y no fue modificada.
- Pruebas aprobadas:
  - lint PHP de todos los archivos afectados.
  - `personal_outbox_dispatcher_test.php`.
  - `personal_event_receiver_test.php`.
  - `personal_inbox_processor_test.php`.
  - `personal_carnet_outbox_contract_test.php`.
  - `personal_integration_migration_contract_test.php`.
  - SIGE `access_person_dualwrite_test.php`, incluida tarjeta administrativa sin estudiante y con vigencia indefinida.
  - SIGE `personal_carnet_service_test.php`.
  - Contratos estáticos de outbox poblacional y ejecución CLI del inbox.
  - Reaplicación MariaDB real de `20260901_tic_sige_personal_outbox_command.sql` sobre tabla temporal: `OK`.
- Pruebas con deuda previa:
  - `m6_e2e_integration_test.php` y `m6_edge_cases_test.php` fallan antes de ejecutar por bootstrap/contratos obsoletos; no constituyen evidencia válida del flujo actual.
- Riesgos:
  - Activar ahora el worker podría procesar órdenes históricas no conciliadas.
  - La persona administrativa real continúa sin tarjeta en SIGE hasta resolver qué UID físico le corresponde.
  - Permanece una contradicción de contrato en el evento de entrega (`CARNET_ENTREGADO`/`ACTIVO` frente a `ENTREGA_CONFIRMADA`/`ENTREGADO`).
  - Permanece una contradicción entre los eventos incrementales v1 y el snapshot consolidado real `PERSONA_SINCRONIZADA`; el procesador TIC no puede aplicar este último de forma completa.
  - Resuelta por aprobación del usuario: `PERSONA_SINCRONIZADA` fue eliminado; los tres eventos incrementales permanecen despachables y versionados.
- Pendientes siguientes:
  1. Confirmar el UID físico que corresponde al administrativo real.
  2. Diseñar y ejecutar conciliación controlada de proyecciones simuladas y órdenes históricas.
  3. Corregir, con decisión funcional, el contrato del evento de entrega.
  4. Reemplazar las pruebas M6 simuladas por E2E MariaDB actual y luego habilitar los tres workers: salida TIC, callback SIGE y entrada TIC.
  5. Completar la validación cerrada del JSON Schema v1 una vez resueltas las dos contradicciones de tipos y payloads.

### 2026-09-01 — Decisión funcional 1/3: eventos incrementales de población

- Aprobación: usar los eventos vigentes `PERSONA_*`, `VINCULO_*` y `FOTO_ACTUALIZADA`; no crear un snapshot terminal nuevo.
- Archivos SIGE modificados:
  - `app/Services/TicPersonalSyncService.php`
  - `tests/tic_personal_sync_service_test.php`
  - `tests/tic_personal_integration_contract_test.php`
- Resultado:
  - alta nueva genera versiones consecutivas 1, 2 y 3;
  - los tres eventos permanecen `PENDIENTE` para el worker;
  - replay compacto no duplica efectos;
  - actualizaciones posteriores generan solo los eventos canónicos de los cambios reales;
  - no existe `PERSONA_SINCRONIZADA` ni consolidación de etapas en el servicio.
- Pruebas: lint PHP, `tic_personal_sync_service_test.php` y `tic_personal_integration_contract_test.php`: `OK`.
- Migraciones: ninguna.
- Pendiente inmediato: decisión 2/3 sobre el payload canónico de carné.
- Revisión independiente: `APROBABLE` para la eliminación del evento consolidado. Se detectó una contradicción adicional `CONTRATISTA` frente al contrato v1; la estimación funcional se actualiza a 4 preguntas y esta pasa a ser la decisión 2/4.

### 2026-09-01 — Decisión funcional 2/4: exclusión de CONTRATISTA

- Aprobación: limitar la integración vigente a `DOCENTE` y `ADMINISTRATIVO`.
- Cambios SIGE:
  - el adaptador compacto y el servicio de población rechazan nuevas operaciones `CONTRATISTA`;
  - las consultas de población no incorporan contratistas;
  - foto, elegibilidad, carné y acceso no consideran un vínculo contratista como habilitante;
  - el enum y las migraciones históricas se conservan para no borrar ni convertir datos existentes;
  - `StudentPersonProjectionService` conserva reconocimiento histórico y no origina operaciones del canal personal.
- Archivos modificados: `AutomaticAccessService.php`, `PersonalCarnetService.php`, `PersonalEligibilityService.php`, `PersonalPhotoService.php`, `PersonalPopulationCommandService.php`, `PersonalPopulationQueryService.php`, `TicPersonalSyncService.php` y `tic_personal_sync_service_test.php`.
- Pruebas: lint de siete servicios; suites de sync compacto, población, foto, elegibilidad, carné, acceso e integración contractual: `OK`.
- Migraciones: ninguna.
- Pendiente inmediato: decisión 3/4 sobre el payload canónico de los eventos de carné.
- Revisión independiente: `APROBABLE`, sin hallazgos P0–P3. Se añadieron casos de contratista histórico excluido del listado, búsqueda exacta y payload; se preserva la búsqueda exacta de estudiantes sin vínculo laboral.

### 2026-09-01 — Decisión funcional 3/4: contrato canónico de carné

- Aprobación: mantener el JSON Schema v1 como única representación del callback de carné.
- SIGE:
  - `PersonalCarnetService` genera `CARNET_ENTREGADO` para la confirmación de entrega;
  - el evento elimina `status` y `comando` y entrega los siete campos exactos de `carnetData`;
  - `estado` representa el registro físico (`ACTIVO|INACTIVO`) y `motivo` sale del carné autoritativo.
- TIC:
  - `PersonalEventReceiverService` implementa validación cerrada del envelope y de `personaData`, `vinculoData`, `fotoData`, `estadoInstitucionalData` y `carnetData`;
  - se elimina la compatibilidad con `tipo_evento`, ya que viola `additionalProperties: false`;
  - `PersonalInboxProcessor` proyecta `EMITIDO`, `ENTREGADO` o `BLOQUEADO` a partir del estado físico y `entregado_en`, y conserva `vigencia_hasta`.
- Archivos modificados:
  - TIC: `developer/Services/PersonalEventReceiverService.php`, `developer/Services/PersonalInboxProcessor.php`, `developer/tests/personal_event_receiver_test.php`, `developer/tests/personal_inbox_processor_test.php`.
  - SIGE: `app/Services/PersonalCarnetService.php`, `tests/personal_carnet_service_test.php`.
- Artefacto de aplicación SIGE: `artifacts/m2_canonical_card_events_sige.patch`.
- Migraciones: ninguna; no se tocaron bases operativas.
- Pruebas ejecutadas: lint PHP; `personal_event_receiver_test.php`, `personal_inbox_processor_test.php` y SIGE `personal_carnet_service_test.php`: todas `OK`.
- Riesgos: callbacks de versiones antiguas quedan fail-closed; workers continúan apagados hasta conciliar órdenes/proyecciones históricas.
- Estado: implementación en revisión independiente. Cuenta regresiva funcional: 1 pregunta pendiente de 4.

### 2026-09-01 — Remediación de revisión decisión 3/4

- Dictamen inicial: `NO APROBABLE` (P1: mapper institucional; P2: colisión persona-versión; P2: métrica engañosa del worker; P3: cobertura).
- Archivos TIC adicionales modificados:
  - `developer/bin/sige_personal_inbox_worker.php`;
  - `developer/migrations/20260831_tic_sige_personal_core.sql`;
  - `developer/migrations/20260901_tic_sige_personal_inbox_persona_version.sql`;
  - `developer/tests/personal_inbox_worker_contract_test.php`;
  - `developer/tests/personal_inbox_person_version_mariadb_test.php`.
- Archivo SIGE adicional modificado: `tests/personal_carnet_service_test.php` mediante `artifacts/m2_review_card_tests_sige.patch`.
- Resultado: mapper único de carné, unicidad durable persona-versión, conflicto HTTP explícito, métricas/exit codes fiables y cobertura de todos los eventos canónicos.
- Migración necesaria: aplicar `20260901_tic_sige_personal_inbox_persona_version.sql` solo después de revisar duplicados; no se ejecutó sobre la base operativa.
- Pruebas: seis suites relevantes `OK`, incluida MariaDB real con reaplicación y rechazo seguro de duplicados históricos.
- Estado: remediado; segundo dictamen independiente solicitado. Cuenta regresiva funcional: 1 pregunta pendiente de 4.
- Segundo dictamen independiente: `APROBABLE`, cero P0–P3. Decisión 3/4 cerrada en código; despliegue condicionado a la migración incremental y conciliación previa si aparecen duplicados.

### 2026-09-01 — Cierre operativo del kiosco administrativo

- Estado: `COMPLETADO EN PRODUCCIÓN` para el carné aprobado terminado en `8967`; no equivale a habilitación general de la UI ni de workers.
- Decisión: cargo ID 2 `Administrativo`, dependencia ID 2 `Coordinación Académica`, ambos con snapshot/version TIC.
- Migración ejecutada: `20260901_tic_sige_personal_inbox_persona_version.sql`; índice único verificado.
- Datos conciliados: persona SIGE v3→v6, vínculo actualizado, carné ID 17 activo/entregado, proyección TIC real v6, dos simulaciones archivadas, outbox TIC 26 retirada y seis eventos SIGE sincronizados.
- Prueba de aceptación: acceso ID 116 `PERMITIDO` en terminal `RECONCILIACION-TIC-SIGE` usando el servicio real del kiosco.
- Revisión independiente: script puntual `APROBABLE` con workers apagados. Advisory lock, doble precondición, recepción limitada a IDs y reejecución idempotente comprobados.
- Pruebas: lint PHP/JS, ACK adversarial, controlador, dispatcher, receiver, processor, migración MariaDB, E2E sobre clones y regresiones SIGE.
- Pendiente bloqueante para habilitar la UI: soportar identidad estudiantil existente sin vínculo laboral mediante `VINCULO_CREAR` confirmado antes de `FOTO_ASOCIAR`; mantener la bandera apagada hasta su prueba y nueva revisión.
- Control de exposición verificado: `personal_ui_config.php` usa variables de entorno con valor exacto `1` y permanece fail-closed por defecto tanto para la UI como para plantillas.
- Pendiente operativo: revisar y conciliar el resto del outbox histórico antes de activar los tres workers.

### 2026-09-01 — Eliminación de simulaciones y desbloqueo de identidad dual

- Estado de datos: `COMPLETADO`; 30 órdenes históricas archivadas, outbox operativo 0, inbox 0 y una sola proyección real v6.
- Estado de código: `APROBABLE`; transporte `PERSONAL_SYNC`, snapshot servidor-servidor, UUID/versión, actor real, autoridad estudiantil y carrera de creación cerrados con pruebas MariaDB.
- Migración aplicada: `20260901_tic_sige_personal_outbox_archive.sql`.
- Pruebas de archivo: script real en dry-run/apply/replay, rollback deliberado, digest alterado, estado no terminal y preservación de fila posterior.
- Workers: ejecución manual de los tres scripts `OK` con colas vacías; programación persistente pendiente de autorización explícita.
- Feature flags: UI y plantillas permanecen fail-closed. Habilitar UI únicamente después de confirmar las tres tareas periódicas; plantillas requieren validación independiente.
- Cuenta regresiva operativa: 1 pregunta pendiente de 1.
- Autorización recibida para tareas `SYSTEM` cada minuto. Dos intentos directos fueron rechazados por permisos/UAC y no dejaron tareas parciales.
- Se creó `developer/bin/instalar_workers_personal.ps1`: requiere PowerShell elevada, registra y prueba los tres workers, habilita la UI solo si todos terminan con código 0, reinicia Apache y revierte tareas/flag ante fallo.
### 2026-09-01 — Checkpoint de endurecimiento operativo (workers aún apagados)

- Decisión vigente: no se habilita `TIC_SIGE_PERSONAL_UI_ENABLED` ni se registran workers hasta superar revisión independiente sin P0/P1.
- Estado real verificado: bandera Machine ausente, cero tareas TIC–SIGE de personal activas, colas TIC limpias y plantillas de personal deshabilitadas.
- Cambios preparados: preflight real `personal_transport_health.php`; wrapper con rotación/saneamiento; instalador con intención de runtime aislado en `C:\ProgramData\SCV\TicSigePersonal`; lectores compatibles de secretos protegidos en TIC y SIGE.
- Pruebas reales ejecutadas: lint PHP de health/configuraciones; parseo PowerShell de instalador/wrapper; contrato del instalador; consulta autenticada de la persona real `1001916903`; respuesta 422 del endpoint compacto; replay idempotente del último evento real; verificación de colas sin pendientes. Resultado funcional del preflight: `PERSONAL_TRANSPORT_HEALTH=OK`.
- No se crearon identidades, carnés, eventos ni proyecciones ficticias. El preflight usa exclusivamente lectura, validación rechazada antes de mutación y replay idempotente de un evento productivo existente.
- Revisión independiente: `NO APROBABLE` para activación. Bloqueantes: instalador y runtime PHP de XAMPP modificables por `Usuarios autentificados`; validación final debe ocurrir después de retirar variables Machine; credencial TIC aún literal; DACL y rollback de secretos requieren mayor robustez.
- Riesgo evitado: ejecutar código mutable de `htdocs` o `C:\xampp\php` como SYSTEM/LOCAL SERVICE. El intento anterior con SYSTEM fue revertido completamente.
- Pendiente inmediato: diseñar un despliegue firmado/hash-pinned desde origen admin-only y un runtime PHP inmutable, migrar también la credencial TIC, validar DACL recursiva/archivo de secretos y repetir health bajo la identidad final sin variables Machine. Solo después se podrán habilitar workers y UI.

### 2026-09-01 — Despliegue productivo de workers inmutables

- Estado: `COMPLETADO`; UI de personal habilitada y protegida por sesión/rol, plantillas de impresión continúan deshabilitadas.
- Runtime productivo: `C:\ProgramData\SCV\TicSigePersonal\runtime`, con PHP, extensiones, CA, configuración y fuentes mínimas fijadas por manifiestos SHA-256; no carga código, INI, DLL ni certificados desde `htdocs` o el XAMPP mutable.
- Credenciales: contraseña TIC y cinco secretos TIC–SIGE migrados a `secrets.json` con DACL protegida; variables Machine eliminadas; no quedan contraseñas literales activas ni comentadas en la configuración revisada.
- Tareas: `TIC-SIGE Personal Outbox`, `SIGE-TIC Personal Callback` y `TIC-SIGE Personal Inbox`; cuenta `SERVICIO LOCAL`, privilegio Limited, periodicidad de un minuto, ejecución permitida con batería y sin interrupción al cambiar a batería.
- Pruebas reales: health autenticado `PERSONAL_TRANSPORT_HEALTH=OK`; consulta de la persona real, endpoint compacto, replay idempotente del evento productivo y colas limpias; dos ejecuciones periódicas de outbox/callback/inbox con exit 0; HTTP directo de UI devuelve 401.
- Datos observados: outbox procesadas 0; callback sin eventos pendientes; inbox seleccionados/procesados/pendientes/fallidos 0. No se crearon datos, carnés, personas ni eventos simulados.
- Rollback probado: tres fallos previos de manifiesto/probe/check HTTP dejaron UI OFF, cero tareas/runtime parcial y restauraron las cinco variables Machine. El último ajuste por batería apagó la UI hasta obtener ejecuciones reales.
- Archivos principales modificados: configuraciones TIC/SIGE de secretos; requires por `__DIR__`; instalador, wrapper, health, probe y corrector de batería; pruebas contractuales; documentación operativa.
- Migraciones de base de datos: ninguna en este incremento operativo.
- Riesgo residual: las actualizaciones del runtime requieren regenerar manifiestos, revisión independiente y despliegue hash-pinned; no se debe editar directamente el paquete de ProgramData.
- Pendiente separado: habilitar plantillas de impresión solo después de disponer y validar los artes definitivos de docente/administrativo.
- Revisión independiente postdespliegue: `APROBABLE`, sin P0–P2. P3 no bloqueante: ejecutar smoke de solo lectura desde una sesión humana real con rol `CARNETIZACION`; no se fabricará ni simulará una sesión para cubrirlo.
