<?php
function escapar($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function exigirSesion(): void
{
    if (empty($_SESSION["usuario_id"])) {
        header("Location: /Seguridad/php/login.php");
        exit;
    }
}
function exigirAdmin(): void
{
    if (empty($_SESSION["usuario_id"]) || ($_SESSION["rol"] ?? "") !== "admin") {
        header("Location: /Seguridad/php/login.php");
        exit;
    }
}
function tokenCsrf(): string
{
    if (empty($_SESSION["token_csrf"])) $_SESSION["token_csrf"] = bin2hex(random_bytes(32));
    return $_SESSION["token_csrf"];
}
function csrfValido(): bool
{
    return isset($_SESSION["token_csrf"], $_POST["token_csrf"])
        && is_string($_POST["token_csrf"])
        && hash_equals($_SESSION["token_csrf"], $_POST["token_csrf"]);
}
