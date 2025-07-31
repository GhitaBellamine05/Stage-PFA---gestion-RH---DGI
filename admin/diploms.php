<?php 
include "../sql/db.php";
$PPR = $_GET["PPR"];

$db = Database::getInstance()->getConnection();

// Initialize search and filter variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$institution = isset($_GET['institution']) ? $_GET['institution'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Base query
$sql = "SELECT * FROM diplome WHERE PPR = :PPR";
$params = [':PPR' => $PPR];

// Add search condition
if (!empty($search)) {
    $sql .= " AND Lib LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Add filter conditions
if (!empty($level)) {
    $sql .= " AND niveau = :level";
    $params[':level'] = $level;
}

if (!empty($institution)) {
    $sql .= " AND etablissement = :institution";
    $params[':institution'] = $institution;
}

if (!empty($year)) {
    $sql .= " AND annee = :year";
    $params[':year'] = $year;
}

if (!empty($type)) {
    $sql .= " AND type = :type";
    $params[':type'] = $type;
}

// Get unique values for filters
$filterSql = "SELECT 
    GROUP_CONCAT(DISTINCT niveau) as levels,
    GROUP_CONCAT(DISTINCT etablissement) as institutions,
    GROUP_CONCAT(DISTINCT annee) as years,
    GROUP_CONCAT(DISTINCT type) as types
    FROM diplome WHERE PPR = :PPR";
$filterStmt = $db->prepare($filterSql);
$filterStmt->bindparam(":PPR", $PPR);
$filterStmt->execute();
$filterData = $filterStmt->fetch(PDO::FETCH_ASSOC);

// Prepare and execute main query
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$diploms = $stmt->fetchall(PDO::FETCH_ASSOC);

if(isset($_POST["supprimer"])) {
    $id = $_POST["id"];
    $sql1 = "DELETE FROM diplome WHERE id = :ID";
    $stmt1 = $db->prepare($sql1);
    $stmt1->bindparam(":ID", $id);
    $stmt1->execute();
    header("Location: diploms.php?PPR=$PPR");
    exit();
}
$role = $_COOKIE['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diplomas Management</title>
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
            position: relative;
            border: 1px solid var(--border-color);
        }
        
        h1 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 28px;
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
        
        .return-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            background-color: var(--secondary-blue);
            color: var(--text-dark);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .return-btn:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(157, 195, 230, 0.3);
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
            background-color: #748cf5ff
;
        }
        
        .delete-form {
            display: inline;
        }
        
        .delete-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #ff6b6b;
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
            
            .return-btn {
                position: static;
                margin-bottom: 15px;
                display: inline-flex;
                width: auto;
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

        /* New styles for search and filters */
        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            background-color: #e6f0ff;
            padding: 20px;
            border-radius: 8px;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .filter-box {
            flex: 1;
            min-width: 200px;
        }
        
        .search-box input, .filter-box select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
        }
        
        .filter-btn {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background-color: #748cf5ff;
        }
        
        .reset-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .reset-btn:hover {
            background-color: #5a6268;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        @media (max-width: 768px) {
            .search-filter-container {
                flex-direction: column;
            }
            
            .filter-actions {
                width: 100%;
            }
            
            .filter-btn, .reset-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-graduation-cap"></i> Gestion des Diplômes</h1>
        <a href="../gestion.php?PPR=<?= htmlspecialchars($PPR) ?>" class="return-btn">
            <i class="fas fa-arrow-left"></i> Retour au dashboard
        </a>
        
        <?php if ($role === 'admin'): ?>
        <a href="diploms_add.php?PPR=<?= htmlspecialchars($PPR) ?>" class="add-btn">
            <i class="fas fa-plus-circle"></i> Ajouter un diplôme
        </a>
        <?php endif; ?>
        
        <!-- Search and Filter Section -->
        <form method="GET" action="">
            <input type="hidden" name="PPR" value="<?= htmlspecialchars($PPR) ?>">
            
            <div class="search-filter-container">
                <div class="search-box">
                    <label for="search" style="display: block; margin-bottom: 5px; font-weight: 500;">Recherche par nom:</label>
                    <input type="text" id="search" name="search" placeholder="Entrer le titre du diplôme..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <div class="filter-box">
                    <label for="level" style="display: block; margin-bottom: 5px; font-weight: 500;">Niveau:</label>
                    <select id="level" name="level">
                        <option value="">tous les niveaux</option>
                        <?php 
                        $levels = !empty($filterData['levels']) ? explode(',', $filterData['levels']) : [];
                        foreach ($levels as $lvl): 
                        ?>
                            <option value="<?= htmlspecialchars($lvl) ?>" <?= $level === $lvl ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lvl) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-box">
                    <label for="institution" style="display: block; margin-bottom: 5px; font-weight: 500;"> Établissement:</label>
                    <select id="institution" name="institution">
                        <option value="">Tous les établissements </option>
                        <?php 
                        $institutions = !empty($filterData['institutions']) ? explode(',', $filterData['institutions']) : [];
                        foreach ($institutions as $inst): 
                        ?>
                            <option value="<?= htmlspecialchars($inst) ?>" <?= $institution === $inst ? 'selected' : '' ?>>
                                <?= htmlspecialchars($inst) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-box">
                    <label for="year" style="display: block; margin-bottom: 5px; font-weight: 500;">Année:</label>
                    <select id="year" name="year">
                        <option value="">Tous les années </option>
                        <?php 
                        $years = !empty($filterData['years']) ? explode(',', $filterData['years']) : [];
                        foreach ($years as $yr): 
                        ?>
                            <option value="<?= htmlspecialchars($yr) ?>" <?= $year === $yr ? 'selected' : '' ?>>
                                <?= htmlspecialchars($yr) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-box">
                    <label for="type" style="display: block; margin-bottom: 5px; font-weight: 500;">Type:</label>
                    <select id="type" name="type">
                        <option value="">Tous les types</option>
                        <?php 
                        $types = !empty($filterData['types']) ? explode(',', $filterData['types']) : [];
                        foreach ($types as $typ): 
                        ?>
                            <option value="<?= htmlspecialchars($typ) ?>" <?= $type === $typ ? 'selected' : '' ?>>
                                <?= htmlspecialchars($typ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Appliquer
                    </button>
                    <a href="diploms.php?PPR=<?= htmlspecialchars($PPR) ?>" class="reset-btn">
                        <i class="fas fa-sync-alt"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-id-card"></i> ID</th>
                    <th><i class="fas fa-book"></i> Titre</th>
                    <th><i class="fas fa-layer-group"></i> Niveau</th>
                    <th><i class="fas fa-university"></i> Établissement</th>
                    <th><i class="fas fa-calendar-alt"></i> Année</th>
                    <th><i class="fas fa-tag"></i> Type</th>
                    <?php if ($role === 'admin'): ?>
                    <th><i class="fas fa-cog"></i> Actions</th>    
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($diploms)): ?>
                    <tr>
                        <td colspan="<?= $role === 'admin' ? 7 : 6 ?>" style="text-align: center;">
                           Aucun diplôme trouvé avec les critères sélectionnés
                     </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($diploms as $diplom): ?>
                        <tr>
                            <td><?= htmlspecialchars($diplom["id"]) ?></td>
                            <td><?= htmlspecialchars($diplom["Lib"]) ?></td>
                            <td><?= htmlspecialchars($diplom["niveau"]) ?></td>
                            <td><?= htmlspecialchars($diplom["etablissement"]) ?></td>
                            <td><?= htmlspecialchars($diplom["annee"]) ?></td>
                            <td><?= htmlspecialchars($diplom["type"]) ?></td>
                            <?php if ($role === 'admin'): ?>
                            <td class="action-cell">
                                <a href="diploms_edit.php?ID=<?= htmlspecialchars($diplom["id"]) ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this diploma?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($diplom["id"]) ?>">
                                    <button type="submit" name="supprimer" class="delete-btn">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>