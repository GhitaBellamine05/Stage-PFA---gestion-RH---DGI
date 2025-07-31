<?php 
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../sql/db.php";
$db = Database::getInstance()->getConnection();

$success_message = '';
$error_message = '';

// Initialize variables to keep form values after submit
$nom = $prenom = $Cin = $d_naiss = $d_recrutement = '';
$sit_familliale = $genre = $role = '';
$email = $fonction = $grade = '';

if(isset($_POST["save"])) {
    // Preserve posted values to refill form in case of error
    $nom = $_POST["nom"] ?? '';
    $prenom = $_POST["prenom"] ?? '';
    $Cin = $_POST["Cin"] ?? '';
    $d_naiss = $_POST["d_naiss"] ?? '';
    $d_recrutement = $_POST["d_recrutement"] ?? '';
    $sit_familliale = $_POST["sit_familliale"] ?? '';
    $genre = $_POST["genre"] ?? '';
    $role = $_POST["role"] ?? '';
    $email = $_POST["email"] ?? '';
    $fonction = $_POST["fonction"] ?? '';
    $grade = $_POST["grade"] ?? '';

    try {
        // Initialize image variables
        $imgData = null;
        $mimeType = null;
        
        // Handle file upload
        if(isset($_FILES['photo'])) {
            switch ($_FILES['photo']['error']) {
                case UPLOAD_ERR_OK:
                    // Check file size (limit to 5MB)
                    $maxFileSize = 5 * 1024 * 1024;
                    if ($_FILES['photo']['size'] > $maxFileSize) {
                        throw new Exception("File size exceeds maximum limit of 5MB.");
                    }
                    
                    // Verify the file is an image
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    $detectedType = mime_content_type($_FILES['photo']['tmp_name']);
                    
                    if(!in_array($detectedType, $allowedTypes)) {
                        throw new Exception("Only JPG, PNG, and GIF files are allowed.");
                    }
                    
                    // Get image data
                    $imgData = file_get_contents($_FILES['photo']['tmp_name']);
                    if($imgData === false) {
                        throw new Exception("Failed to read image file.");
                    }
                    $mimeType = $detectedType;
                    break;
                
                case UPLOAD_ERR_NO_FILE:
                    // Image required — throw error to prevent saving
                    throw new Exception("Please upload a photo (JPG, PNG, or GIF).");
                    break;
                
                default:
                    $uploadErrors = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in form',
                        UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                        UPLOAD_ERR_EXTENSION => 'PHP extension stopped the file upload'
                    ];
                    throw new Exception($uploadErrors[$_FILES['photo']['error']] ?? 'Unknown upload error');
            }
        } else {
            throw new Exception("Photo upload is required.");
        }

        // Prepare SQL query
        $sql = "INSERT INTO utilisateurs 
                (nom, prenom, Cin, d_naiss, d_recrutement, sit_familliale, genre, role, email, fonction, grade, img, mime_type) 
                VALUES 
                (:nom, :prenom, :Cin, :d_naiss, :d_recrutement, :sit_familiale, :genre, :role, :email, :fonction, :grade, :img, :mime)";
        
        $stmt = $db->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(":nom", $nom);
        $stmt->bindParam(":prenom", $prenom);
        $stmt->bindParam(":Cin", $Cin);
        $stmt->bindParam(":d_naiss", $d_naiss);
        $stmt->bindParam(":d_recrutement", $d_recrutement);
        $stmt->bindParam(":sit_familiale", $sit_familliale);
        $stmt->bindParam(":genre", $genre);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":fonction", $fonction);
        $stmt->bindParam(":grade", $grade);
        $stmt->bindParam(":img", $imgData, PDO::PARAM_LOB);
        $stmt->bindParam(":mime", $mimeType);
        
        // Execute the statement
        if($stmt->execute()) {
            $success_message = "User created successfully!";
            // Clear form after success
            $nom = $prenom = $Cin = $d_naiss = $d_recrutement = '';
            $sit_familliale = $genre = $role = '';
            $email = $fonction = $grade = '';
             header("refresh:2;url=../dash.php");
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database error: " . $errorInfo[2]);
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>New User</title>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <style>
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-bg: #f0f7ff;
            --table-bg: #ffffff;
            --text-dark: #2e3a4d;
            --text-light: #ffffff;
            --border-color: #c5e0ff;
            --highlight-blue: #e6f2ff;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .form-container {
            max-width: 800px;
            width: 100%;
            background-color: var(--table-bg);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }
        
        h2 {
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
        
        .form-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-field {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        input[type="text"],
        input[type="date"],
        input[type="email"],
        select {
            width: 90%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input:focus,
        select:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(91, 155, 213, 0.2);
        }
        
        .photo-upload-container {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            padding: 20px;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            background-color: rgba(197, 224, 255, 0.1);
        }
        
        .photo-preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-color);
            margin-bottom: 15px;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .photo-preview i {
            font-size: 3rem;
            color: var(--primary-blue);
        }
        
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .file-upload-btn:hover {
            background-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .file-upload-input {
            display: none;
        }
        
        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 5px;
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
            background-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        @media (max-width: 600px) {
            .form-group {
                grid-template-columns: 1fr;
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
    <div class="form-container">
        <h2><i class="fas fa-user-plus"></i> Nouvel Utilisateur</h2>

        <?php if($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="photo-upload-container">
                <div class="photo-preview-container">
                    <div class="photo-preview" id="photoPreview">
                        <i class="fas fa-user"></i>
                        <img id="previewImage" style="display: none;" alt="Aperçu de la photo" />
                    </div>
                    <button
                        type="button"
                        class="file-upload-btn"
                        onclick="document.getElementById('photoInput').click()"
                    >
                        <i class="fas fa-camera"></i> Choisir une photo
                    </button>
                    <input
                        type="file"
                        id="photoInput"
                        name="photo"
                        class="file-upload-input"
                        accept="image/jpeg, image/png, image/gif"
                        required
                    />
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($nom); ?>" required />
                </div>

                <div class="form-field">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required />
                </div>

                <div class="form-field">
                    <label for="Cin">CIN</label>
                    <input type="text" name="Cin" id="Cin" value="<?php echo htmlspecialchars($Cin); ?>" required />
                </div>

                <div class="form-field">
                    <label for="d_naiss">Date de naissance</label>
                    <input type="date" name="d_naiss" id="d_naiss" value="<?php echo htmlspecialchars($d_naiss); ?>" required />
                </div>

                <div class="form-field">
                    <label for="d_recrutement">Date de recrutement</label>
                    <input type="date" name="d_recrutement" id="d_recrutement" value="<?php echo htmlspecialchars($d_recrutement); ?>" required />
                </div>

                <div class="form-field">
                    <label for="sit_familliale">Situation familiale</label>
                    <select name="sit_familliale" id="sit_familliale" required>
                        <option value="">Sélectionner</option>
                        <option value="Célibataire" <?php if($sit_familliale === "Single") echo "selected"; ?>>Célibataire</option>
                        <option value="Marié(e)" <?php if($sit_familliale === "Married") echo "selected"; ?>>Marié(e)</option>
                        <option value="Divorcé(e)" <?php if($sit_familliale === "Divorced") echo "selected"; ?>>Divorcé(e)</option>
                        <option value="Veuf/Veuve" <?php if($sit_familliale === "Widowed") echo "selected"; ?>>Veuf/Veuve</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="genre">Genre</label>
                    <select name="genre" id="genre" required>
                        <option value="">Sélectionner</option>
                        <option value="homme" <?php if($genre === "homme") echo "selected"; ?>>Homme</option>
                        <option value="femme" <?php if($genre === "femme") echo "selected"; ?>>Femme</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="role">Rôle</label>
                    <select name="role" id="role" required>
                        <option value="">Sélectionner</option>
                        <option value="Admin" <?php if($role === "Admin") echo "selected"; ?>>Administrateur</option>
                        <option value="User" <?php if($role === "User") echo "selected"; ?>>Utilisateur</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" />
                </div>

                <div class="form-field">
                    <label for="fonction">Fonction</label>
                    <input type="text" name="fonction" id="fonction" value="<?php echo htmlspecialchars($fonction); ?>" />
                </div>

                <div class="form-field">
                    <label for="grade">Grade</label>
                    <input type="text" name="grade" id="grade" value="<?php echo htmlspecialchars($grade); ?>" />
                </div>
            </div>

            <div class="buttons">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <button type="submit" name="save" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById("photoInput").addEventListener("change", function (e) {
            const preview = document.getElementById("photoPreview");
            const previewImage = document.getElementById("previewImage");
            const icon = preview.querySelector("i");

            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();

                reader.onload = function (event) {
                    previewImage.src = event.target.result;
                    previewImage.style.display = "block";
                    icon.style.display = "none";
                };

                reader.readAsDataURL(e.target.files[0]);
            } else {
                previewImage.style.display = "none";
                icon.style.display = "block";
            }
        });
    </script>
</body>
</html>