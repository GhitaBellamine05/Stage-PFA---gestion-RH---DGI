<?php
session_start();
include("../sql/db.php");

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$user = $_SESSION['user'];
$PPR = $user['PPR'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $passwordConfirm) {
        $error = "Passwords do not match.";
    } else {
        $imgData = null;
        $mimeType = null;
        if (isset($_FILES['img_profile']) && $_FILES['img_profile']['error'] === UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES['img_profile']['tmp_name']);
            $mimeType = $_FILES['img_profile']['type'];
        }

        try {
            if ($imgData !== null) {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE utilisateurs SET email = :email, password = :password, img_profile = :img, mime_type2 = :mime WHERE PPR = :ppr";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':password', $hash);
                } else {
                    $sql = "UPDATE utilisateurs SET email = :email, img_profile = :img, mime_type2 = :mime WHERE PPR = :ppr";
                    $stmt = $db->prepare($sql);
                }
                $stmt->bindParam(':img', $imgData, PDO::PARAM_LOB);
                $stmt->bindParam(':mime', $mimeType);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':ppr', $PPR);
                $stmt->execute();
            } else {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE utilisateurs SET email = :email, password = :password WHERE PPR = :ppr";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':password', $hash);
                } else {
                    $sql = "UPDATE utilisateurs SET email = :email WHERE PPR = :ppr";
                    $stmt = $db->prepare($sql);
                }
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':ppr', $PPR);
                $stmt->execute();
            }

            $_SESSION['user']['email'] = $email;
            $success = "Profile updated successfully.";
        } catch (Exception $e) {
            $error = "Failed to update profile: " . $e->getMessage();
        }
    }
}

$stmt = $db->prepare("SELECT img_profile, mime_type2 FROM utilisateurs WHERE PPR = :ppr");
$stmt->bindParam(':ppr', $PPR);
$stmt->execute();
$userImg = $stmt->fetch(PDO::FETCH_ASSOC);

$imageSrc = '';
if ($userImg && $userImg['img_profile']) {
    $base64 = base64_encode($userImg['img_profile']);
    $mime = htmlspecialchars($userImg['mime_type2']);
    $imageSrc = "data:$mime;base64,$base64";
}
$nom = $_COOKIE['nom'] ?? 'Unknown';
$prenom = $_COOKIE['prenom'] ?? 'User';

$fullName = htmlspecialchars("$prenom $nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profile</title>
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
        
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .profile-image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
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
        
        input[type="email"],
        input[type="password"],
        input[type="file"] {
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
        
        .file-upload-wrapper {
            position: relative;
            margin-top: 15px;
            text-align: center;
        }
        
        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: var(--secondary-blue);
            color: var(--text-dark);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .file-upload-btn:hover {
            background-color: #748cf5ff;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
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
            width: 100%;
            justify-content: center;
            text-decoration: none;
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
        
        small {
            font-size: 0.85em;
            color: #6c757d;
            display: block;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2><i class="fas fa-user-cog"></i> Mettre à jour votre profil</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-image-container">
                <label>Photo de profil</label>
                <?php if ($imageSrc): ?>
                    <img src="<?php echo $imageSrc; ?>" alt="Photo de profil" class="profile-image"/>
                <?php else: ?>
                    <div class="profile-image">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=5b9bd5&color=fff&size=150" class="profile-avatar" alt="<?= $fullName ?>">
                    </div>
                <?php endif; ?>
                
                <div class="file-upload-wrapper">
                    <button type="button" class="file-upload-btn">
                        <i class="fas fa-camera"></i> Changer la photo
                    </button>
                    <input type="file" name="img_profile" id="img_profile" class="file-upload-input" accept="image/*" />
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" name="email" id="email" required 
                       value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" />
            </div>
            
            <div class="form-group">
                <label for="password">
                    Nouveau mot de passe <small>(Laisser vide pour conserver le mot de passe actuel)</small>
                </label>
                <input type="password" name="password" id="password" />
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmer le nouveau mot de passe</label>
                <input type="password" name="password_confirm" id="password_confirm" />
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="javascript:history.back()" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
        </form>
    </div>

    <script>
        // Mise à jour de l'affichage du fichier sélectionné
        document.getElementById('img_profile').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Aucun fichier sélectionné';
            const uploadBtn = document.querySelector('.file-upload-btn');
            uploadBtn.innerHTML = `<i class="fas fa-camera"></i> ${fileName}`;
            
            // Aperçu de la nouvelle image
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const profileImage = document.querySelector('.profile-image');
                    if (profileImage.tagName === 'IMG') {
                        profileImage.src = event.target.result;
                    } else {
                        // Si c'est le conteneur div avec avatar, le remplacer par une image
                        const newImg = document.createElement('img');
                        newImg.src = event.target.result;
                        newImg.className = 'profile-image';
                        newImg.alt = 'Photo de profil';
                        profileImage.replaceWith(newImg);
                    }
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>