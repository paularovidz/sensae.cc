server {
    listen 80;
    server_name ops.sensae.cc;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name ops.sensae.cc;

    ssl_certificate /etc/ssl/cloudflare/sensae.cc.pem;
    ssl_certificate_key /etc/ssl/cloudflare/sensae.cc.key;

    root /home/deploy/apps/snoezelen/ops/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api/ {
        proxy_pass http://127.0.0.1:8090/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
    }
}
