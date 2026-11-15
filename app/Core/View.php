<?php
/**
 * View: carga una vista (archivo de app/Views) y la envuelve en el layout común.
 * Las variables del arreglo $datos quedan disponibles dentro de la vista.
 */
class View
{
    public static function render(string $vista, array $datos = []): void
    {
        extract($datos);
        ob_start();
        require __DIR__ . '/../Views/' . $vista . '.php';
        $contenido = ob_get_clean();
        require __DIR__ . '/../Views/layout.php';
    }
}
