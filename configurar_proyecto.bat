@echo off
setlocal
title Configuracion local - Consorcio de Bancas

set "PROYECTO=%~dp0"
set "PHP_DIR=%PROYECTO%Seguridad\php"
set "PREPEND=%PHP_DIR%\auditoria_prepend.php"
set "HTACCESS=%PHP_DIR%\.htaccess"

if not exist "%PREPEND%" (
    echo ERROR: No se encontro:
    echo %PREPEND%
    echo Coloca este BAT en la carpeta raiz del proyecto.
    pause
    exit /b 1
)

set "PREPEND_APACHE=%PREPEND:\=/%"
(
    echo ^<IfModule php_module^>
    echo     php_value auto_prepend_file "%PREPEND_APACHE%"
    echo ^</IfModule^>
) > "%HTACCESS%"

if errorlevel 1 (
    echo ERROR: No se pudo crear Seguridad\php\.htaccess
    pause
    exit /b 1
)

echo.
echo Configuracion local de auditoria creada correctamente.
echo Archivo: %HTACCESS%
echo.
echo Reinicia Apache desde XAMPP.
echo No subas este .htaccess local a GitHub.
pause
