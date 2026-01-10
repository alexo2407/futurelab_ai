#!/bin/bash

# Script para configurar OpenAI en FutureLab AI
# Uso: ./setup_openai.sh

echo "==================================="
echo "  Configuración de OpenAI DALL-E 3"
echo "==================================="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "config/worker.php" ]; then
    echo "❌ Error: Ejecuta este script desde el directorio raíz del proyecto"
    exit 1
fi

# Ejecutar SQL para agregar configuración
echo "📝 Agregando configuración a la base de datos..."
mysql -u root -p futurelab_ai < add_openai_config.sql

if [ $? -eq 0 ]; then
    echo "✅ Configuración agregada exitosamente"
else
    echo "❌ Error al agregar configuración"
    exit 1
fi

echo ""
echo "==================================="
echo "  Próximos pasos:"
echo "==================================="
echo ""
echo "1. Ve al panel de administración: http://localhost/futurelab-ai/admin/config"
echo ""
echo "2. Configura los siguientes valores:"
echo "   - openai_api_key: Tu API key de OpenAI"
echo "   - openai_enabled: 1 (para habilitar)"
echo ""
echo "3. Opcional - Ajusta estos valores según tus necesidades:"
echo "   - openai_model: dall-e-3 (o dall-e-2)"
echo "   - openai_image_size: 1024x1024 (o 1024x1792, 1792x1024)"
echo "   - openai_image_quality: standard (o hd para mayor calidad)"
echo ""
echo "4. Ejecuta el worker:"
echo "   php -f config/worker.php"
echo ""
echo "✅ ¡Listo! El sistema ahora soporta OpenAI DALL-E 3"
echo ""
