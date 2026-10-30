-- Se ejecuta UNA sola vez, al crear el volumen de MySQL por primera vez.
-- (mysql:8.0 lo corre sobre la base indicada en MYSQL_DATABASE)
-- Las tablas las crean las migraciones de Laravel, no este script.
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
