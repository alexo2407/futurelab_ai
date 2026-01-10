-- =====================================================
-- FutureLab AI - Actualización de Password Admin
-- =====================================================
-- Ejecuta este archivo para actualizar el password del admin
-- =====================================================

USE futurelab_ai;

-- Actualizar password del admin
UPDATE users 
SET password_hash = '$2y$10$tW.m5wv7iAukUlEiYe4G/OBYKfOuAu2CHxgiI9KieQGxeErtlLByK' 
WHERE username = 'admin';

-- Verificar actualización
SELECT id, username, is_active, 
       CASE 
           WHEN password_hash = '$2y$10$tW.m5wv7iAukUlEiYe4G/OBYKfOuAu2CHxgiI9KieQGxeErtlLByK' 
           THEN 'ACTUALIZADO ✓' 
           ELSE 'NO ACTUALIZADO' 
       END as password_status
FROM users 
WHERE username = 'admin';

-- =====================================================
-- NUEVA CREDENCIAL:
-- Usuario: admin
-- Password: secret123
-- =====================================================
