<?php
session_start();
include("sql/db.php");

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['user']['PPR'];

// Get user's warnings
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        "SELECT w.*, a.date as absence_date 
         FROM absence_warnings w
         JOIN absences a ON w.absence_id = a.id
         WHERE w.employee_id = :employee_id
         ORDER BY w.warning_date DESC"
    );
    $stmt->bindParam(':employee_id', $currentUserId);
    $stmt->execute();
    $warnings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle warning acknowledgment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acknowledge'])) {
    $warningId = $_POST['warning_id'];
    
    try {
        $stmt = $db->prepare("UPDATE absence_warnings SET is_acknowledged = TRUE WHERE id = :id");
        $stmt->bindParam(':id', $warningId);
        $stmt->execute();
        
        $success = "Avertissement confirmé avec succès !";
        header("Refresh:0"); // Refresh the page
    } catch (PDOException $e) {
        $error = "Erreur lors de la confirmation de l'avertissement : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avertissements d'Absence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Blue Light Theme for Absence Warnings */
        :root {
          --primary-blue: #4361ee;
          --light-blue: #e6f0ff;
          --soft-blue: #f5f9ff;
          --dark-blue: #1a2a6c;
          --text-color: #2c3e50;
          --light-gray: #f8f9fa;
          --success-green: #28a745;
          --warning-yellow: #ffc107;
          --danger-red: #dc3545;
        }
        h2{
          color: #4361ee
        }

        body {
          background-color: #e6f0ff;
          color: var(--text-color);
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
          max-width: 1200px;
        }

        /* Card Styling */
        .card {
          border: none;
          border-radius: 10px;
          box-shadow: 0 4px 12px rgba(67, 97, 238, 0.1);
          overflow: hidden;
        }

        .card-header {
          background-color: var(--primary-blue);
          color: white;
          padding: 1.25rem;
          border-bottom: none;
        }

        .card-header h5 {
          font-weight: 600;
        }

        .card-body {
          padding: 1.5rem;
          background-color: white;
        }

        /* Alert Styling */
        .alert {
          border-radius: 8px;
          border: none;
          padding: 1rem 1.25rem;
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

        /* Table Styling */
        .table {
          width: 100%;
          border-collapse: separate;
          border-spacing: 0;
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

        /* Status Badges */
        .badge {
          padding: 6px 10px;
          font-weight: 500;
          font-size: 0.75rem;
          border-radius: 20px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }

        .bg-success {
          background-color: rgba(40, 167, 69, 0.1) !important;
          color: var(--success-green) !important;
        }

        .bg-warning {
          background-color: rgba(255, 193, 7, 0.1) !important;
          color: var(--warning-yellow) !important;
        }

        /* Buttons */
        .btn {
          border-radius: 6px;
          padding: 8px 16px;
          font-weight: 500;
          transition: all 0.2s ease;
        }

        .btn-primary {
          background-color: var(--primary-blue);
          border-color: var(--primary-blue);
        }

        .btn-primary:hover {
          background-color: var(--dark-blue);
          border-color: var(--dark-blue);
          transform: translateY(-1px);
        }

        .btn-sm {
          padding: 5px 12px;
          font-size: 0.85rem;
        }

        /* Table Row States */
        .table-success {
          background-color: rgba(40, 167, 69, 0.05) !important;
        }

        .table-warning {
          background-color: rgba(255, 193, 7, 0.05) !important;
        }

        /* Icons */
        .bi {
          margin-right: 6px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
          .table-responsive {
            border: 1px solid rgba(67, 97, 238, 0.1);
            border-radius: 8px;
            overflow-x: auto;
          }
          
          .table thead {
            display: none;
          }
          
          .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
          }
          
          .table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid rgba(67, 97, 238, 0.05);
          }
          
          .table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--primary-blue);
            margin-right: 1rem;
          }
          
          .table tbody td:last-child {
            border-bottom: none;
          }
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
             <h2 class="mb-4"><i class="bi bi-exclamation-triangle-fill"></i> Avertissements d'Absence</h2>
         <h3><a href="dash.php" class="return-button">
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
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Mes Avertissements</h5>
            </div>
            <div class="card-body">
                <?php if (empty($warnings)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> Vous n'avez aucun avertissement d'absence.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Date d'Absence</th>
                                    <th>Message</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($warnings as $warning): ?>
                                    <tr class="<?= $warning['is_acknowledged'] ? 'table-success' : 'table-warning' ?>">
                                        <td><?= date('d M, Y', strtotime($warning['warning_date'])) ?></td>
                                        <td><?= date('d M, Y', strtotime($warning['absence_date'])) ?></td>
                                        <td><?= $warning['message'] ?></td>
                                        <td>
                                            <?= $warning['is_acknowledged'] ? 
                                                '<span class="badge bg-success">Confirmé</span>' : 
                                                '<span class="badge bg-warning">En Attente</span>' ?>
                                        </td>
                                        <td>
                                            <?php if (!$warning['is_acknowledged']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="warning_id" value="<?= $warning['id'] ?>">
                                                    <button type="submit" name="acknowledge" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-check-circle"></i> Confirmer
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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