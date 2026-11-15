<?php
/**
 * Csrf: protege los formularios contra peticiones falsificadas (RNF-07).
 * Cada sesión tiene un token secreto que debe viajar en todo envío POST.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf'];
    }

    /** Campo oculto para poner dentro de cada formulario. */
    public static function campo(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    /** Verifica el token (del formulario o de la cabecera X-CSRF-Token en peticiones fetch). */
    public static function verificar(): bool
    {
        $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($t) && hash_equals(self::token(), $t);
    }
}
