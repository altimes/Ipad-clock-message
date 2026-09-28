<?php
// Server-side initialization on page startup
$messageFile = 'message.txt';
$configFile = 'presets.ini';

// Read live message
$currentMessage = file_exists($messageFile) ? file_get_contents($messageFile) : '';

// Read server-side presets or fall back to default configurations
$serverPresets = file_exists($configFile) ? parse_ini_file($configFile) : [];
$preset1 = isset($serverPresets['preset_1']) ? $serverPresets['preset_1'] : "Meeting in Progress";
$preset2 = isset($serverPresets['preset_2']) ? $serverPresets['preset_2'] : "Out of Office";
$preset3 = isset($serverPresets['preset_3']) ? $serverPresets['preset_3'] : "Do Not Disturb";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Controller</title>
    <style type="text/css">
        html,body {margin:0;padding:0;background-color:#121214;color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,sans-serif;}
        body {padding:20px;display:flex;justify-content:center;}
        .admin-panel {background:#1a1a1e;width:100%;max-width:600px;padding:30px;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,0.3);}
        h2 {margin-top:0;font-size:24px;font-weight:600;letter-spacing:-0.5px;}
        .status-badge {display:inline-block;padding:6px 12px;border-radius:20px;font-size:13px;background:#27272a;color:#9ca3af;margin-bottom:20px;transition: background-color 0.3s, color 0.3s;}
        textarea {width:100%;height:120px;background:#27272a;border:1px solid #3f3f46;border-radius:8px;color:#fff;padding:12px;font-size:16px;box-sizing:border-box;resize:vertical;}
        .main-actions {display:grid;grid-template-columns:1fr auto;gap:12px;margin-top:15px;}
        button {font-size:15px;font-weight:600;padding:12px 20px;border:none;border-radius:8px;cursor:pointer;transition:background 0.2s;}
        .btn-primary {background:#2563eb;color:#fff;}
        .btn-danger {background:#dc2626;color:#fff;}
        .btn-secondary {background:#3f3f46;color:#fff;}
        .section-title {margin-top:35px;font-size:14px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;border-bottom:1px solid #27272a;padding-bottom:8px;}
        .shortcut-grid {display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:15px;}
        .shortcut-btn {background:#27272a;color:#fff;border:1px solid #3f3f46;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .configure-row {margin-top:15px;display:grid;grid-template-columns:1fr auto;gap:10px;}
        select {background:#27272a;border:1px solid #3f3f46;color:#fff;padding:8px;border-radius:6px;}
    </style>
</head>
<body>
    <div class="admin-panel">
        <h2>iPad Monitor Controller</h2>
        <div id="status" class="status-badge">Ready</div>
        
        <!-- Render current active text directly inside textarea on page load -->
        <textarea id="message-input" placeholder="Type a message to send to the iPad display..."><?php echo htmlspecialchars($currentMessage); ?></textarea>
        
        <div class="main-actions">
            <button class="btn-primary" onclick="submitText(document.getElementById('message-input').value)">Publish Message</button>
            <button class="btn-danger" onclick="clearMessage()">Clear Screen</button>
        </div>
        <div class="section-title">Quick Presets</div>
        <div class="shortcut-grid">
            <button id="slot-1" class="shortcut-btn" onclick="triggerShortcut(1)">Preset 1</button>
            <button id="slot-2" class="shortcut-btn" onclick="triggerShortcut(2)">Preset 2</button>
            <button id="slot-3" class="shortcut-btn" onclick="triggerShortcut(3)">Preset 3</button>
        </div>
        <div class="section-title">Configure Presets</div>
        <div class="configure-row">
            <select id="slot-selector">
                <option value="1">Assign to Preset 1</option>
                <option value="2">Assign to Preset 2</option>
                <option value="3">Assign to Preset 3</option>
            </select>
            <button class="btn-secondary" onclick="saveCurrentAsShortcut()">Save Text as Preset</button>
        </div>
    </div>
    <script>
        const statusBadge = document.getElementById('status');
        const msgInput = document.getElementById('message-input');
        
        // Local variable tracks the real-time server string to manage input state safely
        let currentLiveValue = msgInput.value;

        // Hydrate configuration presets array natively from PHP server values
        const presets = {
            1: <?php echo json_encode($preset1); ?>,
            2: <?php echo json_encode($preset2); ?>,
            3: <?php echo json_encode($preset3); ?>
        };

        function updateButtonLabels() {
            for (let i = 1; i <= 3; i++) { 
                document.getElementById(`slot-${i}`).textContent = presets[i]; 
            }
        }

        async function saveCurrentAsShortcut() {
            const text = msgInput.value.trim();
            if (!text) { showStatus("Type text first", "#dc2626"); return; }
            const slot = document.getElementById('slot-selector').value;
            
            showStatus("Saving preset to server...", "#9ca3af");
            
            try {
                const response = await fetch('write.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=save_preset&slot=${slot}&text=${encodeURIComponent(text)}`
                });
                
                if (response.ok) {
                    presets[slot] = text;
                    updateButtonLabels();
                    showStatus(`Saved to Preset ${slot}!`, "#2563eb");
                    setTimeout(() => { showStatus("Ready", "#27272a"); }, 2000);
                } else {
                    showStatus("Server preset save error.", "#dc2626");
                }
            } catch (err) {
                showStatus("Preset connection failed.", "#dc2626");
            }
        }

        function triggerShortcut(slotId) {
            msgInput.value = presets[slotId];
            submitText(presets[slotId]);
        }

        function clearMessage() { 
            msgInput.value = ""; 
            submitText(""); 
        }

        async function submitText(textStr) {
            showStatus("Updating server...", "#9ca3af");
            try {
                const response = await fetch('write.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'message=' + encodeURIComponent(textStr)
                });
                if (response.ok) { 
                    currentLiveValue = textStr; // Instantly lock our tracking flag to newly pushed string
                    showStatus(textStr === "" ? "Display Cleared (Clock Active)" : "Message Live!", "#10b981"); 
                }
                else { showStatus("Server write error.", "#dc2626"); }
            } catch (err) { showStatus("Connection failed.", "#dc2626"); }
        }

        // Live checking mechanism: Keeps text area aligned if external processes rewrite message.txt
        async function fetchExternalUpdates() {
            // Halt sync if operator is actively selecting or working inside input element
            if (document.activeElement === msgInput) return;

            try {
                const response = await fetch('write.php?check_live=1');
                if (response.ok) {
                    const text = await response.text();
                    
                    // If text on server changed externally, sync the control display view
                    if (text !== currentLiveValue) {
                        msgInput.value = text;
                        currentLiveValue = text;
                        showStatus("Sync updated externally", "#2563eb");
                        setTimeout(() => { showStatus("Ready", "#27272a"); }, 2000);
                    }
                }
            } catch (err) {
                console.error("Failed background status loop sync:", err);
            }
        }

        function showStatus(text, color) { 
            statusBadge.textContent = text; 
            statusBadge.style.backgroundColor = color; 
            statusBadge.style.color = "#fff"; 
        }

        // Initial running sequence
        updateButtonLabels();
        
        // Checks server state every 3 seconds for variations executed outside this controller panel instance
        setInterval(fetchExternalUpdates, 3000);
    </script>
</body>
</html>