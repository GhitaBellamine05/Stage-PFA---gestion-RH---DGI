<?php
include "sql/db.php";

$db = Database::getInstance()->getConnection();

$stage = "request"; // or "reset"
$error = '';
$message = '';
$email = '';
$show_reset_form = false;

if (isset($_POST["reset_request"])) {
    $email = trim($_POST["email"]);

    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stage = "reset";
        $show_reset_form = true;
    } else {
        $error = "Aucun compte trouvé avec cet e-mail.";
    }
} elseif (isset($_POST["reset_password"])) {
    $email = $_POST["email"];
    $new_password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
        $stage = "reset";
        $show_reset_form = true;
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE utilisateurs SET password = :password WHERE email = :email");
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $email);
        if ($stmt->execute()) {
            $message = "Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $error = "Une erreur s'est produite lors de la mise à jour.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié | Portail Employé</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #88a5e2ff;
            --primary-dark: #2980b9;
            --secondary-color: #f8f9fa;
            --accent-color: #e74c3c;
            --text-color: #2c3e50;
            --text-light: #7f8c8d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #4361ee;
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), #3f37c9);
            background-attachment: fixed;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), #3f37c9);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .login-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .login-body {
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
            color: var(--text-color);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--text-light);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
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
            background: #748cf5ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .login-footer a:hover {
            color: #748cf5ff;
            text-decoration: underline;
        }

        .error-message {
            background: #fdecea;
            color: var(--accent-color);
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        .message-icon {
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
            }

            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-key"></i> Réinitialisation du mot de passe</h2>
                <p>
                    <?php echo $stage === "request" ? "Entrez votre email pour changer le mot de passe" : "Entrez votre nouveau mot de passe"; ?>
                </p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle message-icon"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle message-icon"></i>
                        <span><?php echo $message; ?></span>
                    </div>
                <?php elseif ($show_reset_form): ?>
                    <form method="POST">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="form-group">
                            <label for="password">Nouveau mot de passe</label>
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirmer" required>
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Mettre à jour le mot de passe
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="email">Adresse email</label>
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-control" placeholder="Entrez votre email" required>
                        </div>
                        <button type="submit" name="reset_request" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Réinitialiser
                        </button>
                    </form>
                <?php endif; ?>

                <div class="login-footer">
                    <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>