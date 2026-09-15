# Diagnóstico de contexto TIC–SIGE

Fecha de recuperación: 2026-08-30  
Alcance: lectura documental SIGE y revisión estática del código TIC.  
Estado: contexto base recuperado; ninguna implementación iniciada.

## Autoridades funcionales vigentes

- TIC es la fuente de verdad de estudiantes, inscripciones y fecha de registro.
- Q10 es la fuente de verdad del estado académico.
- SIGE es la fuente de verdad de carnés físicos y accesos.
- Docentes y administrativos se gestionan operativamente desde TIC, pero se almacenan autoritativamente en SIGE.
- Cargo y dependencia se administran exclusivamente en TIC por usuarios con rol activo exacto `CARNETIZACION`.
- SIGE recibe de esos catálogos solamente ID externo, nombre snapshot y versión.
- SIGE no puede modificar datos maestros de estudiantes pertenecientes a TIC.
- No se reutilizará sede como dependencia y no se crearán maestros TIC de docentes o administrativos.

## Documentos SIGE leídos completamente

- `DOC-385-ADR-Poblacion-Unificada-SIGE.md`: define `personas`, extensiones/vínculos, autoridad por dominio, personal autoritativo en SIGE, catálogos TIC versionados y un solo carné físico actual por persona.
- `DOC-670-Tareas-Pendientes-Cierre.md`: registra el estado y pendientes operativos del contrato estudiantil TIC–Q10–SIGE.
- `DOC-680-Informe-Integral-Implementacion-TIC-Q10-SIGE.md`: describe el contrato estudiantil desplegado, outboxes, comandos, eventos, conciliación, accesos y sondeo Q10.
- `DOC-930-Backlog.md`: mantiene pendiente la expansión de población a docentes y administrativos.
- `DOC-940-Backlog-Separado-TIC-SIGE.md`: separa responsabilidades y prohíbe reutilizar el formulario `FUNCIONARIO`, sede, programa o inscripción para personal.

## Arquitectura general observada en TIC

TIC es una aplicación PHP tradicional con vistas raíz, JavaScript jQuery/AJAX, reglas `mod_rewrite`, controladores por parámetro `case`, modelos SQL y un wrapper PDO. La integración nueva está concentrada en `developer/Services`, `developer/Models`, `developer/Controller`, `developer/bin` y `developer/migrations`.

Entradas principales:

- `.htaccess` enruta páginas, controladores legados y las APIs SIGE de salud, eventos de carné y accesos.
- `index.php` redirige según sesión; `login.php` autentica por AJAX; `dashboard.php` incluye `developer/security.php`.
- `gestionarinscripcion.php` y `javascripts/gestionarinscripcion.js` contienen el flujo productivo de carnetización estudiantil y la consola de órdenes de carné SIGE.

## Flujo de datos y autenticación

- La sesión web usa `SiigaBv`, datos de usuario, perfil y rol. El login compara SHA-1 y regenera el ID de sesión.
- El acceso a páginas se protege mediante `developer/security.php`, pero varios controladores legados no validan sesión ni autorización de manera uniforme por operación.
- Las APIs SIGE usan un Bearer token inbound independiente; las llamadas TIC → SIGE usan otro secreto outbound obtenido del entorno.
- Las consultas nuevas de integración usan PDO con consultas preparadas, transacciones, bloqueos y `ATTR_EMULATE_PREPARES=false`. El wrapper legado abre una conexión por consulta y transforma errores en `false` o resultados vacíos.

## Contrato estudiantil implementado

1. Las mutaciones relevantes de `inscripcion` generan filas en `sige_estudiante_outbox` mediante triggers.
2. `SigeStudentOutbox` reclama eventos en orden por inscripción, usa lease, reintentos y máximo de 12 intentos.
3. `SigeWebhook` reconstruye el perfil desde TIC, consulta Q10 y envía a SIGE. Solo incluye `fecha_registro` cuando `estado_inscripcion = 3` y la fecha es válida.
4. `Q10Gateway` carga/valida el directorio paginado; `Q10AcademicPoller` limita la población a estudiantes con carné proyectado o UID y exige doble ausencia antes de confirmar `INACTIVO`.
5. Las órdenes físicas se escriben en `sige_orden_outbox`, se envían con `expected_version` y solo se consideran aceptadas con ACK correlacionado.
6. SIGE publica eventos versionados; TIC conserva historial y una proyección técnica, ignora versiones obsoletas y reconcilia contra la autoridad SIGE.
7. SIGE envía accesos de forma idempotente por `sige_id`; TIC conserva auditoría y responde `accepted_ids`.

## Lógica core legada relevante

- `inscripcion` contiene identidad, programa, sede, lote, estado de proceso de carnetización, fecha, foto y campos heredados de RFID.
- Los estados legados de carnetización son 1 `EN PROCESO`, 2 `REALIZADO`, 3 `ENTREGADO` y 4 `CORRECCION`.
- TIC fija `fecha_inscripcion` al marcar entrega y la limpia al revertir ciertos estados; esto coincide con la autoridad TIC sobre fecha de registro, siempre que no se confunda con el estado físico autoritativo del carné.
- Existe una acción heredada `ActivarChip` que escribe directamente `chip_carnet` y `uid_rfid` en `inscripcion`; también existe el flujo nuevo de órdenes a SIGE. Antes de cambiar o retirar el flujo heredado debe definirse compatibilidad productiva.
- La categoría `FUNCIONARIO` está comentada, pero el JavaScript conserva una rama que trata el cargo como `programa.tipo_control = 2` y lo liga a una sede. No debe reactivarse ni reutilizarse.
- TIC conserva estructuras históricas `docente` y `pro_docente` usadas por autenticación/carga académica. No se ampliarán ni se tomarán como el nuevo maestro de personal SIGE.

## Catálogos de cargo y dependencia

Estado real:

- El cargo legacy vive en `programa` con `tipo_control = 2` y requiere `id_sedefk`.
- No se encontró un catálogo propio de dependencia en el código revisado.
- Los endpoints de cargo legacy no implementan todavía el contrato requerido de versión, snapshot ni autorización exacta `CARNETIZACION`.

Decisión para futuras tareas:

- El cargo legacy puede ser punto de partida transitorio únicamente si se desacopla semánticamente de sede y se versiona sin alterar el comportamiento productivo existente.
- Dependencia debe ser un catálogo TIC propio; nunca un alias de sede.
- La nueva consola de personal debe ser separada del formulario de inscripción.

## Migraciones existentes en TIC

- `20260828_doc640_sige_carnets.sql`
- `20260828_doc650_compat.sql`
- `20260828_doc650_reconciliation.sql`
- `20260828_doc670_access_sync.sql`
- `20260828_doc680_student_outbox.sql`
- `20260828_doc681_student_outbox_triggers.sql`
- `20260828_doc683_student_outbox_filter.sql`
- `20260828_doc684_q10_academic_poll.sql`

Migraciones futuras, aún no diseñadas ni ejecutadas:

- Catálogo TIC versionado de dependencias.
- Adecuación versionada y desacoplada del catálogo de cargos.
- Outbox/inbox y proyección técnica TIC de personal, sin almacenar su maestro ni su foto.
- Cambios SIGE para identidad poblacional, vínculos múltiples, snapshots de catálogos, auditoría, foto, presencia y veto histórico de RFID.

## Contradicciones y límites detectados

- No existe contradicción que justifique modificar ahora el comportamiento: el contrato estudiantil implementado y la expansión pendiente de personal son alcances distintos.
- El formulario heredado de funcionario y el cargo ligado a sede contradicen el diseño nuevo si se reutilizan. Permanecerán intactos e inactivos hasta implementar un flujo separado.
- El alias `CARNETIZACION → OPERATIVO` está limitado hoy a órdenes estudiantiles. No puede reutilizarse para personal ni catálogos, donde debe exigirse el rol exacto `CARNETIZACION`.
- Las tablas legadas de docentes existen y participan en módulos académicos. La prohibición vigente se interpreta como no crear ni ampliar maestros TIC para la nueva población; eliminar o migrar el legado sería un cambio funcional independiente que requiere autorización explícita.
- La escritura directa heredada del UID en `inscripcion` convive con la autoridad física de SIGE. Cualquier tarea que toque asignación de RFID deberá resolver primero si ese endpoint queda como compatibilidad, se convierte en comando o se retira.

## Diagnóstico y deuda técnica

- Autorización y CSRF no son uniformes en controladores legados.
- Contraseñas con SHA-1 y recuperación con prácticas antiguas.
- Escritura de archivos de foto desde datos base64 sin validación robusta observable.
- Dependencias frontend remotas y librerías antiguas.
- Dos modelos de carné (`SigeCarnet.php` y `SigeCarnetCompat.php`) elevan el riesgo de divergencia; el controlador productivo usa `SigeCarnetCompat`.
- La configuración/migración debe comprobarse contra MariaDB real antes de despliegue; no se consultó ni alteró la base de datos en esta fase.
- No hay repositorio Git disponible en la raíz entregada, por lo que el estado de cambios no pudo verificarse con `git status`.

## Riesgos de próximas implementaciones

- Romper carnetización estudiantil productiva al compartir rutas, formularios o tablas.
- Convertir sede o programa en dependencia de forma implícita.
- Persistir localmente maestros o fotos de personal en TIC.
- Otorgar permisos por alias en vez de rol exacto.
- Perder orden/idempotencia/versionado en comandos y eventos.
- Desactivar un carné cuando aún existe otro vínculo habilitante.
- Crear dos carnés actuales para una misma persona.
- Tratar excepciones manuales como presencia real o aforo exacto.

## Pruebas y verificaciones ejecutadas

- Lectura completa de los cinco documentos SIGE mediante agente de contexto dedicado.
- Inventario estático de archivos TIC excluyendo dependencias de terceros.
- Trazado estático de rutas, sesión, modelos de inscripción, servicios TIC–SIGE, Q10, APIs, workers y migraciones.
- Búsqueda estática de usos de docente, administrativo, cargo, dependencia, sede, UID y rol `CARNETIZACION`.
- Validación `php -l` satisfactoria en 13 archivos críticos de configuración, servicios, modelos y controladores de la integración.
- No se ejecutaron migraciones, escrituras de base de datos, llamadas HTTP, workers ni pruebas contra producción.

## Archivos modificados en esta fase

- `code_diagnostic_report.md`: creado como bitácora documental de contexto.

No se modificó código de aplicación, JavaScript, SQL, configuración ni datos.

## Pendientes conocidos

- Mantener como backlog operativo los pendientes de cierre estudiantil de DOC-670: backfill, pruebas E2E, usuario auxiliar, workers, semántica de eliminación, criterio Q10, recuperación manual, credenciales, respaldo y evidencias.
- Implementar la expansión de personal por tareas separadas y revisadas.
- Formular preguntas funcionales una por una cuando la tarea concreta las vuelva necesarias.
- Ejecutar un agente de revisión de código antes de cerrar cada implementación futura.

Preguntas funcionales documentales estimadas: 6. Cuenta regresiva inicial: 6 pendientes.

## Decisión 2026-08-30: mismo flujo de creación para estudiantes y personal

La creación de carnés de docentes y administrativos seguirá el mismo patrón operativo y técnico de los estudiantes, sin copiar su modelo académico.

Flujo común aprobado:

1. Operador autorizado inicia la operación en TIC.
2. TIC captura o consulta la identidad necesaria y registra una orden durable.
3. TIC envía a SIGE un comando autenticado, idempotente y versionado.
4. SIGE revalida la entidad, sus reglas de habilitación, el UID y la versión dentro de una transacción.
5. SIGE persiste el carné físico como fuente de verdad.
6. SIGE emite un evento versionado hacia TIC.
7. TIC conserva historial, ACK y proyección técnica para la operación y consulta.

Para personal, la llave será `persona_uuid`, nunca `id_inscripcion`. No se copiarán Q10, programa, sede, lote, etapa, matrícula, fecha estudiantil, vigencia semestral, pago de reactivación ni campos RFID de `inscripcion`.

### Diagnóstico de reutilización

Reutilizable o generalizable:

- Captura/lectura de RFID y confirmación explícita.
- UUID de operación, `expected_persona_version`, outbox, dispatcher, reintentos y ACK correlacionado.
- Inbox y outbox SIGE, eventos monotónicos, historial, proyección y conciliación.
- Control de una sola orden pendiente y un solo carné físico actual.
- Protección CSRF, autenticación, autorización de servidor y auditoría.

Debe permanecer separado:

- Pantalla y rutas de personal.
- Contrato basado en persona y vínculos.
- Política de elegibilidad de personal.
- Baja/reactivación institucional y evidencia de Talento Humano.
- Catálogos de cargo y dependencia.
- Manejo temporal de foto; la copia autoritativa queda en SIGE.

### Recomendación de arquitectura

- Crear una consola `Carnés de personal SIGE`, sin descomentar `FUNCIONARIO`.
- Separar en el backend las operaciones de población de la emisión física. Crear/actualizar persona y vínculos debe terminar antes de ordenar el primer carné.
- Compartir infraestructura mediante componentes parametrizables solo después de caracterizar la regresión estudiantil. No convertir directamente `SigeCarnetCompat` en un servicio genérico sin pruebas, porque depende de `inscripcion` y de reglas estudiantiles.
- Mantener tablas TIC únicamente de catálogo, orden, evento, proyección y auditoría. No persistir una tabla maestra de docentes/administrativos.
- Implementar cargo y dependencia como catálogos limpios y versionados de TIC. No usar sede como dependencia ni trasladar la relación heredada `programa.id_sedefk` al perfil.
- Exigir en cada endpoint de personal el rol activo exacto `CARNETIZACION`; no aplicar el alias estudiantil `OPERATIVO`.
- En SIGE, validar persona activa, vínculo habilitante, foto, RH, celular, versión y UID antes de emitir. Administrativo requiere cargo y dependencia; docente no los usa.
- Para personal, `vigencia_hasta` será `NULL`, no se consulta Q10 y la existencia de otro vínculo activo impide una baja indebida del carné.

### Plan de implementación propuesto

#### Fase 0 — Cierre contractual y caracterización

- Confirmar el significado funcional de “mismo flujo”.
- Congelar contratos estudiantiles actuales con pruebas de caracterización.
- Inventariar dependencias de `id_inscripcion`, Q10, UID legacy, lotes, estados y reportes.
- Definir versionado de API y matriz de errores recuperables/terminales.

#### Fase 1 — Modelo poblacional y contratos SIGE

- Implementar o completar `personas`, vínculos y extensión administrativa.
- Relacionar carnés y accesos por persona manteniendo compatibilidad estudiantil.
- Incorporar una sola credencial actual, veto histórico de UID y política por tipo de población.
- Crear comandos idempotentes de persona/vínculo/foto y carné, con inbox, outbox y auditoría.

#### Fase 2 — Catálogos TIC

- Crear catálogos versionados y auditables de cargo y dependencia.
- Implementar CRUD exclusivo para `CARNETIZACION`.
- Publicar hacia SIGE ID, nombre snapshot y versión.
- No modificar ni reutilizar sede como dependencia.

#### Fase 3 — Consola proxy de personal TIC

- Crear vista, JavaScript, controlador y servicios separados.
- Consultar, crear y actualizar personas/vínculos directamente en SIGE mediante comandos durables.
- Transferir la foto de forma autenticada y temporal, con limpieza compensatoria.
- Mostrar estado pendiente, procesado, rechazado o en conflicto sin almacenar el maestro localmente.

#### Fase 4 — Emisión de carné de personal

- Reutilizar UX de lectura RFID, confirmación y consulta de estado.
- Encolar por `persona_uuid` con `expected_persona_version`.
- Hacer que SIGE valide elegibilidad y cree el carné.
- Recibir evento, actualizar proyección técnica e historial TIC.
- Incorporar estados operativos equivalentes a en proceso/listo/entregado solo como proyección o evento, nunca como autoridad paralela.

#### Fase 5 — Ciclo de vida institucional

- Baja/reactivación con solicitud identificable de Talento Humano.
- Evaluación agregada de vínculos activos.
- Reemplazo por pérdida, robo o daño con UID nuevo.
- Veto permanente del UID retirado y auditoría before/after.

#### Fase 6 — Pruebas, revisión y despliegue

- Unitarias de política estudiante/docente/administrativo.
- Integración TIC → SIGE → TIC y replay idempotente.
- Concurrencia, versión obsoleta, UID duplicado/vetado y caída de SIGE.
- Administrativo sin cargo/dependencia; personal sin foto/RH/celular; doble vínculo.
- Regresión completa de estudiantes, Q10, accesos, reportes y workers.
- Revisión mediante agente de código, respaldo, migración reversible, despliegue gradual y conciliación posterior.

### Archivos previstos, aún no creados

- TIC: nueva vista y JavaScript de personal, controlador/modelos de catálogos, servicio/outbox de población, servicio de órdenes de personal, endpoints de eventos y migraciones de catálogos/proyección técnica.
- SIGE: migraciones poblacionales, servicios de persona/vínculos/foto, comandos de carné por persona, eventos y compatibilidad estudiantil.

### Preguntas de esta decisión

Estimación específica: 3 preguntas, a formular una por una. Cuenta regresiva actual: 3 pendientes.

### Decisión funcional confirmada: módulo separado en sidebar

Se confirma que la gestión y carnetización de docentes y administrativos tendrá una opción independiente en la sidebar de TIC y se implementará como módulo separado del flujo estudiantil.

Consecuencias aprobadas:

- No se descomentará ni reutilizará la categoría `FUNCIONARIO` de `gestionarinscripcion.php`.
- No se reutilizarán el formulario, controlador, rutas ni tabla `inscripcion` como almacenamiento de personal.
- La nueva opción compartirá únicamente componentes visuales y servicios técnicos seguros: captura de foto/RFID, confirmación, outbox, versionado, eventos, proyección y auditoría.
- El menú deberá mostrarse solo a perfiles autorizados y cada endpoint exigirá en servidor el rol activo exacto `CARNETIZACION`.
- El nombre de trabajo del módulo será `Carnés de personal SIGE`; su nombre visible definitivo puede ajustarse sin cambiar el contrato.

Cuenta regresiva actual de este diseño: 2 preguntas pendientes.

### Decisión funcional confirmada: asistente visual por pasos

La primera creación de personal y su carné se presentará al auxiliar como un asistente continuo dentro del módulo separado.

Pasos previstos:

1. Identidad y tipo de vínculo.
2. Datos obligatorios y, para administrativos, cargo y dependencia.
3. Captura y confirmación de fotografía.
4. Validación autoritativa de persona/vínculos en SIGE.
5. Lectura o ingreso de RFID y confirmación de emisión.
6. Seguimiento de la orden, resultado y entrega.

Aunque la experiencia sea continua, el backend conservará operaciones durables separadas para población, foto y carné. Cada paso tendrá estado persistente de integración, idempotencia y posibilidad de reintento seguro; TIC no persistirá el maestro ni la foto autoritativa.

Cuenta regresiva actual de este diseño: 1 pregunta pendiente.

### Decisión funcional confirmada: autorización exclusiva

Todo el módulo `Carnés de personal SIGE` será exclusivo para usuarios cuyo rol activo sea exactamente `CARNETIZACION`.

La restricción cubre:

- Visualización de la opción en la sidebar.
- Consulta de personas y proyecciones técnicas.
- Creación y actualización de docentes y administrativos.
- Administración de cargo y dependencia.
- Captura y transferencia de foto.
- Primera emisión, reemplazo y bloqueo de carnés.
- Baja y reactivación institucional.
- Consulta de órdenes, conflictos, historial y auditoría.

No se autoriza acceso excepcional a `PROGRAMADOR` y no se aceptan aliases como `OPERATIVO`. La autorización deberá comprobarse nuevamente en cada endpoint del servidor; ocultar la opción en la interfaz no será suficiente.

Cuenta regresiva final de este diseño: 0 preguntas pendientes.

## Backlog formal de implementación

El desglose ejecutable, sus dependencias, criterios de aceptación, pruebas y puertas de revisión se mantienen en `implementation_plan_tic_sige_personal.md`.

Orden aprobado de trabajo: contratos y regresión base; catálogos TIC; población SIGE; transporte durable; asistente; emisión/producción; ciclo institucional; operación y despliegue.

Estado al crear el backlog: ninguna migración ejecutada y ningún archivo funcional modificado.

## Auditoría del código real SIGE previa a implementación

La revisión del código real confirmó que el contrato estudiantil ya implementa idempotencia, hash canónico, comandos versionados, outbox de eventos, leases, backoff, ACK correlacionado, UID histórico único, aliases TIC y manejo compensatorio de fotografías.

No existen todavía `personas`, `persona_uuid`, vínculos docente/administrativo, snapshots de cargo/dependencia, foto por persona ni carné basado en persona. El esquema y los servicios continúan ligados a `estudiantes`, documento, Q10, fecha académica y vigencia semestral.

Decisión de implementación: extraer o generalizar los mecanismos existentes; no duplicarlos ni copiar las clases estudiantiles con sus reglas embebidas.

Primera implementación documental/técnica:

- Contratos JSON Schema v1 para persona, vínculo, carné, ACK y evento.
- Documento de rutas, secuencia, idempotencia, errores y exclusiones.
- Prueba de caracterización CLI del flujo estudiantil y separación del contrato personal.
- Ningún cambio funcional o de base de datos antes de la revisión independiente.

## Estado del incremento M0 tras primera revisión

La primera revisión independiente no encontró hallazgos críticos, pero pidió completar los contratos antes de aprobarlos. La remediación incorporó contrato ejecutable de foto, baja/reactivación institucional atómica, una sola `persona_version`, vínculo inmutable por UUID, eventos cerrados por tipo/entidad, ACK correlacionado con replay exacto y envelope separado para errores sin correlación.

La caracterización estudiantil ahora cubre reglas observables de payload, ACK, evento, Q10, orden/backoff del outbox, descarte de eventos obsoletos, idempotencia de accesos, reconciliación y doble confirmación de usuarios no encontrados. Además, los ejemplos válidos e inválidos se ejecutan contra JSON Schema Draft 2020-12.

No se modificó comportamiento productivo ni se creó/ejecutó ninguna migración. El detalle de archivos, comandos de prueba, riesgos y estados está en `implementation_plan_tic_sige_personal.md`.

### Ajustes derivados de la segunda revisión M0

El ciclo institucional quedó inequívoco para personas con vínculos múltiples: el comando declara alcance `VINCULO` o `PERSONA`, opera en una sola transacción y publica un único evento agregado por `persona_version`. Así se evita que una entrega parcial o desordenada pierda el cambio de persona, vínculo o carné.

La línea base ya no depende principalmente de búsquedas textuales. Una nueva suite ejecuta sobre dobles y SQLite en memoria los rechazos de comandos, la idempotencia de accesos, la doble ausencia Q10, la reconciliación que no decrementa versiones y la ruta real de descarte de evento obsoleto. Se extrajo una función pura del poller Q10 sin alterar su expresión ni resultado. No se accedió a bases productivas y no hay migraciones.

## Avance M1: catálogos TIC

Quedaron aprobados INT-0101, INT-0102 e INT-0103: DDL idempotente de cargo/dependencia/auditoría, servicio transaccional versionado y autorización exacta con CSRF. No se reutilizó sede, programa o inscripción y no se crearon maestros de docentes/administrativos.

La migración permanece sin ejecutar y todavía no hay rutas ni pantalla expuestas. El siguiente incremento es INT-0104, que debe integrar el guard aprobado en cada lectura/mutación y mapear 401/403/409/422 sin confiar en datos del cliente.

### Cierre M1

M1 fue aprobado integralmente sin hallazgos abiertos. Incluye migración pendiente, servicio versionado/auditado, guard de usuario–perfil–rol exacto, CSRF, API, controlador, interfaz y snapshot mínimo para vínculos administrativos. La funcionalidad queda fail-closed por feature flag apagado y sin opción de sidebar; no se ejecutaron migraciones ni se modificaron datos.

El siguiente trabajo es M2 en el proyecto SIGE: personas, vínculos, compatibilidad estudiantil y almacenamiento autoritativo. Requiere escritura en `C:\xampp\htdocs\Sige`, fuera del workspace TIC actual.

### Preflight M2

La migración poblacional debe ser aditiva: crear persona y extensión 1:1, hacer backfill, validar equivalencia y desplegar dual-write antes de relajar columnas legacy. No es seguro cambiar simultáneamente todas las FK de carnés, accesos e integración porque el código productivo aún escribe `estudiante_id`.

Antes del DDL definitivo debe resolverse la autoridad de los campos comunes cuando una misma identidad tenga simultáneamente extensión ESTUDIANTE y vínculo DOCENTE/ADMINISTRATIVO. Hasta esa decisión no se modifica SIGE.

Decisión M2-01: en una identidad dual, TIC mantiene la autoridad sobre todos los datos maestros estudiantiles. El canal operativo de personal no podrá modificarlos; SIGE conserva autoridad sobre vínculos, fotografía, carné físico y accesos.

Decisión M2-02: `persona_vinculos` mantendrá una sola fila corriente por persona y tipo, con unicidad `(persona_id, tipo_vinculo)`, versión monotónica y auditoría histórica separada. Se permiten simultáneamente DOCENTE y ADMINISTRATIVO.

Decisión M2-03: formatos definitivos alineados con M0/M1: tipo de documento 20, número 50, nombres/apellidos 150, IDs externos BIGINT positivos, snapshot 190 y versiones BIGINT positivas. Con esto INT-0201 queda desbloqueada.

### Diagnóstico posterior a INT-0201

La primera expansión poblacional ya existe en el árbol local de SIGE y no fue aplicada a `sige_db`. Mantiene intactas las rutas productivas basadas en estudiantes y agrega el modelo autoritativo necesario para personal sin reutilizar sede, programa, inscripción ni crear tablas maestras TIC de docentes/administrativos. `estudiantes.persona_id` permanece nullable hasta que INT-0202 incorpore dual-write; endurecerla en INT-0201 rompería el alta productiva actual.

La validación ejecutable sobre MariaDB 10.4.32 descubrió y corrigió dos incompatibilidades antes del despliegue: ausencia de `RANDOM_BYTES()` y un no-op dinámico que producía resultados pendientes en la segunda ejecución. La migración corregida se aplicó dos veces en un esquema temporal y demostró idempotencia del backfill y de su auditoría.

El límite vigente es deliberado: carnés, accesos e integración todavía referencian `estudiante_id`. El siguiente cambio seguro debe expandir esas entidades a `persona_id` con backfill y compatibilidad dual, reconciliar también las altas estudiantiles ocurridas después de INT-0201 y solo entonces imponer `NOT NULL`; no se debe habilitar el flujo de personal hasta cerrar esa transición y la recepción autoritativa de comandos.

### Diagnóstico INT-0202A

La expansión compatible ya está implementada en una migración no desplegada. Conserva todas las columnas legacy y añade `persona_id` nullable a carnés, accesos y outbox. La prueba sobre MariaDB demuestra que los escritores actuales siguen funcionando sin conocer la nueva columna, por lo cual 010 puede preceder al cambio de código sin detener altas, accesos o eventos estudiantiles.

El estado intermedio no es suficiente para personal: cualquier escritura posterior al backfill queda temporalmente sin `persona_id`. INT-0202B deberá resolver la persona dentro de la misma transacción y escribir ambos identificadores; después será necesaria una reconciliación de la ventana entre migración y dual-write.

La revisión de INT-0202A precisó dos límites adicionales. `version_estado` no puede reutilizarse como versión del stream personal, por lo que el outbox expandido solo se indexa —no se hace único— por `persona_id`. Además, una contradicción histórica entre la persona derivada del estudiante y la derivada del carné no se resuelve por precedencia implícita: queda nula y debe entrar a reconciliación explícita.

### Diagnóstico de escritores para INT-0202B

El dual-write comienza necesariamente en `StudentSyncService`: es el único escritor que puede crear un estudiante después del backfill y, por tanto, debe crear o resolver su persona, aplicar la autoridad TIC sobre datos maestros y enlazar `estudiantes.persona_id` dentro de la transacción del webhook. Agregar `persona_id` solamente a los INSERT de carné, acceso y outbox dejaría nuevas identidades sin origen válido.

Después deben adaptarse dos límites transaccionales independientes: `CardCommandService` escribe carné, versión y outbox en una transacción; `AccesoController` y `ManualAccessService` generan tres variantes de acceso. Por riesgo y capacidad de prueba, el cambio se divide en B1 (proyección de persona), B2 (carné/outbox) y B3 (accesos). La reconciliación masiva y los `NOT NULL` siguen perteneciendo a INT-0202C.

### Implementación diagnóstica INT-0202B1

`StudentSyncService` ahora delega la extensión poblacional a un servicio transaccional específico. La creación de estudiante y persona comparte la transacción del inbox del webhook, por lo que un fallo de documento, unicidad, auditoría o enlace revierte la operación completa. Una persona de personal preexistente se reutiliza por documento normalizado y conserva sus vínculos; al adquirir extensión estudiantil, TIC prevalece solamente sobre los campos maestros comunes.

El canal Q10 permanece aislado: puede provocar la creación del enlace faltante usando el maestro estudiantil ya almacenado, pero nunca acepta sus datos de entrada como actualización de identidad. B1 no modifica contratos de respuesta ni incorpora aún `persona_id` en carnés, outbox o accesos.

El contrato transaccional se aplica ahora en dos niveles: `StudentSyncService::sync()` rechaza antes de cualquier consulta/escritura si no existe transacción y `StudentPersonProjectionService` repite el guard antes de sus propios locks. Esto evita que usos futuros del servicio público, fuera del controlador actual, autoconfirmen estudiante o alias antes de fallar la proyección.

### Implementación diagnóstica INT-0202B2

El límite transaccional de `CardCommandService` ahora contiene persona, carné, versión estudiantil, comando y outbox. Antes de aplicar el comando asegura el enlace estudiante-persona mediante B1 y completa perezosamente carnés legacy nulos; las nuevas emisiones escriben ambos identificadores desde el inicio. El payload v1 permanece estudiantil y no se reescribe.

El productor secundario de outbox en `StudentSyncService` también escribe `persona_id` para `REACTIVACION_REQUERIDA`. La prueba de fallo deliberado en la unicidad legacy del outbox confirma que el reemplazo completo se revierte. El siguiente hueco de dual-write está limitado a los tres caminos de acceso automático/manual de B3.

La revisión añadió una frontera transaccional interna: `integracion_comandos` se conserva mediante savepoint, mientras cualquier mutación poblacional o física posterior se revierte antes de confirmar un rechazo. Asimismo, un carné con persona contradictoria se considera dato para reconciliación y produce 409; nunca se corrige por precedencia silenciosa ni se publica con otra persona en outbox.

### Implementación diagnóstica INT-0202B3

Los tres escritores de acceso ya transportan `persona_id`: lectura RFID automática, autorización sobre una denegación y autorización sin lectura por documento. La resolución se ejecuta con locks dentro de la misma transacción que inserta el evento, pero es deliberadamente de solo lectura respecto de población y carnés. Portería no puede crear una persona, enlazar un estudiante ni reparar un carné.

Una identidad contradictoria se deniega y registra sin atribución poblacional; una identidad todavía no proyectada tampoco se inventa. Las autorizaciones manuales ligadas preservan la persona —o la ausencia de persona— del evento automático original, mientras el par creado sin RFID escribe el mismo identificador ya proyectado en ambas filas. Esto mantiene inmutabilidad, idempotencia y compatibilidad con incidencias desconocidas.

B3 no generaliza aún la evaluación para docentes/administrativos. El controlador sigue aplicando las reglas estudiantiles de Q10, latch, cartera y vigencia, por lo que habilitar personal antes de implementar sus reglas propias sería incorrecto. También continúan pendientes la alternancia `FUERA`/`DENTRO`, la dirección inferida y el tratamiento explícito de `vigencia_hasta = NULL`.

La revisión independiente endureció dos límites. La evaluación automática ahora reside en un servicio transaccional ejecutable, manteniendo al controlador como adaptador HTTP y haciendo comprobable el rollback real. La idempotencia manual dejó de comparar solo algunos campos: un hash canónico liga el UUID al modo y a todos los datos semánticos de la solicitud, incluida la identidad solicitada por acceso relacionado o documento. Una migración compatible conserva nulos legacy y estos solo se completan tras reconstruir y validar una coincidencia segura.

El endurecimiento final distingue evidencia legacy suficiente de una inferencia insegura. Una operación histórica ligada a `acceso_id` puede reconstruirse por su referencia inmutable; una operación histórica sin RFID no conserva el documento snapshot original y, por tanto, su replay falla cerrado con exigencia de UUID nuevo. El servicio también normaliza `acceso_id` antes de decidir cualquier ruta, impidiendo divergencias entre el modo declarado por el hash y el ejecutado.

INT-0202B3 cerró con dictamen independiente aprobable y sin hallazgos abiertos. El siguiente límite técnico es la reconciliación: debe completar enlaces atribuibles, aislar contradicciones y endurecer únicamente las columnas cuya nulabilidad no sea funcional. En particular, `accesos.persona_id` debe conservar nulos legítimos para UID desconocido e incidencias sin identidad.

### Implementación diagnóstica INT-0202C1

El reconciliador poblacional es un proceso interno independiente del endpoint de conciliación TIC. Opera en dry-run por defecto, conserva una ejecución durable y clasifica los registros antes de permitir cambios. La aplicación usa el proyector B1 para estudiantes y únicamente propaga una persona coherente hacia carné, outbox y accesos legacy; cualquier conflicto bloqueante revierte el lote completo.

El diagnóstico confirmó que `estudiantes.persona_id` todavía no puede endurecerse: MariaDB valida cada sentencia y el alta B1 inserta temporalmente un nulo antes de enlazarlo en la misma transacción. C2 podrá endurecer carné y outbox después de dos verificaciones sin nulos nuevos, pero accesos, hash manual, vigencia y por ahora estudiantes deben continuar nullable.

La revisión reforzó el contrato operativo de C1: solo puede existir una ejecución mediante lock asesor global; el cierre auditable pertenece al mismo commit que las reparaciones; y la proyección estudiantil conserva autoridad TIC pero identifica al reconciliador y su `run_uuid` como ejecutor real. El dry-run ahora es una representación completa de candidatos, pendientes, nulos legítimos y conflictos, incluido outbox aún nulo con fuentes incompatibles.

La clasificación también anticipa efectos encadenados: un acceso cuyo estudiante y carné todavía carecen de persona aparece como `PENDIENTE_PROYECCION`, porque APPLY puede proyectar primero al estudiante, después al carné y finalmente atribuir el acceso dentro del mismo lote.

INT-0202C1 cerró con dictamen aprobable y sin hallazgos abiertos. El endurecimiento estructural queda deliberadamente separado: solo podrá ejecutarse después de un dry-run sin conflictos, aplicar todos los lotes necesarios y comprobar en ciclos posteriores que carnés y outbox no vuelven a producir nulos.

### Implementación diagnóstica INT-0202C2

El endurecimiento se materializa en dos migraciones independientes y recuperables. Cada una valida datos, estructura y dependencias antes de alterar la nulabilidad; después preserva y verifica la FK autoritativa. La separación evita presentar carnés y outbox como una unidad transaccional inexistente en MariaDB.

Los `estudiante_id` legacy permanecen obligatorios: relajarlos antes del contrato y stream personal permitiría filas que los lectores actuales no saben procesar. También permanecen nullable estudiantes —por el orden de B1—, accesos e hash manual —por incidencias legítimas— y vigencia —por la política aprobada para personal.

La primera revisión de C2 reveló que el preflight debía demostrar el cierre de C1 con evidencia durable, no mediante una condición operativa externa. Las dos migraciones ahora consultan la auditoría de reconciliación: bloquean cualquier ejecución activa y solo continúan cuando el último APPLY completado tiene cero mutaciones y cero incidencias posteriores.

También se eliminó la ambigüedad estructural. Los nombres de índices no son prueba suficiente: se validan columna, orden y unicidad, incluida la naturaleza generada de `persona_activa_id`. La recuperación de cada FK ausente y el rechazo del rollback 013 antes de 014 quedaron cubiertos sobre MariaDB real. La suite completa permanece verde y `sige_db` continúa sin las migraciones 013/014.

La segunda revisión precisó que una reconciliación válida no elimina necesariamente todos los nulos: accesos desconocidos, conflictos históricos y hashes manuales ambiguos se preservan por diseño. El gate final excluye esos contadores no bloqueantes y conserva las comprobaciones estrictas sobre estudiantes, carnés y outbox. Además, 013 compara la expresión normalizada de `persona_activa_id`, y su rollback detecta 014 por estructura aunque el registro de migración falte tras un corte entre commits implícitos.

INT-0202C2 cerró con dictamen independiente aprobable y sin hallazgos abiertos. Esto completa en código la transición estudiantil hacia identidad de persona para proyección, carnés, outbox y accesos compatibles; no equivale a un despliegue productivo ni habilita todavía el flujo de personal. La siguiente frontera debe implementar recepción y operación de docentes/administrativos conservando los `estudiante_id` legacy hasta que todos los lectores y contratos personales estén preparados.

### Implementación diagnóstica INT-0203

El registro legacy `integracion_comandos` no era reutilizable: exige documento estudiantil, usa `expected_version` académico-operativa y su replay cambia el HTTP a 200. INT-0203 incorpora un inbox personal único para comandos de persona y vínculo, junto con una outbox versionada por `(persona_id, persona_version)`. Esto separa el stream personal sin duplicar maestros ni mezclarlo con `estudiantes.version_estado`.

El servicio bloquea el agregado con `FOR UPDATE`, compara `expected_persona_version`, usa savepoint para conservar rechazos correlacionados y confirma efectos, doble auditoría de vínculo/agregado y evento en una sola transacción. La precedencia estudiantil se comprueba por la marca de autoridad y por la existencia real de la extensión, de modo que una inconsistencia histórica tampoco permite modificar maestros TIC desde personal.

La consulta autoritativa devuelve persona y vínculos paginados, sin campos académicos. Las rutas existen bajo autenticación Bearer pero permanecen fail-closed mediante feature flag apagado. El envío de la outbox a TIC no forma parte de este incremento: se implementará en M3 antes de habilitar el canal.

La primera revisión independiente impidió cerrar INT-0203. El listado actual no distingue población personal de la búsqueda exacta necesaria para descubrir una identidad estudiantil antes de anexarle un vínculo, y el formato de celular no está definido más allá de longitud. Ambos puntos requieren decisión funcional. Además, el control de snapshots debe recordar el máximo observado por catálogo/ID a través de personas y reasignaciones; el rollback 015 debe tolerar tablas ausentes; el esquema de consulta debe expresar las invariantes de vínculo; y faltan pruebas adversariales HTTP, cross-endpoint, concurrencia y estados DDL parciales.

La consulta quedó resuelta con dos modos explícitos: colección general restringida a personas con vínculo institucional y búsqueda exacta capaz de descubrir una identidad estudiantil sin vínculo. El segundo modo solo previene duplicados y permite anexar vínculos; mantiene intacta la autoridad TIC sobre maestros estudiantiles.

La segunda decisión cerró el celular colombiano en diez dígitos con prefijo `3`, almacenado normalizado. La remediación técnica reemplazó la comparación contra el vínculo corriente por un ledger high-water global de snapshots TIC: es una memoria de validación de versiones, no una tabla maestra administrable. Así, cambiar temporalmente de cargo no permite regresar después a una versión antigua del ID anterior, ni dos personas pueden introducir nombres diferentes para el mismo ID/versión.

El rollback 015 ahora tolera tablas ausentes y reaplicación; las consultas JSON preservan las invariantes de vínculo. La cobertura ejecuta el router apagado, los envelopes HTTP, replay entre rutas y carreras reales de persona, vínculo y documento. Ninguna migración fue aplicada a `sige_db`.

INT-0203 cerró con dictamen aprobable y sin hallazgos abiertos. INT-0204 quedó implementado en código el 2026-08-31 con foto autoritativa fuera del webroot, normalización JPEG 350×350, validación real, hash de entrada y salida, publicación compensatoria, versión agregada, auditoría, inbox/outbox y replay exacto. La migración 016 no se aplicó a `sige_db` y la ruta continúa deshabilitada. No se implementaron elegibilidad, carné personal ni interfaz TIC.

La primera revisión de INT-0204 detectó que `ROOT_PATH/storage` seguía dentro del `DocumentRoot` real de XAMPP. Se cambió el destino predeterminado a `C:\xampp\sige-storage\personas\fotos`, configurable por ambiente y rechazado mediante canonicalización real antes y después de crear directorios si cae bajo `htdocs`. También se endurecieron auditoría de entrada/salida, integridad del no-op, resultado incierto del commit, orientaciones EXIF, restricciones DDL, códigos del problem envelope y pruebas adversariales. El dictamen final fue `APROBABLE`, sin hallazgos P0–P3 abiertos.

Antes de INT-0205 se resolvió una incompatibilidad para identidades duales: TIC ya almacenaba celular y RH, pero no los enviaba a SIGE y el canal personal no podía completar maestros estudiantiles. El webhook estudiantil ahora los proyecta de forma compatible hacia `personas`; ausencia preserva, `null` explícito limpia y Q10 no escribe maestros. No hubo migración ni cambio de autoridad. Queda pendiente ejecutar una sincronización controlada para identidades duales históricas después del despliegue.

## Nuevo Contexto Recuperado (Integración TIC ↔ SIGE)

Durante la última revisión de contexto, se identificaron nuevos componentes de integración operativa y pruebas en TIC:

### 1. Pruebas de Integración (`sige_test.php`)
- Existe un script dedicado de pruebas unitarias/integración en `developer/sige_test.php` que verifica la conectividad y flujo de eventos.
- Evalúa cuatro aspectos fundamentales:
  1. Existencia de tablas de logs y proyecciones (`sige_log_accesos`, `sige_webhook_log`).
  2. Emisión de webhooks a SIGE usando la clase `SigeWebhook`.
  3. Inserción directa en el modelo `SigeAccesos` para control de duplicados.
  4. Pruebas HTTP directas vía cURL al endpoint local `/api/v1/sige/accesos` probando autenticación (401) y payload válido (201).

### 2. Modelo de Accesos y Logs (`Models/SigeAccesos.php`)
- `SigeAccesos` persiste los eventos de lectura RFID que envía SIGE (torniquetes) hacia TIC.
- Almacena campos como `sige_id`, `carnet_id`, `uid_leido`, `tipo_movimiento`, `resultado` y `motivo_rechazo`.
- Controla la idempotencia verificando `sige_id` para no duplicar los registros de log.

### 3. Configuración Centralizada (`Config/sige_config.php`)
- Centraliza las variables de entorno de la integración.
- Provee `SIGE_WEBHOOK_URL`, `SIGE_API_KEY` (Outbound), `SIGE_INBOUND_API_KEY`, `SIGE_CARNET_COMMAND_URL`, `SIGE_CARNET_RECONCILIATION_URL`, `Q10_API_KEY`.
- Todo se lee desde el entorno y tiene defaults seguros.

### 4. Controlador de Inscripción (`Controller/inscripcionController.php`)
- El controlador principal heredado tiene ahora los casos `EstadoCarnetSige` y `CrearOrdenCarnet` integrados para consultar el estado en SIGE e iniciar la creación autoritativa del carnet.
- Los dispatchers se invocan inmediatamente (`SigeOrdenDispatcher`, `SigeStudentOutbox`) tras guardar/actualizar inscripciones o carnets para minimizar latencia usando triggers de base de datos.
- Las funciones heredadas (como `ActivarChip`, `GuardarInscripcion`, `EditarInscripcion`, `EntregaCarnet`, `CambiarRecibido`) se mantienen, pero ahora se acoplan con el outbox intentando despacho asíncrono para informar a SIGE de inmediato.

## Contexto de Arquitectura y Negocio (Documentos SIGE 05-INTEGRACIONES)

Se ha revisado la documentación oficial de la integración (DOC-640 a DOC-680) ubicada en el proyecto SIGE, de donde se extraen las siguientes directrices y reglas de negocio irrefutables:

### 1. Fuentes de Verdad
- **TIC:** Fuente autoritativa de la identidad, inscripción, trámite y fecha de registro. Se encarga de originar los comandos para operaciones sobre carnets.
- **Q10:** Fuente de verdad del estado académico (`ACTIVO` o `INACTIVO`).
- **SIGE:** Fuente de verdad del carnet físico (UID RFID, estado operativo, bloqueos, versión, historial) y del control de accesos físicos.
- **Proyección TIC:** TIC mantiene una proyección de solo lectura del estado del carnet proveniente de SIGE para mostrarla en su UI, pero las decisiones de acceso recaen en SIGE.

### 2. Contrato y Comandos (Idempotencia)
- La comunicación es asíncrona mediante un patrón de **Inbox / Outbox** y reintentos (al minuto, a los 5, 15 y 30 mins).
- Todas las operaciones (`ASIGNACION`, `REEMPLAZO`, `BLOQUEO`, `REACTIVACION_AUTORIZADA`) enviadas desde TIC incluyen un `id_operacion` (UUID) generado por TIC y una versión esperada (`expected_version`).
- La reactivación de un carnet (tras un bloqueo) es un comando explícito originado en TIC, no un cambio automático que ocurra solo porque el estudiante vuelva a ser `ACTIVO` en Q10.
- Pérdida o robo exige obligatoriamente un UID RFID diferente (`REEMPLAZO`). SIGE rechaza la reutilización del mismo carnet robado/perdido.

### 3. Tareas Programadas y Sincronización
La integración depende de workers CLI corriendo cada minuto vía el Programador de Tareas de Windows (ejecutando como `SYSTEM`):
- `TIC-Sync-Students` (TIC -> SIGE): Envío de estudiantes.
- `TIC-Sync-CardOrders` (TIC -> SIGE): Envío de comandos de carnets.
- `TIC-Poll-Q10-Academic`: Consulta de estado académico en Q10.
- `SIGE-Sync-CardEvents` (SIGE -> TIC): Propagación del estado del carnet.
- `SIGE-Sync-AccessLogs` (SIGE -> TIC): Sincronización de accesos físicos.
Adicionalmente, hay una rutina de conciliación que corre (o debe correr) cada 30 minutos para garantizar consistencia.

### 4. Pendientes / Tareas Incompletas Identificadas (DOC-670 / DOC-680)
La implementación tiene áreas aún por cerrar:
1. **Backfill SIGE -> TIC:** Falta migrar proyecciones de carnets desde SIGE hacia TIC (existen 11 carnets en SIGE pero solo 6 proyecciones actuales en TIC).
2. **Conciliación Periódica:** Falta activar definitivamente el cron job de conciliación (`reconciliar_sige_carnets.php`).
3. **Healthcheck:** Falta configurar y persistir la tarea periódica `check_tic_health.php`.
4. **Reintento Manual:** Queda pendiente desarrollar un botón "Reintentar sincronización de datos con SIGE" en TIC para recuperación en caso de fallos.
5. **Autenticación Direccional:** Ambos sistemas usan claves cruzadas (`SIGE_OUTBOUND_API_KEY`, `SIGE_INBOUND_API_KEY`) para garantizar que la comunicación local está autenticada. Todo ocurre usando HTTPS o localhost con validación estricta de variables de entorno de máquina.

## Actualización 2026-08-31: cierre en código de M2

M2 quedó cerrado en código con población autoritativa, compatibilidad de identidad estudiantil, fotografía autoritativa y política interna de elegibilidad de personal en SIGE. La elegibilidad no crea endpoints ni modifica datos: comprueba persona activa, RH, celular, foto físicamente íntegra y al menos un vínculo habilitante. Para administrativos contrasta ID, snapshot y versión con el high-water técnico conocido; no replica el catálogo ni utiliza sede.

La evaluación bloqueante exige una transacción activa y mantiene locks sobre persona, vínculos, foto y snapshots observados para su reutilización segura durante M5. Una prueba MariaDB confirmó exclusión concurrente y ausencia de mutaciones. La suite final pasó 18/18 en SIGE y 7/7 en TIC; el agente revisor emitió dictamen `APROBABLE` sin P0–P3.

No se creó migración para elegibilidad ni se aplicaron 009–016 en producción. La bandera de personal continúa apagada. El siguiente corte es M3: transporte durable TIC→SIGE, recepción de eventos y proyección estrictamente técnica en TIC.
## Diagnóstico y corrección del kiosco para personal — 2026-09-01

### Hallazgo confirmado

- El lector sí capturó el UID administrativo. SIGE auditó la lectura como `DENEGADO/NO_REGISTRADO`, sin `carnet_id` ni `persona_id`.
- El UID solo existía en la proyección técnica y en payloads históricos de TIC; no existía en `SIGE.carnets`.
- TIC registraba los comandos de carné directamente como `EXITOSO` y modificaba `sige_personal_proyeccion` en la misma transacción. SIGE nunca recibía la asignación, por lo que el kiosco no podía reconocerla.
- Aunque el carné existiera, `AutomaticAccessService` comparaba `vigencia_hasta = NULL` contra la fecha actual. En PHP esa comparación clasificaba la vigencia indefinida del personal como vencida.

### Fallos de integración asociados

- El dispatcher enviaba todos los comandos al endpoint de población; `CARNET` requiere `/api/v1/tic/carnets/eventos`.
- El endpoint de carné responde `ACEPTADO` (202), pero TIC solo aceptaba `PROCESADO` como ACK válido.
- SIGE emite el campo canónico `tipo`; el receptor TIC exigía el alias inexistente `tipo_evento`.
- Receptor, procesador y migración usaban nombres incompatibles para las columnas del inbox. El esquema desplegado y la migración vigente usan `version_estado`, `payload` y `error_proceso`.
- No existía un worker CLI para despachar `sige_personal_outbox`.
- La base operativa contiene `sige_personal_outbox.comando`, pero la migración base no la declara; se añadió una migración idempotente para instalaciones limpias o rezagadas.
- La prueba de acceso reveló que el servicio omitía validar contradicciones estudiante/persona cuando `carnets.persona_id` ya estaba poblado.

### Corrección realizada

- Los comandos de carné quedan `PENDIENTE`; TIC no modifica su proyección hasta recibir el evento de SIGE.
- El dispatcher enruta `CARNET` a su endpoint dedicado y acepta ACK `ACEPTADO` o `PROCESADO`.
- El receptor normaliza `tipo` y conserva compatibilidad temporal con `tipo_evento`.
- Receptor y procesador fueron alineados con el esquema desplegado. La única migración añadida corrige la deriva estructural de `outbox.comando`; no requiere ejecución en la base actual porque la columna ya existe.
- Se creó `developer/bin/procesar_sige_personal_outbox.php` con exclusión mutua.
- El kiosco admite vigencia indefinida y siempre resuelve la identidad física mediante `AccessPersonResolver`.

### Riesgos y pendientes

- No se habilitó el worker en el Programador de tareas: existen órdenes históricas y proyecciones simuladas que deben conciliarse antes de procesar automáticamente.
- SIGE tiene una persona administrativa real elegible pero sin carné. TIC tiene dos proyecciones simuladas entregadas. Asociar cualquiera de esos UID a la persona real requiere confirmación operativa; no se infirió.
- El contrato JSON de evento define `CARNET_ENTREGADO` y un estado físico `ACTIVO/INACTIVO`, mientras el servicio actual emite `ENTREGA_CONFIRMADA` y estados UI `EMITIDO/ENTREGADO`. No se cambió este comportamiento por ser una contradicción funcional fuera del defecto puntual del kiosco.
- Las pruebas M6 históricas no son ejecutables contra el servicio SIGE actual: cargan el servicio sin bootstrap y modelan tablas/contratos antiguos. Deben sustituirse por una E2E MariaDB real antes de habilitar workers.

### Remediación posterior a revisión independiente

- Se eliminó también la mutación optimista de población en `personalController.php` y el guardado de fotografías locales. El alta/vínculo solo genera outbox; SIGE conserva la foto autoritativa.
- El worker SIGE de callbacks acepta cualquier HTTP 2xx únicamente si recibe JSON `PROCESADO` y el `id_evento` coincide exactamente.
- El worker TIC de inbox quedó restringido a CLI y usa lock fuera del webroot.
- El kiosco exige que un carné sin estudiante pertenezca a una persona `ACTIVA` con al menos un vínculo laboral `ACTIVO`; no aplica Q10 a personal.
- La revisión independiente permanece abierta por dos contradicciones de contrato: el snapshot consolidado `PERSONA_SINCRONIZADA` no existe en el contrato v1 y el evento real de carné no coincide con `carnetData` v1. Resolverlas requiere decisión funcional antes de habilitar workers.
- Decisión funcional aprobada para población: se eliminó `PERSONA_SINCRONIZADA` y la consolidación. SIGE conserva en `PENDIENTE` los eventos canónicos `PERSONA_*`, `VINCULO_*` y `FOTO_ACTUALIZADA` en orden estricto de `persona_version`; el replay compacto no duplica eventos.
- Revisión independiente: el cambio incremental es aprobable. Hallazgo residual: el adaptador compacto todavía admite `CONTRATISTA`, pero el contrato v1 de vínculo solo acepta `DOCENTE|ADMINISTRATIVO`. Un evento contratista sería rechazado por el receptor cerrado y podría bloquear la secuencia posterior.
- Decisión funcional aprobada: `CONTRATISTA` fue retirado de los flujos operativos de población, consulta, foto, elegibilidad, carné y acceso. El enum y la proyección estudiantil histórica no se redujeron para evitar una migración destructiva o reinterpretar registros preexistentes.
- Revisión final de la decisión 2/4: `APROBABLE`, sin P0–P3. La búsqueda exacta exige estudiante o vínculo permitido, el listado y los snapshots excluyen contratistas históricos, y las filas históricas permanecen intactas.
- El receptor ya rechaza envelopes básicos y tipos fuera de v1, pero la revisión exige validación completa del JSON Schema: `id_operacion`, propiedades adicionales, fecha Bogotá, correlación tipo/entidad y `data` específico. Esta validación se cerrará después de resolver el formato de carné restante para no codificar dos veces un contrato contradictorio.
- La migración `comando` fue ejecutada dos veces sobre una tabla temporal MariaDB con filas históricas de carné y población; backfill y `NOT NULL` pasaron sin modificar tablas operativas.

### Decisión funcional 3/4 — evento canónico de carné (2026-09-01)

- Se adopta sin variantes el `carnetData` del JSON Schema v1. SIGE publica el estado físico `ACTIVO|INACTIVO`, no los estados visuales de TIC.
- La entrega publica `CARNET_ENTREGADO`; se elimina del callback el tipo interno `ENTREGA_CONFIRMADA`.
- El evento contiene exactamente: `sige_carnet_id`, `uid_rfid`, `estado`, `motivo`, `fecha_emision`, `vigencia_hasta` y `entregado_en`. Se retiraron `status` y `comando` del evento porque no pertenecen al contrato cerrado.
- TIC deriva su estado visual: `ACTIVO` sin entrega → `EMITIDO`; `ACTIVO` entregado → `ENTREGADO`; `INACTIVO` → `BLOQUEADO`.
- El receptor TIC valida completamente el envelope, correlación tipo/entidad y payload específico del esquema v1. Ya no acepta el alias `tipo_evento` ni propiedades adicionales.
- La contradicción de carné y la deuda de validación completa quedan resueltas. Las menciones anteriores que las describen como pendientes son historial del diagnóstico, no estado vigente.
- Archivos TIC: `developer/Services/PersonalEventReceiverService.php`, `developer/Services/PersonalInboxProcessor.php`, `developer/tests/personal_event_receiver_test.php`, `developer/tests/personal_inbox_processor_test.php`.
- Archivos SIGE: `app/Services/PersonalCarnetService.php`, `tests/personal_carnet_service_test.php`.
- Migraciones: ninguna. Datos productivos y workers: no modificados.
- Pruebas: lint de los cuatro archivos de servicio/prueba principales; receptor de eventos, procesador de inbox y servicio de carné SIGE: `OK`.
- Riesgo restante: los emisores históricos que todavía produzcan `tipo_evento`, `ENTREGA_CONFIRMADA` o payloads parciales serán rechazados deliberadamente; antes de habilitar workers deben conciliarse/archivarse las órdenes simuladas.
- Preguntas funcionales: 1 pendiente de 4, relativa a la conciliación del UID físico real.

### Remediación de revisión independiente — decisión 3/4

- Primer dictamen: `NO APROBABLE` con 1 P1, 2 P2 y 1 P3.
- P1 corregido: el carné anidado en `ESTADO_INSTITUCIONAL_CAMBIADO` usa el mismo mapper físico→UI que los eventos directos y persiste también `vigencia_hasta`.
- P2 corregido: el contrato durable exige unicidad `(persona_uuid, version_estado)`; el receptor responde `PERSON_VERSION_CONFLICT` ante una colisión y el procesador no descarta silenciosamente duplicados históricos.
- P2 corregido: el procesador devuelve un resumen con seleccionados, procesados, pendientes y fallidos; el worker finaliza con código 2 si quedan pendientes/fallos y con 1 ante una excepción fatal.
- P3 corregido: se añadieron pruebas de reemplazo, baja y reactivación institucional, colisión persona-versión, migración MariaDB idempotente/fail-closed y estructura de los cuatro eventos SIGE.
- Migración nueva pendiente de despliegue: `developer/migrations/20260901_tic_sige_personal_inbox_persona_version.sql`. Antes de aplicarla debe conciliarse cualquier duplicado histórico; el script se detiene sin eliminar datos si los encuentra.
- Pruebas posteriores: receiver, processor, worker contract, migración MariaDB, carné SIGE e integración contractual SIGE: `OK`.
- Segundo dictamen independiente: `APROBABLE`, sin hallazgos P0–P3. Condición operativa: desplegar primero la unicidad persona-versión y conciliar cualquier duplicado que haga fallar cerrada la migración antes de activar el worker.

## Conciliación productiva del carné administrativo — 2026-09-01

### Decisiones aplicadas

- El UID físico aprobado terminado en `8967` corresponde a la persona administrativa real con documento `1001916903` y UUID SIGE `77cc18e9-c856-4a87-8b18-ef4527a74cfe`.
- Cargo TIC autoritativo: ID 2, snapshot `Administrativo`, versión 1. Dependencia TIC: ID 2, snapshot `Coordinación Académica`, versión 1.
- Las dos proyecciones simuladas se conservaron íntegramente en auditoría antes de retirarlas. No se activaron workers globales.

### Resultado operativo

- SIGE corrigió el vínculo administrativo, creó el carné autoritativo ID 17, lo marcó entregado y elevó `persona_version` de 3 a 6.
- TIC recibió y procesó en orden seis eventos canónicos; la proyección real quedó `ENTREGADO`, versión 6 y sin inbox pendiente.
- El acceso real de verificación ID 116, ejecutado mediante el mismo `AutomaticAccessService` del kiosco, fue `PERMITIDO`.
- Las dos simulaciones quedaron auditadas con `ARCHIVAR_SIMULACION`; la orden histórica TIC 26 quedó `FALLIDA` para impedir su reenvío.
- Los seis eventos de SIGE quedaron `SINCRONIZADO`.

### Archivos modificados o creados

- TIC: `personalController.php`, `SigePersonalClient.php`, `sige_config.php`, `PersonalOutboxDispatcher.php`, `PersonalInboxProcessor.php`, `gestionarpersonal.js` y sus pruebas contractuales.
- Operación: `artifacts/reconcile_admin_card_20260901.php`, `artifacts/verify_admin_card_20260901.php` y `developer/tests/reconcile_admin_card_clone_test.php`.
- SIGE: `TicPersonalSyncService.php`, `PersonalCarnetService.php` y sus pruebas, aplicados mediante los artefactos de parche documentados.

### Migración y pruebas

- Se aplicó en TIC `developer/migrations/20260901_tic_sige_personal_inbox_persona_version.sql`. El índice único `(persona_uuid, version_estado)` está presente; no se encontraron duplicados bloqueantes.
- Pasaron lint PHP, `node --check`, pruebas de controlador/catálogos, ACK estricto, dispatcher, receiver, processor, migración MariaDB, E2E clonada con dos ejecuciones y suites SIGE de población, sync y carné.
- La E2E confirmó que una reejecución no duplica auditoría, eventos, carné, proyección ni acceso y no procesa eventos ajenos.

### Riesgos y pendientes vigentes

- Los workers TIC→SIGE, callback SIGE→TIC e inbox TIC continúan apagados hasta conciliar las demás órdenes históricas; no deben activarse como parte de esta corrección puntual.
- La UI general de personal sigue detrás del feature flag. La revisión detectó un caso pendiente: una identidad estudiantil existente sin vínculo laboral necesita secuencia `VINCULO_CREAR → ACK/version → FOTO_ASOCIAR`; no debe habilitarse la UI hasta implementar y probar esa orquestación.
- El feature flag fue corregido tras auditoría: UI y plantillas quedan apagadas por defecto y solo aceptan habilitación explícita mediante variable de entorno con valor exacto `1`.
- La advertencia CLI de `ini_set()` proviene de cargar dos bootstraps con salida previa durante las pruebas combinadas; no afectó transacciones ni resultados, pero conviene limpiar el bootstrap de herramientas operativas.

## Cierre de simulaciones y caso estudiante→personal — 2026-09-01

- El outbox operativo TIC quedó vacío. Las 30 órdenes históricas no autoritativas fueron verificadas mediante manifiesto SHA-256, archivadas recuperablemente en `sige_personal_outbox_archivo` y retiradas en una transacción.
- La proyección operativa contiene únicamente la persona administrativa real conciliada, versión 6; no quedan proyecciones simuladas.
- El flujo de alta/actualización usa exclusivamente `PERSONAL_SYNC` como comando de transporte y el adaptador compacto SIGE como orquestador real `persona → vínculo → foto`.
- Para personas existentes, TIC rehace la consulta servidor-servidor y no confía en identidad enviada por el navegador. SIGE exige UUID, versión esperada y auxiliar real, rechaza identidad estudiantil divergente y no permite que una carrera `PERSONA_CREAR` se convierta en actualización.
- La prueba MariaDB cubre: estudiante existente sin vínculo, vínculo administrativo, foto posterior, eventos v2/v3/v4, actor real, intención obsoleta, manipulación del maestro y carrera de creación.
- Se eliminó la credencial simulada `DEV_API_KEY`; solo se utilizan credenciales outbound reales y el cliente falla cerrado.
- Los tres workers pasan ejecución manual con colas vacías. La creación persistente de tareas del Programador de Windows quedó pendiente de autorización explícita por usar cuenta `SYSTEM`, privilegio alto y frecuencia de un minuto.
- La UI permanece apagada hasta activar y verificar outbound TIC→SIGE, callback SIGE→TIC e inbox TIC. Plantillas continúan separadas y apagadas.
- Revisión independiente: código y retiro histórico `APROBABLE`, sin P0–P3 abiertos. Preguntas operativas pendientes: 1 de 1.
- El usuario autorizó las tres tareas como `SYSTEM`; Windows negó el registro desde la sesión no elevada y no creó tareas parciales. Se dejó un instalador fail-closed con rollback en `developer/bin/instalar_workers_personal.ps1`, pendiente únicamente de ejecución bajo UAC administrativo.
## Diagnóstico operativo TIC–SIGE de personal — 2026-09-01

### Arquitectura general y flujo

La integración de personal usa outbox TIC, receptor autoritativo SIGE, outbox de eventos SIGE, webhook TIC e inbox/proyección TIC. Los tres workers requeridos son salida TIC, callback SIGE y entrada TIC. La UI está protegida por rol/sesión y una bandera operativa que permanece apagada mientras el transporte no tenga ejecución periódica segura.

### Diagnóstico de seguridad de ejecución

La lógica y el transporte real pasan el preflight, pero la plataforma Windows/XAMPP concede `Modify` a usuarios autenticados sobre `htdocs`, `php.exe`, `php.ini` y extensiones. Programar esos binarios o scripts como SYSTEM o LOCAL SERVICE crea un cruce de identidad explotable. Por ello ninguna tarea quedó instalada y la UI continúa deshabilitada.

Las variables de entorno Machine tampoco son un almacén de secretos adecuado para claves de integración. Se preparó lectura compatible desde un archivo protegido, pero la migración no debe ejecutarse hasta disponer de runtime inmutable, DACL recursiva exacta, rollback completo y prueba bajo la identidad final.

### Recomendación vinculante

Desplegar un paquete autocontenido fuera de DocumentRoot desde un origen verificado por firma o SHA-256, incluir un PHP runtime con escritura exclusiva de administradores/SYSTEM, conceder a la identidad worker solo lectura/ejecución y separar logs con escritura acotada. Migrar todas las credenciales —incluida la base TIC— al almacén protegido, retirar las variables Machine, reiniciar Apache y ejecutar el preflight real en ese estado final antes de habilitar la UI.

### Resultado de implementación

La recomendación fue aplicada. El runtime y secretos viven fuera de DocumentRoot, las tareas se ejecutan como Servicio Local con permisos limitados, PHP usa un INI aislado y rutas verificadas, y los logs tienen escritura separada y rotación. El equipo reportó batería activa; se eliminaron las restricciones predeterminadas del Programador y se confirmó una ejecución nueva real de cada worker antes de habilitar la UI. Estado final: tres tareas `Ready`, `LastTaskResult=0`, health real correcto y acceso no autenticado rechazado con HTTP 401.
