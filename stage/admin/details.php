<?php 
include "../sql/db.php";

$PPR = $_GET["PPR"];
$db = Database::getInstance()->getConnection();

$imgData = null;
$mimeType = null;
$updateImage = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save"])) {
    if (isset($_FILES["img"]) && $_FILES["img"]["error"] === UPLOAD_ERR_OK) {
        $imgData = file_get_contents($_FILES["img"]["tmp_name"]);
        $mimeType = $_FILES["img"]["type"];
        $updateImage = true;
    }

    $sqlUpdate = "UPDATE utilisateurs SET 
        nom = :nom,
        prenom = :prenom,
        Cin = :Cin,
        d_naiss = :d_naiss,
        d_recrutement = :d_recrutement,
        sit_familliale = :sit_familliale,
        genre = :genre,
        role = :role,
        email = :email,
        fonction = :fonction,
        grade = :grade";

    if ($updateImage) {
        $sqlUpdate .= ", img = :img, mime_type = :mime_type";
    }

    $sqlUpdate .= " WHERE PPR = :ppr";

    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->bindParam(":nom", $_POST["nom"]);
    $stmtUpdate->bindParam(":prenom", $_POST["prenom"]);
    $stmtUpdate->bindParam(":Cin", $_POST["Cin"]);
    $stmtUpdate->bindParam(":d_naiss", $_POST["d_naiss"]);
    $stmtUpdate->bindParam(":d_recrutement", $_POST["d_recrutement"]);
    $stmtUpdate->bindParam(":sit_familliale", $_POST["sit_familliale"]);
    $stmtUpdate->bindParam(":genre", $_POST["genre"]);
    $stmtUpdate->bindParam(":role", $_POST["role"]);
    $stmtUpdate->bindParam(":email", $_POST["email"]);
    $stmtUpdate->bindParam(":fonction", $_POST["fonction"]);
    $stmtUpdate->bindParam(":grade", $_POST["grade"]);
    if ($updateImage) {
        $stmtUpdate->bindParam(":img", $imgData, PDO::PARAM_LOB);
        $stmtUpdate->bindParam(":mime_type", $mimeType);
    }
    $stmtUpdate->bindParam(":ppr", $PPR);
    $stmtUpdate->execute();
}

$sql = "SELECT * FROM utilisateurs WHERE PPR = :ppr";
$stmt = $db->prepare($sql);
$stmt->bindParam(":ppr", $PPR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$role = $_COOKIE['role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></title>
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

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control {
            width: 90%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 155, 213, 0.2);
        }

        .form-control:disabled {
            background-color: #f9f9f9;
            color: #777;
        }

        .profile-image-container {
            grid-column: span 2;
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-blue);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .file-label:hover {
            background-color: #748cf5ff;
        }

        .button-group {
            grid-column: span 2;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background-color: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
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

        @media (max-width: 768px) {
            .profile-form {
                grid-template-columns: 1fr;
            }

            .profile-image-container, .button-group {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1><i class="fas fa-user-edit"></i> Profil</h1>

        <div class="user-info">
            <i class="fas fa-user-tie"></i>
            <p>Profil de: <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . htmlspecialchars($user['nom'])) ?></strong></p>
        </div>

        <form method="POST" enctype="multipart/form-data" id="profileForm" class="profile-form">
            <div class="profile-image-container">
                <img src="../image.php?PPR=<?php echo urlencode($user['PPR']); ?>" alt="Photo de profil" id="profileImg" class="profile-image">
                <input type="file" name="img" id="img" class="file-input" accept="image/*" disabled>
                <label for="img" class="file-label" id="fileLabel" style="display:none;">
                    <i class="fas fa-camera"></i> Changer la photo
                </label>
            </div>

            <?php
            $fields = [
                "nom" => "Nom",
                "prenom" => "Prénom",
                "Cin" => "CIN",
                "email" => "Email",
                "d_naiss" => "Date de naissance",
                "d_recrutement" => "Date de recrutement",
                "sit_familliale" => "Situation familiale",
                "genre" => "Sexe",
                "role" => "Rôle",
                "fonction" => "Fonction",
                "grade" => "Grade"
            ];

            foreach ($fields as $key => $label) {
                echo '<div class="form-group">
                    <label for="' . $key . '">' . $label . '</label>
                    <input type="' . ($key === "email" ? "email" : ($key === "d_naiss" || $key === "d_recrutement" ? "date" : "text")) . '" 
                        class="form-control" id="' . $key . '" name="' . $key . '" 
                        value="' . htmlspecialchars($user[$key]) . '" disabled>
                </div>';
            }
            ?>

            <div class="button-group">
                <?php if ($role === 'admin'): ?>
                    <button type="button" class="btn btn-primary" id="editBtn">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn" name="save" style="display:none">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                <?php endif; ?>

                <a href="../pdf/pdf.php?PPR=<?php echo urlencode($user['PPR']); ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> Télécharger PDF
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const fileLabel = document.getElementById('fileLabel');
        const inputs = document.querySelectorAll('#profileForm input:not([type="file"])');
        const fileInput = document.getElementById('img');
        const profileImg = document.getElementById('profileImg');

        editBtn.addEventListener('click', () => {
            inputs.forEach(input => input.disabled = false);
            fileLabel.style.display = 'inline-block';
            fileInput.disabled = false;
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        });

        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (evt) {
                    profileImg.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
    </script>
</body>
</html>