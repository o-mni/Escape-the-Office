# Escape the Office

A minimalist escape-room style web challenge. The landing page is just a clue board; the actual keypad interface hides in a maintenance subdirectory that must be discovered with directory enumeration tooling (e.g., Gobuster).

## Running locally

Serve this folder however you like. For a quick setup:

```
python -m http.server 8000
```

Visit `http://localhost:8000/` to read the riddle. It hints that the janitors tucked the keypad away inside the maintenance wing. Use a tool like Gobuster to brute-force directories until you find `/maintenance/`. Inside that directory lives `door.html`, which contains the interactive keypad UI.

```
gobuster dir -u http://localhost:8000 -w /path/to/wordlist
```

Once `/maintenance/door.html` is located, inspect the provided assets (such as `files/room_key.jpg`) to retrieve the keypad code and unlock the final flag.
