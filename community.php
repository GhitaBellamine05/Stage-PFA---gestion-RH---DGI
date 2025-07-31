<?php
session_start();
include "sql/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$currentUserPPR = $_SESSION['user']['PPR'];

// Get current user data including profile picture
$userStmt = $db->prepare("SELECT nom, prenom, img_profile, mime_type2 FROM utilisateurs WHERE PPR = ?");
$userStmt->execute([$currentUserPPR]);
$currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    $stmt = $db->prepare("INSERT INTO forum_posts (PPR, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$currentUserPPR, $title, $content]);
    header("Location: community.php");
    exit();
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['comment_content'];
    
    $stmt = $db->prepare("INSERT INTO forum_comments (post_id, PPR, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $currentUserPPR, $content]);
    header("Location: community.php");
    exit();
}

// Get all forum posts with author names and profile pictures
$posts = $db->query("
    SELECT fp.*, u.nom, u.prenom, u.img_profile, u.mime_type2
    FROM forum_posts fp
    JOIN utilisateurs u ON fp.PPR = u.PPR
    ORDER BY fp.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get comments for each post with profile pictures
foreach ($posts as &$post) {
    $stmt = $db->prepare("
        SELECT fc.*, u.nom, u.prenom, u.img_profile, u.mime_type2
        FROM forum_comments fc
        JOIN utilisateurs u ON fc.PPR = u.PPR
        WHERE fc.post_id = ?
        ORDER BY fc.created_at ASC
    ");
    $stmt->execute([$post['id']]);
    $post['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($post); // Break the reference

// Function to display profile image
function displayProfileImage($user) {
    if ($user['img_profile']) {
        $base64 = base64_encode($user['img_profile']);
        $mime = $user['mime_type2'] ?: 'image/jpeg';
        return "data:$mime;base64,$base64";
    } else {
        $name = urlencode($user['prenom'] . ' ' . $user['nom']);
        return "https://ui-avatars.com/api/?name=$name&background=3498db&color=fff";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Communitaire</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4361ee;
            --secondary-blue: #88a5e2ff;
            --light-blue: #ddebfbff;
            --lighter-blue: #f0f8ff;
            --dark-blue: #2c3e50;
            --light-gray: #f8f9fa;
            --white: #ffffff;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e6f0ff;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 1f000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1, h2, h3, h4 {
            color: #4361ee;
        }
        
        .forum-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(52, 152, 219, 0.2);
        }
        
        .post-form {
            background-color: var(--white);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(52, 152, 219, 0.1);
        }
        
        .post-form h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
        }
        
        .post-form input[type="text"],
        .post-form textarea,
        .comment-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            transition: border 0.3s;
            font-family: inherit;
        }
        
        .post-form input[type="text"]:focus,
        .post-form textarea:focus,
        .comment-form textarea:focus {
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .post-form textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .post-card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(52, 152, 219, 0.1);
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-blue);
        }
        
        .post-author {
            font-weight: 600;
            color: black;
        }
        
        .post-date {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .post-content {
            margin-bottom: 20px;
            padding: 15px;
            background-color: var(--light-blue);
            border-radius: 6px;
            line-height: 1.6;
        }
        
        .comments-section {
            margin-top: 30px;
            padding-left: 20px;
            border-left: 3px solid var(--lighter-blue);
        }
        
        .comment {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .comment:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .comment-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--lighter-blue);
        }
        
        .comment-content-wrapper {
            flex: 1;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .comment-author {
            font-weight: 500;
            color: var(--dark-blue);
        }
        
        .comment-date {
            color: #7f8c8d;
            font-size: 0.85em;
        }
        
        .comment-content {
            padding-left: 10px;
            color: #555;
            line-height: 1.5;
        }
        
        .comment-form {
            margin-top: 25px;
            padding-top: 20px;
            display: flex;
            gap: 15px;
        }
        
        .current-user-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-blue);
        }
        
        .comment-form-wrapper {
            flex: 1;
        }
        
        .comment-form h4 {
            color: var(--primary-blue);
            margin-bottom: 15px;
        }
        
        .comment-form textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            transform: translateY(-1px);
        }
        
        .no-posts {
            text-align: center;
            padding: 40px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            color: #95a5a6;
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
    <div class="main-content">
        <div class="container">
            <div class="forum-header">
                <h1><i class="fas fa-users" style="color: var(--primary-blue);"></i> Forum Communautaire</h1>
                <br>
                <br>   <a href="dash.php" class="return-button">
                <i class="fas fa-arrow-left"></i> Retour au Tableau de Bord
            </a>
            </div>
            
            <!-- Formulaire de création de post -->
            <div class="post-form">
                <h3><i class="fas fa-edit"></i> Partagez Vos Pensées</h3>
              
                <form method="POST">
                    <input type="text" name="title" placeholder="Titre du post" required>
                    <textarea name="content" placeholder="À quoi pensez-vous ?" required></textarea>
                    <button type="submit" name="create_post" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publier
                    </button>
                </form>
            </div>
            
            <!-- Liste des posts -->
            <div class="posts-list">
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-comments fa-2x" style="margin-bottom: 15px;"></i>
                        <h3>Aucun post pour l'instant</h3>
                        <p>Soyez le premier à lancer la conversation !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div class="author-info">
                                    <img src="<?= displayProfileImage($post) ?>" 
                                         alt="Photo de profil" class="profile-picture">
                                    <span class="post-author"><?= htmlspecialchars($post['prenom'] . ' ' . $post['nom']) ?></span>
                                </div>
                                <span class="post-date"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                            </div>
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <div class="post-content">
                                <?= nl2br(htmlspecialchars($post['content'])) ?>
                            </div>
                            
                            <!-- Section des commentaires -->
                            <div class="comments-section">
                                <h4><i class="fas fa-comments"></i> Commentaires (<?= count($post['comments']) ?>)</h4>
                                
                                <?php foreach ($post['comments'] as $comment): ?>
                                    <div class="comment">
                                        <img src="<?= displayProfileImage($comment) ?>" 
                                             alt="Photo de profil" class="comment-picture">
                                        <div class="comment-content-wrapper">
                                            <div class="comment-header">
                                                <span class="comment-author"><?= htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']) ?></span>
                                                <span class="comment-date"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                            </div>
                                            <div class="comment-content">
                                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Formulaire de commentaire -->
                                <div class="comment-form">
                                    <img src="<?= displayProfileImage($currentUser) ?>" 
                                         alt="Votre profil" class="current-user-picture">
                                    <div class="comment-form-wrapper">
                                        <form method="POST">
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <textarea name="comment_content" placeholder="Ajouter un commentaire..." required></textarea>
                                            <button type="submit" name="add_comment" class="btn btn-primary">
                                                <i class="fas fa-comment"></i> Commenter
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
</html>