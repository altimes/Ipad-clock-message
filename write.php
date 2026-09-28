<?php
// Ensure file operations remain isolated and clean
$messageFile = 'message.txt';
$configFile = 'presets.ini';

// Handle POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Action A: Publish or Clear Live Message
    if (isset($_POST['message'])) {
        if (file_put_contents($messageFile, $_POST['message']) !== false) {
            echo 'Success';
        } else {
            http_response_code(500);
            echo 'Error writing message file';
        }
        exit;
    }
    
    // Action B: Save Configuration Presets to Server
    if (isset($_POST['action']) && $_POST['action'] === 'save_preset') {
        $slot = filter_input(INPUT_POST, 'slot', FILTER_VALIDATE_INT);
        $text = isset($_POST['text']) ? trim($_POST['text']) : '';
        
        if ($slot >= 1 && $slot <= 3) {
            // Read existing presets or start fresh
            $presets = file_exists($configFile) ? parse_ini_file($configFile) : [];
            $presets['preset_' . $slot] = $text;
            
            // Build out clean INI file content string
            $iniContent = "";
            foreach ($presets as $key => $value) {
                // Escape quotes for safe configuration parsing
                $iniContent .= $key . ' = "' . addslashes($value) . "\"\n";
            }
            
            if (file_put_contents($configFile, $iniContent) !== false) {
                echo 'Success';
            } else {
                http_response_code(500);
                echo 'Error writing preset configuration';
            }
        } else {
            http_response_code(400);
            echo 'Invalid slot assignment';
        }
        exit;
    }
}

// Handle GET Requests (Used by management panel loop to watch for external text changes)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_live'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo file_exists($messageFile) ? file_get_contents($messageFile) : '';
    exit;
}
?>
