# 🎨 Guía Completa: Sistema de Imágenes de Referencia

## ✅ Sistema Implementado y Listo

El sistema **YA ESTÁ COMPLETO** y funcional para usar imágenes de referencia con fal.ai.

---

## 📸 Cómo Funciona

### Concepto:
**Tu Foto + Tu Arte = Tú en el Arte**

1. **Imagen 1 (Participante):** La foto que tomas en el evento
2. **Imagen 2 (Referencia):** Tu obra de arte, ilustración, render 3D, o foto estilizada
3. **Resultado:** El participante reemplaza al sujeto del arte, manteniendo el estilo original

---

## 🎯 Flujo Completo

### 1. Configura tu Carrera

**Ve a:** http://localhost/futurelab_ai/admin/careers/edit?id=1

#### A. **Sube tu Imagen de Referencia**

En la sección "**Imagen de Referencia (Arte Base)**":

- **Opción 1:** Arrastra y suelta tu imagen (JPG, PNG, WebP)
- **Opción 2:** Pega una URL directa

**Ejemplo de arte de referencia:**
- Ilustración digital de un astronauta
- Render 3D de un ingeniero en su oficina
- Foto estilizada de un médico en quirófano
- Arte conceptual de cualquier profesión

#### B. **Configura el Prompt**

**Prompt Recomendado para Reemplazo:**
```
"Usa la primera imagen como referencia de estilo. Reemplaza completamente al sujeto 
de esa imagen con la segunda imagen de {nombre}. Mantén exactamente el mismo estilo 
artístico, iluminación, ambiente y composición de la imagen de referencia. 
Preserva la identidad facial de {nombre}."
```

**Variables disponibles:**
- `{nombre}` - Nombre completo del participante
- `{carrera}` - Nombre de la carrera

### 2. Captura Fotos de Participantes

**Ve a:** http://localhost/futurelab_ai/admin/generate

1. Toma la foto del participante
2. Llena sus datos (nombre, apellido, carrera)
3. Genera el participante
4. El sistema guarda la foto y lo marca como `status='queued'`

### 3. Ejecuta el Worker

```bash
cd c:\xampp\htdocs\futurelab_ai
php -f config/worker.php
```

**El worker automáticamente:**

1. ✅ Lee la foto del participante
2. ✅ Carga la imagen de referencia de la carrera
3. ✅ **Sube AMBAS imágenes** a fal.ai storage
4. ✅ Envía el prompt con las 2 imágenes
5. ✅ fal.ai genera la imagen fusionada
6. ✅ Descarga y guarda el resultado
7. ✅ Marca como `status='done'`

**Console Output:**
```
[1] Juan Pérez - Iniciando procesamiento...
[1] ✓ Usando imagen de referencia local
[1] Generando imagen con IA (falai)...
[1] ✓ Completado exitosamente
```

---

## 🔧 Configuración de fal.ai (Recomendada)

**Ve a:** http://localhost/futurelab_ai/admin/config

```
Proveedor: fal.ai
Modelo: Gemini 3 Pro Image Preview (Edit)
Tamaño: 1024x1792 (vertical) o 1024x1024 (cuadrado)
Resolución: 1K (rápido) o 2K (más calidad)
Formato: PNG (mejor calidad)
Núm. Imágenes: 1
Búsqueda Web: ✗ (no necesaria)
Modo Síncrono: ✗ (guardar historial)
Habilitar: ✓
```

---

## 📋 Payload Enviado a fal.ai

Cuando hay imagen de referencia:

```json
{
  "prompt": "Usa la primera imagen como referencia de estilo...",
  "image_urls": [
    "https://fal.run/storage/upload/participant_photo.jpg",  // Foto del participante
    "https://fal.run/storage/upload/reference_art.jpg"       // Tu arte de referencia
  ],
  "num_images": 1,
  "aspect_ratio": "9:16",
  "output_format": "png",
  "resolution": "1K"
}
```

---

## 🎨 Ejemplos de Prompts Efectivos

### 1. **Reemplazo Completo (Recomendado)**
```
"Toma la primera imagen como referencia de estilo y composición. Reemplaza 
completamente al sujeto de esa imagen con {nombre} (segunda imagen). Mantén 
exactamente el mismo fondo, iluminación, colores y estilo artístico de la 
referencia. Preserva la identidad facial y rasgos de {nombre}."
```

### 2. **Fusión Artística**
```
"Transforma a {nombre} adoptando el estilo visual de la primera imagen de 
referencia. Mantén la pose, vestimenta y ambiente de la referencia pero con 
la apariencia facial de {nombre}. Estilo consistente."
```

### 3. **Contexto Profesional**
```
"Coloca a {nombre} en el mismo contexto profesional de la imagen de referencia. 
{nombre} debe aparecer como {carrera} con la misma iluminación cinemática, 
fondo y atmósfera de la referencia. Preserva identidad facial."
```

---

## 🚀 Casos de Uso

### Caso 1: Evento de Graduación
**Referencia:** Ilustración de un graduado con toga
**Resultado:** Cada participante aparece con toga en el mismo estilo artístico

### Caso 2: Feria de Carreras
**Referencia:** Arte conceptual de diferentes profesiones
**Resultado:** Estudiantes se ven en su profesión ideal manteniendo su identidad

### Caso 3: Evento Temático
**Referencia:** Render 3D de astronauta/superhéroe/personaje
**Resultado:** Participantes se convierten en el personaje con su rostro

---

## 🔍 Verificación del Sistema

### 1. **Verifica que la Imagen se Guardó**

```sql
SELECT id, name, ai_prompt, reference_image_path, reference_image_url 
FROM careers 
WHERE id = 1;
```

Deberías ver algo como:
```
reference_image_path: /storage/references/career_1_1736553xxx.jpg
```

### 2. **Verifica el Archivo Físico**

```
c:\xampp\htdocs\futurelab_ai\storage\references\career_1_1736553xxx.jpg
```

### 3. **Prueba en el Worker**

El worker debe mostrar:
```
[1] ✓ Usando imagen de referencia local
```

---

## 🎯 Workflow Completo (Resumen)

```
1. ADMIN CONFIGURA
   └─ Sube imagen de referencia
   └─ Configura prompt
   └─ Activa carrera

2. OPERADOR CAPTURA
   └─ Toma foto del participante
   └─ Genera QR
   └─ Participante queda en cola

3. WORKER PROCESA
   └─ Lee foto participante
   └─ Carga imagen referencia
   └─ Sube ambas a fal.ai
   └─ Envía prompt
   └─ Descarga resultado
   └─ Guarda y marca done

4. PARTICIPANTE VE
   └─ Escanea QR
   └─ Ve su foto transformada
   └─ Se ve a sí mismo en el arte
```

---

## 🛠️ Troubleshooting

### ❌ "No hay imagen de referencia"
**Causa:** No se subió o no se guardó
**Solución:** 
1. Ve a admin/careers/edit
2. Verifica que haya preview de imagen
3. Haz clic en "Guardar Cambios"

### ❌ "reference_image_path is null"
**Causa:** Permisos de escritura en `/storage/references/`
**Solución:**
```bash
mkdir c:\xampp\htdocs\futurelab_ai\storage\references
icacls "c:\xampp\htdocs\futurelab_ai\storage\references" /grant Users:F
```

### ❌ "El resultado no se parece al arte"
**Causa:** Prompt mal configurado
**Solución:** Usa el prompt recomendado de arriba

### ❌ "Imagen de referencia no se está usando"
**Causa:** fal.ai no está configurado como proveedor
**Solución:** Ve a admin/config y selecciona "fal.ai"

---

## ✅ Checklist Final

Antes de tu evento, verifica:

- [ ] Imagen de referencia subida en la carrera
- [ ] Prompt configurado correctamente
- [ ] fal.ai seleccionado como proveedor
- [ ] API Key de fal.ai configurada
- [ ] fal.ai habilitado
- [ ] Worker puede acceder a `/storage/references/`
- [ ] Prueba con 1 participante primero

---

## 🎉 ¡Listo para Producción!

**Tu sistema ahora puede:**
✅ Subir imágenes de referencia
✅ Almacenarlas localmente o usar URLs
✅ Enviar 2 imágenes a fal.ai
✅ Reemplazar sujetos manteniendo estilo
✅ Generar resultados profesionales

**Ejemplos reales:**
- Estudiantes → Profesionistas en acción
- Asistentes → Personajes de videojuegos
- Participantes → Superhéroes/Astronautas
- Graduados → Versión artística con toga

---

**¿Dudas?** Revisa los logs del worker con:
```bash
php -f config/worker.php 2>&1 | tee worker_log.txt
```
