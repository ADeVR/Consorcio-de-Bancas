#!/usr/bin/env python3
"""
Analizador de auditoría del Consorcio de Bancas.
Lee auditoria.jsonl, genera resumen_auditoria.json y envía por Gmail
las alertas nuevas, si existe correo_config.json.
Solo usa la biblioteca estándar de Python.
"""
from __future__ import annotations

import argparse
import hashlib
import json
import smtplib
import ssl
from collections import Counter
from datetime import datetime, timedelta, timezone
from email.message import EmailMessage
from pathlib import Path
from typing import Any

BASE_DIR = Path(__file__).resolve().parents[1]
DEFAULT_INPUT = BASE_DIR / "datos" / "auditoria.jsonl"
DEFAULT_OUTPUT = BASE_DIR / "datos" / "resumen_auditoria.json"
MAIL_CONFIG = BASE_DIR / "datos" / "correo_config.json"
SENT_STATE = BASE_DIR / "datos" / "alertas_enviadas.json"


def parse_fecha(value: Any) -> datetime | None:
    if not isinstance(value, str) or not value:
        return None
    try:
        parsed = datetime.fromisoformat(value.replace("Z", "+00:00"))
        if parsed.tzinfo is None:
            parsed = parsed.replace(tzinfo=timezone.utc)
        return parsed
    except ValueError:
        return None


def cargar_registros(path: Path) -> tuple[list[dict], int]:
    registros: list[dict] = []
    invalidas = 0
    if not path.exists():
        return registros, invalidas
    with path.open("r", encoding="utf-8") as archivo:
        for linea in archivo:
            linea = linea.strip()
            if not linea:
                continue
            try:
                dato = json.loads(linea)
                if isinstance(dato, dict):
                    registros.append(dato)
                else:
                    invalidas += 1
            except json.JSONDecodeError:
                invalidas += 1
    return registros, invalidas


def analizar(registros: list[dict], umbral: int, ventana_minutos: int) -> dict:
    eventos, estados, usuarios = Counter(), Counter(), Counter()
    fallidos: dict[str, list[datetime]] = {}
    fechas_validas: list[datetime] = []

    for registro in registros:
        evento = str(registro.get("evento") or "SIN_EVENTO")
        estado = str(registro.get("estado") or "SIN_ESTADO")
        eventos[evento] += 1
        estados[estado] += 1

        usuario = registro.get("usuario") or registro.get("usuario_intentado")
        if usuario:
            usuario = str(usuario)
            usuarios[usuario] += 1
        if evento == "INTENTO_INICIO_SESION" and estado == "NO_AUTENTICADO" and usuario:
            fecha = parse_fecha(registro.get("fecha"))
            if fecha:
                fallidos.setdefault(usuario, []).append(fecha)

        fecha_general = parse_fecha(registro.get("fecha"))
        if fecha_general:
            fechas_validas.append(fecha_general)

    alertas = []
    ventana = timedelta(minutes=ventana_minutos)
    for usuario, fechas in fallidos.items():
        fechas.sort()
        inicio = 0
        cluster_inicio = 0
        for fin, fecha_actual in enumerate(fechas):
            while fecha_actual - fechas[inicio] > ventana:
                inicio += 1
            # Cada vez que se alcanza el umbral en una ventana, identificamos
            # el evento que hizo alcanzar el umbral. Ese ID evita correos repetidos.
            if fin - inicio + 1 == umbral:
                disparador = fechas[fin]
                firma = f"{usuario}|{disparador.isoformat()}|{umbral}|{ventana_minutos}"
                id_alerta = hashlib.sha256(firma.encode("utf-8")).hexdigest()
                alertas.append({
                    "id_alerta": id_alerta,
                    "tipo": "POSIBLES_INTENTOS_REPETIDOS",
                    "usuario": usuario,
                    "intentos_fallidos": umbral,
                    "ventana_minutos": ventana_minutos,
                    "desde": fechas[inicio].isoformat(),
                    "hasta": disparador.isoformat(),
                    "recomendacion": (
                        "Revisar los intentos de acceso de esta cuenta. "
                        "La alerta no confirma por sí sola un ataque."
                    ),
                })
                # Evita generar otro umbral dentro de la misma ráfaga.
                cluster_inicio = fin
        # No se necesita usar cluster_inicio; el ID del evento umbral permite
        # distinguir ráfagas separadas y deduplicarlas entre ejecuciones.

    fechas_validas.sort()
    return {
        "generado_en": datetime.now().astimezone().isoformat(timespec="seconds"),
        "fuente": "auditoria.jsonl",
        "total_eventos": len(registros),
        "eventos_por_tipo": dict(sorted(eventos.items())),
        "eventos_por_estado": dict(sorted(estados.items())),
        "actividad_por_usuario": dict(usuarios.most_common()),
        "intentos_fallidos_identificados": sum(len(v) for v in fallidos.values()),
        "alertas": alertas,
        "periodo_registrado": {
            "desde": fechas_validas[0].isoformat() if fechas_validas else None,
            "hasta": fechas_validas[-1].isoformat() if fechas_validas else None,
        },
    }


def cargar_estado_envios() -> dict:
    if not SENT_STATE.exists():
        return {"enviadas": []}
    try:
        data = json.loads(SENT_STATE.read_text(encoding="utf-8"))
        return data if isinstance(data, dict) else {"enviadas": []}
    except (OSError, json.JSONDecodeError):
        return {"enviadas": []}


def enviar_alertas_nuevas(alertas: list[dict]) -> tuple[int, int, str]:
    if not MAIL_CONFIG.exists():
        return 0, 0, (
            "No se encontró correo_config.json. Copia correo_config.example.json "
            "como correo_config.json y completa los datos."
        )

    try:
        config = json.loads(MAIL_CONFIG.read_text(encoding="utf-8"))
        remitente = config["remitente"].strip()
        destinatario = config["destinatario"].strip()
        app_password = config["contrasena_aplicacion"].replace(" ", "").strip()
        servidor = config.get("servidor", "smtp.gmail.com")
        puerto = int(config.get("puerto", 587))
        if not all((remitente, destinatario, app_password)):
            raise ValueError("Faltan remitente, destinatario o contraseña de aplicación.")
    except (OSError, KeyError, ValueError, json.JSONDecodeError) as exc:
        return 0, 0, f"No se pudo leer correo_config.json: {exc}"

    estado = cargar_estado_envios()
    enviadas = set(estado.get("enviadas", []))
    nuevas = [a for a in alertas if a.get("id_alerta") not in enviadas]
    if not nuevas:
        return 0, 0, "No hay alertas nuevas para enviar."

    enviadas_ok = 0
    errores = []
    contexto = ssl.create_default_context()
    try:
        with smtplib.SMTP(servidor, puerto, timeout=25) as smtp:
            smtp.ehlo()
            smtp.starttls(context=contexto)
            smtp.ehlo()
            smtp.login(remitente, app_password)
            for alerta in nuevas:
                mensaje = EmailMessage()
                mensaje["Subject"] = "Alerta de seguridad - Consorcio de Bancas"
                mensaje["From"] = remitente
                mensaje["To"] = destinatario
                mensaje.set_content(
                    "Se detectó una posible actividad sospechosa en el sistema.\n\n"
                    f"Usuario intentado: {alerta['usuario']}\n"
                    f"Intentos fallidos: {alerta['intentos_fallidos']}\n"
                    f"Ventana: {alerta['ventana_minutos']} minutos\n"
                    f"Desde: {alerta['desde']}\n"
                    f"Hasta: {alerta['hasta']}\n\n"
                    f"Recomendación: {alerta['recomendacion']}\n\n"
                    "Mensaje automático del módulo de auditoría del Consorcio de Bancas."
                )
                try:
                    smtp.send_message(mensaje)
                    enviadas.add(alerta["id_alerta"])
                    enviadas_ok += 1
                except Exception as exc:
                    errores.append(str(exc))
    except Exception as exc:
        return 0, len(nuevas), f"No se pudo conectar o autenticar con Gmail: {exc}"

    SENT_STATE.parent.mkdir(parents=True, exist_ok=True)
    SENT_STATE.write_text(
        json.dumps({"enviadas": sorted(enviadas)}, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    detalle = f"Correos enviados: {enviadas_ok}."
    if errores:
        detalle += " Errores al enviar algunos correos: " + " | ".join(errores)
    return enviadas_ok, len(errores), detalle


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Analiza auditoría y envía alertas nuevas mediante Gmail."
    )
    parser.add_argument("--entrada", type=Path, default=DEFAULT_INPUT)
    parser.add_argument("--salida", type=Path, default=DEFAULT_OUTPUT)
    parser.add_argument("--umbral", type=int, default=5)
    parser.add_argument("--ventana", type=int, default=10)
    args = parser.parse_args()

    if args.umbral < 2 or args.ventana < 1:
        parser.error("--umbral debe ser >= 2 y --ventana debe ser >= 1")

    registros, invalidas = cargar_registros(args.entrada)
    resumen = analizar(registros, args.umbral, args.ventana)
    resumen["lineas_invalidas_omitidas"] = invalidas

    args.salida.parent.mkdir(parents=True, exist_ok=True)
    args.salida.write_text(
        json.dumps(resumen, ensure_ascii=False, indent=4), encoding="utf-8"
    )

    print("Análisis completado.")
    print(f"Eventos analizados: {resumen['total_eventos']}")
    print(f"Alertas detectadas: {len(resumen['alertas'])}")

    enviados, errores, mensaje = enviar_alertas_nuevas(resumen["alertas"])
    print(mensaje)
    print(f"Resumen generado en: {args.salida}")
    if invalidas:
        print(f"Líneas inválidas omitidas: {invalidas}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
