<?php
session_start();
include("../sql/db.php");

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Récupérer la politique actuelle
try {
    $db = Database::getInstance()->getConnection();
    $policy = $db->query("SELECT * FROM absence_policies LIMIT 1")->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Mettre à jour la politique si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maxHours = $_POST['max_unjustified_hours'];
    $warningMessage = $_POST['warning_message'];
    
    try {
        $stmt = $db->prepare(
            "UPDATE absence_policies 
             SET max_unjustified_hours = :max_hours, 
                 warning_message = :warning_message 
             WHERE id = :id"
        );
        $stmt->bindParam(':max_hours', $maxHours);
        $stmt->bindParam(':warning_message', $warningMessage);
        $stmt->bindParam(':id', $policy['id']);
        $stmt->execute();
        
        $success = "Politique mise à jour avec succès !";
        $policy = $db->query("SELECT * FROM absence_policies LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour de la politique : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique d'Absence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
/* Thème Bleu Clair pour la Politique d'Absence */
:root {
  --primary-blue: #4361ee;
  --secondary-blue: #3a0ca3;
  --light-blue: #e6f0ff;
  --soft-blue: #f5f9ff;
  --dark-blue: #4361ee;
  --text-color: #2c3e50;
  --success-green: #28a745;
  --danger-red: #dc3545;
}

body {
  background-color: #e6f0ff;
  color: var(--text-color);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
  max-width: 800px;
  padding-top: 2rem;
}

h2 {
  color: var(--dark-blue);
  font-weight: 600;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--light-blue);
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.1);
  overflow: hidden;
}

.card-header {
  background-color: var(--primary-blue);
  color: white;
  padding: 1.25rem 1.5rem;
  border-bottom: none;
}

.card-header h5 {
  margin-bottom: 0;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-body {
  padding: 2rem;
  background-color: white;
}

.form-control, .form-select {
  border-radius: 6px;
  padding: 10px 15px;
  border: 1px solid rgba(67, 97, 238, 0.2);
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
}

.form-label {
  font-weight: 500;
  color: var(--dark-blue);
  margin-bottom: 8px;
}

textarea.form-control {
  min-height: 120px;
}

.btn-primary {
  background-color: var(--primary-blue);
  border: none;
  padding: 10px 24px;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background-color: var(--secondary-blue);
  transform: translateY(-2px);
}

.alert {
  border-radius: 8px;
  border: none;
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
}

.alert-success {
  background-color: rgba(40, 167, 69, 0.1);
  color: var(--success-green);
  border-left: 4px solid var(--success-green);
}

.alert-danger {
  background-color: rgba(220, 53, 69, 0.1);
  color: var(--danger-red);
  border-left: 4px solid var(--danger-red);
}

.bi {
  font-size: 1.2rem;
}

@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  h2 {
    font-size: 1.5rem;
  }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.card {
  animation: fadeIn 0.5s ease-out;
}
.return-button {
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
        
        .return-button:hover {
            background-color: #748cf5ff;;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
    </style>
</head>
<body>
    
    <div class="container py-4">
         <div style="display:flex;gap:20em;margin-top:0,5em">
              <h2 class="mb-4"><i class="bi bi-sliders"></i> Politique d'Absence</h2> 
              <h3><a href="javascript:history.back()" class="return-button">
                <i class="fas fa-arrow-left"></i> Retour
        </a> </h3>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Modifier la Politique d'Absence</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="max_unjustified_hours" class="form-label">Heures Maximales Non Justifiées Avant Avertissement</label>
                        <input type="number" class="form-control" id="max_unjustified_hours" 
                               name="max_unjustified_hours" value="<?= $policy['max_unjustified_hours'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="warning_message" class="form-label">Message d'Avertissement</label>
                        <textarea class="form-control" id="warning_message" name="warning_message" 
                                  rows="3" required><?= $policy['warning_message'] ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à Jour la Politique</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
