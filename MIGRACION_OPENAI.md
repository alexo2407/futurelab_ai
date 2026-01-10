# Migración a OpenAI GPT-Image-1

Este documento explica cómo usar GPT-Image-1 de OpenAI para la generación de imágenes.

## ¿Por qué GPT-Image-1?

### Ventajas sobre DALL-E 3 y Gemini:

✅ **Multimodal**: Acepta texto + imágenes como entrada  
✅ **Usa la foto del participante**: Transforma la foto real en lugar de generar desde cero  
✅ **Mejor seguimiento de instrucciones**: Entiende mejor los prompts complejos  
✅ **Más opciones**: Transparencia, formatos (PNG, JPEG, WebP), compresión  
✅ **Prompts más largos**: Hasta 32,000 caracteres  
✅ **Futuro-proof**: DALL-E 3 será deprecado en Mayo 2026  
✅ **Más económico**: Desde $0.011 por imagen  

### Comparación:

| Característica | GPT-Image-1 | DALL-E 3 | Gemini |
|----------------|-------------|----------|--------|
| **Input** | Texto + Imágenes | Solo texto | Texto + Imágenes |
| **Edición** | ✅ Sí | ❌ No | ✅ Sí |
| **Prompt máx** | 32,000 chars | 4,000 chars | Variable |
| **Formatos** | PNG, JPEG, WebP | Solo PNG | PNG |
| **Arquitectura** | PHP directo | PHP directo | PHP → Node.js |
| **Precio (1024x1024)** | $0.011-0.167 | $0.04-0.08 | Gratis (límites) |

## Instalación

### Paso 1: La configuración ya está en la base de datos

Si ya ejecutaste el SQL anterior, GPT-Image-1 ya está configurado como modelo por defecto.

### Paso 2: Configurar API Key

1. Ve al panel de administración: `http://localhost/futurelab-ai/admin/config`

2. En la sección "Configuración de OpenAI GPT-Image-1", configura:
   - **openai_api_key**: Tu API key de OpenAI
   - **openai_model**: `gpt-image-1` (ya seleccionado por defecto)
   - **openai_image_quality**: `medium` (recomendado)
   - **openai_enabled**: Marca el checkbox para habilitar

3. Click en "Guardar Configuración"

### Paso 3: Ajustar prompts de carreras

GPT-Image-1 usa la foto del participante como base, así que los prompts deben describir la **transformación** deseada.

**Ejemplos de buenos prompts:**

```
Ingeniería de Software:
"Transforma esta foto en una imagen profesional de {nombre} como ingeniero de software, 
trabajando en una oficina tech moderna con múltiples monitores mostrando código, 
iluminación profesional, ambiente corporativo, alta calidad"

Medicina:
"Transforma esta foto en una imagen profesional de {nombre} como doctor, 
usando bata blanca médica en un hospital moderno, iluminación profesional, 
expresión confiable, alta calidad"

Arquitectura:
"Transforma esta foto en una imagen profesional de {nombre} como arquitecto, 
en una oficina de diseño moderna revisando planos arquitectónicos, 
iluminación natural, estilo profesional"
```

**Actualiza los prompts en la base de datos:**
- Ve a: `http://localhost/futurelab-ai/admin/carreras`
- Edita cada carrera
- Actualiza el campo "Prompt de IA" con descripciones de transformación

## Uso

### Ejecutar el worker:

```bash
php -f config/worker.php
```

El worker mostrará:
```
✓ Usando OpenAI GPT-Image-1
✓ Modelo: gpt-image-1
✓ Tamaño: 1024x1024
✓ Calidad: medium
```

Y al procesar cada participante:
```
[1] ✓ Usando GPT-Image-1 con foto del participante
[1] ✓ Imagen generada por GPT-Image-1!
[1] ✓ Imagen guardada: /storage/results/result_1_...png
```

## Costos de GPT-Image-1

| Calidad | Tamaño 1024x1024 | Tamaño 1024x1792 |
|---------|------------------|------------------|
| **Low** | $0.011 | $0.016 |
| **Medium** | $0.042 | $0.063 |
| **High** | $0.167 | $0.250 |

**Recomendación**: Usa `medium` para mejor balance calidad/precio.

## Ventajas para tu caso de uso

### Antes (DALL-E 3):
- Solo prompt de texto
- Genera imagen desde cero
- No usa la foto del participante
- Puede no parecerse a la persona

### Ahora (GPT-Image-1):
- ✅ Usa la **foto real** del participante
- ✅ **Transforma** la foto según el prompt
- ✅ **Preserva la identidad** de la persona
- ✅ Mejor resultado final

## Diferencias con Gemini

| Aspecto | GPT-Image-1 | Gemini |
|---------|-------------|--------|
| **Arquitectura** | PHP directo | PHP → Node.js → Gemini |
| **Complejidad** | Simple | Compleja (2 servicios) |
| **Imagen de referencia** | ❌ No soporta | ✅ Sí soporta |
| **Foto del participante** | ✅ Usa como base | ✅ Usa en composición |
| **Costo** | ~$0.042/imagen | Gratis (con límites) |
| **Mantenimiento** | Fácil | Requiere Node.js |

## Troubleshooting

### Error: "API Key de OpenAI es requerida"
- Verifica que configuraste `openai_api_key` en el panel de admin
- Asegúrate que `openai_enabled = 1`

### Error: "Error de OpenAI API (HTTP 401)"
- Tu API key es inválida o expiró
- Genera una nueva en https://platform.openai.com/api-keys

### Error: "Error de OpenAI API (HTTP 429)"
- Excediste el límite de rate limit
- Espera unos minutos o aumenta tu plan en OpenAI

### Las imágenes no se parecen a la persona
- Verifica que el modelo sea `gpt-image-1` (no `dall-e-3`)
- Asegúrate que la foto del participante sea clara y de buena calidad
- Mejora el prompt para describir mejor la transformación deseada

## Soporte

Para más información:
- Documentación de OpenAI GPT-Image: https://platform.openai.com/docs/guides/images
- Panel de API Keys: https://platform.openai.com/api-keys
- Uso y límites: https://platform.openai.com/usage
