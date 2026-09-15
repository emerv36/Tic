# Contrato TIC–SIGE para personal v1

Estado: vigente; población persona/vínculo implementada en INT-0203  
Versión: `1.0`  
Fecha: 2026-08-30

## Alcance

Este contrato agrega docentes y administrativos sin modificar el contrato estudiantil vigente. TIC inicia las operaciones; SIGE es autoridad de persona, vínculos, foto, carné físico y accesos.

Los contratos usan `persona_uuid`. `numero_documento` solo es un atributo de identidad y no reemplaza el identificador inmutable.

## Esquemas

- `persona-command.schema.json`: alta y actualización de identidad.
- `vinculo-command.schema.json`: alta y actualización de vínculos.
- `estado-institucional-command.schema.json`: baja o reactivación institucional atómica.
- `foto-command.schema.json`: metadatos JSON de la carga multipart idempotente.
- `carnet-command.schema.json`: emisión, reemplazo, bloqueo y entrega.
- `integration-ack.schema.json`: respuesta correlacionada por operación.
- `integration-problem.schema.json`: error HTTP anterior a una correlación válida.
- `integration-event.schema.json`: evento versionado SIGE → TIC.
- `persona-query-response.schema.json`: consulta autoritativa paginada de persona y vínculos.

Todos los comandos requieren:

- `contract_version = 1.0`.
- UUID v4 en `id_operacion`.
- `expected_persona_version` no negativa; es la versión única del agregado persona.
- fecha Bogotá con formato `Y-m-d H:i:s`.
- actor con rol exacto `CARNETIZACION`.
- idempotencia por UUID y hash canónico del payload completo.

Un UUID repetido con el mismo hash devuelve exactamente el mismo código HTTP y ACK almacenado. El mismo UUID con un payload diferente devuelve HTTP 409 `ID_OPERACION_CONFLICTIVO`.

`persona_version` es monotónica y ordena todas las mutaciones de identidad, vínculos, foto y carné de una persona. SIGE la incrementa una sola vez por transacción aceptada. TIC debe usar el último valor confirmado como `expected_persona_version`; no mezcla este stream con `estudiantes.version_estado` del contrato legacy.

## Endpoints candidatos

| Método | Ruta | Esquema |
|---|---|---|
| POST | `/api/integracion/tic/personas/comandos` | persona |
| POST | `/api/integracion/tic/personas/vinculos/comandos` | vínculo |
| POST | `/api/integracion/tic/personas/estado/comandos` | baja/reactivación institucional |
| POST | `/api/integracion/tic/personas/{persona_uuid}/foto` | multipart autenticado + `metadata` conforme a foto |
| POST | `/api/integracion/tic/personal/carnets/comandos` | carné |
| GET | `/api/integracion/tic/personas` | consulta paginada autoritativa |
| POST | `/api/v1/sige/eventos-personal` en TIC | evento SIGE → TIC |

Las rutas se mantienen separadas de los endpoints estudiantiles para permitir despliegue gradual y regresión independiente.

La consulta admite `page`, `page_size` (máximo 100), `persona_uuid` y búsqueda documental exacta mediante el par `tipo_documento` + `numero_documento`. No expone estado académico, inscripción, programa, sede, lote ni fecha estudiantil.

El listado sin filtro exacto incluye únicamente personas con vínculo DOCENTE o ADMINISTRATIVO. Una búsqueda exacta sí puede descubrir una identidad estudiantil todavía sin vínculo para evitar duplicarla; esa consulta no autoriza modificar sus maestros. El celular se normaliza retirando espacios, guiones y paréntesis y se almacena como diez dígitos colombianos con prefijo `3`.

## Secuencia de primera emisión

1. TIC envía `PERSONA_CREAR` o consulta la persona existente.
2. SIGE confirma `persona_uuid` y `persona_version`.
3. TIC crea el vínculo con `expected_persona_version`.
4. Para administrativos, TIC envía cargo y dependencia como ID, nombre snapshot y versión.
5. TIC transfiere la foto; SIGE valida y almacena la copia autoritativa.
6. TIC envía `ASIGNACION` de carné por `persona_uuid`.
7. SIGE revalida persona, vínculos, foto, RH, celular, UID y versión en una transacción.
8. SIGE persiste el carné con `vigencia_hasta = NULL`, registra auditoría y encola evento.
9. TIC recibe el evento idempotente y actualiza únicamente su proyección técnica.

## Reglas por población

### Docente

- No usa cargo, dependencia, programa, sede, lote ni Q10.
- Requiere persona activa, vínculo docente activo, RH, celular y foto.

### Administrativo

- Requiere cargo y dependencia activos.
- SIGE almacena snapshots/versiones; nunca administra esos catálogos.
- No usa programa, sede, lote ni Q10.

### Vínculos múltiples

- Una persona puede tener vínculos docente y administrativo simultáneos.
- Existe un solo carné físico actual por persona.
- Desactivar un vínculo no bloquea el carné si queda otro vínculo habilitante.

## Fotos

La foto no se incluye como base64 en la outbox. TIC usa un endpoint multipart autenticado con dos partes: `metadata`, conforme a `foto-command.schema.json`, y `foto`, con el binario. SIGE reserva `id_operacion` y hash canónico de metadatos antes de publicar, valida MIME real, tamaño, dimensiones y SHA-256, publica de forma compensatoria y elimina temporales. TIC elimina su temporal después del ACK. Un replay exacto devuelve el ACK almacenado; un UUID con metadatos o hash distintos devuelve 409.

SIGE normaliza la entrada JPEG/PNG a JPEG cuadrado de 350×350 píxeles, calidad 85, corrige orientación EXIF cuando está disponible y elimina metadatos al recodificar. `archivo.sha256`, `mime_declarado` y `bytes` identifican la entrada transferida; el evento `FOTO_ACTUALIZADA` informa `sha256`, `mime` y `bytes` del JPEG autoritativo publicado. La auditoría conserva ambas identidades sin exponer la ruta interna.

## Baja y reactivación institucional

`BAJA_INSTITUCIONAL` y `REACTIVACION_INSTITUCIONAL` son comandos agregados, no una secuencia cliente de vínculo + carné. El campo `alcance` determina si la solicitud afecta un `VINCULO` concreto (requiere `vinculo_uuid`) o toda la `PERSONA` (exige `vinculo_uuid = null` y afecta todos sus vínculos habilitantes). Dentro de una sola transacción SIGE:

1. valida `expected_persona_version` y la solicitud de Talento Humano;
2. bloquea la persona, el carné actual y el vínculo indicado o todos los vínculos de la persona, según el alcance;
3. cambia el vínculo objetivo o todos los vínculos afectados;
4. recalcula vínculos habilitantes;
5. bloquea o conserva el carné según el resultado agregado;
6. establece `persona.estado = INACTIVA` cuando no queda ningún vínculo habilitante, o `ACTIVA` cuando la reactivación deja al menos uno;
7. incrementa `persona_version`, audita before/after y encola exactamente un evento agregado `ESTADO_INSTITUCIONAL_CAMBIADO` con el resultado completo de persona, vínculos afectados y carné.

El evento agregado evita resultados parciales con la misma versión: un consumidor aplica el snapshot completo una vez y descarta cualquier replay exacto sin perder subcambios. Una reactivación de alcance `VINCULO` es parcial; una de alcance `PERSONA` reactiva todos los vínculos institucionalmente elegibles de la solicitud. Ninguna reutiliza la operación académica por pago. La unicidad de vínculo se basa en `vinculo_uuid`; SIGE puede impedir más de un vínculo activo del mismo tipo mediante su regla de dominio.

Todas las fechas y correos se validan semánticamente en el receptor además de cumplir el esquema: una fecha debe existir realmente en calendario y un correo no puede limitarse a coincidir con una expresión regular.

## Errores y reintentos

| HTTP | Código típico | Clasificación TIC |
|---:|---|---|
| 200/201 | `PROCESADO` | éxito, incluido replay exacto |
| 400 | `JSON_INVALIDO` | terminal |
| 401/403 | `NO_AUTORIZADO` | terminal y alerta |
| 404 | `PERSONA_NO_ENCONTRADA` | terminal |
| 409 | `CONFLICTO_OBSOLETO`, `ID_OPERACION_CONFLICTIVO`, `UID_NO_DISPONIBLE` | conflicto terminal; requiere conciliación o corrección |
| 413/415 | payload o tipo inválido | terminal |
| 422 | regla de dominio | terminal |
| 429 | límite temporal | reintento con backoff |
| 500/502/503/504 | indisponibilidad | reintento con el mismo UUID |

## Exclusiones obligatorias

Los contratos de personal no admiten `tic_id_inscripcion`, `id_inscripcion`, `estado_academico`, Q10, programa, sede, lote, etapa, `fecha_inscripcion`, vigencia académica o reactivación por pago.

## Autorización

El literal `usuario.rol = CARNETIZACION` es un dato de auditoría, no una credencial. TIC valida sesión activa, rol exacto y CSRF antes de encolar. SIGE autentica el canal con la credencial inbound y confía en el actor solo después de validar el origen técnico. Ningún endpoint autoriza únicamente por el valor escrito dentro del JSON.
