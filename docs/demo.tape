Output docs/demo.gif

Set FontSize 16
Set Width 800
Set Height 500
Set Theme "Dracula"
Set TypingSpeed 50ms
Set PlaybackSpeed 1.0
Set Shell "bash"

# Set up environment
Env PATH "/home/developer/.config/herd-lite/bin:/home/developer/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

Sleep 500ms

# Navigate to demo app and show prompt
Type "cd /tmp/laravelbase-demo"
Enter
Sleep 300ms

# Clear to have a clean start
Type "clear"
Enter
Sleep 300ms

# The money shot
Type "php artisan make:module Product"
Sleep 600ms
Enter
Sleep 6s

# Show all generated files
Type "find app/Enums app/Filters app/Http app/Interfaces app/Models app/Policies app/Repositories app/Services tests -name '*.php' | sort"
Enter
Sleep 3s

# Preview the controller
Type "head -50 app/Http/Controllers/ProductController.php"
Enter
Sleep 4s

Sleep 2s
