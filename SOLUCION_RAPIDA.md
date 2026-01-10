# ⚡ SOLUCIÓN RÁPIDA - "Usuario o contraseña incorrectos"

## 🔥 HAZ ESTO AHORA (30 segundos)

### 1. Abre este link en tu navegador:

```
http://localhost/futurelab-ai/actualizar_password.php
```

### 2. Verás una pantalla que dice "PASSWORD ACTUALIZADO EXITOSAMENTE" ✅

### 3. Haz click en el botón "IR AL LOGIN"

### 4. Ingresa:
- **Usuario:** `admin`
- **Password:** `secret123`

---

## ✅ ¡Listo! Ya deberías poder entrar

Si el paso 1 no funciona, significa que Apache no está corriendo o la base de datos no está importada.

### Plan B: Verifica lo básico

**¿Apache está corriendo?**
- Abre el panel de XAMPP
- Verifica que Apache y MySQL tengan luz verde

**¿La base de datos existe?**
- Abre: http://localhost/phpmyadmin
- Busca en el lado izquierdo la base de datos `futurelab_ai`
- Si NO aparece, importa el archivo `futurelab_ai.sql`

**¿El archivo actualizar_password.php existe?**
- Verifica que esté en: `/Applications/XAMPP/xamppfiles/htdocs/futurelab-ai/actualizar_password.php`

---

## 🆘 Si todavía no funciona

Abre: http://localhost/futurelab-ai/test.php

Este test te dirá exactamente qué está fallando.

---

## 📞 Después de actualizar el password

Podrás acceder a:
- **Login:** http://localhost/futurelab-ai/
- **Generar Participante:** (aparece después de login)
- **Muro Público:** http://localhost/futurelab-ai/wall
- **Panel Admin:** (aparece después de login)
