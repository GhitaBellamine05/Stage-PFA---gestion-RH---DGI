<?php
include "sql/db.php";
$db = Database::getInstance()->getConnection();

// Gestion des filtres
$filter_lib = $_GET['filter_lib'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$filter_niveau = $_GET['filter_niveau'] ?? '';

// Gestion de la suppression
if (isset($_GET['delete_id'])) {
    $id_to_delete = $_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM diplome WHERE id = ?");
    $stmt->execute([$id_to_delete]);
    header("Location: ?");
    exit;
}

// Vérifier si nous visualisons les employés avec un diplôme spécifique
$view_employees = isset($_GET['view_employees']);
$current_diplome_id = $_GET['id'] ?? null;

// Construire la requête de base pour les diplômes
$sql = "SELECT * FROM diplome WHERE 1=1";
$params = [];

if (!empty($filter_lib)) {
    $sql .= " AND Lib LIKE :lib";
    $params[':lib'] = "%$filter_lib%";
}

if (!empty($filter_type)) {
    $sql .= " AND type = :type";
    $params[':type'] = $filter_type;
}

if (!empty($filter_niveau)) {
    $sql .= " AND niveau = :niveau";
    $params[':niveau'] = $filter_niveau;
}

$sql .= " ORDER BY Lib";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$diplomes_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtenir les valeurs distinctes pour les menus déroulants
$types = $db->query("SELECT DISTINCT type FROM diplome ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
$niveaux = $db->query("SELECT DISTINCT niveau FROM diplome ORDER BY niveau")->fetchAll(PDO::FETCH_COLUMN);

// Si on visualise les employés, obtenir les infos du diplôme et ses employés
$current_diplome = null;
$employees = [];
if ($view_employees && $current_diplome_id) {
    // Obtenir les infos du diplôme actuel
    $stmt = $db->prepare("SELECT * FROM diplome WHERE id = ?");
    $stmt->execute([$current_diplome_id]);
    $current_diplome = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtenir les employés avec ce diplôme - CORRECTED QUERY
    $stmt = $db->prepare("
        SELECT u.* 
        FROM utilisateurs u
        JOIN diplome d ON u.PPR = d.PPR
        WHERE d.id = ?
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute([$current_diplome_id]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$role = $_COOKIE['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Diplômes</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-bg: #f0f7ff;
            --table-bg: #ffffff;
            --text-dark: #2e3a4d;
            --text-light: #ffffff;
            --border-color: #c5e0ff;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #e6f0ff;
            color: var(--text-dark);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: var(--primary-blue);
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        /* Section Filtre */
        .filter-section {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        input:focus,
        select:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 155, 213, 0.2);
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-button:hover {
            text-decoration: underline;
        }
        
        /* Grille des Cartes de Diplômes */
        .diplomes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .diplome-card {
            background-color: var(--table-bg);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .diplome-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .diplome-card h3 {
            color: var(--primary-blue);
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        .diplome-info {
            margin-bottom: 15px;
        }
        
        .diplome-info p {
            margin-bottom: 8px;
            display: flex;
        }
        
        .diplome-info strong {
            min-width: 120px;
            display: inline-block;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #748cf5ff;
        }
        
        .btn-secondary {
            background-color: var(--secondary-blue);
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background-color: #8ab5e0;
        }
        
        .btn-danger {
            background-color: var(--error-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d35400;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        /* Liste des Employés */
        .employees-section {
            margin-top: 40px;
            background-color: var(--table-bg);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .employees-list {
            margin-top: 20px;
        }
        
        .employee-card {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .employee-card:last-child {
            border-bottom: none;
        }
        
        .employee-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            overflow: hidden;
        }
        
        .employee-photo i {
            font-size: 20px;
            color: var(--primary-blue);
        }
        
        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .employee-info {
            flex: 1;
        }
        
        .employee-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .employee-position {
            color: #666;
            font-size: 14px;
        }
        
        .no-employees, .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .diplomes-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <i class="fas fa-graduation-cap"></i>
            <?= $view_employees ? "Employés avec ce diplôme" : "Gestion des Diplômes" ?>
        </h1>
        <a href="dash.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>

        <?php if ($view_employees && $current_diplome): ?>
            <!-- Bouton Retour lorsqu'on visualise les employés -->
            <a href="?" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste des diplômes
            </a>
            
            <br><br>
            <!-- Informations sur le diplôme actuel -->
            <div class="diplome-card">
                <h3><?= htmlspecialchars($current_diplome['Lib']) ?></h3>
                <div class="diplome-info">
                    <p><strong>Type:</strong> <?= htmlspecialchars($current_diplome['type']) ?></p>
                    <p><strong>Niveau:</strong> <?= htmlspecialchars($current_diplome['niveau']) ?></p>
                    <p><strong>Établissement:</strong> <?= htmlspecialchars($current_diplome['etablissement']) ?></p>
                </div>
                <div class="action-buttons">
                    <?php if ($role === 'admin'): ?>
                    <a href="admin/diploms_edit.php?ID=<?= urlencode($current_diplome['id']) ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="?delete_id=<?= urlencode($current_diplome['id']) ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce diplôme ?')">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Liste des Employés -->
            <div class="employees-section">
                <h2><i class="fas fa-users"></i> Employés avec ce diplôme</h2>
                
                <?php if (count($employees) > 0): ?>
                    <div class="employees-list">
                        <?php foreach ($employees as $employee): ?>
                            <div class="employee-card">
                                <div class="employee-photo">
                                    <?php if (!empty($employee['img_profile'])): ?>
                                        <img src="data:<?= htmlspecialchars($employee['mime_type']) ?>;base64,<?= base64_encode($employee['img_profile']) ?>" alt="Photo de profil">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="employee-info">
                                    <div class="employee-name">
                                        <?= htmlspecialchars($employee['prenom']) ?> <?= htmlspecialchars($employee['nom']) ?>
                                    </div>
                                    <div class="employee-position">
                                        <?= htmlspecialchars($employee['fonction']) ?> - <?= htmlspecialchars($employee['grade']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-employees">
                        <i class="fas fa-info-circle"></i> Aucun employé trouvé avec ce diplôme
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <!-- Section Filtre -->
            <br><br>
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="filter_lib">Libellé</label>
                            <input type="text" id="filter_lib" name="filter_lib" value="<?= htmlspecialchars($filter_lib) ?>" placeholder="Rechercher par libellé">
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter_type">Type</label>
                            <select id="filter_type" name="filter_type">
                                <option value="">Tous les types</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>" <?= $filter_type === $type ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter_niveau">Niveau</label>
                            <select id="filter_niveau" name="filter_niveau">
                                <option value="">Tous les niveaux</option>
                                <?php foreach ($niveaux as $niveau): ?>
                                    <option value="<?= htmlspecialchars($niveau) ?>" <?= $filter_niveau === $niveau ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($niveau) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Liste Principale des Diplômes -->
            <?php if (count($diplomes_list) > 0): ?>
                <div class="diplomes-grid">
                    <?php foreach ($diplomes_list as $diplome): ?>
                        <div class="diplome-card">
                            <h3><?= htmlspecialchars($diplome['Lib']) ?></h3>
                            <div class="diplome-info">
                                <p><strong>Type:</strong> <?= htmlspecialchars($diplome['type']) ?></p>
                                <p><strong>Niveau:</strong> <?= htmlspecialchars($diplome['niveau']) ?></p>
                                <p><strong>Établissement:</strong> <?= htmlspecialchars($diplome['etablissement']) ?></p>
                            </div>
                            <div class="action-buttons">
                                <a href="?view_employees=true&id=<?= urlencode($diplome['id']) ?>" class="btn btn-primary">
                                    <i class="fas fa-users"></i> Employés
                                </a>
                                <?php if ($role === 'admin'): ?>
                                <a href="admin/diploms_edit.php?ID=<?= urlencode($diplome['id']) ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="?delete_id=<?= urlencode($diplome['id']) ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce diplôme ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle"></i> Aucun diplôme trouvé avec les critères sélectionnés
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>