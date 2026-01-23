#!/bin/bash
# =============================================================================
# Setup VPS Hetzner avec Docker pré-installé
# Usage: ssh root@IP_SERVEUR 'bash -s' < setup.sh
# =============================================================================

set -e

echo "=== Setup VPS Hetzner pour Sensea ==="

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# =============================================================================
# 1. Vérification Docker
# =============================================================================
log_info "Vérification de Docker..."
if ! command -v docker &> /dev/null; then
    log_error "Docker n'est pas installé. Sélectionnez l'App 'Docker CE' lors de la création du VPS."
    exit 1
fi

if ! command -v docker compose &> /dev/null; then
    log_error "Docker Compose n'est pas installé."
    exit 1
fi

log_info "Docker $(docker --version | cut -d' ' -f3) détecté"

# =============================================================================
# 2. Création utilisateur deploy
# =============================================================================
log_info "Création de l'utilisateur deploy..."
if id "deploy" &>/dev/null; then
    log_warn "L'utilisateur deploy existe déjà"
else
    useradd -m -s /bin/bash -G docker deploy
    mkdir -p /home/deploy/.ssh
    cp ~/.ssh/authorized_keys /home/deploy/.ssh/
    chown -R deploy:deploy /home/deploy/.ssh
    chmod 700 /home/deploy/.ssh
    chmod 600 /home/deploy/.ssh/authorized_keys
    log_info "Utilisateur deploy créé"
fi

# =============================================================================
# 3. Installation Nginx + Certbot
# =============================================================================
log_info "Installation de Nginx et Certbot..."
apt update -qq
apt install -y -qq nginx certbot python3-certbot-nginx

# =============================================================================
# 4. Création des dossiers
# =============================================================================
log_info "Création des dossiers..."
mkdir -p /opt/app/data/{mysql,uploads}
mkdir -p /opt/app/infrastructure
chown -R deploy:deploy /opt/app

# =============================================================================
# 5. Configuration Nginx (placeholder)
# =============================================================================
log_info "Configuration Nginx..."
cat > /etc/nginx/sites-available/sensea << 'NGINX'
server {
    listen 80;
    server_name suivi.sensea.cc;

    # Frontend
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # API
    location /api/ {
        proxy_pass http://127.0.0.1:8080/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Uploads
        client_max_body_size 15M;
    }
}
NGINX

# Activer le site
ln -sf /etc/nginx/sites-available/sensea /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test et reload
nginx -t && systemctl reload nginx

# =============================================================================
# 6. Configuration Firewall (si pas déjà fait par Hetzner)
# =============================================================================
log_info "Configuration du firewall..."
if command -v ufw &> /dev/null; then
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw --force enable
fi

# =============================================================================
# 7. Instructions finales
# =============================================================================
echo ""
echo "==========================================="
echo -e "${GREEN}Setup terminé !${NC}"
echo "==========================================="
echo ""
echo "Prochaines étapes :"
echo ""
echo "1. Cloner le repo (en tant que deploy) :"
echo "   su - deploy"
echo "   git clone https://github.com/[REPO] /opt/app"
echo ""
echo "2. Configurer l'environnement :"
echo "   cd /opt/app"
echo "   cp .env.example .env"
echo "   nano .env"
echo ""
echo "3. Lancer l'application :"
echo "   docker compose -f infrastructure/docker-compose.prod.yml up -d"
echo ""
echo "4. Exécuter les migrations :"
echo "   docker exec sensea_api php /var/www/html/migrations/migrate.php"
echo ""
echo "5. Configurer SSL :"
echo "   sudo certbot --nginx -d suivi.sensea.cc"
echo ""
echo "==========================================="
