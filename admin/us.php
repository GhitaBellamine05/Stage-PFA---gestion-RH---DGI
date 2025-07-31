<?php 
include "../sql/db.php";

$PPR = $_GET["PPR"];
$db = Database::getInstance()->getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save"])) {
    $sqlUpdate = "UPDATE us SET 
        lib = :lib,
        cellule_mere = :cellule_mere,
        type = :type,
        Batiment = :Batiment
        WHERE PPR = :ppr";

    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->bindParam(":lib", $_POST["lib"]);
    $stmtUpdate->bindParam(":cellule_mere", $_POST["cellule_mere"]);
    $stmtUpdate->bindParam(":type", $_POST["type"]);
    $stmtUpdate->bindParam(":Batiment", $_POST["batiment"]);
    $stmtUpdate->bindParam(":ppr", $PPR);
    $stmtUpdate->execute();

    header("Location: " . $_SERVER['PHP_SELF'] . "?PPR=" . urlencode($PPR));
    exit;
}

$sql = "SELECT * FROM us WHERE PPR = :ppr";
$stmt = $db->prepare($sql);
$stmt->bindParam(":ppr", $PPR);
$stmt->execute();
$us = $stmt->fetch(PDO::FETCH_ASSOC);
$role = $_COOKIE['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'Unité de Service</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-bg: #e6f0ff;
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
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .profile-container {
            max-width: 800px;
            width: 100%;
            background-color: var(--table-bg);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 40px;
            border: 1px solid var(--border-color);
        }

        h2 {
            color: var(--primary-blue);
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-dark);
        }

        input[type="text"] {
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

        input:disabled {
            background-color: #f9f9f9;
            color: #666;
            cursor: not-allowed;
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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
            flex: 1;
            justify-content: center;
            text-decoration: none;
            text-align: center;
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

        .btn-secondary {
            background-color: var(--primary-blue);
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
        }

        .btn-return {
            background-color: #f1f1f1;
            color: var(--text-dark);
        }

        .btn-return:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 30px 20px;
            }

            h2 {
                font-size: 24px;
            }

            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2><i class="fas fa-building"></i> Profil de l'Unité de Service</h2>

        <form method="POST" id="profileForm">
            <div class="form-group">
                <label for="lib">Libellé</label>
                <input type="text" name="lib" id="lib" value="<?= htmlspecialchars($us["lib"]) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="cellule_mere">Cellule Mère</label>
                <input type="text" name="cellule_mere" id="cellule_mere" value="<?= htmlspecialchars($us["cellule_mere"]) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <input type="text" name="type" id="type" value="<?= htmlspecialchars($us["type"]) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="batiment">Bâtiment</label>
                <input type="text" name="batiment" id="batiment" value="<?= htmlspecialchars($us["Batiment"]) ?>" disabled>
            </div>

            <div class="buttons">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <?php if ($role === 'admin'): ?>
                    <button type="button" id="editBtn" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button type="submit" name="save" id="saveBtn" class="btn btn-primary" style="display:none;">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const inputs = document.querySelectorAll('#profileForm input');

        editBtn.addEventListener('click', function () {
            inputs.forEach(input => input.disabled = false);
            editBtn.style.display = 'none';
            saveBtn.style.display = 'flex';
        });
    </script>
</body>
</html>