<?php 
include "../sql/db.php";
$PPR = $_GET["PPR"] ?? null;

if(isset($_POST["add"])){
    header("Location: add_historique.php?PPR=$PPR");
    exit();
}

$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM hist_affectation WHERE PPR = :PPR";
$stmt = $db->prepare($sql);
$stmt->bindParam(":PPR", $PPR);
$stmt->execute();
$historiques = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST["supprimer"])){
    $id = $_POST["id"];
    $sql1 = "DELETE FROM hist_affectation WHERE id = :ID";
    $stmt1 = $db->prepare($sql1);
    $stmt1->bindParam(":ID", $id);
    $stmt1->execute();
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}
$role = $_COOKIE['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique d'affectation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-bg: #e6f0ff;
            --table-bg: #ffffff;
            --text-dark: #2e3a4d;
            --text-light: #ffffff;
            --border-color: #c5e0ff;
            --error-red: #ff6b6b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            padding: 30px;
            color: var(--text-dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--table-bg);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 30px;
            border: 1px solid var(--border-color);
        }
        
        h2 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-blue);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        
        .add-btn:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 155, 213, 0.3);
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background-color: var(--primary-blue);
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        tr {
            background-color: var(--table-bg);
            transition: all 0.2s ease;
        }
        
        tr:nth-child(even) {
            background-color: #f8fbff;
        }
        
        tr:hover {
            background-color: #e6f2ff;
        }
        
        .action-cell {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .edit-btn {
            background-color: var(--secondary-blue);
            color: var(--text-dark);
            border: none;
            cursor: pointer;
        }
        
        .edit-btn:hover {
            background-color: #748cf5ff;
        }
        
        .delete-form {
            display: inline;
        }
        
        .delete-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: var(--error-red);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .delete-btn:hover {
            background-color: #ff5252;
        }
        
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            background-color: rgba(91, 155, 213, 0.1);
            color: var(--primary-blue);
            border-left: 4px solid var(--primary-blue);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .container {
                padding: 20px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            th, td {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .action-cell {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-history"></i> Historique des Affectations</h2>
        
        <form method="POST" class="text-end">
                <?php if ($role === 'admin'): ?>
            <button type="submit" name="add" class="add-btn">
                <i class="fas fa-plus-circle"></i> Ajouter
            </button>
            <?php endif; ?>
             <a class="add-btn" href="../gestion.php?PPR=<?php echo htmlspecialchars($_GET['PPR'] ?? '', ENT_QUOTES); ?>" class="btn btn-secondary">
             <i class="fas fa-arrow-left"></i> Retour
            </a>

        </form>
        
        <?php if (!empty($historiques)): ?>
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-code"></i> Code US</th>
                        <th><i class="fas fa-calendar-start"></i> Date Début</th>
                        <th><i class="fas fa-calendar-end"></i> Date Fin</th>
                        <?php if ($role === 'admin'): ?> <th><i class="fas fa-cog"></i> Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historiques as $historique): ?>
                        <tr>
                            <td><?= htmlspecialchars($historique["Code"]) ?></td>
                            <td><?= htmlspecialchars($historique["date_debut"]) ?></td>
                            <td><?= htmlspecialchars($historique["date_fin"]) ?></td>
                            <td class="action-cell">
                                 <?php if ($role === 'admin'): ?>
                                <a href="historique_edit.php?ID=<?= htmlspecialchars($historique["id"]) ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <form method="POST" class="delete-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($historique["id"]) ?>">
                                    <button type="submit" name="supprimer" class="delete-btn">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                </form>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert">Aucun enregistrement trouvé pour ce PPR</div>
        <?php endif; ?>
    </div>
</body>
</html>