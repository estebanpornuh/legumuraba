<?php
/** Auditoria: registra quién hizo qué, cuándo y desde qué IP (RNF-08). */
class Auditoria
{
    public static function log(string $accion, string $entidad, ?int $entidadId = null, ?int $usuarioId = null): void
    {
        $s = Database::pdo()->prepare('INSERT INTO auditoria (usuario_id, accion, entidad, entidad_id, fecha_hora, ip) VALUES (?, ?, ?, ?, NOW(), ?)');
        $s->execute([$usuarioId ?? (Auth::id() ?: null), $accion, $entidad, $entidadId, $_SERVER['REMOTE_ADDR'] ?? '']);
    }
}
