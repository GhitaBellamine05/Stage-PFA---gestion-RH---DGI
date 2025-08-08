<?php
ob_start();
session_start();
include "sql/db.php";

// Check if user is admin safely
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
$currentUserPPR = $_COOKIE['PPR'] ?? null;

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $PPR = $_GET["PPR"] ?? '';
    
    if(isset($_POST["detail"])) {
        header("Location: admin/details.php?PPR=".urlencode($PPR));
        exit();
    }
    elseif(isset($_POST["us"])) {
        header("Location: admin/us.php?PPR=".urlencode($PPR));
        exit();
    }
    elseif(isset($_POST["diploms"])) {
        header("Location: admin/diploms.php?PPR=".urlencode($PPR));
        exit();
    }
    elseif(isset($_POST["historique"])) {
        header("Location: admin/historique.php?PPR=".urlencode($PPR));
        exit();
    }
    elseif($isAdmin && isset($_POST["delete_employee"])) {
        try {
            $deletePPR = $_POST["delete_employee"];
            
            // Prevent self-deletion
            if($currentUserPPR && $deletePPR == $currentUserPPR) {
                header("Location: ".$_SERVER['PHP_SELF']."?PPR=".$PPR."&error=self_delete");
                exit();
            }
            
            // Start transaction for atomic operations
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();
            
            // Delete all related records
            $tables = [
                'diplome', 
                'hist_affectation', 
                'us', 
                'reclamations',
                'forum_posts',
                'forum_comments',
                'utilisateurs'
            ];
            
            foreach ($tables as $table) {
                $stmt = $db->prepare("DELETE FROM $table WHERE PPR = ?");
                $stmt->execute([$deletePPR]);
            }
            
            $db->commit();
            
            header("Location: dash.php?deleted=1");
            exit();
            
        } catch(PDOException $e) {
            // Rollback on error
            if(isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            error_log("Delete error: ".$e->getMessage());
            header("Location: ".$_SERVER['PHP_SELF']."?PPR=".$PPR."&error=1");
            exit();
        }
    }
}

$PPR = $_GET["PPR"] ?? '';
$success = isset($_GET['deleted']);
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Employé</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-bg: #f0f7ff;
            --table-bg: #ffffff;
            --text-dark: #2e3a4d;
            --text-light: #ffffff;
            --border-color: #c5e0ff;
            --highlight-blue: #e6f2ff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
        }
        
        .dashboard-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--table-bg);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }
        
        h1 {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .nav-button {
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
        
        .nav-button:hover {
             background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
        
        .return-button {
            background-color: #6c757d;
            width: 100%;
            max-width: 200px;
            margin: 0 auto;
        }
        
        .return-button:hover {
            background-color: #5a6268;
        }
        
        .user-info {
            background-color: var(--highlight-blue);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: var(--primary-blue);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
        
        @media (max-width: 600px) {
            .button-group {
                flex-direction: column;
            }
            
            .nav-button {
                width: 100%;
            }
        } .delete-button {
            background-color: #dc3545;
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
        }
        
        .delete-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .confirmation-box {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        
        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord employé</h1>
    
        <?php if($PPR): ?>
        <div class="user-info">
            <i class="fas fa-user-tie"></i>
            <p>Visualisation de l'employé avec PPR : <strong><?php echo htmlspecialchars($PPR); ?></strong></p>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                Employé et tous les enregistrements liés supprimés avec succès !
            </div>
        <?php elseif($error === '1'): ?>
            <div class="alert alert-danger">
                Erreur lors de la suppression de l'employé. Veuillez réessayer.
            </div>
        <?php elseif($error === 'self_delete'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Restriction de sécurité : Vous ne pouvez pas supprimer votre propre profil !
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="button-group">
                <button type="submit" name="detail" class="nav-button">
                    <i class="fas fa-id-card"></i> Détails personnels
                </button>
                <button type="submit" name="diploms" class="nav-button">
                    <i class="fas fa-graduation-cap"></i> Diplômes
                </button>
                <button type="submit" name="historique" class="nav-button">
                    <i class="fas fa-briefcase"></i> Historique professionnel
                </button>
                <button type="submit" name="us" class="nav-button">
                    <i class="fas fa-cog"></i> Unités
                </button>
                
                <?php if($isAdmin && $PPR): ?>
                <button type="button" id="deleteBtn" class="delete-button">
                    <i class="fas fa-trash-alt"></i> Supprimer l'employé
                </button>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Boîte de dialogue de confirmation -->
        <div id="confirmationDialog" class="confirmation-dialog">
            <div class="confirmation-box">
                <h3>Confirmer la suppression</h3>
                <p>Êtes-vous sûr de vouloir supprimer cet employé ? Cette action est irréversible.</p>
                <div class="confirmation-buttons">
                    <form action="" method="POST" style="display: inline;">
                        <input type="hidden" name="delete_employee" value="<?php echo $PPR; ?>">
                        <button type="submit" class="delete-button">Confirmer la suppression</button>
                    </form>
                    <button id="cancelDelete" class="nav-button">Annuler</button>
                </div>
            </div>
        </div>
        
        <div class="button-container">
            <a href="dash.php" class="nav-button return-button">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        
        <div class="footer">
            <i class="far fa-copyright"></i>
            <p>Système de gestion des employés <?php echo date('Y'); ?></p>
        </div>
    </div>

    <script>
        // Gérer la confirmation de suppression
        document.getElementById('deleteBtn')?.addEventListener('click', function() {
            document.getElementById('confirmationDialog').style.display = 'flex';
        });
        
        document.getElementById('cancelDelete')?.addEventListener('click', function() {
            document.getElementById('confirmationDialog').style.display = 'none';
        });
    </script>
</body>
</html>
<?php
ob_end_flush(); // Envoyer le tampon de sortie
?>