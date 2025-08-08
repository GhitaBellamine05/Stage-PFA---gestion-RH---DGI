<?php
session_start();
include "sql/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$currentUserPPR = $_SESSION['user']['PPR'];
$userRole = $_COOKIE['role'] ?? 'user';

// Handle new complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    $stmt = $db->prepare("INSERT INTO reclamations (PPR, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$currentUserPPR, $title, $content]);
    header("Location: reclamations.php");
    exit();
}

// Handle admin response
if ($userRole === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_response'])) {
    $complaint_id = $_POST['complaint_id'];
    $response = $_POST['response'];
    $status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE reclamations SET admin_response = ?, status = ?, resolved_at = NOW() WHERE id = ?");
    $stmt->execute([$response, $status, $complaint_id]);
    header("Location: reclamations.php");
    exit();
}

// Get complaints - users only see their own, admins see all
if ($userRole === 'admin') {
    $complaints = $db->query("
        SELECT r.*, u.nom, u.prenom 
        FROM reclamations r
        JOIN utilisateurs u ON r.PPR = u.PPR
        ORDER BY r.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare("
        SELECT r.*, u.nom, u.prenom 
        FROM reclamations r
        JOIN utilisateurs u ON r.PPR = u.PPR
        WHERE r.PPR = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$currentUserPPR]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réclamations</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS remains unchanged */
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-blue: #e6f2ff;
            --lighter-blue: #f0f8ff;
            --dark-blue: #2c3e50;
            --light-gray: #f8f9fa;
            --white: #ffffff;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e6f0ff;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1, h2, h3, h4 {
            color: #4361ee;
        }
        
        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(52, 152, 219, 0.2);
        }
        
        .complaint-form {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(52, 152, 219, 0.1);
        }
        
        .complaint-form h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
        }
        
        .complaint-form input[type="text"],
        .complaint-form textarea,
        .response-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            transition: border 0.3s;
            font-family: inherit;
        }
        
        .complaint-form input[type="text"]:focus,
        .complaint-form textarea:focus,
        .response-form textarea:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .complaint-form textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .complaint-card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(52, 152, 219, 0.1);
        }
        
        .complaint-card h3 {
            color: var(--dark-blue);
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        .complaint-author {
            font-weight: 600;
            color: var(--secondary-blue);
        }
        
        .complaint-date {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-left: 10px;
        }
        
        .complaint-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-resolved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .complaint-content {
            margin-bottom: 20px;
            padding: 15px;
            background-color: var(--light-blue);
            border-radius: 6px;
            line-height: 1.6;
        }
        
        .admin-response {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--lighter-blue);
            border-radius: 6px;
            border-left: 4px solid var(--primary-blue);
        }
        
        .admin-response h4 {
            color: var(--primary-blue);
            margin-bottom: 10px;
        }
        
        .response-form {
            margin-top: 20px;
            padding: 20px;
            background-color: var(--light-gray);
            border-radius: 6px;
        }
        
        .response-form h4 {
            color: var(--primary-blue);
            margin-bottom: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            transform: translateY(-1px);
        }
        
        select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            width: 100%;
            max-width: 200px;
        }
        
        .no-complaints {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        .no-complaints i {
            font-size: 2.5em;
            color: #bdc3c7;
            margin-bottom: 15px;
        }
        
        .resolved-date {
            color: #7f8c8d;
            font-size: 0.85em;
            margin-top: 10px;
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
    <div class="main-content">
        <div class="container">
            <div class="complaint-header">
                <h1><i class="fas fa-exclamation-circle" style="color: var(--primary-blue);"></i> Gestion des Réclamations</h1>
                <br>   <a href="dash.php" class="return-button">
                <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
            </a>
            </div>
            
            <!-- Complaint creation form (only for regular users) -->
            <?php if ($userRole !== 'admin'): ?>
            <div class="complaint-form">
                <h3><i class="fas fa-plus-circle"></i> Soumettre une Réclamation</h3>
                <form method="POST">
                    <input type="text" name="title" placeholder="Titre de la réclamation" required>
                    <textarea name="content" placeholder="Décrivez votre réclamation en détail..." required></textarea>
                    <button type="submit" name="submit_complaint" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Soumettre
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Complaints list -->
            <div class="complaints-list">
                <?php if (empty($complaints)): ?>
                    <div class="no-complaints">
                        <i class="fas fa-info-circle"></i>
                        <p>Aucune réclamation trouvée</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="complaint-card">
                            <div class="complaint-header">
                                <div>
                                    <?php if ($userRole === 'admin'): ?>
                                        <span class="complaint-author"><?= htmlspecialchars($complaint['prenom'] . ' ' . $complaint['nom']) ?></span>
                                    <?php else: ?>
                                        <span class="complaint-author">Votre réclamation</span>
                                    <?php endif; ?>
                                    <span class="complaint-date"><?= date('d/m/Y H:i', strtotime($complaint['created_at'])) ?></span>
                                </div>
                                <span class="complaint-status status-<?= str_replace(' ', '_', $complaint['status']) ?>">
                                    <?= 
                                        $complaint['status'] === 'pending' ? 'En Attente' : 
                                        ($complaint['status'] === 'in_progress' ? 'En Cours' : 'Résolue')
                                    ?>
                                </span>
                            </div>
                            <h3><?= htmlspecialchars($complaint['title']) ?></h3>
                            <div class="complaint-content">
                                <?= nl2br(htmlspecialchars($complaint['content'])) ?>
                            </div>
                            
                            <?php if ($complaint['admin_response']): ?>
                                <div class="admin-response">
                                    <h4><i class="fas fa-reply"></i> Réponse de l'Administrateur</h4>
                                    <p><?= nl2br(htmlspecialchars($complaint['admin_response'])) ?></p>
                                    <?php if ($complaint['resolved_at']): ?>
                                        <p class="resolved-date"><small>Résolue le: <?= date('d/m/Y H:i', strtotime($complaint['resolved_at'])) ?></small></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($userRole === 'admin' && $complaint['status'] !== 'resolved'): ?>
                                <div class="response-form">
                                    <h4><i class="fas fa-reply"></i> Répondre à cette réclamation</h4>
                                    <form method="POST">
                                        <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                                        <textarea name="response" placeholder="Votre réponse..." required></textarea>
                                        <select name="status">
                                            <option value="in_progress">En Cours</option>
                                            <option value="resolved">Résolue</option>
                                        </select>
                                        <button type="submit" name="admin_response" class="btn btn-primary">
                                            <i class="fas fa-check-circle"></i> Soumettre la Réponse
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>