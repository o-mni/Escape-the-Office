# Escape the Office

A minimalist escape-room style web challenge. The landing page is just a clue board; the actual keypad interface hides in a maintenance subdirectory that must be discovered with directory enumeration tooling (e.g., Gobuster).

## Running locally

Serve this folder with PHP so the keypad can call the back-end validator. For a quick setup:

```
php -S 127.0.0.1:8000
```

Visit `http://localhost:8000/` to read the riddle. It hints that the janitors tucked the keypad away inside the maintenance wing. Use a tool like Gobuster to brute-force directories until you find `/maintenance/`. Inside that directory lives `door.php` (the legacy `/door.html` now simply redirects), which contains the interactive keypad UI and communicates with `validate_code.php` on the server.

```
gobuster dir -u http://localhost:8000 -w /path/to/wordlist
```

Once `/maintenance/door.php` is located, inspect the provided assets (such as `files/room_key.jpg`) to retrieve the keypad code and unlock the final flag.
