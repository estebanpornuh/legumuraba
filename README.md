# LegumUrabá – Legumbrería (PHP + MySQL)

Sistema web de inventario y caja. Arquitectura en 3 capas con patrón MVC (ver entregable 1.3).

## Instalación en XAMPP
1. Copia esta carpeta (`legumbreria-php`) a `C:\xampp\htdocs\` y renómbrala `legumbreria`.
2. Abre el panel de XAMPP y enciende **Apache** y **MySQL**.
3. Abre `http://localhost/phpmyadmin` → pestaña **Importar** → elige `database/schema.sql` → **Importar**.
   (Esto crea la base de datos `legumbreria` con todas sus tablas.)
4. Revisa `config/config.php` (en XAMPP el usuario es `root` y la contraseña está vacía).
5. Abre `http://localhost/legumbreria/` → crea la cuenta del administrador.
6. En **Usuarios** crea a tus empleados (rol Vendedor: solo ven Caja y Existencias).
7. En **Proveedores** registra productores/distribuidores y crea, si corresponde, una cuenta de acceso vinculada a cada proveedor.
8. El proveedor inicia sesión con su correo y contraseña y consulta únicamente sus propias entregas desde **Mis entregas**.

## Estructura
- `public/`         → único directorio expuesto: `index.php` (Front Controller), estilos, JS y `uploads/`.
- `app/Core/`       → Database (PDO), Auth (sesión y roles), Csrf, View, Auditoria, helpers.
- `app/Controllers/`→ reciben la petición, validan permisos y llaman a los servicios.
- `app/Services/`   → reglas de negocio y transacciones (VentaService, EntradaService...).
- `app/Models/`     → repositorios: todo el SQL con sentencias preparadas.
- `app/Views/`      → pantallas HTML.
- `database/schema.sql` → modelo relacional (13 tablas).

## Acceso de proveedores
El administrador registra primero al proveedor en el módulo **Proveedores** y luego crea su cuenta asociada. El portal de proveedor es de solo consulta y filtra las entradas por el `proveedor_id` de la sesión; no permite acceder a caja, inventario, usuarios ni reportes administrativos.
