<?php 
include "../sql/db.php";

$id = $_GET["ID"];

$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM hist_affectation WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$historique = $stmt->fetch(PDO::FETCH_ASSOC);

if(isset($_POST["retour"])){
    header("location:historique.php?PPR={$historique["PPR"]}");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save"])) {
    $sqlUpdate = "UPDATE hist_affectation SET 
        Code=:Code,
        date_debut=:date_debut,
        date_fin=:date_fin
        WHERE id = :id";

    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->bindParam(":Code", $_POST["Code"]);
    $stmtUpdate->bindParam(":date_debut", $_POST["date_debut"]);
    $stmtUpdate->bindParam(":date_fin", $_POST["date_fin"]);
    $stmtUpdate->bindParam(":id", $id);
    $stmtUpdate->execute();
    header("location:historique.php?PPR={$historique["PPR"]}");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Historique d'Affectation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #5b9bd5;
            --secondary-blue: #9dc3e6;
            --light-bg: #f0f7ff;
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
        }
        
        .container {
            max-width: 600px;
            width: 100%;
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
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 155, 213, 0.2);
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            align-items:center;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4a8bc9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 155, 213, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--secondary-blue);
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background-color: #8ab5e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(157, 195, 230, 0.3);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .buttons {
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
        <h2><i class="fas fa-history"></i> Modifier Historique</h2>
        
        <form method="POST" id="profileForm">
            <div class="form-group">
                <label for="Code"><i class="fas fa-building"></i> Code US</label>
                <input type="text" name="Code" id="Code" value="<?= htmlspecialchars($historique["Code"]) ?>">
            </div>
            
            <div class="form-group">
                <label for="date_debut"><i class="fas fa-calendar-start"></i> Date du début</label>
                <input type="date" name="date_debut" id="date_debut" value="<?= htmlspecialchars($historique["date_debut"]) ?>">
            </div>
            
            <div class="form-group">
                <label for="date_fin"><i class="fas fa-calendar-end"></i> Date de la fin</label>
                <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($historique["date_fin"]) ?>">
            </div>
            
            <div class="buttons">
                <button type="submit" name="save" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="submit" name="retour" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
            </div>
        </form>
    </div>
</body>
</html>