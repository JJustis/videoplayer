<?php
// get_chat.php
$videoName = $_GET['v'];
$uploadDir = 'uploads/';
$chatFile = $uploadDir . pathinfo($videoName, PATHINFO_FILENAME) . '_chat.json';

if (file_exists($chatFile)) {
    $chatMessages = json_decode(file_get_contents($chatFile), true);
    echo json_encode($chatMessages);
} else {
    echo json_encode([]);
}