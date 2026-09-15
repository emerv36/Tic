<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/developer/Services/PersonalCatalogAuthorization.php';
require_once __DIR__ . '/developer/Config/personal_ui_config.php';

if (!TIC_SIGE_PERSONAL_UI_ENABLED) {
    http_response_code(404);
    echo '<div class="alert alert-info">El módulo de carnetización de personal aún no está habilitado.</div>';
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
    error_log('gestionarpersonal: ' . $exception->getMessage());
    http_response_code(500);
    echo '<div class="alert alert-danger">No fue posible validar el acceso.</div>';
    return;
}
?>
<style>
    .carnet-mockup {
        width: 250px;
        height: 400px;
        background-color: #fff;
        background-image: url('plantillas/front_personal.png');
        background-size: cover;
        background-position: center;
        border: 1px solid #ccc;
        border-radius: 12px;
        position: relative;
        margin: 0 auto 20px auto;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        overflow: hidden;
        user-select: none;
    }

    .carnet-photo-area {
        width: 142px;
        height: 142px;
        background: transparent;
        position: absolute;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        right: auto;
        overflow: hidden;
        z-index: 2;
        border-radius: 50%;
    }

    .carnet-photo-area video,
    .carnet-photo-area canvas {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        transform: scaleX(-1);
    }
    
    .carnet-data {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        text-align: center;
    }
    .carnet-value {
        position: absolute;
        left: 10px;
        right: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        z-index: 3;
    }
    #preview-nombres {
        top: 218px;
        font-size: 18px;
        color: #202b4d;
        font-family: 'Arial Black', Impact, sans-serif;
        text-transform: uppercase;
        font-weight: 900;
        letter-spacing: -0.5px;
    }
    #preview-apellidos {
        top: 242px;
        font-size: 14px;
        color: #00baf2;
        font-family: 'Arial Black', Impact, sans-serif;
        text-transform: uppercase;
        font-weight: 900;
    }
    #preview-identificacion {
        top: 270px;
        font-size: 16px;
        color: #202b4d;
        font-weight: bold;
    }
    #preview-rh {
        top: 304px;
        font-size: 18px;
        color: #00baf2;
        font-family: 'Arial Black', Impact, sans-serif;
        font-weight: 900;
    }
    #preview-cargo {
        bottom: 15px;
        left: 15px;
        width: 175px;
        font-size: 13px;
        color: #fff;
        font-family: 'Arial Black', Impact, sans-serif;
        font-weight: 900;
        text-align: left;
        line-height: 1.1;
        white-space: normal;
        text-transform: uppercase;
    }
    #preview-letra {
        bottom: 16px;
        right: 15px;
        width: 40px;
        font-size: 26px;
        color: #fff;
        font-family: 'Arial Black', Impact, sans-serif;
        font-weight: 900;
        text-align: center;
        z-index: 3;
        position: absolute;
    }
</style>

<div class="content-wrapper" id="personal-ui-app" data-csrf="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <section class="content-header">
        <h1>Registro Personal <small>Asistente TIC–SIGE</small></h1>
    </section>
    
    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                    <div class="col-md-7" style="border-right: 1px solid #ddd;">
                        <h4>Buscar Funcionario en SIGE</h4>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Tipo Documento</label>
                                    <select id="pers-tipo-doc" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="CC">Cédula de Ciudadanía (CC)</option>
                                        <option value="CE">Cédula de Extranjería (CE)</option>
                                        <option value="PA">Pasaporte (PA)</option>
                                        <option value="TI">Tarjeta de Identidad (TI)</option>
                                        <option value="PE">Permiso Especial (PE)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Número Documento</label>
                                    <div class="input-group">
                                        <input type="text" id="pers-num-doc" class="form-control" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" id="pers-btn-buscar">Buscar</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="pers-form-identidad" style="display:none; margin-top:20px;">
                            <h4 style="border-bottom: 1px solid #eee; padding-bottom:10px;">
                                Datos Maestros <span id="pers-badge-estado" class="label label-default pull-right">NUEVO</span>
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombres <span class="text-danger">*</span></label>
                                        <input type="text" id="pers-nombres" class="form-control master-field">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" id="pers-apellidos" class="form-control master-field">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Correo Electrónico <span class="text-danger">*</span></label>
                                        <input type="email" id="pers-correo" class="form-control master-field">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>RH</label>
                                        <select id="pers-rh" class="form-control master-field">
                                            <option value="">Seleccione...</option>
                                            <option value="O+">O+</option><option value="O-">O-</option>
                                            <option value="A+">A+</option><option value="A-">A-</option>
                                            <option value="B+">B+</option><option value="B-">B-</option>
                                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Celular</label>
                                        <input type="text" id="pers-celular" class="form-control master-field">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Género</label>
                                        <select id="pers-genero" class="form-control master-field">
                                            <option value="">...</option>
                                            <option value="M">Masc</option>
                                            <option value="F">Fem</option>
                                            <option value="O">Otro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 style="border-bottom: 1px solid #eee; padding-bottom:10px; margin-top:20px;">Vínculo Institucional</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tipo <span class="text-danger">*</span></label>
                                        <select id="pers-tipo-vinculo" class="form-control">
                                            <option value="">Seleccione...</option>
                                            <option value="DOCENTE">DOCENTE</option>
                                            <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8" id="pers-form-catalogos" style="display:none;">
                                    <div class="form-group">
                                        <label>Cargo <span class="text-danger">*</span></label>
                                        <select id="pers-cargo" class="form-control select2" style="width: 100%;">
                                            <option value="">Cargando...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Dependencia <span class="text-danger">*</span></label>
                                        <select id="pers-dependencia" class="form-control select2" style="width: 100%;">
                                            <option value="">Cargando...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- COLUMNA DERECHA: CAMARA Y PREVIEW -->
                    <div class="col-md-5 text-center">
                        <h4 style="color: #003366; font-weight: bold; margin-top:0;">Previsualización & Cámara</h4>
                        
                        <div class="carnet-mockup" id="preview-carnet-mockup">
                            <div class="carnet-photo-area">
                                <video id="pers-camara-video" playsinline autoplay muted></video>
                                <canvas id="pers-camara-canvas" style="display:none"></canvas>
                                <img id="pers-foto-preview" src="assets/images/fotoperfil/user.png" style="display:none; width:100%; height:100%; object-fit:cover;">
                            </div>
                            
                            <div class="carnet-data">
                                <div class="carnet-value" id="preview-nombres">---</div>
                                <div class="carnet-value" id="preview-apellidos">---</div>
                                <div class="carnet-value" id="preview-identificacion">---</div>
                                <div class="carnet-value" id="preview-rh">---</div>
                                <div class="carnet-value" id="preview-cargo">---</div>
                                <div id="preview-letra"></div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <input type="file" id="pers-foto-file" accept="image/jpeg, image/png" style="display:none;">
                            <div id="pers-snap-controls">
                                <button type="button" class="btn btn-info" id="pers-btn-camara-iniciar"><i class="fa fa-camera"></i> Activar Cámara</button>
                                <button type="button" class="btn btn-success" id="pers-btn-camara-capturar" style="display:none;"><i class="fa fa-camera"></i> Capturar Foto</button>
                                <button type="button" class="btn btn-primary" onclick="$('#pers-foto-file').click()"><i class="fa fa-folder-open"></i> Subir Archivo</button>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="alert alert-info" id="pers-confirm-status" style="display:none;">
                            Enviando datos al servidor...
                        </div>
                        <button type="button" class="btn btn-primary btn-lg btn-block" id="pers-btn-submit" disabled>
                            <i class="fa fa-save"></i> Guardar y Enviar a SIGE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="javascripts/gestionarpersonal.js?v=<?= time() ?>"></script>
