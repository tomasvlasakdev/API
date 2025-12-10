<?php
include_once "../config/config.php";

function saveSubscription($subId, $isSubscribed, $userId = 1)
{
    global $db;
    // 
    $stmt = $db->prepare("SELECT subId FROM pushNotifs WHERE subId = :subId");
    $stmt->execute([":subId" => $subId]);
    $result = $stmt->fetch();
    if ($result) {
        return "Id already exists.";
    } else {
        $sql = "INSERT INTO pushNotifs (subId, isSubscribed, userId) VALUES (:subId, :isSubscribed, :userId)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':subId' => $subId,
            ':isSubscribed' => $isSubscribed,  
            ':userId' => 1
        ]);
        return "Saved: $subId & $isSubscribed";
    }
}

$input = json_decode(file_get_contents("php://input"), true);
$subId = $input['subId'] ?? null;
$isSubscribed = $input['isSubscribed'] ?? null;
$isSubscribed = $isSubscribed ? 1 : 0; // convert to integer
$userId = $input['userId'] ?? null; // not needed

if (!empty($subId)) {
    echo saveSubscription($subId, $isSubscribed, 1);
} else {
    echo "Invalid data";
}
?>