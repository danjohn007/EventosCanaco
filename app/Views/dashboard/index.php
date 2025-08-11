<?php
ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="/public/eventos/crear" class="btn btn-canaco">
        <i class="fas fa-plus me-2"></i>Crear Evento
    </a>
</div>

<!-- Dashboard Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Eventos Total
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($metrics['total_events']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Eventos Hoy
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($metrics['events_today']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Registros Total
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($metrics['total_registrations']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Ocupación Promedio
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $metrics['avg_occupation'] ?>%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Events List -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Eventos Recientes</h6>
        <a href="/public/eventos" class="btn btn-sm btn-outline-primary">Ver Todos</a>
    </div>
    <div class="card-body">
        <?php if (empty($events)): ?>
            <div class="text-center py-4">
                <i class="fas fa-calendar-plus fa-3x text-gray-300 mb-3"></i>
                <p class="text-muted">No hay eventos registrados</p>
                <a href="/public/eventos/crear" class="btn btn-canaco">Crear Primer Evento</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card event-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?= htmlspecialchars($event['titulo']) ?></h6>
                                <span class="badge bg-<?= $event['estado'] === 'publicado' ? 'success' : ($event['estado'] === 'borrador' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($event['estado']) ?>
                                </span>
                            </div>
                            
                            <p class="text-muted small mb-2">
                                <i class="fas fa-calendar me-1"></i>
                                <?= Utils::formatDate($event['fecha_inicio']) ?>
                            </p>
                            
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars(Utils::truncate($event['ubicacion'], 30)) ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?= $event['total_registrations'] ?>/<?= $event['cupo'] ?: '∞' ?> registros
                                </small>
                                <div class="btn-group" role="group">
                                    <a href="/public/eventos/editar?id=<?= $event['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/public/eventos/asistentes?id=<?= $event['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" title="Asistentes">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <?php if ($event['estado'] === 'publicado'): ?>
                                    <a href="/public/evento/<?= $event['slug'] ?>" 
                                       class="btn btn-sm btn-outline-success" title="Ver Público" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Dashboard - CANACO Eventos';
include __DIR__ . '/../layouts/admin.php';
?>