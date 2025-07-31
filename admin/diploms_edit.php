<?php 
include "../sql/db.php";

$id = $_GET["ID"];

$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM diplome WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$diploms = $stmt->fetch(PDO::FETCH_ASSOC);

if(isset($_POST["retour"])){
    header("location:diploms.php?PPR={$diploms["PPR"]}");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save"])) {
    $sqlUpdate = "UPDATE diplome SET 
        Lib=:lib,
        niveau=:niveau,
        etablissement=:eta,
        annee=:annee,
        type=:type
        WHERE id = :id";

    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->bindParam(":lib", $_POST["lib"]);
    $stmtUpdate->bindParam(":niveau", $_POST["niveau"]);
    $stmtUpdate->bindParam(":eta", $_POST["etablissement"]);
    $stmtUpdate->bindParam(":type", $_POST["Type"]);
    $stmtUpdate->bindParam(":annee", $_POST["annee"]);
    $stmtUpdate->bindParam(":id", $id);
    
    $stmtUpdate->execute();
    header("location:diploms.php?PPR={$diploms["PPR"]}");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Diploma</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: var(--text-dark);
        }
        
        .container {
            background-color: var(--table-bg);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 30px;
            width: 100%;
            max-width: 600px;
            border: 1px solid var(--border-color);
        }
        
        h2 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        label {
            font-weight: 500;
            color: var(--text-dark);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        label i {
            color: var(--primary-blue);
            width: 20px;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="Type"],
        select {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="Type"]:focus,
        select:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 155, 213, 0.2);
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 15px;
        }
        
        .btn-save {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-save:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 155, 213, 0.3);
        }
        
        .btn-back {
            background-color: var(--secondary-blue);
            color: var(--text-dark);
        }
        
        .btn-back:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(157, 195, 230, 0.3);
        }
        
        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
         .btn-secondary {
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
        
        .btn-secondary:hover {
            background-color: #748cf5ff;;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-graduation-cap"></i> Modifier Diplôme</h2>
        
        <form method="POST" id="profileForm">
            <div class="form-group">
                <label for="libelee"><i class="fas fa-book"></i> Titre</label>
                <input type="text" name="lib" id="libelee" value="<?= htmlspecialchars($diploms["Lib"]) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="niveau"><i class="fas fa-layer-group"></i> Niveau</label>
                <input type="text" id="niveau" name="niveau" value="<?= htmlspecialchars($diploms["niveau"]) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="etablissement"><i class="fas fa-university"></i> Institution</label>
                <input type="text" name="etablissement" id="etablissement" value="<?= htmlspecialchars($diploms["etablissement"]) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="annee"><i class="fas fa-calendar-alt"></i> Date</label>
                <input type="date" name="annee" id="annee" value="<?= htmlspecialchars($diploms["annee"]) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="Type"><i class="fas fa-tag"></i> Type</label>
                <input type="text" name="Type" id="Type" value="<?= htmlspecialchars($diploms["type"]) ?>" required>
            </div>
            
            <div class="buttons">
                <button type="submit" name="save" class="btn btn-save">
                    <i class="fas fa-save"></i> Sauvegarder
                </button>
                  <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </form>
    </div>
</body>
</html>