#!/bin/bash
set -e

# Run Vite dev server in background for hot-reload (CSS/JS)
if [ "$APP_ENV" = "local" ]; then
    echo "[entrypoint] Starting Vite dev server..."
    npm run dev -- --host 0.0.0.0 &

    # Wait for Vite to create the hot file, then fix the URL for the browser
    sleep 2
    echo "http://localhost:${VITE_HOST_PORT:-5174}" > /var/www/html/public/hot
    echo "[entrypoint] Vite dev server ready on port ${VITE_HOST_PORT:-5174}"
fi

# Start Apache
exec apache2-foreground
