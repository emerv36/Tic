<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/developer/Services/PersonalCatalogAuthorization.php';
require_once __DIR__ . '/developer/Config/personal_catalog_config.php';

if (!TIC_SIGE_PERSONAL_CATALOGS_ENABLED) {
    http_response_code(404);
    echo '<div class="alert alert-info">El módulo de catálogos de personal aún no está habilitado.</div>';
    return;
}

try {
    $authorization = new PersonalCatalogAuthorization();
    $authorization->autorizar($_SESSION);
    $csrfToken = PersonalCatalogAuthorization::asegurarToken($_SESSION);
} catch (PersonalCatalogUnauthorizedException $exception) {
    http_response_code(401);
    echo '<div class="alert alert-danger">La sesión no está autenticada.</div>';
    return;
} catch (PersonalCatalogForbiddenException $exception) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Esta opción requiere el rol CARNETIZACION.</div>';
    return;
} catch (Throwable $exception) {
    error_log('gestionarcatalogospersonal: ' . $exception->getMessage());
    http_response_code(500);
    echo '<div class="alert alert-danger">No fue posible validar el acceso.</div>';
    return;
}
?>
<div class="content-wrapper" id="personal-catalog-app" data-csrf="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <section class="content-header">
        <h1>Catálogos de personal <small>TIC–SIGE</small></h1>
    </section>
    <section class="content">
        <div class="alert alert-info">Cargo y dependencia son administrados exclusivamente en TIC. No corresponden a sede ni programa.</div>
        <div class="row">
            <?php foreach (array('CARGO' => 'Cargos', 'DEPENDENCIA' => 'Dependencias') as $catalogCode => $catalogLabel): ?>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?= htmlspecialchars($catalogLabel, ENT_QUOTES, 'UTF-8') ?></h3>
                        <button type="button" class="btn btn-primary btn-sm pull-right personal-catalog-new" data-catalog="<?= $catalogCode ?>">Nuevo</button>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-hover" data-catalog-table="<?= $catalogCode ?>">
                            <thead><tr><th>Nombre</th><th>Versión</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody><tr><td colspan="4">Cargando…</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<div class="modal fade" id="personal-catalog-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm"><div class="modal-content">
        <div class="modal-header bg-primary">
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Catálogo de personal</h4>
        </div>
        <div class="modal-body">
            <input type="hidden" id="personal-catalog-type">
            <input type="hidden" id="personal-catalog-id">
            <input type="hidden" id="personal-catalog-version">
            <div class="form-group">
                <label for="personal-catalog-name">Nombre</label>
                <input type="text" maxlength="190" class="form-control" id="personal-catalog-name" autocomplete="off">
            </div>
            <div class="form-group" id="personal-catalog-state-group">
                <label for="personal-catalog-state">Estado</label>
                <select class="form-control" id="personal-catalog-state">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="personal-catalog-save">Guardar</button>
        </div>
    </div></div>
</div>
<script src="javascripts/gestionarcatalogospersonal.js"></script>
