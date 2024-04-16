<?php

// Telegram bot token
$botToken = "7064162762:AAGZ_O5fFkubtWia6S4u0QN5PcOzQ6xErJs";

// Define your channel usernames and their corresponding links
$channels = [
    '@FreeIGAccountUpdates' => 'https://t.me/FreeIGAccountUpdates',
    '@ff' => 'https://t.me/ff'
];

// Get update data from Telegram
$update = json_decode(file_get_contents("php://input"), TRUE);

// Get chat ID
$chatID = $update["message"]["chat"]["id"];

// Check if the user is a member of all required channels
$missingChannels = [];
foreach ($channels as $channelUsername => $channelLink) {
    $response = json_decode(file_get_contents("https://api.telegram.org/bot{$botToken}/getChatMember?chat_id={$channelUsername}&user_id={$chatID}"), true);
    if (!$response || $response["ok"] !== true || ($response["result"]["status"] !== "member" && $response["result"]["status"] !== "creator" && $response["result"]["status"] !== "administrator")) {
        $missingChannels[] = ["name" => $channelUsername, "link" => $channelLink];
    }
}

// Respond based on channel membership
if (empty($missingChannels)) {
    // User is a member of all required channels
    sendMessage($chatID, "Welcome! You are a member of all required channels.");
} else {
    // User is not a member of all required channels
    $message = "To use this bot, you need to join the following channels:\n\n";
    $keyboard = [
        "inline_keyboard" => []
    ];
    foreach ($missingChannels as $missingChannel) {
        $keyboard["inline_keyboard"][] = [
            [
                "text" => $missingChannel["name"],
                "url" => $missingChannel["link"]
            ]
        ];
    }
    sendMessageWithInlineKeyboard($chatID, $message, $keyboard);
}

// Function to send message
function sendMessage($chatID, $message) {
    $url = "https://api.telegram.org/bot" . $GLOBALS['botToken'] . "/sendMessage?chat_id=" . $chatID . "&text=" . urlencode($message);
    file_get_contents($url);
}

// Function to send message with inline keyboard
function sendMessageWithInlineKeyboard($chatID, $message, $keyboard) {
    $url = "https://api.telegram.org/bot" . $GLOBALS['botToken'] . "/sendMessage";
    $data = [
        'chat_id' => $chatID,
        'text' => $message,
        'reply_markup' => json_encode($keyboard)
    ];
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
}
