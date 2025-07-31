<?php
session_start();
include("sql/db.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Initialiser les variables de filtre
$filters = [
    'nom' => $_GET['nom'] ?? '',
    'prenom' => $_GET['prenom'] ?? '',
    'Cin' => $_GET['Cin'] ?? '',
    'd_naiss' => $_GET['d_naiss'] ?? '',
    'd_recrutement' => $_GET['d_recrutement'] ?? '',
    'sit_familliale' => $_GET['sit_familliale'] ?? '',
    'genre' => $_GET['genre'] ?? '',
    'role' => $_GET['role'] ?? '',
    'email' => $_GET['email'] ?? '',
    'fonction' => $_GET['fonction'] ?? '',
    'grade' => $_GET['grade'] ?? ''
];

// Construire la requête SQL avec filtres
$sql = "SELECT * FROM utilisateurs WHERE 1=1";
$params = [];

foreach ($filters as $field => $value) {
    if (!empty($value)) {
        $sql .= " AND $field LIKE :$field";
        $params[":$field"] = "%$value%";
    }
}

// Ajouter le tri si spécifié
$sort = $_GET['sort'] ?? '';
$order = $_GET['order'] ?? 'ASC';
if (!empty($sort) && in_array(strtoupper($order), ['ASC', 'DESC'])) {
    $sql .= " ORDER BY $sort $order";
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtenir les valeurs distinctes pour les menus déroulants
    $distinctValues = [
        'sit_familliale' => $db->query("SELECT DISTINCT sit_familliale FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN),
        'genre' => $db->query("SELECT DISTINCT genre FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN),
        'role' => $db->query("SELECT DISTINCT role FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN),
        'fonction' => $db->query("SELECT DISTINCT fonction FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN),
        'grade' => $db->query("SELECT DISTINCT grade FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN)
    ];
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Obtenir les statistiques
try {
    // Compter les employés
    $userCountStmt = $db->query("SELECT COUNT(*) AS count FROM utilisateurs");
    $userCount = $userCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Compter les diplômes
    $diplomeCountStmt = $db->query("SELECT COUNT(*) AS count FROM diplome");
    $diplomeCount = $diplomeCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Compter les unités
    $usCountStmt = $db->query("SELECT COUNT(*) AS count FROM us");
    $usCount = $usCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    die("Erreur lors de la récupération des statistiques : " . $e->getMessage());
}

// Obtenir l'image de profil de l'utilisateur actuel
$currentUserPPR = $_SESSION['user']['PPR'];
$currentUserImg = null;

try {
    $stmt = $db->prepare(query: "SELECT img_profile, mime_type2 FROM utilisateurs WHERE PPR = :ppr");
    $stmt->bindParam(':ppr', $currentUserPPR);
    $stmt->execute();
    $currentUserImg = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de l'image : " . $e->getMessage());
}

$currentImageSrc = '';
if ($currentUserImg && $currentUserImg['img_profile']) {
    $currentImageSrc = "data:" . $currentUserImg['mime_type2'] . ";base64," . base64_encode($currentUserImg['img_profile']);
}

// Obtenir les données utilisateur depuis les cookies
$nom = $_COOKIE['nom'] ?? 'Inconnu';
$prenom = $_COOKIE['prenom'] ?? 'Utilisateur';
$role = $_COOKIE['role'] ?? 'Utilisateur';
$fullName = htmlspecialchars("$prenom $nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des utilisateurs </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-blue: #4361ee;
      --secondary-blue: #3f37c9;
      --light-blue: #e6f0fa;
      --dark-blue: #212529;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #e6f0ff;
    }
    
    .main-content {
      padding: 2rem;
      margin-top: 20px;
      margin-left : 5em;
      margin-right : 5em;
    }
    
    .filter-panel {
      background-color: white;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .filter-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid #eee;
    }
    
    .filter-title {
      color: var(--primary-blue);
      font-size: 1.25rem;
      margin: 0;
    }
    
    .filter-group {
      margin-bottom: 1rem;
    }
    
    .filter-group label {
      font-weight: 500;
      color: var(--dark-blue);
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .btn-primary {
      background-color: var(--primary-blue);
      border-color: var(--primary-blue);
    }
    
    .btn-primary:hover {
      background-color: var(--secondary-blue);
      border-color: var(--secondary-blue);
    }
    
    .btn-outline-primary {
      color: var(--primary-blue);
      border-color: var(--primary-blue);
    }
    
    .btn-outline-primary:hover {
      background-color: var(--primary-blue);
      color: white;
    }
    
    .user-card {
      border: none;
      border-radius: 8px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      height: 100%;
    }
    
    .user-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .user-image-container {
      position: relative;
      height: 180px;
      overflow: hidden;
    }
    
    .user-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    
    .user-card:hover .user-image {
      transform: scale(1.05);
    }
    
    .user-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: var(--primary-blue);
      color: white;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .user-info {
      padding: 1.25rem;
      background-color: white;
    }
    
    .user-info h5 {
      color: var(--dark-blue);
      margin-bottom: 0.75rem;
      font-weight: 600;
    }
    
    .user-info p {
      color: #666;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }
    
    .user-role {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-top: 0.5rem;
    }
    
    .role-admin {
      background-color: #d4edda;
      color: #155724;
    }
    
    .role-user {
      background-color: #f8d7da;
      color: #721c24;
    }
  
    .tax-office-header {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .tax-office-header svg {
      color: var(--primary-blue);
    }
    
    .tax-office-header h1 {
      margin: 0;
      font-size: 1.5rem;
      color: var(--primary-blue);
      font-weight: 600;
    }
    
    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }
      
      .filter-panel {
        padding: 1rem;
      }
      
      .user-image-container {
        height: 150px;
      }
    }
     .nav-button {
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
        
        .nav-button:hover {
            background-color: #748cf5ff;;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91, 155, 213, 0.3);
        }
        
  </style>
</head>
<body>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">      
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <div style="margin-left:45%;">
              <h2 class="mb-0" style="color: var(--primary-blue);"> <i class="bi bi-people-fill"></i> Gestion des Utilisateurs</h2>
            </div>
            <br>
            <div style="display:flex;gap:2em;">
              <?php if ($role === 'admin'): ?>
            <a href="admin/user_add.php" class="nav-button return-button">
              <i class="bi bi-plus-circle"></i> Ajouter un Utilisateur
            </a>
            <br>
            <?php endif; ?>
            <a href="dash.php" class="nav-button return-button">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            </div>
             
          </div>
        </div>
      </div>

      <!-- Filter Panel -->
      <div class="filter-panel">
        <div class="filter-header">
          <h3 class="filter-title"><i class="bi bi-funnel me-2"></i>Filtrer les Utilisateurs</h3>
          <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="bi bi-funnel"></i> Afficher/Masquer
          </button>
        </div>
        
        <div class="collapse show" id="filterCollapse">
          <form method="GET" action="">
            <div class="row">
              <div class="col-md-4">
                <div class="filter-group">
                  <label for="nom"><i class="bi bi-person"></i> Nom</label>
                  <input type="text" class="form-control" id="nom" name="nom" 
                         value="<?= htmlspecialchars($filters['nom']) ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="filter-group">
                  <label for="prenom"><i class="bi bi-person"></i> Prénom</label>
                  <input type="text" class="form-control" id="prenom" name="prenom" 
                         value="<?= htmlspecialchars($filters['prenom']) ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="filter-group">
                  <label for="Cin"><i class="bi bi-credit-card"></i> CIN</label>
                  <input type="text" class="form-control" id="Cin" name="Cin" 
                         value="<?= htmlspecialchars($filters['Cin']) ?>">
                </div>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-md-3">
                <div class="filter-group">
                  <label for="role"><i class="bi bi-person-badge"></i> Rôle</label>
                  <select class="form-select" id="role" name="role">
                    <option value="">Tous les Rôles</option>
                    <?php foreach ($distinctValues['role'] as $role): ?>
                      <option value="<?= htmlspecialchars($role) ?>" <?= $filters['role'] === $role ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="filter-group">
                  <label for="genre"><i class="bi bi-gender-ambiguous"></i> Genre</label>
                  <select class="form-select" id="genre" name="genre">
                    <option value="">Tous les Genres</option>
                    <?php foreach ($distinctValues['genre'] as $genre): ?>
                      <option value="<?= htmlspecialchars($genre) ?>" <?= $filters['genre'] === $genre ? 'selected' : '' ?>>
                        <?= htmlspecialchars($genre) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="filter-group">
                  <label for="fonction"><i class="bi bi-briefcase"></i> Fonction</label>
                  <select class="form-select" id="fonction" name="fonction">
                    <option value="">Toutes les Fonctions</option>
                    <?php foreach ($distinctValues['fonction'] as $fonction): ?>
                      <option value="<?= htmlspecialchars($fonction) ?>" <?= $filters['fonction'] === $fonction ? 'selected' : '' ?>>
                        <?= htmlspecialchars($fonction) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="filter-group">
                  <label for="grade"><i class="bi bi-award"></i> Grade</label>
                  <select class="form-select" id="grade" name="grade">
                    <option value="">Tous les Grades</option>
                    <?php foreach ($distinctValues['grade'] as $grade): ?>
                      <option value="<?= htmlspecialchars($grade) ?>" <?= $filters['grade'] === $grade ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grade) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-filter"></i> Appliquer
                </button>
                <a href="?" class="btn btn-outline-secondary ms-2">
                  <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- User Grid -->
      <div class="row g-4">
        <?php foreach ($users as $user): ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="gestion.php?PPR=<?= urlencode($user['PPR']) ?>" class="text-decoration-none">
              <div class="card user-card">
                <div class="user-image-container">
                  <img src="image.php?PPR=<?= urlencode($user['PPR']) ?>" class="user-image" alt="Image de <?= htmlspecialchars($user['nom']) ?>">
                  <span class="user-badge"><?= htmlspecialchars($user['grade']) ?></span>
                </div>
                <div class="user-info">
                  <h5><?= htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']) ?></h5>
                  <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                  <p><i class="bi bi-briefcase"></i> <?= htmlspecialchars($user['fonction']) ?></p>
                  <p><i class="bi bi-building"></i> <?= htmlspecialchars($user['sit_familliale']) ?></p>
                  <span class="user-role <?= 
                    $user['role'] === 'admin' ? 'role-admin' : 
                    ($user['role'] === 'manager' ? 'role-manager' : 'role-user') 
                  ?>">
                    <?= htmlspecialchars($user['role']) ?>
                  </span>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script>
    // Toggle sidebar on mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.querySelector('.sidebar').classList.toggle('active');
    });

    // Handle filter toggling
    document.querySelectorAll('.filter-group select').forEach(select => {
      select.addEventListener('change', function() {
        this.form.submit();
      });
    });
  </script>
</body>
</html>