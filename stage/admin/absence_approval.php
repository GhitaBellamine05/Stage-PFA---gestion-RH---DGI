<?php
session_start();
include("../sql/db.php");

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Obtenir la politique d'absence
try {
    $db = Database::getInstance()->getConnection();
    $policy = $db->query("SELECT * FROM absence_policies LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $maxUnjustifiedHours = $policy['max_unjustified_hours'] ?? 8;
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Gérer l'approbation/le refus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $absenceId = $_POST['absence_id'];
    $action = $_POST['action'];
    
    try {
        $db->beginTransaction();
        
        // Mettre à jour le statut de l'absence
        $stmt = $db->prepare("UPDATE absences SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $action);
        $stmt->bindParam(':id', $absenceId);
        $stmt->execute();
        
        // Si refusée et non justifiée, vérifier si un avertissement doit être envoyé
        if ($action === 'rejected') {
            $absence = $db->query("SELECT * FROM absences WHERE id = $absenceId")->fetch(PDO::FETCH_ASSOC);
            
            if (!$absence['is_justified']) {
                // Calculer le total des heures non justifiées pour l'employé
                $employeeId = $absence['employee_id'];
                $totalHours = $db->query(
                    "SELECT SUM(8) as total_hours 
                     FROM absences 
                     WHERE employee_id = $employeeId 
                     AND status = 'rejected' 
                     AND is_justified = FALSE"
                )->fetch(PDO::FETCH_ASSOC)['total_hours'] ?? 0;
                
                // Envoyer un avertissement si le seuil est dépassé
                if ($totalHours >= $maxUnjustifiedHours && !$absence['warning_sent']) {
                    $warningDate = date('Y-m-d');
                    $message = $policy['warning_message'];
                    
                    $stmt = $db->prepare(
                        "INSERT INTO absence_warnings (employee_id, absence_id, warning_date, message) 
                         VALUES (:employee_id, :absence_id, :warning_date, :message)"
                    );
                    $stmt->bindParam(':employee_id', $employeeId);
                    $stmt->bindParam(':absence_id', $absenceId);
                    $stmt->bindParam(':warning_date', $warningDate);
                    $stmt->bindParam(':message', $message);
                    $stmt->execute();
                    
                    // Marquer l'avertissement comme envoyé
                    $db->exec("UPDATE absences SET warning_sent = TRUE WHERE id = $absenceId");
                }
            }
        }
        
        $db->commit();
        $success = "Statut de l'absence mis à jour avec succès !";
    } catch (PDOException $e) {
        $db->rollBack();
        $error = "Erreur lors du traitement de l'absence : " . $e->getMessage();
    }
}

// Obtenir les absences en attente
try {
    $stmt = $db->prepare(
        "SELECT a.*, u.nom, u.prenom 
         FROM absences a
         JOIN utilisateurs u ON a.employee_id = u.PPR
         WHERE a.status = 'pending'
         ORDER BY a.date DESC"
    );
    $stmt->execute();
    $pendingAbsences = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approbation des absences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --light-blue: #e6f2ff;
            --medium-blue: #4259c2ff;
            --dark-blue: #4361ee;
            --accent-blue: #xxff;
            --soft-shadow: 0 4px 12px #c7d3edff;
        }

        body {
            background-color: #e6f0ff;
            background-image: linear-gradient(to bottom, var(--light-blue), #f8f9fa);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: #f2f6fdff;
            border-radius: 12px;
            box-shadow: var(--soft-shadow);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--medium-blue);
        }

        .card {
            border: 1px solid var(--medium-blue);
            border-radius: 10px;
            box-shadow: var(--soft-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            background-color: #3498db;
            border-bottom: 1px solid var(--medium-blue);
            padding: 1rem 1.5rem;
        }
        .card-body {
            background-color: whitesmoke !important;
            border-bottom: 1px solid var(--medium-blue);
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        .btn-success {
            background-color: #2ecc71;
            border-color: #27ae60;
        }

        .btn-danger {
            background-color: #e74c3c;
            border-color: #c0392b;
        }

        .btn-info {
            background-color: var(--accent-blue);
            border-color: var(--dark-blue);
        }

        .btn-warning {
            background-color: #f39c12;
            border-color: #e67e22;
        }

        .table th {
            background-color: #4361ee;
            color: white;
            font-weight: 500;
            padding: 12px 15px;
        }

        .table td {
            padding: 10px 15px;
            vertical-align: middle;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        h2 {
            color: var(--dark-blue);
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 12px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .bi {
            margin-right: 6px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--light-blue);
        }

        .table-striped tbody tr:hover {
            background-color: var(--medium-blue);
            transition: background-color 0.2s ease;
        }

        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .text-dark {
            color: #333 !important;
        }
          .return-button {
            background-color: #4361ee;
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
            margin-right:-1,5em;
            text-decoration: none;

        }
        
        .return-button:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div style="display:flex;gap:20em;margin-top:0,5em">
              <h2 class="mb-4"><i class="bi bi-clipboard-x"></i> Approbation des absences</h2> 
              <h3><a href="../dash.php" class="return-button">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a> </h3>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-policy"></i> Politique d'absence</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-clock"></i> Heures maximales non justifiées :</strong> 
                        <span class="badge bg-primary"><?= $maxUnjustifiedHours ?> heures</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="bi bi-chat-text"></i> Message d'avertissement :</strong> 
                        "<?= $policy['warning_message'] ?>"</p>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <a href="absence_policy.php" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Modifier la politique
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Absences en attente</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pendingAbsences)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                        <h5 class="mt-3">Aucune absence en attente</h5>
                        <p class="text-muted">Toutes les demandes d'absence ont été traitées</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover ">
                            <thead>
                                <tr>
                                    <th class="bg-primary"><i class="bi bi-person"></i> Employé</th>
                                    <th  class="bg-primary"><i class="bi bi-calendar"></i> Date</th>
                                    <th  class="bg-primary"><i class="bi bi-chat-left-text"></i> Motif</th>
                                    <th  class="bg-primary"><i class="bi bi-check-circle"></i> Justifiée</th>
                                    <th  class="bg-primary"><i class="bi bi-file-earmark"></i> Document</th>
                                    <th  class="bg-primary"><i class="bi bi-gear"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingAbsences as $absence): ?>
                                    <tr>
                                        <td><?= $absence['nom'] ?> <?= $absence['prenom'] ?></td>
                                        <td><?= date('d M Y', strtotime($absence['date'])) ?></td>
                                        <td><?= $absence['reason'] ?></td>
                                        <td>
                                            <span class="badge <?= $absence['is_justified'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $absence['is_justified'] ? 'Oui' : 'Non' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($absence['justification_doc']): ?>
                                                <a href="<?= $absence['justification_doc'] ?>" target="_blank" class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Voir
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Aucun</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form method="POST">
                                                    <input type="hidden" name="absence_id" value="<?= $absence['id'] ?>">
                                                    <button type="submit" name="action" value="approved" class="btn btn-success btn-sm">
                                                        <i class="bi bi-check"></i> Approuver
                                                    </button>
                                                </form>
                                                <form method="POST">
                                                    <input type="hidden" name="absence_id" value="<?= $absence['id'] ?>">
                                                    <button type="submit" name="action" value="rejected" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-x"></i> Rejeter
                                                    </button>
                                                </form>
                                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
