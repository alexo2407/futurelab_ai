-- Agregar configuración de OpenAI a la tabla system_config
-- Ejecutar este script en la base de datos

-- Insertar configuración de OpenAI
INSERT INTO system_config (config_key, config_value, description) VALUES
('openai_api_key', '', 'API Key de OpenAI para GPT-Image-1'),
('openai_enabled', '0', 'Habilitar/deshabilitar generación de imágenes con OpenAI (1=habilitado, 0=deshabilitado)'),
('openai_model', 'gpt-image-1', 'Modelo de OpenAI a usar (gpt-image-1, dall-e-3, dall-e-2)'),
('openai_image_size', '1024x1024', 'Tamaño de imagen a generar (1024x1024, 1024x1792, 1792x1024)'),
('openai_image_quality', 'medium', 'Calidad de imagen (low, medium, high)')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    description = VALUES(description);
