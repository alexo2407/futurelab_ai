-- =====================================================
-- FutureLab AI - SQL Seed con Passwords Reales
-- =====================================================
-- 
-- Este archivo actualiza los passwords de los usuarios
-- con hashes reales generados con password_hash()
-- 
-- CREDENCIALES:
-- admin / admin123
-- operador / oper123
-- viewer / view123
-- =====================================================

USE futurelab_ai;

-- Actualizar passwords de usuarios con hashes reales
UPDATE users SET password_hash = '$2y$10$tW.m5wv7iAukUlEiYe4G/OBYKfOuAu2CHxgiI9KieQGxeErtlLByK' WHERE username = 'admin';
-- Password: secret123

UPDATE users SET password_hash = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' WHERE username = 'operador';
-- Password: oper123

UPDATE users SET password_hash = '$2y$10$9uVo2v7S3IJIpP9l7c9KxePKiYZe1cE.JlrWXxkGZ7KxHXvS8kCzG' WHERE username = 'viewer';
-- Password: view123

-- Verificar que los usuarios existen
SELECT 
    u.id,
    u.username,
    u.is_active,
    GROUP_CONCAT(r.name) as roles
FROM users u
LEFT JOIN user_roles ur ON u.id = ur.user_id
LEFT JOIN roles r ON ur.role_id = r.id
GROUP BY u.id, u.username, u.is_active;

-- Limpiar participantes de ejemplo (opcional)
-- DELETE FROM participants WHERE id > 0;

-- =====================================================
-- SCRIPT PARA GENERAR NUEVOS PASSWORDS
-- =====================================================
-- Para generar nuevos passwords, ejecuta este script PHP:
-- 
-- <?php
-- echo "admin123: " . password_hash('admin123', PASSWORD_DEFAULT) . "\n";
-- echo "oper123: " . password_hash('oper123', PASSWORD_DEFAULT) . "\n";
-- echo "view123: " . password_hash('view123', PASSWORD_DEFAULT) . "\n";
-- ?>
-- 
-- Luego actualiza las queries UPDATE de arriba con los nuevos hashes
-- =====================================================
