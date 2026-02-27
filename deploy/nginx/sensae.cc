server {
    listen 80;
    server_name sensae.cc www.sensae.cc;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name sensae.cc www.sensae.cc;

    ssl_certificate /etc/ssl/cloudflare/sensae.cc.pem;
    ssl_certificate_key /etc/ssl/cloudflare/sensae.cc.key;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 20M;
    }
}
