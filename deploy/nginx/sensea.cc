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

    root /home/deploy/apps/snoezelen/www/dist;
    index index.html;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    location /_astro/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location /images/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location / {
        try_files $uri $uri/ $uri.html /index.html;
    }
}
