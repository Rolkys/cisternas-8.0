<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚛 Sistema de Gestión de Cisternas</title>
    <style>
        /* Estilos amigables para operarios */
        :root {
            --primary-color: #2c5aa0;
            --secondary-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #ffffff;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            background-color: var(--secondary-color);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #1e3c72);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
        }
        
        .btn-operario {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-operario:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card-operario {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card-operario:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .alert-operario {
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            padding: 1rem 1.5rem;
        }
        
        .table-operario {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table-operario thead {
            background: linear-gradient(135deg, var(--primary-color), #1e3c72);
            color: white;
        }
        
        .table-operario tbody tr:hover {
            background-color: #f1f3f5;
        }
        
        .user-info {
            background-color: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            font-size: 1.5rem;
        }
    </style>
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

    {{-- ════════════════════════════ BARRA DE NAVEGACIÓN AMIGABLE ════════════════════════════ --}}
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2"
                href="{{ route('cisterna.index') }}">
                <i class="bi bi-truck" style="font-size: 2rem;"></i>
                <span>🚛 Sistema Cisternas</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                @auth
                    <div class="user-info d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle text-white"></i>
                        <span class="text-white small fw-bold">{{ auth()->user()->name }}</span>
                        <span class="badge bg-warning text-dark">{{ auth()->user()->role }}</span>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users') }}" class="btn btn-operario btn-warning">
                            <i class="bi bi-people-fill"></i> 
                            <span class="d-none d-md-inline">Usuarios</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-operario btn-danger">
                            <i class="bi bi-power"></i> 
                            <span class="d-none d-md-inline">Salir</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ════════════════════════ MENSAJES AMIGABLES ════════════════════════ --}}
    <div class="main-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-operario" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>¡Éxito!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show alert-operario" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>¡Atención!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

    {{-- ══════════════════════════ CONTENIDO PRINCIPAL ════════════════════════════ --}}
    <main class="main-container">
        @yield('content')
    </main>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Script para mejorar experiencia del operario --}}
    <script>
        // Auto-ocultar alertas después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
        
        // Confirmaciones amigables
        function confirmarAccion(mensaje) {
            return confirm('⚠️ ' + mensaje + '\n\n¿Estás seguro de continuar?');
        }
    </script>
</body>
</html>
