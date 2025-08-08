<?php
session_start();
include "sql/db.php";
$db = Database::getInstance()->getConnection();
$loggedIn = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portail Employé RH DGI - Ministère de l'Economie et des Finances - Maroc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee; 
            --primary-light: #3f37c9;
            --primary-dark: #0d4b8a;
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
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .official-header {
            background-color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-img {
            height: 70px;
            margin-right: 1rem;
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
            border-left: 2px solid var(--primary-color);
            padding-left: 1rem;
        }

        .logo-text span {
            display: block;
            font-size: 0.9rem;
            font-weight: normal;
            color: var(--text-light);
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 5rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-top: -1px; 
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.9;
        }

        .features {
            padding: 4rem 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--primary-color);
            font-size: 2rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            margin: 0.5rem auto 0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--secondary-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-dark);
        }

        .auth-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: white;
            color: var(--primary-color);
            box-shadow: var(--shadow);
            border: 1px solid white;
        }

        .btn-primary:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        /* News section */
        .news-section {
            background: var(--secondary-color);
            padding: 4rem 0;
        }

        .news-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .news-header {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            font-weight: bold;
        }

        .news-content {
            padding: 1.5rem;
        }

        .news-date {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        /* Pied de page */
        footer {
            background: var(--text-color);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .footer-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            color: var(--primary-light);
        }

        .footer-section p {
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: var(--transition);
        }

        .footer-links a:hover {
            opacity: 1;
            color: var(--primary-light);
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-icons a {
            color: white;
            font-size: 1.2rem;
            transition: var(--transition);
            opacity: 0.8;
        }

        .social-icons a:hover {
            color: var(--primary-light);
            opacity: 1;
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .logo-container {
                margin-bottom: 1rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .auth-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
            
            .footer-section {
                flex: 100%;
                text-align: center;
            }
            
            .social-icons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Official Header with Logo -->
    <header class="official-header">
        <div class="container header-content">
            <div class="logo-container">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c6/Mef-maroc.png" alt="Logo Ministère des Finances" class="logo-img">
                <div class="logo-text">
                    Ministère de l'Economie et des Finances
                    <span>Direction Générale des Impôts - DGI</span>
                </div>
            </div>
            <?php if ($loggedIn): ?>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Section Hero -->
    <section class="hero">
        <div class="hero-content container">
            <h1>Portail RH DGI</h1>
            <h3>L'outil de gestion du capital humain de la DGI</h3>
            <p>Accédez à des statistiques complètes sur les employés, gérez les unités organisationnelles et suivez le développement professionnel, le tout en un seul endroit sécurisé.</p>
            
            <?php if (!$loggedIn): ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a>
                    <a href="signup.php" class="btn btn-outline">
                        <i class="fas fa-user-plus"></i> Créer un compte
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Nos Services</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Statistiques Complètes</h3>
                    <p>Visualisez et analysez les données des employés avec des tableaux de bord interactifs et des rapports détaillés.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h3>Gestion des Unités</h3>
                    <p>Organisez et gérez efficacement les différentes unités organisationnelles de votre administration.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Développement Professionnel</h3>
                    <p>Suivez les formations, compétences et évolutions de carrière de vos collaborateurs.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="container">
            <h2 class="section-title">Actualités</h2>
            <div class="news-card">
                <div class="news-header">Nouvelle version du portail</div>
                <div class="news-content">
                    <div class="news-date">15 juin 2025</div>
                    <p>Découvrez les nouvelles fonctionnalités de notre portail employé, conçu pour améliorer votre expérience utilisateur et vous fournir des outils plus performants.</p>
                </div>
            </div>
            <div class="news-card">
                <div class="news-header">Formation à venir</div>
                <div class="news-content">
                    <div class="news-date">5 juillet 2025</div>
                    <p>Une session de formation sur l'utilisation avancée du portail sera organisée le mois prochain. Inscrivez-vous dès maintenant.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pied de page -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Ministère de l'Economie et des Finances</h3>
                    <p>Direction Générale des Impôts</p>
                    <p>Portail des Employés - Version 2.0</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Liens Utiles</h3>
                    <ul class="footer-links">
                        <li><a href="https://www.tax.gov.ma" target="_blank">Site officiel</a></li>
                        <li><a href="#">Ressources humaines</a></li>
                        <li><a href="#">Formations</a></li>
                        <li><a href="#">Documents officiels</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p><i class="fas fa-envelope"></i> webmaster@tax.gov.ma</p>
                    <p><i class="fas fa-phone"></i>  05 37 27 37 27</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Ministère de l'Economie et de Finance - Tous droits réservés</p>
            </div>
        </div>
    </footer>
</body>
</html>