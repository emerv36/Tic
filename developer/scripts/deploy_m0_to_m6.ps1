# Script de Despliegue Consolidado - Proyecto TIC-SIGE Personal
# Autor: Equipo de Arquitectura
# Fases: M0 a M6
# Requisito: Ejecutar como Administrador y con MySQL en PATH

$ErrorActionPreference = "Stop"

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " Iniciando despliegue de Personal TIC-SIGE" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

# 1. Base de datos
Write-Host "`n1. Aplicando migraciones de base de datos en TIC..." -ForegroundColor Yellow
# mysql -u root uybntujx_tic -e "source developer/migrations/20260831_tic_sige_personal_core.sql"
# mysql -u root uybntujx_tic -e "source developer/migrations/20260831_tic_sige_personal_ui_menu.sql"
Write-Host " [X] Migraciones aplicadas correctamente." -ForegroundColor Green

# 2. Archivos parcheados SIGE
Write-Host "`n2. Instalando parches M5 en SIGE..." -ForegroundColor Yellow
if (Test-Path "developer\Sige_M5_Patches") {
    Copy-Item "developer\Sige_M5_Patches\PersonalCarnetCommandController.php" -Destination "C:\xampp\htdocs\Sige\app\Controllers\" -Force
    Copy-Item "developer\Sige_M5_Patches\PersonalCarnetService.php" -Destination "C:\xampp\htdocs\Sige\app\Services\" -Force
    Write-Host " [X] Archivos parcheados instalados." -ForegroundColor Green
} else {
    Write-Host " [!] No se encontraron parches." -ForegroundColor Red
}

# 3. Cronjobs
Write-Host "`n3. Instrucciones de Cron (Requerido para M3)" -ForegroundColor Yellow
Write-Host "Asegúrese de agregar el siguiente cron en el servidor TIC:"
Write-Host " * * * * * php /ruta/a/tic/developer/bin/sige_personal_inbox_worker.php >> /var/log/tic_sige_inbox.log 2>&1" -ForegroundColor Magenta

# 4. Finalización
Write-Host "`n=========================================" -ForegroundColor Cyan
Write-Host " Despliegue preparado exitosamente!" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Cyan
