<?php

require_once __DIR__ . '/PersonalCatalogAuthorization.php';
require_once __DIR__ . '/PersonalCatalogService.php';

/** Orquesta la API HTTP de catálogos sin depender de variables globales. */
class PersonalCatalogApi {
    private $authorization;
    private $catalogs;

    public function __construct(PersonalCatalogAuthorization $authorization, PersonalCatalogService $catalogs) {
        $this->authorization = $authorization;
        $this->catalogs = $catalogs;
    }

    public function handle($case, $method, array $request, array $session) {
        $case = trim((string) $case);
        $method = strtoupper(trim((string) $method));
        $mutation = in_array($case, array('Crear', 'Actualizar'), true);

        try {
            if (($case === 'Listar' && $method !== 'GET') || ($mutation && $method !== 'POST')) {
                return $this->response(405, false, 'METODO_NO_PERMITIDO', 'Método no permitido.');
            }
            if (!in_array($case, array('Listar', 'Crear', 'Actualizar'), true)) {
                return $this->response(404, false, 'OPERACION_NO_ENCONTRADA', 'Operación no encontrada.');
            }

            $actor = $this->authorization->autorizar(
                $session,
                $request['csrf_token'] ?? null,
                $mutation
            );
            $catalogo = $request['catalogo'] ?? '';

            if ($case === 'Listar') {
                $include = $this->booleano($request['incluir_inactivos'] ?? '1');
                return array('http' => 200, 'body' => array(
                    'success' => true,
                    'data' => $this->catalogs->listar($catalogo, $include)
                ));
            }
            if ($case === 'Crear') {
                $item = $this->catalogs->crear($catalogo, $request['nombre'] ?? '', $actor);
                return array('http' => 201, 'body' => array('success' => true, 'data' => $item));
            }

            $item = $this->catalogs->actualizar(
                $catalogo,
                $request['id'] ?? null,
                $request['nombre'] ?? '',
                $request['estado'] ?? '',
                $request['expected_version'] ?? null,
                $actor
            );
            return array('http' => 200, 'body' => array('success' => true, 'data' => $item));
        } catch (PersonalCatalogUnauthorizedException $exception) {
            return $this->response(401, false, 'NO_AUTORIZADO', $exception->getMessage());
        } catch (PersonalCatalogForbiddenException $exception) {
            return $this->response(403, false, 'ROL_NO_AUTORIZADO', $exception->getMessage());
        } catch (PersonalCatalogCsrfException $exception) {
            return $this->response(403, false, 'CSRF_INVALIDO', $exception->getMessage());
        } catch (PersonalCatalogDuplicateException $exception) {
            return $this->response(409, false, 'NOMBRE_DUPLICADO', $exception->getMessage());
        } catch (PersonalCatalogOperationConflictException $exception) {
            return $this->response(409, false, 'OPERACION_CONFLICTIVA', $exception->getMessage());
        } catch (PersonalCatalogConflictException $exception) {
            return $this->response(409, false, 'VERSION_OBSOLETA', $exception->getMessage());
        } catch (PersonalCatalogValidationException $exception) {
            return $this->response(422, false, 'DATOS_INVALIDOS', $exception->getMessage());
        }
    }

    private function booleano($value) {
        if ($value === true || $value === 1 || $value === '1') return true;
        if ($value === false || $value === 0 || $value === '0') return false;
        throw new PersonalCatalogValidationException('incluir_inactivos inválido.');
    }

    private function response($http, $success, $status, $message) {
        return array('http' => $http, 'body' => array(
            'success' => $success,
            'status' => $status,
            'mensaje' => $message
        ));
    }
}
