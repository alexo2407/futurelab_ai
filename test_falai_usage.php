<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test fal.ai Usage API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .endpoint-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="card">
            <div class="card-body p-4">
                <h1 class="mb-4">
                    <i class="bi bi-graph-up"></i> 
                    fal.ai Usage Stats
                </h1>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">API Key de fal.ai:</label>
                    <input type="password" 
                           class="form-control" 
                           id="apiKey" 
                           placeholder="Ingresa tu API Key"
                           value="">
                    <small class="text-muted">Tu API key no será guardada, solo se usa para la consulta</small>
                </div>
                
                <button onclick="checkUsage()" class="btn btn-primary btn-lg w-100 mb-3">
                    <i class="bi bi-search"></i> Consultar Uso
                </button>
                
                <!-- Loading -->
                <div id="loading" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Consultando fal.ai...</p>
                </div>
                
                <!-- Error -->
                <div id="error" class="alert alert-danger" style="display: none;"></div>
                
                <!-- Results -->
                <div id="results" style="display: none;">
                    <h3 class="mb-3">📊 Estadísticas (últimas 24h)</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="stat-card">
                                <h5>Total Requests</h5>
                                <h2 id="totalRequests">-</h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stat-card">
                                <h5>Total Gastado</h5>
                                <h2 id="totalCost">$-</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h4>Desglose por Modelo:</h4>
                        <div id="endpoints"></div>
                    </div>
                    
                    <div class="mt-4">
                        <h4>Respuesta Raw (JSON):</h4>
                        <pre id="rawResponse"></pre>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="/admin/config" class="btn btn-outline-light">
                ← Volver a Config
            </a>
        </div>
    </div>
    
    <script>
        async function checkUsage() {
            const apiKey = document.getElementById('apiKey').value;
            
            if (!apiKey) {
                alert('Por favor ingresa tu API Key');
                return;
            }
            
            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            document.getElementById('results').style.display = 'none';
            
            try {
                const response = await fetch('/api/config/falai-usage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'api_key=' + encodeURIComponent(apiKey)
                });
                
                const data = await response.json();
                
                document.getElementById('loading').style.display = 'none';
                
                if (data.ok) {
                    // Show results
                    document.getElementById('results').style.display = 'block';
                    document.getElementById('totalRequests').textContent = data.usage.total_requests;
                    document.getElementById('totalCost').textContent = '$' + data.usage.total_cost.toFixed(4);
                    
                    // Endpoints breakdown
                    const endpointsDiv = document.getElementById('endpoints');
                    endpointsDiv.innerHTML = '';
                    
                    if (Object.keys(data.usage.endpoints).length === 0) {
                        endpointsDiv.innerHTML = '<p class="text-muted">No hay registros de uso en las últimas 24 horas</p>';
                    } else {
                        for (const [endpoint, stats] of Object.entries(data.usage.endpoints)) {
                            endpointsDiv.innerHTML += `
                                <div class="endpoint-item">
                                    <strong>${endpoint}</strong><br>
                                    <small>
                                        Requests: <span class="badge bg-primary">${stats.count}</span> |
                                        Costo: <span class="badge bg-success">$${stats.cost.toFixed(4)}</span>
                                    </small>
                                </div>
                            `;
                        }
                    }
                    
                    // Raw response
                    document.getElementById('rawResponse').textContent = JSON.stringify(data, null, 2);
                    
                } else {
                    // Show error
                    document.getElementById('error').style.display = 'block';
                    document.getElementById('error').textContent = '❌ Error: ' + data.error;
                }
                
            } catch (err) {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('error').style.display = 'block';
                document.getElementById('error').textContent = '❌ Error de conexión: ' + err.message;
            }
        }
        
        // Auto-load API key from config if available
        window.addEventListener('DOMContentLoaded', function() {
            // Puedes pre-cargar la API key desde localStorage si la guardaste antes
            const savedKey = localStorage.getItem('falai_api_key_test');
            if (savedKey) {
                document.getElementById('apiKey').value = savedKey;
            }
        });
        
        // Save API key on input
        document.getElementById('apiKey').addEventListener('input', function(e) {
            localStorage.setItem('falai_api_key_test', e.target.value);
        });
    </script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
