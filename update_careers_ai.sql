-- Agregar campos a la tabla careers para personalización
ALTER TABLE `careers` 
ADD COLUMN `ai_prompt` text DEFAULT NULL COMMENT 'Prompt personalizado para Gemini AI',
ADD COLUMN `reference_image_path` varchar(255) DEFAULT NULL COMMENT 'Ruta de imagen de referencia',
ADD COLUMN `reference_image_url` varchar(500) DEFAULT NULL COMMENT 'URL externa de imagen de referencia';

-- Actualizar algunas carreras con prompts de ejemplo
-- Estos son prompts COMPLETOS listos para usar
-- Puedes usar {nombre} y {carrera} como variables que se reemplazarán automáticamente

UPDATE `careers` 
SET `ai_prompt` = 'Usa la primera imagen como plantilla base. Reemplaza al sujeto principal de esa imagen con {nombre} de la segunda foto. Mantén exactamente el mismo estilo, ambiente tecnológico, colores y composición de la imagen de referencia. {nombre} debe aparecer trabajando con código y tecnología en ese mismo entorno.'
WHERE `name` LIKE '%Ingenier% Sistemas%' OR `name` LIKE '%Ingenier% Software%' OR `name` LIKE '%Informática%';

UPDATE `careers` 
SET `ai_prompt` = 'Toma la primera imagen como base. Sustituye a la persona de la imagen de referencia por {nombre} de la segunda foto. Conserva el estilo artístico, paleta de colores y ambiente creativo exactos. {nombre} debe integrarse naturalmente en ese espacio de diseño.'
WHERE `name` LIKE '%Diseño Gráfico%' OR `name` LIKE '%Diseño%';

UPDATE `careers` 
SET `ai_prompt` = 'Utiliza la primera imagen como plantilla. Reemplaza al profesional de la imagen de referencia con {nombre} de la segunda foto. Mantén el ambiente corporativo,vestimenta profesional y composición idénticos. {nombre} debe verse como un líder empresarial en ese mismo contexto.'
WHERE `name` LIKE '%Administraci%' OR `name` LIKE '%Negocios%';

UPDATE `careers`
SET `ai_prompt` = 'Usa la primera imagen como guía. Sustituye al profesional de salud de la referencia por {nombre} de la segunda foto. Conserva el ambiente hospitalario, uniformes y equipamiento médico exactos. {nombre} debe aparecer como profesional de la salud en ese mismo entorno.'
WHERE `name` LIKE '%Medicina%' OR `name` LIKE '%Enfermería%' OR `name` LIKE '%Salud%';

-- Si quieres ver los cambios:
SELECT id, name, ai_prompt, reference_image_path 
FROM careers 
LIMIT 10;
