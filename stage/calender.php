<?php
include "sql/db.php";
session_start();

$db = Database::getInstance()->getConnection();

$userPPR = $_COOKIE['PPR'];
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isAdmin) {
        if (isset($_POST['add_event'])) {
            // Add new event
            $stmt = $db->prepare("INSERT INTO events (title, description, start_date, end_date, created_by) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['start_date'],
                $_POST['end_date'],
                $userPPR
            ]);
            $eventId = $db->lastInsertId();
            
            // Assign employees
            if (!empty($_POST['employees'])) {
                $assignStmt = $db->prepare("INSERT INTO event_assignments (event_id, employee_ppr) VALUES (?, ?)");
                foreach ($_POST['employees'] as $employeePPR) {
                    $assignStmt->execute([$eventId, $employeePPR]);
                }
            }
        }
        elseif (isset($_POST['delete_event'])) {
            // Delete event
            $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$_POST['event_id']]);
        }
    }
}

// Get events for the current user
if ($isAdmin) {
    $events = $db->query("SELECT * FROM events ORDER BY start_date")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare("SELECT e.* FROM events e 
                         JOIN event_assignments ea ON e.id = ea.event_id
                         WHERE ea.employee_ppr = ?
                         ORDER BY e.start_date");
    $stmt->execute([$userPPR]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all employees for admin dropdown
$employees = [];
if ($isAdmin) {
    $employees = $db->query("SELECT PPR, nom, prenom FROM utilisateurs ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #3f37c9;
            --light-bg: #f0f8ff;
            --table-bg: #ffffff;
            --text-dark: #2e3a4d;
            --border-color: #c5e0ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e6f0ff;
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--table-bg);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 10px;
        }

        .calendar-container {
            margin-bottom: 30px;
        }

        #calendar {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .event-form {
            background-color: #e6f0ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }

        .btn-primary:hover {
            background-color: #4361ee;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .employee-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: 4px;
        }

        .employee-checkbox {
            margin-right: 10px;
        }
        .return-button{
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
            margin-right:-1,5em;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1><i class="far fa-calendar-alt"></i> Calendrier des Événements</h1>
        <h4 ><a href="dash.php" class=" return-button">
                    <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
                </a></h4>
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>

        <?php if ($isAdmin): ?>
        <div class="event-form">
            <h2><i class="fas fa-plus-circle"></i> Ajouter un Événement</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Date et Heure de Début</label>
                    <input type="datetime-local" id="start_date" name="start_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">Date et Heure de Fin</label>
                    <input type="datetime-local" id="end_date" name="end_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Employés Concernés</label>
                    <div class="employee-list">
                        <?php foreach ($employees as $employee): ?>
                        <div>
                            <input type="checkbox" class="employee-checkbox" name="employees[]" 
                                   value="<?= htmlspecialchars($employee['PPR']) ?>" id="emp_<?= $employee['PPR'] ?>">
                            <label for="emp_<?= $employee['PPR'] ?>">
                                <?= htmlspecialchars($employee['prenom']). ' ' . htmlspecialchars($employee['nom']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button type="submit" name="add_event" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer l'Événement
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const eventsData = <?php echo json_encode(array_map(function($event) use ($isAdmin) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_date'],
                'end' => $event['end_date'],
                'description' => $event['description'] ?? '',
                'color' => $isAdmin ? '#4361ee' : '#28a745'
            ];
        }, $events)); ?>;
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',  // Only show month view
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''  // Remove view switching buttons
            },
            views: {
                dayGridMonth: {  // Configure only the month view
                    titleFormat: { year: 'numeric', month: 'long' }
                }
            },
            events: eventsData,
            eventClick: function(info) {
                const description = info.event.extendedProps.description || 'Pas de description';
                alert(`Détails de l'événement:\n\nTitre: ${info.event.title}\n\nDescription: ${description}`);
                
                <?php if ($isAdmin): ?>
                if (confirm("Voulez-vous supprimer cet événement?")) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="event_id" value="${info.event.id}">
                        <input type="hidden" name="delete_event" value="1">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
                <?php endif; ?>
            }
        });
        
        calendar.render();
    }
});
</script>
</body>
</html>