<?php
include("sql/db.php");
$db = Database::getInstance()->getConnection();

if (isset($_GET['PPR'])) {
    $PPR = $_GET['PPR'];

    $stmt = $db->prepare("SELECT img, mime_type FROM utilisateurs WHERE PPR = :ppr");
    $stmt->bindParam(':ppr', $PPR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        header("Content-Type: " . $row['mime_type']);
        echo $row['img'];
    } else {
        http_response_code(404);
        echo "Image not found.";
    }
} else {
    http_response_code(400);
    echo "Missing ID.";
}
?>
