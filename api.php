<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Parametri di connessione al tuo Database MySQL
$host = "89.46.68.223";
$db_name = "Sql792123_1";
$username = "Sql792123";
$password = "gx2abh2ux2"; // Inserisci la password del tuo MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connessione fallita: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. LEGGI TUTTE LE PRENOTAZIONI (GET)
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM prenotazioni ORDER BY data ASC, slot_index ASC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
    exit;
}

// 2. SALVA / AGGIORNA PRENOTAZIONE (POST)
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Dati non validi"]);
        exit;
    }

    $sql = "INSERT INTO prenotazioni (id, data, slot_index, slot_time, tipo, stato, docente, email, classe, materia, note, is_recurring) 
            VALUES (:id, :data, :slot_index, :slot_time, :tipo, :stato, :docente, :email, :classe, :materia, :note, :is_recurring)
            ON DUPLICATE KEY UPDATE stato = :stato";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $data['id'],
        ':data' => $data['date'],
        ':slot_index' => $data['slotIndex'],
        ':slot_time' => $data['slotTime'],
        ':tipo' => $data['type'],
        ':stato' => $data['status'],
        ':docente' => $data['docente'] ?? null,
        ':email' => $data['email'] ?? null,
        ':classe' => $data['classe'] ?? null,
        ':materia' => $data['materia'] ?? null,
        ':note' => $data['note'] ?? null,
        ':is_recurring' => !empty($data['isRecurring']) ? 1 : 0
    ]);

    echo json_encode(["status" => "success"]);
    exit;
}

// 3. ELIMINA PRENOTAZIONE (DELETE)
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM prenotazioni WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ID mancante"]);
    }
    exit;
}
?>