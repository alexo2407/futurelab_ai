-- Agregar configuración de fal.ai a la tabla system_config
-- Ejecutar este script en la base de datos futurelab_ai

-- =======================
-- CONFIGURACIÓN FAL.AI
-- =======================

INSERT INTO system_config (config_key, config_value, description) VALUES
('ai_provider', 'openai', 'Proveedor de IA a usar (openai, falai)'),
('falai_api_key', '', 'API Key de fal.ai'),
('falai_enabled', '0', 'Habilitar/deshabilitar generación de imágenes con fal.ai (1=habilitado, 0=deshabilitado)'),
('falai_model', 'fal-ai/gemini-3-pro-image-preview/edit', 'Modelo de fal.ai a usar'),
('falai_image_size', '1024x1024', 'Tamaño de imagen a generar (1024x1024, 1024x1792, 1792x1024)'),
('falai_resolution', '1K', 'Resolución de imagen: 1K (rápido), 2K (balanceado), 4K (alta calidad)'),
('falai_output_format', 'png', 'Formato de salida: jpeg, png, webp'),
('falai_num_images', '1', 'Número de imágenes a generar (1-4)'),
('falai_enable_web_search', '0', 'Habilitar búsqueda web para mejorar resultados (1=sí, 0=no)'),
('falai_sync_mode', '0', 'Modo síncrono - devuelve data URI directo (1=sí, 0=no)')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    description = VALUES(description);

-- =======================
-- NOTA IMPORTANTE
-- =======================
-- Después de ejecutar este script:
-- 1. Ve a http://localhost/futurelab_ai/admin/config
-- 2. Ingresa tu API Key de fal.ai
-- 3. Asegúrate de que 'ai_provider' esté configurado como 'falai'
