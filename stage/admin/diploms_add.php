<?php
include "../sql/db.php";

$PPR = $_GET["PPR"] ?? "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = Database::getInstance()->getConnection();
    $sql = "INSERT INTO diplome (Lib, niveau, etablissement, annee, type, PPR) 
            VALUES (:lib, :niveau, :etablissement, :annee, :type, :PPR)";
    $stmt = $db->prepare($sql);
    $stmt->bindparam(":lib", $_POST["lib"]);
    $stmt->bindparam(":niveau", $_POST["niveau"]);
    $stmt->bindparam(":etablissement", $_POST["etablissement"]);
    $stmt->bindparam(":annee", $_POST["annee"]);
    $stmt->bindparam(":type", $_POST["type"]);
    $stmt->bindparam(":PPR", $_POST["PPR"]);
    $stmt->execute();

    // Redirection vers diploms.php avec le PPR soumis
    header("Location: diploms.php?PPR=" . urlencode($_POST["PPR"]));
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ajouter un nouveau diplôme</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root {
          --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-bg: #e6f0ff;
            --table-bg: #ffffff;
            --text-dark: #2e3a4d;
            --text-light: #ffffff;
            --border-color: #c5e0ff;
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

        h1 {
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
        input[type="date"] {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }

        input[type="text"]:focus,
        input[type="date"]:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 155, 213, 0.2);
        }

        .btn-container {
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
            text-decoration: none;
            color: inherit;
        }

        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
        }

        .btn-submit:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 155, 213, 0.3);
        }

        .btn-back {
            background-color: var(--secondary-blue);
            color: var(--text-dark);
            text-align: center;
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

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-graduation-cap"></i> Ajouter un nouveau diplôme</h1>

        <form action="" method="POST">
            <div class="form-group">
                <label for="lib"><i class="fas fa-book"></i> Nom du diplôme</label>
                <input type="text" name="lib" id="lib" required />
            </div>

            <div class="form-group">
                <label for="niveau"><i class="fas fa-layer-group"></i> Niveau</label>
                <input type="text" name="niveau" id="niveau" required />
            </div>

            <div class="form-group">
                <label for="etablissement"><i class="fas fa-university"></i> Établissement</label>
                <input type="text" name="etablissement" id="etablissement" required />
            </div>

            <div class="form-group">
                <label for="annee"><i class="fas fa-calendar-alt"></i> Année</label>
                <input type="date" name="annee" id="annee" required />
            </div>

            <div class="form-group">
                <label for="type"><i class="fas fa-tag"></i> Type</label>
                <input type="text" name="type" id="type" required />
            </div>

            <div class="form-group">
                <label for="PPR"><i class="fas fa-id-badge"></i> PPR</label>
                <input type="text" name="PPR" id="PPR" value="<?= htmlspecialchars($PPR) ?>" />
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-plus-circle"></i> Ajouter le diplôme
                </button>
                <a href="diploms.php?PPR=<?= urlencode($PPR) ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </form>
    </div>
</body>
</html>
