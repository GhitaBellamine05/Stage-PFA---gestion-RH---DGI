<?php 
include "../sql/db.php";
$PPR = $_COOKIE["PPR"];
$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM diplome WHERE PPR=:PPR";
$stmt = $db->prepare($sql);
$stmt->bindparam(":PPR", $PPR);
$stmt->execute();
$diploms = $stmt->fetchall(PDO::FETCH_ASSOC);

if(isset($_POST["supprimer"])){
    $id = $_POST["id"];
    $sql1 = "DELETE from diplome WHERE id=:ID";
    $stmt1 = $db->prepare($sql1);
    $stmt1->bindparam(":ID", $id);
    $stmt1->execute();
    // Refresh the page after deletion
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes diplômes</title>
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
        
        h1 {
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
            background-color: #8ab5e0;
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
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            width: 25%;
            justify-content: center;
            text-decoration:none
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 155, 213, 0.3);
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-graduation-cap"></i> Mes diplômes</h1>
        <br><br>
        <a href="javascript:history.back()" class="btn btn-primary" >
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-id-card"></i> ID</th>
                    <th><i class="fas fa-book"></i> Titre</th>
                    <th><i class="fas fa-layer-group"></i> Niveau</th>
                    <th><i class="fas fa-university"></i> Institution</th>
                    <th><i class="fas fa-calendar-alt"></i> Année</th>
                    <th><i class="fas fa-tag"></i> Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($diploms as $diplom): ?>
                    <tr>
                        <td><?= htmlspecialchars($diplom["id"]) ?></td>
                        <td><?= htmlspecialchars($diplom["Lib"]) ?></td>
                        <td><?= htmlspecialchars($diplom["niveau"]) ?></td>
                        <td><?= htmlspecialchars($diplom["etablissement"]) ?></td>
                        <td><?= htmlspecialchars($diplom["annee"]) ?></td>
                        <td><?= htmlspecialchars($diplom["type"]) ?></td>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>