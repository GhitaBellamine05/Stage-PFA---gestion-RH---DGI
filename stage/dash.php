<?php
session_start();
include("sql/db.php");
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$currentUserPPR = $_SESSION['user']['PPR'];
$currentUserImg = null;

try {
    $db = Database::getInstance()->getConnection();
      $stats = [];
    
    // Nombre d'employés
    $stmt = $db->query("SELECT COUNT(*) AS count FROM utilisateurs");
    $stats['employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Nombre de diplômes
    $stmt = $db->query("SELECT COUNT(*) AS count FROM diplome");
    $stats['diplomas'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Nombre d'unités
    $stmt = $db->query("SELECT COUNT(*) AS count FROM us");
    $stats['unities'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Répartition par genre
    $stmt = $db->query("SELECT genre, COUNT(*) as count FROM utilisateurs GROUP BY genre");
    $stats['gender'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Répartition par âge
    $stmt = $db->query("
        SELECT 
            FLOOR(DATEDIFF(CURDATE(), d_naiss)/365) AS age,
            COUNT(*) as count
        FROM utilisateurs
        GROUP BY FLOOR(DATEDIFF(CURDATE(), d_naiss)/365)
        ORDER BY age
    ");
    $ageData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['age'] = [
        ['age_range' => '20-29', 'count' => 0],
        ['age_range' => '30-39', 'count' => 0],
        ['age_range' => '40-49', 'count' => 0],
        ['age_range' => '50-59', 'count' => 0],
        ['age_range' => '60+', 'count' => 0]
    ];
    
    foreach ($ageData as $ageGroup) {
        $age = $ageGroup['age'];
        if ($age >= 20 && $age < 30) {
            $stats['age'][0]['count'] += $ageGroup['count'];
        } elseif ($age >= 30 && $age < 40) {
            $stats['age'][1]['count'] += $ageGroup['count'];
        } elseif ($age >= 40 && $age < 50) {
            $stats['age'][2]['count'] += $ageGroup['count'];
        } elseif ($age >= 50 && $age < 60) {
            $stats['age'][3]['count'] += $ageGroup['count'];
        } elseif ($age >= 60) {
            $stats['age'][4]['count'] += $ageGroup['count'];
        }
    }
    $stmt = $db->query("SELECT role, COUNT(*) as count FROM utilisateurs GROUP BY role");
    $stats['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT img_profile, mime_type2 FROM utilisateurs WHERE PPR = :ppr");
    $stmt->bindParam(':ppr', $currentUserPPR);
    $stmt->execute();
    $currentUserImg = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

$currentImageSrc = '';
if ($currentUserImg && $currentUserImg['img_profile']) {
    $currentImageSrc = "data:" . $currentUserImg['mime_type2'] . ";base64," . base64_encode($currentUserImg['img_profile']);
}

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
  <title>Tableau de bord - Gestion RH</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      
      --primary-color: #4361ee;
      --secondary-color: #3f37c9; 
      --accent-color: #4895ef;
      --light-color: #f8f9fa;
      --dark-color: #212529;
      --sidebar-width: 300px;
      --navbar-height: 70px;
      --transition: all 0.3s ease;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --info-color: #17a2b8;
    }

    body {
      background-color: #f0f8ff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #212529;
    }
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: #e6f0ff;
      color: var(--dark-color);
      padding-top: var(--navbar-height);
      transition: var(--transition);
      z-index: 1000;
      box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
    }

    .sidebar-profile {
      padding: 20px;
      border-bottom: 1px solid #d1e0ff;
      text-align: center;
      background: linear-gradient(to right, #e6f0ff, #d1e0ff);
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 10px;
    }

    .profile-name {
      font-weight: 600;
      margin-bottom: 5px;
    }

    .profile-role {
      font-size: 0.8rem;
      color: #4361ee;
      background-color: #d1e0ff;
      padding: 3px 10px;
      border-radius: 20px;
      display: inline-block;
    }

    .sidebar-menu {
      padding: 20px 0;
      flex-grow: 1;
      overflow-y: auto;
      background-color: #e6f0ff;
    }

    .sidebar-menu .nav-link {
      color: var(--dark-color);
      padding: 12px 25px;
      margin: 3px 0;
      border-radius: 0;
      transition: var(--transition);
      display: flex;
      align-items: center;
      font-weight: 500;
    }

    .sidebar-menu .nav-link:hover,
    .sidebar-menu .nav-link.active {
      color: var(--primary-color);
      background-color: rgba(67, 97, 238, 0.2);
      border-left: 3px solid var(--primary-color);
    }

    .sidebar-menu .nav-link i {
      margin-right: 12px;
      font-size: 1.1rem;
      width: 20px;
      text-align: center;
      color: #4361ee;
    }

    /* Styles de la barre de navigation */
    .navbar {
      height: var(--navbar-height);
      background-color: #e6f0ff;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
      position: fixed;
      top: 0;
      left: var(--sidebar-width);
      right: 0;
      z-index: 1000;
      padding: 0 25px;
      border-bottom: 1px solid #d1e0ff;

    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #d1e0ff;
    }

    .main-content {
      margin-left: var(--sidebar-width);
      padding: 25px;
      padding-top: calc(var(--navbar-height) + 25px);
      min-height: 100vh;
      transition: var(--transition);
      background-color: #f8fafc;
    }

    .stats-card {
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      color: white;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
      height: 100%;
      border: none;
    }

    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-card.employees {
      background: linear-gradient(135deg, #4361ee, #3a0ca3);
    }

    .stats-card.diplomas {
      background: linear-gradient(135deg, #4cc9f0, #4895ef);
    }

    .stats-card.unities {
      background: linear-gradient(135deg, #3a86ff, #3f37c9);
    }

    .stats-card i {
      font-size: 2rem;
      margin-bottom: 15px;
      opacity: 0.8;
    }

    .stats-card .count {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .stats-card .label {
      font-size: 1rem;
      opacity: 0.9;
    }

    .chart-card {
      background-color: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      border: 1px solid #d1e0ff;
      height: 100%;
    }

    .chart-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .chart-card-title {
      font-weight: 600;
      color: var(--dark-color);
      margin: 0;
      font-size: 1.1rem;
    }

    .chart-container {
      position: relative;
      height: 250px;
      width: 100%;
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .navbar {
        left: 0;
      }
      
      .main-content {
        margin-left: 0;
      }
    }

    .sidebar-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--dark-color);
    }

    @media (max-width: 992px) {
      .sidebar-toggle {
        display: block;
      }
    }

    .breadcrumb {
      background-color: transparent;
      padding: 0;
      font-size: 0.9rem;
    }

    .section-header {
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .section-title {
      font-weight: 600;
      margin: 0;
      color: #4361ee;
    }
  </style>
</head>
<body>
  <!-- Barre latérale -->
  <div class="sidebar">
    <!-- Section profil utilisateur -->
    <div class="sidebar-profile">
      <?php if (!empty($currentImageSrc)): ?>
        <img src="<?= $currentImageSrc ?>" class="profile-avatar" alt="<?= $fullName ?>">
      <?php else: ?>
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=4361ee&color=fff" class="profile-avatar" alt="<?= $fullName ?>">
      <?php endif; ?>
      <h5 class="profile-name"><?= $fullName ?></h5>
      <span class="profile-role"><?= $role ?></span>
    </div>
    
    <!-- Menu principal -->
    <!-- Pour tous les employés -->
    <div class="sidebar-menu">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link active" href="dash.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="user_management.php">
            <i class="bi bi-people-fill"></i> Gestion des employées
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="unities.php">
            <i class="bi bi-building"></i> Unités
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="diplomes.php">
            <i class="bi bi-award-fill"></i> Diplômes
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="community.php">
            <i class="bi bi-people-fill"></i> Communauté
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reclamations.php">
            <i class="bi bi-exclamation-triangle-fill"></i> Réclamations
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="leave_management.php">
            <i class="bi bi-calendar-event"></i> Congés & Absences
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="warnings.php">
            <i class="bi bi-exclamation-octagon-fill"></i> Mes avertissements
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="rapport.php">
            <i class="bi bi-clipboard-data"></i> Rapports d'activité
          </a>
        </li>
          <li class="nav-item">
          <a class="nav-link" href="calender.php">
    <i class="bi bi-calendar3"></i> Calendrier
          </a>
        </li>
        
       
        
        <!-- Pour admin seulement -->
        <?php if ($role === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link" href="absence.php">
            <i class="bi bi-file-earmark-text-fill"></i> Liste des absences
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin/leave_approval.php">
            <i class="bi bi-clipboard-check"></i> Approuver les congés
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin/absence_approval.php">
            <i class="bi bi-check-square-fill"></i> Approbation des absences
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin/absence_policy.php">
            <i class="bi bi-file-earmark-text-fill"></i> Politique d'absence
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
    
    <!-- Pied de page -->
    <div class="sidebar-footer p-3 text-center border-top">
      <a href="logout.php" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </a>
    </div>
  </div>

  <!-- Barre de navigation -->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand ms-3" href="#">
        <div class="tax-office-header" style="display: flex; align-items: center; gap: 10px; padding: 15px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#4361ee" viewBox="0 0 16 16" style="flex-shrink: 0;">
            <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022ZM6 8.694 1 10.36V15h5V8.694ZM7 15h2v-1.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5V15h2V1.309l-7 3.5V15Z"/>
            <path d="M2 11h1v1H2v-1Zm2 0h1v1H4v-1Zm-2 2h1v1H2v-1Zm2 0h1v1H4v-1Zm4-4h1v1H8V9Zm2 0h1v1h-1V9Zm-2 2h1v1H8v-1Zm2 0h1v1h-1v-1Zm2-2h1v1h-1V9Zm0 2h1v1h-1v-1ZM8 7h1v1H8V7Zm2 0h1v1h-1V7Zm2 0h1v1h-1V7ZM8 5h1v1H8V5Zm2 0h1v1h-1V5Zm2 0h1v1h-1V5Zm0-2h1v1h-1V3Z"/>
          </svg>
          <h1 style="margin: 0; font-size: 1.5rem; color: #4361ee; font-weight: 600;">
            Direction Régionale des Impôts   
          </h1>
        </div>
      </a>
      
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
              <?php if (!empty($currentImageSrc)): ?>
                <img src="<?= $currentImageSrc ?>" class="user-avatar" alt="<?= $fullName ?>">
              <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullName) ?>&background=4361ee&color=fff" class="user-avatar" alt="<?= $fullName ?>">
              <?php endif; ?>
              <span class="ms-2 d-none d-lg-inline"><?= $fullName ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="Profile/Myprofile.php"><i class="bi bi-person me-2"></i> Mon profil</a></li>
              <li><a class="dropdown-item" href="Profile/Mydiploms.php"><i class="bi bi-file-earmark-text me-2"></i> Mes diplômes</a></li>
              <li><a class="dropdown-item" href="Profile/MyHistory.php"><i class="bi bi-building me-2"></i> Mon historique</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Déconnexion</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Contenu principal -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="row mb-4">
        <div class="col-12">
          <div class="section-header">
            <div>
              <h2 class="section-title">Aperçu du tableau de bord</h2>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#"><i class="bi bi-house-door"></i> Accueil</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- Cartes de statistiques -->
      <div class="row mb-4 justify-content-center">
        <div class="col-md-3">
          <div class="stats-card employees">
            <i class="bi bi-people-fill"></i>
            <div class="count"><?= number_format($stats['employees']) ?></div>
            <div class="label">Employés totaux</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card diplomas">
            <i class="bi bi-file-earmark-text"></i>
            <div class="count"><?= number_format($stats['diplomas']) ?></div>
            <div class="label">Diplômes</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card unities">
            <i class="bi bi-building"></i>
            <div class="count"><?= number_format($stats['unities']) ?></div>
            <div class="label">Unités</div>
          </div>
        </div>
      </div>

      <!-- Section des graphiques -->
      <div class="row mb-4">
        <!-- Répartition par genre -->
        <div class="col-md-6">
          <div class="chart-card">
            <div class="chart-card-header">
              <h3 class="chart-card-title">Répartition par genre</h3>
            </div>
            <div class="chart-container">
              <canvas id="genderChart"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Répartition par âge -->
        <div class="col-md-6">
          <div class="chart-card">
            <div class="chart-card-header">
              <h3 class="chart-card-title">Répartition par âge</h3>
            </div>
            <div class="chart-container">
              <canvas id="ageChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Répartition par rôle -->
         <div class="row justify-content-center"> 
        <div class="col-md-8 ">
          <div class="chart-card">
            <div class="chart-card-header">
              <h3 class="chart-card-title">Répartition par rôle</h3>
            </div>
            <div class="chart-container">
              <canvas id="roleChart"></canvas>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Basculer la barre latérale sur mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      document.querySelector('.sidebar').classList.toggle('active');
    });

    // Graphique de répartition par genre
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(genderCtx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode(array_column($stats['gender'], 'genre')) ?>,
        datasets: [{
          data: <?= json_encode(array_column($stats['gender'], 'count')) ?>,
          backgroundColor: [
            '#4361ee',
            '#f72585',
            '#4cc9f0'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });

    // Graphique de répartition par âge
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    const ageChart = new Chart(ageCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_column($stats['age'], 'age_range')) ?>,
        datasets: [{
          label: 'Employés',
          data: <?= json_encode(array_column($stats['age'], 'count')) ?>,
          backgroundColor: '#4895ef',
          borderColor: '#4361ee',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Nombre d\'employés'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Tranche d\'âge'
            }
          }
        }
      }
    });

    // Graphique de répartition par rôle
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    const roleChart = new Chart(roleCtx, {
      type: 'pie',
      data: {
        labels: <?= json_encode(array_column($stats['roles'], 'role')) ?>,
        datasets: [{
          data: <?= json_encode(array_column($stats['roles'], 'count')) ?>,
          backgroundColor: [
            '#4361ee',
            '#4cc9f0',
            '#f72585',
            '#4895ef',
            '#3a0ca3'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>