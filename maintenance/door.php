<?php
// Interactive keypad page that now validates codes server-side.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Door Lock</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="door-scene">
    <div class="container">
        <div class="panel door-console">
            <h1>Exit Door Controls</h1>
            <p>You found the maintenance wing, but this panel won't surrender that easily. The reinforced door dominates
                the hallway, humming with dormant power.</p>
            <p class="muted">Riddle: "The janitor's log whispers, 'scan the smudged blueprint hidden in the evidence
                crate; the digits glow when light bends twice.' Whatever clue the maintenance crew left inside those
                artifacts should expose the code."</p>
            <div class="door-layout">
                <div class="door-area">
                    <div class="exit-door" id="exit-door" role="button" aria-label="Locked door" tabindex="0">
                        <div class="door-window"></div>
                        <button class="door-handle" id="door-handle" aria-label="Try handle"></button>
                    </div>
                    <p id="door-status" class="muted door-status">The door is locked. Maybe the keypad has the answer.
                    </p>
                </div>
                <div class="keypad-area">
                    <div class="keypad-preview" id="keypad-preview" role="button" aria-label="Open keypad" tabindex="0">
                        <div class="keypad-dots"></div>
                        <div class="keypad-dots"></div>
                        <div class="keypad-dots"></div>
                    </div>
                    <p class="muted keypad-hint">Tap the keypad to zoom in and enter the 4-digit code.</p>
                    <div id="door-alert" class="door-alert muted" role="status" aria-live="polite"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="keypad-modal" id="keypad-modal" role="dialog" aria-modal="true" aria-label="Enter keypad code">
        <div class="keypad">
            <div class="keypad-display" id="keypad-display">____</div>
            <div class="key-grid">
                <button class="key" data-key="1">1</button>
                <button class="key" data-key="2">2</button>
                <button class="key" data-key="3">3</button>
                <button class="key" data-key="4">4</button>
                <button class="key" data-key="5">5</button>
                <button class="key" data-key="6">6</button>
                <button class="key" data-key="7">7</button>
                <button class="key" data-key="8">8</button>
                <button class="key" data-key="9">9</button>
                <button class="key action" data-action="clear">Clear</button>
                <button class="key" data-key="0">0</button>
                <button class="key action" data-action="submit">Enter</button>
            </div>
            <button class="keypad-close" id="keypad-close" aria-label="Close keypad">&times;</button>
            <div id="keypad-message" class="muted"></div>
        </div>
    </div>
    <div class="flag-overlay" id="flag-overlay" aria-hidden="true">
        <div class="flag-content">
            <h2>Door Unlocked</h2>
            <p id="flag-text">Flag will display once the keypad unlocks.</p>
            <button id="copy-flag" class="btn" disabled>Copy Flag</button>
            <button id="close-flag" class="btn secondary">Close</button>
            <p id="copy-feedback" class="muted" aria-live="polite"></p>
        </div>
    </div>
    <script>
        (function () {
            const doorPanel = document.getElementById('exit-door');
            const doorStatus = document.getElementById('door-status');
            const handle = document.getElementById('door-handle');
            const preview = document.getElementById('keypad-preview');
            const modal = document.getElementById('keypad-modal');
            const closeBtn = document.getElementById('keypad-close');
            const display = document.getElementById('keypad-display');
            const message = document.getElementById('keypad-message');
            const doorAlert = document.getElementById('door-alert');
            const flagOverlay = document.getElementById('flag-overlay');
            const flagText = document.getElementById('flag-text');
            const copyButton = document.getElementById('copy-flag');
            const copyFeedback = document.getElementById('copy-feedback');
            const closeButton = document.getElementById('close-flag');
            let inputCode = '';
            let unlocked = false;
            let currentFlag = '';
            let submitting = false;

            function updateDisplay() {
                const padded = inputCode.padEnd(4, '_');
                display.textContent = padded;
            }

            function resetInput() {
                inputCode = '';
                submitting = false;
                updateDisplay();
                message.textContent = '';
            }

            function openModal() {
                modal.classList.add('open');
                resetInput();
            }

            function closeModal() {
                modal.classList.remove('open');
            }

            function handleDoorClick() {
                if (unlocked) {
                    doorStatus.innerHTML = 'The door slides open. Final flag secured.';
                    doorAlert.textContent = 'Door already unlocked.';
                } else {
                    doorStatus.textContent = 'Locked tight. You need the 4-digit code.';
                    doorAlert.textContent = 'Door remains locked.';
                }
            }

            function submitCode() {
                if (submitting) {
                    return;
                }
                if (inputCode.length !== 4) {
                    message.textContent = 'Need 4 digits.';
                    doorAlert.textContent = 'Enter all 4 digits before submitting.';
                    return;
                }

                submitting = true;
                message.textContent = 'Checking code...';
                doorAlert.textContent = 'Sending code to maintenance server.';

                fetch('validate_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ code: inputCode })
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                }).then(function (result) {
                    if (result.success) {
                        unlocked = true;
                        currentFlag = result.flag || '';
                        doorPanel.classList.add('door-open');
                        doorStatus.innerHTML = 'The door slides open. Final flag secured.';
                        message.textContent = 'Correct code! Door unlocked.';
                        doorAlert.textContent = 'Correct code entered. Door unlocked.';
                        showFlagOverlay(currentFlag);
                        closeModal();
                    } else {
                        message.textContent = result.message || 'Incorrect code. Try again.';
                        doorAlert.textContent = 'Wrong code. The lock resets.';
                        resetInput();
                    }
                }).catch(function () {
                    message.textContent = 'Error communicating with maintenance servers. Try again.';
                    doorAlert.textContent = 'Temporary issue. Try again shortly.';
                }).finally(function () {
                    submitting = false;
                });
            }

            function showFlagOverlay(flagValue) {
                flagOverlay.classList.add('visible');
                flagOverlay.setAttribute('aria-hidden', 'false');
                if (flagValue) {
                    flagText.innerHTML = 'Flag: <span class="code">' + flagValue + '</span>';
                    copyButton.disabled = false;
                } else {
                    flagText.textContent = 'Door unlocked, but the flag could not be retrieved.';
                    copyButton.disabled = true;
                }
            }

            function hideFlagOverlay() {
                flagOverlay.classList.remove('visible');
                flagOverlay.setAttribute('aria-hidden', 'true');
                copyFeedback.textContent = '';
            }

            copyButton.addEventListener('click', function () {
                if (!currentFlag) {
                    copyFeedback.textContent = 'Flag unavailable to copy.';
                    return;
                }
                if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(currentFlag).then(function () {
                        copyFeedback.textContent = 'Flag copied to clipboard.';
                    }).catch(function () {
                        copyFeedback.textContent = 'Unable to copy automatically. Copy it manually: ' + currentFlag;
                    });
                } else {
                    copyFeedback.textContent = 'Clipboard unavailable. Copy manually: ' + currentFlag;
                }
            });

            closeButton.addEventListener('click', hideFlagOverlay);
            flagOverlay.addEventListener('click', function (event) {
                if (event.target === flagOverlay) {
                    hideFlagOverlay();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && flagOverlay.classList.contains('visible')) {
                    hideFlagOverlay();
                }
            });

            function bindPseudoButton(element, handler) {
                if (!element) return;
                element.addEventListener('click', handler);
                element.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        handler();
                    }
                });
            }

            handle.addEventListener('click', handleDoorClick);
            bindPseudoButton(doorPanel, handleDoorClick);
            bindPseudoButton(preview, openModal);
            closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('open')) {
                    closeModal();
                }
            });

            document.querySelectorAll('.key').forEach(function (button) {
                button.addEventListener('click', function () {
                    const key = button.getAttribute('data-key');
                    const action = button.getAttribute('data-action');
                    if (action === 'clear') {
                        resetInput();
                        return;
                    }
                    if (action === 'submit') {
                        submitCode();
                        return;
                    }
                    if (key && inputCode.length < 4) {
                        inputCode += key;
                        updateDisplay();
                    }
                });
            });

            updateDisplay();
        })();
    </script>
</body>

</html>
