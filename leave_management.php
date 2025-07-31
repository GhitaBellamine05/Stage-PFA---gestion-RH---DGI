<?php
session_start();
include("sql/db.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Obtenir l'ID de l'utilisateur actuel
$currentUserId = $_SESSION['user']['PPR'];

// Gérer les soumissions de formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_leave'])) {
        // Gérer la soumission de demande de congé
        $leaveType = $_POST['leave_type'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $reason = $_POST['reason'];
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason) 
                                 VALUES (:employee_id, :leave_type_id, :start_date, :end_date, :reason)");
            $stmt->bindParam(':employee_id', $currentUserId);
            $stmt->bindParam(':leave_type_id', $leaveType);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->bindParam(':reason', $reason);
            $stmt->execute();
            
            $success = "Demande de congé soumise avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur lors de la soumission de la demande : " . $e->getMessage();
        }
    } elseif (isset($_POST['report_absence'])) {
        // Gérer le signalement d'absence
        $date = $_POST['absence_date'];
        $reason = $_POST['absence_reason'];
        $isJustified = isset($_POST['is_justified']) ? 1 : 0;
        
        // Gérer le téléchargement de fichier si justifié
        $justificationDoc = null;
        if ($isJustified && isset($_FILES['justification_doc'])) {
            $uploadDir = 'uploads/absences/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['justification_doc']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['justification_doc']['tmp_name'], $targetPath)) {
                $justificationDoc = $targetPath;
            }
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO absences (employee_id, date, reason, is_justified, justification_doc) 
                                 VALUES (:employee_id, :date, :reason, :is_justified, :justification_doc)");
            $stmt->bindParam(':employee_id', $currentUserId);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':is_justified', $isJustified);
            $stmt->bindParam(':justification_doc', $justificationDoc);
            $stmt->execute();
            
            $success = "Absence signalée avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur lors du signalement de l'absence : " . $e->getMessage();
        }
    }
}

// Obtenir les types de congés
try {
    $db = Database::getInstance()->getConnection();
    $leaveTypes = $db->query("SELECT * FROM leave_types")->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtenir les demandes de congé de l'utilisateur
    $stmt = $db->prepare("SELECT lr.*, lt.name as leave_type_name 
                         FROM leave_requests lr
                         JOIN leave_types lt ON lr.leave_type_id = lt.id
                         WHERE lr.employee_id = :employee_id
                         ORDER BY lr.start_date DESC");
    $stmt->bindParam(':employee_id', $currentUserId);
    $stmt->execute();
    $leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtenir les absences de l'utilisateur
    $stmt = $db->prepare("SELECT * FROM absences 
                         WHERE employee_id = :employee_id
                         ORDER BY date DESC");
    $stmt->bindParam(':employee_id', $currentUserId);
    $stmt->execute();
    $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Congés et Absences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
:root {
   --primary-blue: #4361ee;
  --secondary-blue: #88a5e2ff;
  --light-blue: #e6f0ff;
  --soft-blue: #f5f9ff;
  --dark-blue: #1a2a6c;
  --text-color: #2c3e50;
  --light-gray: #f8f9fa;
  --success-green: #28a745;
  --warning-yellow: #ffc107;
  --danger-red: #dc3545;
  --info-blue: #17a2b8;
}
body {
  background-color: #e6f0ff;
  color: var(--text-color);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
}

.container {
  max-width: 1200px;
}

/* Styles d'en-tête */
h2 {
  color: #4361ee;
  font-weight: 600;
  border-bottom: 2px solid var(--light-blue);
  padding-bottom: 10px;
  margin-bottom: 20px;
}

/* Style des cartes */
.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.1);
  overflow: hidden;
  margin-bottom: 25px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(67, 97, 238, 0.15);
}

.card-header {
  background-color: var(--primary-blue);
  color: white;
  padding: 1.25rem 1.5rem;
  border-bottom: none;
  font-weight: 600;
}

.card-header h5 {
  margin-bottom: 0;
  font-weight: 600;
}

.card-body {
  padding: 1.5rem;
  background-color: white;
}

/* Style des alertes */
.alert {
  border-radius: 8px;
  border: none;
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
}

.alert-success {
  background-color: rgba(40, 167, 69, 0.1);
  color: var(--success-green);
  border-left: 4px solid var(--success-green);
}

.alert-danger {
  background-color: rgba(220, 53, 69, 0.1);
  color: var(--danger-red);
  border-left: 4px solid var(--danger-red);
}

.alert-info {
  background-color: rgba(23, 162, 184, 0.1);
  color: var(--info-blue);
  border-left: 4px solid var(--info-blue);
}

/* Style des formulaires */
.form-control, .form-select {
  border-radius: 6px;
  padding: 10px 15px;
  border: 1px solid rgba(67, 97, 238, 0.2);
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
}

.form-label {
  font-weight: 500;
  color: var(--dark-blue);
  margin-bottom: 8px;
}

.form-check-input {
  border: 1px solid rgba(67, 97, 238, 0.5);
}

.form-check-input:checked {
  background-color: var(--primary-blue);
  border-color: var(--primary-blue);
}

/* Style des boutons */
.btn {
  border-radius: 6px;
  padding: 10px 20px;
  font-weight: 500;
  transition: all 0.2s ease;
  border: none;
}

.btn-primary {
  background-color: var(--primary-blue);
}

.btn-primary:hover {
  background-color: var(--secondary-blue);
  transform: translateY(-2px);
}

.btn-warning {
  background-color: var(--warning-yellow);
  color: var(--text-color);
}

.btn-warning:hover {
  background-color: #e0a800;
  transform: translateY(-2px);
}

/* Style des onglets */
.nav-tabs {
  border-bottom: 2px solid var(--light-blue);
}

.nav-tabs .nav-link {
  color: var(--text-color);
  font-weight: 500;
  border: none;
  padding: 12px 20px;
  margin-right: 5px;
  border-radius: 6px 6px 0 0;
  transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
  background-color: var(--light-blue);
  color: var(--primary-blue);
}

.nav-tabs .nav-link.active {
  background-color: var(--primary-blue);
  color: white;
  font-weight: 600;
}

/* Style des tableaux */
.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin-bottom: 0;
}

.table thead th {
  background-color: var(--light-blue);
  color: var(--primary-blue);
  font-weight: 600;
  border: none;
  padding: 12px 15px;
}

.table tbody td {
  padding: 12px 15px;
  vertical-align: middle;
  border-bottom: 1px solid rgba(67, 97, 238, 0.1);
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.table tbody tr:hover {
  background-color: rgba(67, 97, 238, 0.03);
}

/* Indicateurs de statut */
.status-pending {
  color: var(--warning-yellow);
  font-weight: 500;
}

.status-approved {
  color: var(--success-green);
  font-weight: 500;
}

.status-rejected {
  color: var(--danger-red);
  font-weight: 500;
}

.text-success {
  color: var(--success-green) !important;
}

.text-danger {
  color: var(--danger-red) !important;
}

/* Icônes */
.bi {
  margin-right: 8px;
}

/* Ajustements responsives */
@media (max-width: 768px) {
  .row {
    flex-direction: column;
  }
  
  .col-md-6 {
    width: 100%;
    margin-bottom: 20px;
  }
  
  .nav-tabs .nav-link {
    padding: 10px 15px;
    font-size: 0.9rem;
  }
  
  .table-responsive {
    border: 1px solid rgba(67, 97, 238, 0.1);
    border-radius: 8px;
    overflow-x: auto;
  }
}

/* Animation pour les éléments de formulaire */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.card-body form {
  animation: fadeIn 0.5s ease-out;
}
   .return-button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            min-width: 150px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .return-button:hover {
            background-color: #748cf5ff;;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
    </style>
</head>
<body>
    
    <div class="container py-4">
        <div style="display:flex;gap:20em;margin-top:0,5em">
              <h2 class="mb-4"><i class="bi bi-calendar-event"></i> Gestion des Congés et Absences</h2> 
              <h3><a href="dash.php" class="nav-button return-button">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a> </h3>
        </div>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button" role="tab">Demandes de congé</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="absence-tab" data-bs-toggle="tab" data-bs-target="#absence" type="button" role="tab">Absences</button>
            </li>
        </ul>
        
        <div class="tab-content" id="myTabContent">
            <!-- Onglet Demandes de congé -->
            <div class="tab-pane fade show active" id="leave" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-send-plus"></i> Demander un congé</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="leave_type" class="form-label">Type de congé</label>
                                        <select class="form-select" id="leave_type" name="leave_type" required>
                                            <option value="">Sélectionner un type de congé</option>
                                            <?php foreach ($leaveTypes as $type): ?>
                                                <option value="<?= $type['id'] ?>"><?= $type['name'] ?> (max <?= $type['max_days'] ?> jours)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Date de début</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">Date de fin</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Motif</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" name="request_leave" class="btn btn-primary">
                                        <i class="bi bi-send"></i> Soumettre la demande
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="bi bi-list-check"></i> Mes demandes de congé</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($leaveRequests)): ?>
                                    <p>Aucune demande de congé trouvée.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Dates</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($leaveRequests as $request): ?>
                                                    <tr>
                                                        <td><?= $request['leave_type_name'] ?></td>
                                                        <td><?= date('d M Y', strtotime($request['start_date'])) ?> au <?= date('d M Y', strtotime($request['end_date'])) ?></td>
                                                        <td>
                                                            <span class="status-<?= $request['status'] ?>">
                                                                <?= ucfirst($request['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Absences -->
            <div class="tab-pane fade" id="absence" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Signaler une absence</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="absence_date" class="form-label">Date de l'absence</label>
                                        <input type="date" class="form-control" id="absence_date" name="absence_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="absence_reason" class="form-label">Motif</label>
                                        <textarea class="form-control" id="absence_reason" name="absence_reason" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="is_justified" name="is_justified">
                                        <label class="form-check-label" for="is_justified">Cette absence est justifiée</label>
                                    </div>
                                    <div class="mb-3" id="justificationContainer" style="display: none;">
                                        <label for="justification_doc" class="form-label">Document de justification</label>
                                        <input type="file" class="form-control" id="justification_doc" name="justification_doc">
                                    </div>
                                    <button type="submit" name="report_absence" class="btn btn-warning">
                                        <i class="bi bi-send"></i> Signaler l'absence
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Mes absences</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($absences)): ?>
                                    <p>Aucune absence enregistrée.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Motif</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($absences as $absence): ?>
                                                    <tr>
                                                        <td><?= date('d M Y', strtotime($absence['date'])) ?></td>
                                                        <td><?= $absence['reason'] ?></td>
                                                        <td>
                                                            <?= $absence['is_justified'] ? 
                                                                '<span class="text-success">Justifiée</span>' : 
                                                                '<span class="text-danger">Non justifiée</span>' ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
               
<div class="alert alert-info mt-3">
    <i class="bi bi-info-circle"></i> Toutes les absences nécessitent une approbation. Les absences non justifiées dépassant 
    8 heures (temps de travail journalier) peuvent entraîner des mesures disciplinaires.
</div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Afficher/masquer le champ de document de justification basé sur la case à cocher
        document.getElementById('is_justified').addEventListener('change', function() {
            document.getElementById('justificationContainer').style.display = this.checked ? 'block' : 'none';
        });
        
        // Initialiser la fonctionnalité des onglets
        var tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElms.forEach(function(tabEl) {
            tabEl.addEventListener('click', function (event) {
                event.preventDefault();
                var tab = new bootstrap.Tab(tabEl);
                tab.show();
            });
        });
    </script>
</body>
</html>