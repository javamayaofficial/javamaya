#!/bin/sh
# Regenerate compiled CSS (untuk developer; hasil sudah dibundel di rilis).
# Butuh binary tailwindcss standalone: https://github.com/tailwindlabs/tailwindcss/releases
./tailwindcss -c tailwind.config.js -i resources/css/jm.css -o public/assets/jm.css --minify
