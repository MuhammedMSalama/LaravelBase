# Recording the LaravelBase Demo GIF

This guide produces the animated GIF that lives at `docs/demo.gif` and is
embedded at the top of the README.

---

## 1. Install VHS

[VHS](https://github.com/charmbracelet/vhs) renders terminal recordings from a
plain-text `.tape` script — no screen recorder, no jitter, pixel-perfect output.

**macOS (Homebrew):**
```bash
brew install charmbracelet/tap/vhs
```

**Linux (direct binary):**
```bash
# Replace X.Y.Z with the latest release tag from github.com/charmbracelet/vhs/releases
curl -LO https://github.com/charmbracelet/vhs/releases/download/vX.Y.Z/vhs_Linux_x86_64.tar.gz
tar -xzf vhs_Linux_x86_64.tar.gz
sudo mv vhs /usr/local/bin/
```

VHS requires **ttyd** and **ffmpeg** as backends:
```bash
# macOS
brew install ttyd ffmpeg

# Ubuntu / Debian
sudo apt install ttyd ffmpeg
```

---

## 2. Set up a recording environment

Create a fresh Laravel application and install the package:

```bash
composer create-project laravel/laravel demo-app
cd demo-app
composer require muhammedsalama/laravel-base
```

Keep this shell open — the tape script runs commands inside it.

---

## 3. VHS tape script

Save the following as `docs/demo.tape` in the repository root, then run it from
inside the `demo-app` directory:

```
# ─── Output ───────────────────────────────────────────────────────────────────
Output docs/demo.gif

# ─── Terminal settings ────────────────────────────────────────────────────────
Set FontSize 16
Set Width 800
Set Height 550
Set Theme "Dracula"
Set TypingSpeed 55ms
Set PlaybackSpeed 1.0

# ─── Opening pause ────────────────────────────────────────────────────────────
Sleep 800ms

# ─── Money-shot command ───────────────────────────────────────────────────────
Type "php artisan make:module Product"
Sleep 400ms
Enter

# Allow each output line to appear (15 files × ~0.3 s each ≈ 4.5 s)
Sleep 5s

# ─── File tree (shows all 15 generated files at once) ─────────────────────────
Type "find app/Enums app/Filters app/Http app/Interfaces app/Models app/Policies app/Repositories app/Services tests/Feature tests/Unit -name '*.php' | sort"
Enter
Sleep 3s

# ─── Preview the controller (Swagger annotations + ApiResponse in real code) ───
Type "head -52 app/Http/Controllers/ProductController.php"
Enter
Sleep 3500ms

# ─── Final hold on the last line ──────────────────────────────────────────────
Sleep 2500ms
```

---

## 4. Record the GIF

From inside `demo-app`, run:

```bash
vhs /path/to/LaravelBasePackage/docs/demo.tape
```

VHS writes the finished GIF to `docs/demo.gif`.

---

## 5. Verify and commit

```bash
# Check file size — aim for under 3 MB for fast README loading
ls -lh docs/demo.gif

# Optimise if needed (requires gifsicle)
gifsicle -O3 docs/demo.gif -o docs/demo.gif

# Commit
git add docs/demo.gif
git commit -m "docs: add make:module demo GIF"
```

---

## 6. Storyboard reference

| Time | What happens |
|---|---|
| 0:00 – 0:01 | Clean prompt. Cursor blinks. |
| 0:01 – 0:03 | `php artisan make:module Product` types itself out. |
| 0:03 – 0:04 | ENTER. Brief pause. |
| 0:04 – 0:09 | 15 `•  … created:` lines appear one by one. |
| 0:09 – 0:11 | `✔  Product module generated successfully.` in green. Hold. |
| 0:11 – 0:14 | `find … | sort` typed and executed — all 15 files visible in one view. |
| 0:14 – 0:18 | `head -52 … ProductController.php` — viewer sees Swagger annotations, `authorize()`, `ApiResponse::paginated()`. |
| 0:18 – 0:21 | Final freeze. GIF loops back to the clean prompt. |
