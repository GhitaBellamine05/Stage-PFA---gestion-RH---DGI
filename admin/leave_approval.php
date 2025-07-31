<?php
session_start();
include("../sql/db.php");

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Gérer l'approbation/rejet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE leave_requests SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $action);
        $stmt->bindParam(':id', $requestId);
        $stmt->execute();
        
        $success = "Demande de congé mise à jour avec succès !";
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour de la demande : " . $e->getMessage();
    }
}

// Récupérer les demandes en attente
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT lr.*, u.nom, u.prenom, lt.name as leave_type_name 
                         FROM leave_requests lr
                         JOIN utilisateurs u ON lr.employee_id = u.PPR
                         JOIN leave_types lt ON lr.leave_type_id = lt.id
                         WHERE lr.status = 'pending'
                         ORDER BY lr.created_at ASC");
    $stmt->execute();
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation des Demandes de Congé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --light-blue: #e6f2ff;
            --medium-blue: #4259c2ff;
            --dark-blue: #4361ee;
            --accent-blue: #88a5e2ff;
        }
        
        body {
            background-color: #e6f0ff;
            background-image: linear-gradient(to bottom, var(--light-blue), #f8f9fa);
            min-height: 100vh;
        }
        
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 102, 204, 0.1);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            border: 1px solid var(--medium-blue);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 102, 204, 0.1);
        }
        
        .card-header {
            background-color: var(--dark-blue) !important;
            border-bottom: 1px solid var(--medium-blue);
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .table th {
            background-color: var(--medium-blue);
            color: #333;
        }
        
        .alert {
            border-radius: 5px;
        }
        
        h2 {
            color: var(--dark-blue);
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .bi-clipboard-check {
            color: var(--dark-blue);
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--light-blue);
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
              <h2 class="mb-4"><i class="bi bi-clipboard-check"></i> Validation des Demandes de Congé</h2>
              <h3><a href="../dash.php" class="nav-button return-button">
                <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
        </a> </h3>
        </div>
        
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Demandes en Attente</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pendingRequests)): ?>
                    <p>Aucune demande de congé en attente.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="card-header bg-primary text-white">Employé</th>
                                    <th class="card-header bg-primary text-white">Type de Congé</th>
                                    <th class="card-header bg-primary text-white">Dates</th>
                                    <th class="card-header bg-primary text-white">Motif</th>
                                    <th class="card-header bg-primary text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <tr>
                                        <td><?= $request['nom'] ?> <?= $request['prenom'] ?></td>
                                        <td><?= $request['leave_type_name'] ?></td>
                                        <td>
                                            <?= date('d M Y', strtotime($request['start_date'])) ?> au 
                                            <?= date('d M Y', strtotime($request['end_date'])) ?>
                                            (<?= (strtotime($request['end_date']) - strtotime($request['start_date'])) / (60 * 60 * 24) + 1 ?> jours)
                                        </td>
                                        <td><?= $request['reason'] ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" name="action" value="approved" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-circle"></i> Approuver
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <button type="submit" name="action" value="rejected" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-x-circle"></i> Rejeter
                                                </button>
                                            </form>
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