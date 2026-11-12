@echo off
setlocal
cd /d "%~dp0"
python "analizar_auditoria.py"
if errorlevel 1 (
    echo.
    echo No se pudo ejecutar el analisis. Verifica que Python este instalado.
) else (
    echo.
    echo Revisa Seguridad\datos\resumen_auditoria.json
)
pause
