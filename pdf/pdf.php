<?php
require('fpdf/fpdf.php');
include "../sql/db.php";

if (!isset($_GET['PPR'])) {
    die("PPR manquant.");
}
$PPR = $_GET['PPR'];

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE PPR = :ppr");
$stmt->bindParam(":ppr", $PPR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé.");
}

class PDF extends FPDF {
    // Color scheme
    private $primaryBlue = [13, 71, 161];    // Dark blue
    private $lightBlue = [227, 242, 253];    // Light background
    private $textColor = [51, 51, 51];       // Dark gray text
    
    function Header() {
        // Simple header with title and line
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor($this->primaryBlue[0], $this->primaryBlue[1], $this->primaryBlue[2]);
        $this->Cell(0, 10, 'Profil Utilisateur', 0, 1, 'C');
        
        // Thin blue line
        $this->SetDrawColor($this->primaryBlue[0], $this->primaryBlue[1], $this->primaryBlue[2]);
        $this->SetLineWidth(0.3);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(15);
    }

    function AddUserImage($imgData) {
        if (empty($imgData)) {
            return; // Skip if no image
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        try {
            file_put_contents($tempFile, $imgData);
            
            $size = getimagesize($tempFile);
            if ($size !== false) {
                $width = 35; // Compact image size
                $height = $width * ($size[1] / $size[0]);
                
                // Center image
                $this->Image($tempFile, (210 - $width)/2, $this->GetY(), $width, $height);
                $this->Ln($height + 15); // Space after image
            }
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    function Footer() {
        // Simple centered page number
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function InfoRow($label, $value) {
        // Clean two-column layout
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor($this->primaryBlue[0], $this->primaryBlue[1], $this->primaryBlue[2]);
        $this->Cell(50, 8, iconv('UTF-8', 'windows-1252', $label), 0, 0, 'R');
        
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor($this->textColor[0], $this->textColor[1], $this->textColor[2]);
        $this->Cell(0, 8, iconv('UTF-8', 'windows-1252', $value), 0, 1, 'L');
        $this->Ln(2); // Small space between rows
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Add user image
$pdf->AddUserImage($user['img']);

// User information
$labels = [
    "PPR" => "PPR",
    "nom" => "Nom",
    "prenom" => "Prénom", 
    "Cin" => "CIN",
    "email" => "Email",
    "d_naiss" => "Date de naissance",
    "d_recrutement" => "Date de recrutement",
    "sit_familliale" => "Situataion familiale",
    "genre" => "Genre",
    "role" => "Rôle",
    "fonction" => "Fonction",
    "grade" => "Grade"
];

foreach ($labels as $key => $label) {
    if (!isset($user[$key])) continue;
    $pdf->InfoRow($label, $user[$key]);
}

// Add signature space
$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 5, 'Signature:', 0, 1, 'R');
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->Line(150, $pdf->GetY(), 190, $pdf->GetY());

// Output PDF
$pdf->Output('D', 'profil_' . $PPR . '.pdf');