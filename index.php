<?php
// beansmartbin bridge: receives ESP32/SIM800L JSON → sends to Firebase

header("Content-Type: application/json");

// Read JSON or GET query
$json = file_get_contents('php://input');
if (!$json && isset($_GET['data'])) {
    $json = $_GET['data'];
}
if (!$json) {
    echo json_encode(["status" => "error", "message" => "no data received"]);
    exit;
}

$data = json_decode($json, true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "invalid json"]);
    exit;
}

// ---- Firebase details ----
$firebase_url =
  "https://final-year-project-c3753-default-rtdb.asia-southeast1.firebasedatabase.app/bins/bin_001.json";
$firebase_secret = "QxsYwbR2DNMKHprcp26IAAHqncykhBjCg66cSgX5";

// ---- Forward to Firebase ----
$ch = curl_init($firebase_url . "?auth=" . $firebase_secret);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ---- Log ----
file_put_contents("log.txt", date("Y-m-d H:i:s") . " | code:$code | $response\n", FILE_APPEND);

// ---- Reply to ESP32 ----
echo json_encode(["status" => "ok", "firebase_code" => $code]);
flush();
?>
