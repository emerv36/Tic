<?php
if (PHP_SAPI !== 'cli') exit(1);
echo json_encode(array(
    php_ini_loaded_file(),
    ini_get('extension_dir'),
    get_include_path(),
    ini_get('curl.cainfo'),
    ini_get('openssl.cafile'),
    ini_get('error_log'),
    ini_get('upload_tmp_dir'),
    ini_get('session.save_path'),
    ini_get('browscap'),
), JSON_UNESCAPED_SLASHES);
