<?php
// Dashboard URL:
// https://vlasato23.sps-prosek.cz/weby/API/public/interface.php

include_once "../config/config.php";
    
function sendNotification($userId, $message){
    global $db;

    // Get subId that belongs to a unique device
    $sql = "SELECT subId FROM pushNotifs WHERE userId = :userId AND isSubscribed = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([":userId" => $userId]);
    $subIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($subIds)) {
        return "User is not subscribed or not registered";
    }
    $data = [
        "app_id" => "48497eb5-e6d4-40e0-95df-f8cea3dd4de1",
        "include_player_ids" => $subIds,
        "contents" => ["en" => $message]
    ];

    // Initialize CURL
    $curl = curl_init("https://api.onesignal.com/notifications");
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json; charset=utf-8",
        "Authorization: Basic os_v2_app_jbex5npg2raobfo77dhkhxkn4fyyxcbaxb7esbvywqz2gwuwq6ghxbcmjqy63cas4ky7e7p7f3ipknu5a6umcehpdd27tdrvec74lmy"
    ]);

    

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute CURL
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}

sendNotification("1", "This message's solely purpose is to annoy you");

?>