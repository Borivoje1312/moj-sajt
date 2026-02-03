<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "root";    
$password = "";        
$dbname = "podaci";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Greška pri povezivanju sa bazom: " . $conn->connect_error]));
}

// POST — dodavanje podataka
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $naziv = $conn->real_escape_string($input["naziv"]);
    $tip = $conn->real_escape_string($input["tip"]);
    $opis = $conn->real_escape_string($input["opis"]);
    $datum = $conn->real_escape_string($input["datum"]);
    $vreme = $conn->real_escape_string($input["vreme"]);

    // 1️⃣ Prvo sve postojeće postavi na "neaktivan"
    $conn->query("UPDATE podaci3 SET status='0'");

    // 2️⃣ Novi unos dodaj kao "aktivan"
    $sql = "INSERT INTO podaci3 (naziv, tip, opis, datum, vreme, status)
            VALUES ('$naziv', '$tip', '$opis', '$datum', '$vreme', '1')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Podaci uspešno sačuvani i označeni kao aktivni!"]);
    } else {
        echo json_encode(["error" => "Greška pri čuvanju podataka: " . $conn->error]);
    }
    exit;
}

// DELETE — briše sve podatke
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $conn->query("DELETE FROM podaci3");
    echo json_encode(["message" => "Svi podaci su obrisani."]);
    exit;
}

// GET — vraća sve podatke ili filtrirane podatke po tipu
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $tip = isset($_GET['tip']) ? $conn->real_escape_string($_GET['tip']) : '';

    // Ako je tip prosleđen, filtriraj po tipu
    if ($tip) {
        // Pripremljeni SQL upit za filtriranje
        $sql = "SELECT * FROM podaci3 WHERE tip = ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tip);
    } else {
        // Ako tip nije prosleđen, vrati sve podatke
        $sql = "SELECT * FROM podaci3 ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $podaci = [];
    while ($row = $result->fetch_assoc()) {
        $podaci[] = $row;
    }
    echo json_encode($podaci);
    exit;
}

$conn->close();
?>
