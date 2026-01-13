# Consulta de Uso y Créditos de fal.ai

## 📊 Resumen

**IMPORTANTE:** fal.ai **NO tiene un endpoint público** para consultar el balance de créditos o estadísticas de uso vía API.

### **La Realidad:**

❌ **No existe** `GET /usage` público  
❌ **No existe** `GET /balance` público  
❌ **No se puede consultar** el saldo via API  

✅ **Única forma de ver el balance:**  
🌐 **[Dashboard Web de fal.ai](https://fal.ai/dashboard/billing)**

---

## ✅ Funcionalidad Implementada

Hemos creado un **widget informativo** en el panel de configuración que:

- ✅ Muestra un mensaje claro explicando que no hay API disponible
- ✅ Proporciona un **enlace directo al Dashboard** de fal.ai
- ✅ Se integra perfectamente con el

---

## 🔧 Cómo Usar

### **Opción 1: Desde el Panel Admin (Recomendado)**

**Próximamente** se agregará un botón "Ver Uso" en `/admin/config` junto al test de conexión.

### **Opción 2: Llamada API Directa**

```bash
# Usando curl
curl -X POST http://localhost/futurelab_ai/api/config/falai-usage \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "api_key=YOUR_FAL_KEY"
```

### **Opción 3: Desde JavaScript (Admin Panel)**

```javascript
async function checkFalAIUsage() {
    const response = await fetch('/api/config/falai-usage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'api_key=' + encodeURIComponent(falaiApiKey)
    });
    
    const data = await response.json();
    
    if (data.ok) {
        console.log('Total Requests:', data.usage.total_requests);
        console.log('Total Cost:', '$' + data.usage.total_cost);
        console.log('Period:', data.usage.period);
        console.log('Endpoints:', data.usage.endpoints);
    }
}
```

---

## 📄 Respuesta de Ejemplo

### **Éxito (200 OK):**

```json
{
  "ok": true,
  "usage": {
    "total_requests": 45,
    "total_cost": 0.225,
    "period": "24 horas",
    "endpoints": {
      "fal-ai/gemini-3-pro-image-preview/edit": {
        "count": 30,
        "cost": 0.15
      },
      "fal-ai/fast-sdxl": {
        "count": 15,
        "cost": 0.075
      }
    }
  },
  "message": "Estadísticas obtenidas exitosamente"
}
```

### **Interpretación:**

| Campo | Descripción |
|-------|-------------|
| `total_requests` | Total de llamadas API en las últimas 24h |
| `total_cost` | Total gastado en USD |
| `period` | Período consultado (por defecto 24 horas) |
| `endpoints` | Desglose por modelo usado |

---

## 💡 ¿Cómo Calcular Créditos Restantes?

**fal.ai NO expone directamente el balance**, pero puedes estimarlo:

### **Método 1: Ver en Dashboard**
1. Ve a: https://fal.ai/dashboard
2. En la sección "Billing" verás tu balance actual
3. **Nota:** Esta es la única forma 100% precisa

### **Método 2: Estimar con API de Uso**
Si sabes cuántos créditos compraste:

```
Créditos Restantes ≈ Créditos Comprados - Total Gastado (API)
```

**Ejemplo:**
- Compraste: **$10.00** en créditos
- API reporta gastado: **$0.225** (últimas 24h)
- Debes consultar tu total histórico en el dashboard

---

## 🔍 Información Adicional sobre Platform APIs

### **Otros Endpoints Disponibles:**

1. **Usage API** (implementado)
   - Endpoint: `GET https://api.fal.ai/usage`
   - Devuelve: Registros de uso paginados
   - Filtros: fecha, endpoint, usuario

2. **Pricing API** (no implementado)
   - Endpoint: `GET https://api.fal.ai/pricing/{endpoint_id}`
   - Devuelve: Precio unitario por modelo

3. **Estimate Cost API** (no implementado)
   - Endpoint: `POST https://api.fal.ai/estimate`
   - Devuelve: Estimación de costo pre-generación

---

## ⚙️ Parámetros Opcionales (Futuras Mejoras)

El endpoint `/usage` de fal.ai acepta query parameters:

```
GET https://api.fal.ai/usage?start_date=2026-01-01T00:00:00Z&end_date=2026-01-12T23:59:59Z
```

**Parámetros soportados:**
- `start_date`: Fecha inicio (ISO8601)
- `end_date`: Fecha fin (ISO8601)
- `endpoint_id`: Filtrar por modelo específico
- `page`: Paginación
- `page_size`: Tamaño de página

---

## 🎯 Casos de Uso

### **1. Monitoreo de Costos**
```javascript
// Consultar uso cada día
setInterval(async () => {
    const usage = await checkFalAIUsage();
    if (usage.total_cost > 5.00) { // Alerta si gastas más de $5
        alert('⚠️ Has gastado $' + usage.total_cost + ' hoy!');
    }
}, 86400000); // Cada 24h
```

### **2. Reportes de Uso**
```javascript
// Generar reporte diario
const report = `
📊 Reporte diario fal.ai:
- Requests: ${usage.total_requests}
- Costo: $${usage.total_cost}
- Modelo más usado: ${getMostUsedEndpoint(usage.endpoints)}
`;
```

### **3. Validación Pre-Evento**
Antes de un evento, verifica que tienes suficientes créditos:

```php
$usage = getFalAIUsage();
$estimatedCost = $expectedParticipants * 0.005; // $0.005 por imagen

if ($usage['total_cost'] + $estimatedCost > $creditLimit) {
    echo "⚠️ Recarga créditos antes del evento";
}
```

---

## 🚨 Limitaciones

1. **No muestra balance directo** - Solo gastos históricos
2. **Por defecto 24h** - Necesitas implementar filtros de fecha para más
3. **Requiere Admin scope** - La API Key debe tener permisos
4. **Paginado** - Si tienes muchos requests, necesitas manejar paginación

---

## 🔗 Referencias

- [fal.ai Dashboard](https://fal.ai/dashboard)
- [fal.ai Pricing](https://fal.ai/pricing)
- [Platform APIs Docs](https://fal.ai/reference/platform-apis)
- [Usage API](https://fal.ai/reference/platform-apis/usage)

---

## 📝 Notas

- Los créditos comprados **expiran en 365 días**
- Créditos promocionales pueden tener vencimientos variables
- La mejor forma de ver tu balance exacto es en el **dashboard web**

---

**© 2026 FutureLab AI - Sistema de Eventos con IA**
