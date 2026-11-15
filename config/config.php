<?php
// ============================================================
// config.php: datos de conexión a MySQL y opciones generales.
// En XAMPP el usuario por defecto es "root" sin contraseña.
// ============================================================
return [
    'db_host'        => 'localhost',
    'db_name'        => 'legumbreria',
    'db_user'        => 'root',
    'db_pass'        => '',
    'sesion_minutos' => 30,      // la sesión se cierra tras 30 min sin actividad (RNF-09)
];
