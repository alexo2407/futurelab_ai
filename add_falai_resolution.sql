-- Script para agregar solo la configuración de resolución (si ya ejecutaste add_falai_config.sql antes)

INSERT INTO system_config (config_key, config_value, description) VALUES
('falai_resolution', '1K', 'Resolución de imagen: 1K (rápido), 2K (balanceado), 4K (alta calidad)')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    description = VALUES(description);
