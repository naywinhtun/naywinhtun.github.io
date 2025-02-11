<?php
$file = "signaling.json";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents("php://input");
    file_put_contents($file, $input);
    echo json_encode(["status" => "ok"]);
} else {
    if (file_exists($file)) {
        echo file_get_contents($file);
        unlink($file); // Clear the file after reading
    } else {
        echo json_encode([]);
    }
}
?>