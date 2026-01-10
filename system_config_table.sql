-- Tabla para guardar configuraciones del sistema
CREATE TABLE IF NOT EXISTS `system_config` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_group` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`),
  KEY `fk_config_user` (`updated_by`),
  CONSTRAINT `fk_config_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración inicial de Gemini API
INSERT INTO `system_config` (`config_key`, `config_value`, `config_group`, `description`) VALUES
('gemini_api_key', '', 'api', 'Google Gemini API Key'),
('gemini_model', 'gemini-1.5-flash', 'api', 'Modelo de Gemini a usar'),
('gemini_enabled', '1', 'api', 'Habilitar integración con Gemini');
