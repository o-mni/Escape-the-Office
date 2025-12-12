# Escape the Office

A minimalist escape-room style web challenge. The landing page is just a clue board; the actual keypad interface hides in a maintenance subdirectory that must be discovered with directory enumeration tooling (e.g., Gobuster).

## Running locally

Serve this folder with PHP so the keypad can call the back-end validator. For a quick setup:

```
php -S 127.0.0.1:8000
```

Visit `http://localhost:8000/` to read the riddle. It hints that the janitors tucked the keypad away inside the maintenance wing. Use a tool like Gobuster to brute-force directories until you find `/maintenance/`. Inside that directory lives `door.php`, which contains the interactive keypad UI and communicates with `validate_code.php` on the server.

```
gobuster dir -u http://localhost:8000 -w /path/to/wordlist
```

### Configure the secret code

Player-facing files no longer store the keypad code or final flag. Before deploying the challenge:

1. Copy `maintenance/config.sample.php` to `maintenance/config.php`.
2. Update the `door_code` (4 digits) and `flag` values in the new file.
3. Keep `maintenance/config.php` out of version control so solvers cannot view it.

Once `/maintenance/door.php` is located, inspect the provided assets (such as `files/room_key.jpg`) to retrieve the keypad clue and unlock the final flag. The keypad code must match the value in `maintenance/config.php`.
