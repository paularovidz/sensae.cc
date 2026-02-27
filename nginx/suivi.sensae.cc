server {
    listen 80;
    server_name suivi.sensae.cc;

    # Dashboard (fichiers statiques)
    root /home/deploy/apps/snoezelen/frontend/dist;
    index index.html;

    # API - proxy vers container Docker
    location /api/ {
        proxy_pass http://127.0.0.1:8080/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;

        client_max_body_size 15M;
    }

    # SPA fallback
    location / {
        try_files $uri $uri/ /index.html;
    }
}
