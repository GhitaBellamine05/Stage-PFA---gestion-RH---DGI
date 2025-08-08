<?php
include "sql/db.php";

$error = '';
$success = '';

if (isset($_POST['signup'])) {
    $Cin = $_POST['Cin'];
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE Cin = :Cin");
    $stmt->bindParam(":Cin", $Cin);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        $error = "Vous n'êtes pas enregistré comme employé. Veuillez contacter les RH.";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email AND Cin != :Cin");
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":Cin", $Cin);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = "Email déjà enregistré pour un autre employé. Veuillez utiliser un autre email.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE utilisateurs SET email = :email, password = :password WHERE PPR = :ppr");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hash);
            $stmt->bindParam(":ppr", $employee['PPR']);
            
            if ($stmt->execute()) {
                $success = "Inscription réussie ! Redirection vers la connexion...";
                header("refresh:2;url=login.php");
            } else {
                $error = "L'inscription a échoué. Veuillez réessayer.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Portail Employé</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #ffffff;
            --dark-color: #2d3748;
            --shadow: 0 4px 6px rgba(67, 97, 238, 0.1);
            --transition: all 0.3s ease;
            --success-color: #38a169;
            --warning-color: #d69e2e;
            --danger-color: #e53e3e;
            --info-color: #3182ce;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            background-attachment: fixed;
        }

        .signup-container {
            width: 100%;
            max-width: 450px;
            margin: 2rem;
        }

        .signup-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.2);
            overflow: hidden;
            transition: var(--transition);
        }

        .signup-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .signup-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .signup-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .signup-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(67, 97, 238, 0.2);
        }

        .signup-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .signup-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .signup-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            border: 1px solid transparent;
        }

        .alert-error {
            background: #fff5f5;
            color: var(--danger-color);
            border-color: #fed7d7;
        }

        .alert-success {
            background: #f0fff4;
            color: var(--success-color);
            border-color: #c6f6d5;
        }

        .alert i {
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .signup-container {
                margin: 1rem;
            }
            
            .signup-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-card">
            <div class="signup-header">
                <h2><i class="fas fa-user-plus"></i> Inscription</h2>
                <p>Créez votre compte pour accéder au portail</p>
            </div>
            
            <div class="signup-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="Cin">CIN</label>
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" id="Cin" name="Cin" class="form-control" placeholder="Entrez votre CIN" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Adresse Email</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Entrez votre email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Créez un mot de passe" required>
                    </div>
                    
                    <button type="submit" name="signup" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                </form>
                
                <div class="signup-footer">
                    Déjà inscrit ? <a href="login.php">Connectez-vous ici</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>