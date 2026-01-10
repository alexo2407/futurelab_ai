# 🚀 GUÍA DE ACCESO RÁPIDO - FutureLab AI

## ⚡ PASOS PARA ACCEDER AHORA MISMO

### PASO 1: Actualizar Password del Admin

Abre phpMyAdmin o ejecuta este comando en terminal:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
mysql -u root -p futurelab_ai < update_admin_password.sql
```

O desde phpMyAdmin:
1. Ve a http://localhost/phpmyadmin
2. Selecciona base de datos `futurelab_ai`
3. Click en pestaña "SQL"
4. Copia y pega:
```sql
UPDATE users 
SET password_hash = '$2y$10$tW.m5wv7iAukUlEiYe4G/OBYKfOuAu2CHxgiI9KieQGxeErtlLByK' 
WHERE username = 'admin';
```
5. Click "Continuar"

### PASO 2: Verificar que Todo Funciona

Abre en tu navegador:
```
http://localhost/futurelab-ai/test.php
```

Este test verificará:
- ✓ Conexión a base de datos
- ✓ Usuario admin existe
- ✓ Password es correcto
- ✓ Carpetas de storage existen
- ✓ Tablas están creadas

### PASO 3: Acceder al Sistema

```
http://localhost/futurelab-ai/
```

**CREDENCIALES:**
- Usuario: `admin`
- Password: `secret123`

---

## 🔧 SI TODAVÍA NO FUNCIONA

### Opción A: Test Completo

1. Abre: http://localhost/futurelab-ai/test.php
2. Lee los mensajes de error
3. Sigue las instrucciones que aparezcan en rojo

### Opción B: Verificación Manual

**1. ¿Apache está corriendo?**
```bash
# Verifica en el panel de XAMPP que Apache tenga luz verde
```

**2. ¿La base de datos está importada?**
```bash
mysql -u root -p -e "USE futurelab_ai; SHOW TABLES;"
# Debes ver: users, roles, careers, participants, etc.
```

**3. ¿Los permisos están correctos?**
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
chmod -R 777 storage/
```

### Opción C: Acceso Directo a Login

Si las URLs amigables no funcionan, prueba:

```
http://localhost/futurelab-ai/vista/login.php
```

Pero esto NO es recomendado, mejor soluciona el routing.

---

## 📋 DESPUÉS DE LOGIN EXITOSO

Verás opciones según tu rol:

**Como ADMIN:**
- Generar Participante: botón en el header
- Ver Lista: `/admin/participants`
- Ver Muro: `/wall` (nueva pestaña)

**Navegación:**
1. Login exitoso → redirige automáticamente a `/admin/generate`
2. Captura foto con cámara
3. Llena datos del participante
4. Genera código QR
5. Ve al panel admin para ver la lista

---

## 🎯 URLS IMPORTANTES

| Función | URL |
|---------|-----|
| **Test de Sistema** | http://localhost/futurelab-ai/test.php |
| **Login** | http://localhost/futurelab-ai/ |
| **Muro Público** | http://localhost/futurelab-ai/wall |
| **Generar Participante** | http://localhost/futurelab-ai/admin/generate |
| **Panel Admin** | http://localhost/futurelab-ai/admin/participants |

---

## ❓ PREGUNTAS FRECUENTES

**P: ¿Por qué no veo el formulario de login?**
R: Verifica que Apache esté corriendo y que accedas a http://localhost/futurelab-ai/

**P: ¿Dice "usuario o contraseña incorrectos"?**
R: Ejecuta `update_admin_password.sql` desde phpMyAdmin o terminal

**P: ¿Error 404 en todas las páginas?**
R: El mod_rewrite puede no estar activo. Usa `.htaccess-simple`:
```bash
mv .htaccess .htaccess-backup
mv .htaccess-simple .htaccess
```

**P: ¿La cámara no funciona?**
R: Usa un navegador moderno (Chrome/Firefox) y permite el acceso a la cámara

**P: ¿Cómo ejecuto el worker?**
R: En terminal:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
php -f config/worker.php
```

---

## 🎉 ¡LISTO!

Una vez que puedas hacer login con `admin` / `secret123`, el sistema está **completamente funcional**.

Para más detalles, consulta:
- [README.md](README.md) - Documentación completa
- [ACCESO.md](ACCESO.md) - Guía de troubleshooting
- [walkthrough.md](/Users/kudesingmanager/.gemini/antigravity/brain/8818b366-ec2f-4e31-a690-bbc91643c143/walkthrough.md) - Recorrido del sistema
