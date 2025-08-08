<?php
// Définir les données des rapports d'activité avec des URL complètes
$reports = [
    // Rapports 2024
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2024 - Version française',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/fd437b77-cb27-450e-b437-551271adce9d/Rapport+d%27activit%C3%A9+2024.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-fd437b77-cb27-450e-b437-551271adce9d-ptUR-Cf',
        'year' => 2024,
        'language' => 'Français'
    ],
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2024 - Version arabe',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/4cc187b7-57b1-4837-a72e-eedcf001c390/Rapport+d%27activit%C3%A9+2024+arab.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-4cc187b7-57b1-4837-a72e-eedcf001c390-ptUR6Av',
        'year' => 2024,
        'language' => 'Arabe'
    ],
    
    // Rapports 2023
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2023 - Version française',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/5bac39b7-dfb4-4c4b-9428-f021d7e526ea/rapport+d%27activite%CC%81+2023+VF.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-5bac39b7-dfb4-4c4b-9428-f021d7e526ea-p5qGomT', 
        'year' => 2023,
        'language' => 'Français'
    ],
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2023 - Version arabe',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/3f63c48d-5492-4124-9ed5-be205b1c04e2/rapport+d%27activit%C3%A9+2023+AR-DV_compressed+%281%29_compressed-compress%C3%A92.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-3f63c48d-5492-4124-9ed5-be205b1c04e2-p92LxRT', 
        'year' => 2023,
        'language' => 'Arabe'
    ],
    
    // Rapports 2022
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2022 - Version française',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/2a8189c2-73a8-499b-91d6-566c8479de72/Rapport+d%27activit%C3%A9+DGI-2022_comp.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-2a8189c2-73a8-499b-91d6-566c8479de72-oGZAkkd', 
        'year' => 2022,
        'language' => 'Français'
    ],
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2022 - Version arabe',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/9766ea27-819e-4443-895b-8fc5770d3a68/Rapport+d%27activit%C3%A9+DGI+2022+-+Version+arabe+%281%29+COMPRESSE.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-9766ea27-819e-4443-895b-8fc5770d3a68-oGZHWnj',
        'year' => 2022,
        'language' => 'Arabe'
    ],
    // Rapports 2021 
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2021 - Version française',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/309e5cd7-e6bd-4201-8ede-93813695174d/Web-Fr-Rapport+d%27activite%CC%81+DGI+2021.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-309e5cd7-e6bd-4201-8ede-93813695174d-o9Ku7dT',
        'year' => 2021,
        'language' => 'Français'
    ],
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2021 - Version arabe',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/4a5e48ab-e2d0-4f97-aa21-d54d7f90617b/web-Ar-Rapport+d%27activite%CC%81+DGI+2021-Web+vf.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-4a5e48ab-e2d0-4f97-aa21-d54d7f90617b-oa2Cv8Q',
        'year' => 2021,
        'language' => 'Arabe'
    ],
    // Rapports 2020 
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2020 - Version française',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/a426cca8-24ea-4120-a3ce-35e6592cdc71/Rapport%2Bd%27activite%CC%81%2BDGI%2B2020-FR.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-a426cca8-24ea-4120-a3ce-35e6592cdc71-nRND3Kl',
        'year' => 2020,
        'language' => 'Français'
    ],
    [
        'title' => 'Rapport d\'activité de la DGI pour l\'année 2020 - Version arabe',
        'link' => 'https://www.tax.gov.ma/wps/wcm/connect/6aa76ed7-5340-4645-8052-a7260d47d314/Rapport%2Bd%27activite%CC%81%2BDGI%2B2020-AR.pdf?MOD=AJPERES&CACHEID=ROOTWORKSPACE-6aa76ed7-5340-4645-8052-a7260d47d314-nRNDpy7',
        'year' => 2020,
        'language' => 'Arabe'
    ],
];

// Trier les rapports par année (du plus récent au plus ancien)
usort($reports, function($a, $b) {
    return $b['year'] - $a['year'];
});

// Obtenir l'année la plus récente
$latestYear = $reports[0]['year'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports d'Activité de la DGI</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .reports-container {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .reports-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .reports-header h1 {
            color: #4361ee;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .reports-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 25px;
        }
        .reports-tab {
            padding: 12px 25px;
            cursor: pointer;
            border: 1px solid transparent;
            margin-right: 5px;
            color: #4361ee;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .reports-tab:hover {
            color: #88a5e2ff;
        }
        .reports-tab.active {
            color: #88a5e2ff;
            border: 1px solid #e0e0e0;
            border-bottom: 1px solid #f9f9f9;
            margin-bottom: -1px;
            border-radius: 5px 5px 0 0;
            background: #ffffff;
            font-weight: 600;
        }
        .reports-list {
            list-style-type: none;
            padding: 0;
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .report-item {
            padding: 18px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;
        }
        .report-item:last-child {
            border-bottom: none;
        }
        .report-item:hover {
            background-color: #f5f9ff;
        }
        .report-title {
            color: black;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .download-btn {
            background-color: #3498db;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }
        .download-btn:hover {
            background-color: #2980b9;
        }
        .report-year {
            color: #7f8c8d;
            font-size: 16px;
            margin: 20px 0 10px 15px;
            font-weight: 500;
        }
        .report-language {
            color: #95a5a6;
            font-size: 14px;
        }
        h2 {
            color: #4361ee;
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 1.4em;
        }
        body {
            background-color: #e6f0ff;
            margin: 0;
            padding: 30px;
            color: #333;
            line-height: 1.6;
        }
         .return-button {
            background-color: #4361ee;
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
    <div class="reports-container">
        <div class="reports-header">
            <div style="display:flex; justify-content:center;align-items:center">
                <h1>Rapports d'Activité</h1>
                
            </div>
            
            <p>Rapports annuels de la Direction Générale des Impôts</p>
            <h4 ><a href="javascript:history.back()" class="nav-button return-button">
                    <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
                </a></h4>
        </div>

        <div class="reports-tabs">
            <div class="reports-tab active" onclick="filterReports('latest')">Dernier Rapport</div>
            <div class="reports-tab" onclick="filterReports('all')">Tous les Rapports</div>
        </div>
        
        <div id="latest-reports">
            <h2>Rapports pour <?php echo $latestYear; ?></h2>
            <ul class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <?php if ($report['year'] == $latestYear): ?>
                        <li class="report-item">
                            <div>
                                <div class="report-title"><?php echo $report['title']; ?></div>
                                <div class="report-language"><?php echo $report['language']; ?></div>
                            </div>
                            <a href="<?php echo htmlspecialchars($report['link']); ?>" class="download-btn" target="_blank" rel="noopener noreferrer">Télécharger</a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div id="all-reports" style="display: none;">
            <h2>Tous les Rapports d'Activité</h2>
            
            <ul class="reports-list">
                <?php 
                $currentYear = null;
                foreach ($reports as $report): 
                    if ($report['year'] != $currentYear):
                        $currentYear = $report['year'];
                ?>
                        <h3 class="report-year"><?php echo $currentYear; ?></h3>
                    <?php endif; ?>
                    <li class="report-item">
                        <div>
                            <div class="report-title"><?php echo $report['title']; ?></div>
                            <div class="report-language"><?php echo $report['language']; ?></div>
                        </div>
                        <a href="<?php echo htmlspecialchars($report['link']); ?>" class="download-btn" target="_blank" rel="noopener noreferrer">Télécharger</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        function filterReports(type) {
            const latestTab = document.querySelector('.reports-tabs div:nth-child(1)');
            const allTab = document.querySelector('.reports-tabs div:nth-child(2)');
            const latestSection = document.getElementById('latest-reports');
            const allSection = document.getElementById('all-reports');
            
            if (type === 'latest') {
                latestTab.classList.add('active');
                allTab.classList.remove('active');
                latestSection.style.display = 'block';
                allSection.style.display = 'none';
            } else {
                latestTab.classList.remove('active');
                allTab.classList.add('active');
                latestSection.style.display = 'none';
                allSection.style.display = 'block';
            }
        }
    </script>
</body>
</html>