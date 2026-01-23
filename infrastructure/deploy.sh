#!/bin/bash
# =============================================================================
# Déploiement manuel sur le serveur
# Usage: ./deploy.sh [--migrate] [--seed]
# =============================================================================

set -e

COMPOSE_FILE="infrastructure/docker-compose.prod.yml"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }

# =============================================================================
# Parse arguments
# =============================================================================
RUN_MIGRATE=false
RUN_SEED=false

for arg in "$@"; do
  case $arg in
    --migrate)
      RUN_MIGRATE=true
      ;;
    --seed)
      RUN_SEED=true
      ;;
  esac
done

# =============================================================================
# Déploiement
# =============================================================================
cd /opt/app

log_info "Pull des dernières modifications..."
git fetch origin main
git reset --hard origin/main

log_info "Build des containers..."
docker compose -f $COMPOSE_FILE build

log_info "Redémarrage des services..."
docker compose -f $COMPOSE_FILE up -d --remove-orphans

# Attendre que les services soient prêts
log_info "Attente des services..."
sleep 10

# Migrations
if [ "$RUN_MIGRATE" = true ]; then
  log_info "Exécution des migrations..."
  docker exec sensea_api php /var/www/html/migrations/migrate.php
fi

# Seed
if [ "$RUN_SEED" = true ]; then
  log_warn "Exécution du seed (données de test)..."
  docker exec sensea_api php /var/www/html/database/seed.php
fi

# Nettoyage
log_info "Nettoyage des images inutilisées..."
docker image prune -f

# Vérification
log_info "Vérification des services..."

if docker exec sensea_api php -r "echo 'OK';" > /dev/null 2>&1; then
  log_info "✅ API: OK"
else
  log_warn "❌ API: Erreur"
  exit 1
fi

if curl -sf http://127.0.0.1:3000 > /dev/null 2>&1; then
  log_info "✅ Frontend: OK"
else
  log_warn "❌ Frontend: Erreur"
  exit 1
fi

if docker exec sensea_db mysqladmin ping -h localhost --silent > /dev/null 2>&1; then
  log_info "✅ Database: OK"
else
  log_warn "❌ Database: Erreur"
  exit 1
fi

echo ""
log_info "🎉 Déploiement terminé avec succès!"
docker compose -f $COMPOSE_FILE ps
