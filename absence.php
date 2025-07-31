<?php 
include "sql/db.php";
$db = Database::getInstance()->getConnection();

// Initialize search and filter variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM absences a 
JOIN utilisateurs u
ON PPR = employee_id
WHERE 1=1";

$params = [];
// Add search condition
if (!empty($search)) {
    $sql .= " AND (employee_id LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY a.date";

// Prepare and execute main query
$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$absences = $stmt->fetchall(PDO::FETCH_ASSOC);
//print_r($absences);
$role = $_COOKIE['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des abscences</title>
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
            background-color: #748cf5ff;
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
        <h1><i class="fas fa-graduation-cap"></i>Liste des abscences</h1>
        <a href="dash.php" class="return-btn">
            <i class="fas fa-arrow-left"></i> Retour au dashboard
        </a>
        
        <!-- Search and Filter Section -->
        <form method="GET" action="">            
            <div class="search-filter-container">
                <div class="search-box">
                    <label for="search" style="display: block; margin-bottom: 5px; font-weight: 500;">Recherche par nom ou PPR:</label>
                    <input type="text" id="search" name="search" placeholder="Entrer ..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" style="display:none"></button> <!-- Hidden submit button -->
                </div>
            </div>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-id-card"></i> PPR</th>
                    <th><i class="fas fa-book"></i> Nom Complet</th>
                    <th><i class="fas fa-layer-group"></i> Date</th>
                    <th><i class="fas fa-university"></i> Raison</th>
    
                </tr>
            </thead>
            <tbody>
                <?php if (empty($absences)): ?>
                    <tr>
                        <td colspan="<?= $role === 'admin' ? 7 : 6 ?>" style="text-align: center;">
                           Aucun employee trouvé avec le nom donné
                     </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($absences as $absence): ?>
                        <tr>
                            <td><?= htmlspecialchars($absence["PPR"]) ?></td>
                            <td><?= htmlspecialchars($absence["nom"]) ?> <?= htmlspecialchars($absence["prenom"]) ?></td>
                            <td><?= htmlspecialchars($absence["date"]) ?></td>
                            <td><?= htmlspecialchars($absence["reason"]) ?></td>
         
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Submit form when Enter is pressed in search field
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>