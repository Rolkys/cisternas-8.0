{{-- DOC: Proyecto Cisternas | Componente modal de ayuda por rol --}}
<div class="modal fade" id="modalAyuda" tabindex="-1" aria-labelledby="modalAyudaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #e10734 !important; color: #ffffff !important;">
                <h5 class="modal-title" id="modalAyudaLabel">
                    <i class="bi bi-question-circle me-2"></i>Guía de uso
                    <span class="badge ms-2 fs-6 px-2 py-1" style="background-color: rgba(0,0,0,0.25); font-size: 0.7rem !important;">
                        {{ auth()->user()->isOperario() ? 'Operario' : (auth()->user()->isAdmin() ? 'Administrador' : 'Usuario') }}
                    </span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-0">

                @if(auth()->user()->isOperario())
                    {{-- ══════════════ VERSIÓN OPERARIO ══════════════ --}}
                    <div class="p-4">

                        <div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="font-size:0.9rem;">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                            <span>Como <strong>operario</strong> puedes ver el listado y registrar consumos. No puedes crear ni eliminar cisternas.</span>
                        </div>

                        {{-- Sección: Listado --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-list-ul me-1"></i> Botones en cada fila del listado
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-4">

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-success d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-clock"></i> Reloj (verde)
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Registrar consumo</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Abre un pequeño formulario para anotar la <strong>hora real de consumo</strong>.
                                        Introduce la hora en la <strong>línea 1 (L1)</strong> o <strong>línea 2 (L2)</strong> según corresponda.
                                        Solo puedes rellenar una de las dos a la vez.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap; background-color: var(--color-view); color:#fff;">
                                <i class="bi bi-eye"></i> Ver (azul)
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Ver detalles</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Muestra todos los datos completos de la cisterna: conductor, matrículas, fechas, horas, observaciones e incidencias. Solo lectura, no modifica nada.
                                    </p>
                                </div>
                            </div>

                        </div>

                        {{-- Sección: Filtros --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-funnel me-1"></i> Barra de búsqueda y filtros
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-4">

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-primary d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-search"></i> Filtrar
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Buscar cisternas</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Busca por <strong>conductor</strong>, <strong>matrícula cisterna</strong>, <strong>origen</strong> o <strong>destino</strong>.
                                        También puedes filtrar por una fecha concreta o por año. Pulsa el botón azul para aplicar.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-secondary d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-x-lg"></i> Limpiar
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Quitar filtros</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Elimina todos los filtros activos y muestra el listado completo del año actual.
                                    </p>
                                </div>
                            </div>

                        </div>

                        {{-- Leyenda de colores --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-palette me-1"></i> Significado de los colores de las filas
                        </h6>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge py-2 px-3" style="background-color:#adebb3; color:#1a5c28; font-size:0.8rem;"><i class="bi bi-check-circle me-1"></i>Verde — Consumida</span>
                            <span class="badge py-2 px-3" style="background-color:#FF746C; color:#7a1010; font-size:0.8rem;"><i class="bi bi-exclamation-triangle me-1"></i>Rojo — Incidencia</span>
                            <span class="badge py-2 px-3" style="background-color:#90D5FF; color:#0d3d5e; font-size:0.8rem;"><i class="bi bi-calendar-check me-1"></i>Azul — Hoy</span>
                            <span class="badge py-2 px-3" style="background-color:#FFEE8C; color:#5c4a00; font-size:0.8rem;"><i class="bi bi-calendar-event me-1"></i>Amarillo — Futura</span>
                            <span class="badge py-2 px-3" style="background-color:#e9ecef; color:#444; font-size:0.8rem;"><i class="bi bi-hourglass me-1"></i>Gris — Pendiente</span>
                        </div>

                    </div>
                    {{-- FIN OPERARIO --}}

                @else
                    {{-- ══════════════ VERSIÓN ADMIN / USUARIO ══════════════ --}}
                    <div class="p-4">

                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="font-size:0.9rem;">
                            <i class="bi bi-shield-check fs-5"></i>
                            <span>Vista de <strong>{{ auth()->user()->isRoot() ? 'Root' : (auth()->user()->isAdmin() ? 'Administrador' : 'Usuario') }}</strong>. Tienes acceso completo a la gestión de cisternas.</span>
                        </div>

                        {{-- Sección: Botones de fila --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-list-ul me-1"></i> Botones en cada fila del listado
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-4">

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-success d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-clock"></i> Reloj
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Registrar consumo rápido</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Abre un modal para anotar la <strong>hora real de consumo L1 o L2</strong> sin tener que entrar al formulario completo. Solo se puede rellenar una línea a la vez.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap; background-color: var(--color-view); color:#fff;">
                                <i class="bi bi-eye"></i> Ver
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Ver detalles completos</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Muestra todos los campos de la cisterna. Desde esta vista también puedes acceder al botón de editar.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-warning d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap; color:#212529;">
                                <i class="bi bi-pencil"></i> Editar
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Editar cisterna</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Permite modificar todos los campos: OF, conductor, matrículas, origen, destino, transporte, fechas, horas estimadas y reales de consumo, incidencias, observaciones, GlobalGAP y FDA.
                                    </p>
                                </div>
                            </div>

                            @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                                <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-danger d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-trash"></i> Eliminar
                            </span>
                                    <div>
                                        <p class="fw-semibold mb-1" style="font-size:0.88rem;">Eliminar cisterna</p>
                                        <p class="text-muted mb-0" style="font-size:0.83rem;">
                                            Borra permanentemente el registro de la base de datos. Siempre pedirá confirmación antes de ejecutarse. <strong>Esta acción no se puede deshacer.</strong>
                                        </p>
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- Sección: Botones superiores --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-layout-text-window-reverse me-1"></i> Botones del menú superior del listado
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-4">

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-success d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-box-arrow-down-left"></i> Importar Excel
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Importación masiva desde Excel</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Sube un archivo <strong>.xlsx</strong> con varias cisternas a la vez. Se previsualiza cada fila antes de confirmar. Puedes editar los datos en la tabla de previsualización y elegir qué filas incluir o descartar.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-warning d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap; color:#212529;">
                                <i class="bi bi-plus-lg"></i> Cisterna comprada
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Crear cisterna manualmente</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Abre el formulario para registrar una cisterna nueva introduciendo todos los datos a mano: conductor, matrículas, origen, destino, fecha de consumo, observaciones, etc.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-primary d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-box-arrow-up-right"></i> Exportar Excel
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Exportar listado a Excel</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Descarga el listado actual (con los filtros activos en ese momento) en formato <strong>.xlsx</strong> con colores por estado de cada cisterna.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap; background-color:#8e44ad; color:#fff;">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Panel de estadísticas</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Muestra métricas resumidas: total de cisternas, consumidas, pendientes, con incidencias y programadas para hoy. Incluye tabla histórica filtrable por año.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap; background-color: var(--color-info, #17a2b8); color:#fff;">
                                <i class="bi bi-calendar2-week"></i> Planificación
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Gestión de planificación</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Permite añadir, editar y eliminar filas de la planificación de consumo con número de cisterna, destino, fechas y horas estimadas. También exporta la planificación a Excel.
                                    </p>
                                </div>
                            </div>

                            @if(auth()->user()->isRoot() || auth()->user()->isAdmin())
                                <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-dark d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-people"></i> Usuarios
                            </span>
                                    <div>
                                        <p class="fw-semibold mb-1" style="font-size:0.88rem;">Gestión de usuarios</p>
                                        <p class="text-muted mb-0" style="font-size:0.83rem;">
                                            Visible en la barra de navegación superior. Permite crear usuarios nuevos, editar su rol (Root, Administrador, Usuario, Operario), activar o desactivar cuentas y eliminar usuarios.
                                        </p>
                                    </div>
                                </div>
                            @endif

                        </div>

                        {{-- Sección: Filtros --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-funnel me-1"></i> Barra de búsqueda y filtros
                        </h6>
                        <div class="d-flex flex-column gap-2 mb-4">

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-primary d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-search"></i> Filtrar
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Buscar cisternas</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Busca por <strong>conductor</strong>, <strong>matrícula cisterna</strong>, <strong>origen</strong> o <strong>destino</strong>. También filtra por fecha concreta o por año. Se pueden combinar varios filtros a la vez.
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded border">
                            <span class="badge text-bg-secondary d-flex align-items-center gap-1 py-2 px-3" style="font-size:0.78rem; white-space:nowrap;">
                                <i class="bi bi-x-lg"></i> Limpiar
                            </span>
                                <div>
                                    <p class="fw-semibold mb-1" style="font-size:0.88rem;">Quitar todos los filtros</p>
                                    <p class="text-muted mb-0" style="font-size:0.83rem;">
                                        Elimina todos los filtros activos y vuelve al listado completo del año actual.
                                    </p>
                                </div>
                            </div>

                        </div>

                        {{-- Leyenda de colores --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:0.75rem; letter-spacing:0.06em; border-bottom: 1px solid #dee2e6; padding-bottom: 6px;">
                            <i class="bi bi-palette me-1"></i> Significado de los colores de las filas
                        </h6>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge py-2 px-3" style="background-color:#adebb3; color:#1a5c28; font-size:0.8rem;"><i class="bi bi-check-circle me-1"></i>Verde — Consumida</span>
                            <span class="badge py-2 px-3" style="background-color:#FF746C; color:#7a1010; font-size:0.8rem;"><i class="bi bi-exclamation-triangle me-1"></i>Rojo — Incidencia</span>
                            <span class="badge py-2 px-3" style="background-color:#90D5FF; color:#0d3d5e; font-size:0.8rem;"><i class="bi bi-calendar-check me-1"></i>Azul — Programada hoy</span>
                            <span class="badge py-2 px-3" style="background-color:#FFEE8C; color:#5c4a00; font-size:0.8rem;"><i class="bi bi-calendar-event me-1"></i>Amarillo — Futura</span>
                            <span class="badge py-2 px-3" style="background-color:#e9ecef; color:#444; font-size:0.8rem;"><i class="bi bi-hourglass me-1"></i>Gris — Pendiente/sin fecha</span>
                        </div>

                    </div>
                    {{-- FIN ADMIN --}}
                @endif

            </div>{{-- fin modal-body --}}

            <div class="modal-footer" style="border-top: 1px solid #dee2e6;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
