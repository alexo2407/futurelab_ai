# Guía de Implementación: fal.ai Integration

## ✅ Cambios Realizados

Se ha integrado **fal.ai** como proveedor alternativo de IA para la generación de imágenes en el sistema FutureLab AI.

### Archivos Creados

1. **`config/FalAIClient.php`** - Cliente para interactuar con la API de fal.ai
2. **`add_falai_config.sql`** - Script SQL para agregar configuración de fal.ai

### Archivos Modificados

1. **`config/worker.php`** - Actualizado para soportar múltiples proveedores (OpenAI y fal.ai)
2. **`config/OpenAIClient.php`** - Añadidos parámetros opcionales para compatibilidad
3. **`controlador/ConfigControlador.php`** - Añadido soporte para guardar y probar fal.ai
4. **`vista/admin/config.php`** - Nueva interfaz con selector de proveedor y configuración de fal.ai
5. **`index.php`** - Nueva ruta `/api/config/test-falai`

---

## 📋 Pasos de Instalación

### 1. Ejecutar Script SQL

Ejecuta el siguiente script en tu base de datos `futurelab_ai`:

```bash
# Desde MySQL/phpMyAdmin
mysql -u root -p futurelab_ai < add_falai_config.sql
```

O desde phpMyAdmin:
1. Abre http://localhost/phpmyadmin
2. Selecciona la base de datos `futurelab_ai`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `add_falai_config.sql`
5. Haz clic en "Ejecutar"

### 2. Configurar fal.ai desde el Panel

1. Ve a: **http://localhost/futurelab_ai/admin/config**
2. En la sección "**Proveedor de IA para Generación de Imágenes**":
   - Selecciona **fal.ai (Gemini 3 Pro Image Preview)**
   - Haz clic en "Guardar Proveedor"

3. En la sección "**Configuración de fal.ai**":
   - Ingresa tu **API Key** de fal.ai (obtén una en https://fal.ai/dashboard/keys)
   - Selecciona el **Modelo**: `Gemini 3 Pro Image Preview (Edit)` (recomendado)
   - Selecciona el **Tamaño de Imagen**: `1024x1024` o `1024x1792`
   - Marca el checkbox **"Habilitar integración con fal.ai"**
   - Haz clic en "Guardar Configuración"
   
4. Haz clic en "**Probar Conexión**" para verificar que tu API Key es válida

### 3. Ejecutar el Worker

Ahora cuando ejecutes el worker, automáticamente usará fal.ai:

```bash
cd c:\xampp\htdocs\futurelab_ai
php -f config/worker.php
```

Verás en la consola:

```
=== FutureLab AI Worker ===
Iniciando procesamiento de participantes...

Leyendo configuración de IA...
✓ Proveedor seleccionado: fal.ai
✓ Modelo: fal-ai/gemini-3-pro-image-preview/edit
✓ Tamaño: 1024x1024
✓ Cliente fal.ai inicializado
✓ Directorio de resultados creado

--- Iteración #1 ---
Procesando 1 participante(s)...

[1] Juan Pérez - Iniciando procesamiento...
[1] Generando imagen con IA (falai)...
[1] ✓ Completado exitosamente
```

---

## 🎯 Características de fal.ai

### Ventajas sobre OpenAI

1. **Soporte de Múltiples Imágenes** - Puedes enviar tanto la foto del participante como una imagen de referencia de la carrera
2. **Gemini 3 Pro Image Preview** - Modelo especializado en transformación de imágenes con alta fidelidad
3. **Sistema de Cola Asíncrono** - Procesamiento en cola con polling automático
4. **Costos Competitivos** - Consulta precios en https://fal.ai/pricing

### Modelos Disponibles

El sistema soporta los siguientes modelos de fal.ai (configurable desde el panel):

- **`fal-ai/gemini-3-pro-image-preview/edit`** (Recomendado) - Transformación de imágenes
- **`fal-ai/flux-pro/v1.1`** - FLUX Pro v1.1 para generación avanzada
- **`fal-ai/flux/dev`** - FLUX Dev para desarrollo y pruebas

---

## 🔄 Cómo Funciona

### Flujo de Generación con fal.ai

1. **Participante creado** - El operador captura la foto y datos básicos
2. **En cola** - El participante se guarda con `status='queued'`
3. **Worker procesa**:
   - Carga la foto del participante (obligatoria)
   - Carga imagen de referencia de la carrera (opcional)
   - Envía ambas imágenes a fal.ai junto con el prompt
4. **fal.ai procesa**:
   - Sube las imágenes a su storage
   - Encola el trabajo
   - El worker hace polling cada 2 segundos
5. **Resultado listo** - Descarga la imagen generada y la guarda
6. **Actualiza estado** - Marca como `status='done'`

### Uso de Imagen de Referencia

Si tu carrera tiene configurada una `reference_image_path` o `reference_image_url`, el sistema:

1. Carga automáticamente esa imagen
2. La envía a fal.ai como segunda imagen
3. El prompt puede instruir a transformar al participante según el estilo de la referencia

Ejemplo de prompt efectivo:
```
"Toma la primera imagen como referencia de estilo. Reemplaza al sujeto 
de esa imagen con la persona de la segunda foto ({nombre}). Mantén el 
mismo estilo, ambiente y composición de la imagen de referencia."
```

---

## 🔁 Cambiar Entre Proveedores

Puedes cambiar entre  OpenAI y fal.ai en cualquier momento:

1. Ve a: **http://localhost/futurelab_ai/admin/config**
2. En "**Proveedor de IA**", selecciona el que quieras usar
3. Guarda y el worker usará automáticamente ese proveedor

**Nota**: Ambos proveedores pueden estar configurados simultáneamente. El sistema usa el que esté seleccionado en `ai_provider`.

---

## 🧪 Probar la Implementación

### Test 1: Verificar configuración en BD

```sql
SELECT * FROM system_config 
WHERE config_key LIKE 'falai%' OR config_key = 'ai_provider';
```

Deberías ver:
- `ai_provider` = `falai`
- `falai_api_key` = `tu_key`
- `falai_enabled` = `1`
- `falai_model` = `fal-ai/gemini-3-pro-image-preview/edit`

### Test 2: Probar desde el Panel

1. Ve a http://localhost/futurelab_ai/admin/config
2. Haz clic en "Probar Conexión" en la sección de fal.ai
3. Deberías ver: "✓ Conexión exitosa! API Key válida de fal.ai."

### Test 3: Generar un Participante

1. Ve a: http://localhost/futurelab_ai/admin/generate
2. Captura una foto de prueba
3. Llena los datos y genera el participante
4. Ejecuta el worker: `php -f config/worker.php`
5. Verifica que la imagen se genere correctamente

---

## 📝 Configuración Recomendada

### Para Eventos en Vivo

```
Proveedor: fal.ai
Modelo: fal-ai/gemini-3-pro-image-preview/edit
Tamaño: 1024x1792 (formato vertical para historias)
Habilitar: Sí
```

### Para Desarrollo/Pruebas  

```
Proveedor: OpenAI (opcional si tienes créditos gratis)
Modelo: gpt-image-1
Tamaño: 1024x1024
```

---

## 🔧 Troubleshooting

### Error: "API Key de fal.ai no configurada"

**Solución**: 
1. Ve a https://fal.ai/dashboard/keys
2. Crea una nueva API Key
3. Cópiala en el panel de configuración
4. Asegúrate de hacer clic en "Guardar Configuración"

### Error: "fal.ai está deshabilitado"

**Solución**:
1. En el panel de configuración
2. Marca el checkbox "Habilitar integración con fal.ai"
3. Guarda

### Error: "Timeout esperando resultado"

**Causa**: La imagen está tardando más de 120 segundos en procesarse.

**Solución**:
- Verifica tu plan de fal.ai (los planes gratuitos pueden ser más lentos)
- Reduce el tamaño de imagen (usa 1024x1024 en lugar de 1024x1792)
- Espera un momento y reintenta

### Las imágenes no se parecen al participante

**Solución**:
- Asegúrate de estar usando `gemini-3-pro-image-preview/edit` (no DALL-E)
- Verifica que la foto del participante sea clara y bien iluminada
- Ajusta el prompt de la carrera para enfatizar "mantener identidad facial"

---

## 📊 Comparación: OpenAI vs fal.ai

| Característica | OpenAI | fal.ai |
|---|---|---|
| **Soporte múltiples imágenes** | ❌ Solo 1 imagen | ✅ Hasta 2+ imágenes |
| **Fidelidad facial** | ⭐⭐⭐ Buena (gpt-image-1) | ⭐⭐⭐⭐ Excelente (Gemini 3 Pro) |
| **Velocidad** | ~15-30s | ~10-25s |
| **Precio** | Variable | Desde $0.005/imagen |
| **Modelos disponibles** | gpt-image-1, dall-e-3 | FLUX,  Gemini 3 Pro, etc. |

---

## 🎉 ¡Listo!

Tu sistema ahora soporta **fal.ai** como proveedor de IA. Puedes cambiar entre proveedores fácilmente desde el panel de administración.

**Próximos pasos recomendados:**
1. Configura imágenes de referencia para cada carrera
2. Personaliza los prompts por carrera
3. Ajusta el tamaño de imagen según tus necesidades (vertical para redes sociales)

---

**¿Necesitas ayuda?** Revisa los logs del worker para más detalles sobre el procesamiento.
