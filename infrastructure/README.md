# Infrastructure Sensea

## Prérequis

- VPS Hetzner CX22 (ou supérieur) avec l'App "Docker CE"
- Nom de domaine pointant vers l'IP du serveur
- Backups Hetzner activés (+20% du prix)

## Setup initial (une seule fois)

### 1. Lancer le script de setup

```bash
# Depuis votre machine locale
ssh root@IP_SERVEUR 'bash -s' < infrastructure/setup.sh
```

### 2. Cloner le projet

```bash
ssh deploy@IP_SERVEUR
git clone https://github.com/[VOTRE_REPO] /opt/app
cd /opt/app
```

### 3. Configurer l'environnement

```bash
cp infrastructure/.env.example .env
nano .env  # Remplir les valeurs
```

**Générer les secrets :**
```bash
# JWT secrets (64 caractères hex)
openssl rand -hex 32

# Encryption key (32 caractères)
openssl rand -hex 16
```

### 4. Lancer l'application

```bash
docker compose -f infrastructure/docker-compose.prod.yml up -d
```

### 5. Exécuter les migrations

```bash
docker exec sensea_api php /var/www/html/migrations/migrate.php
```

### 6. Configurer SSL

```bash
sudo certbot --nginx -d suivi.sensea.cc
```

## Déploiement

### Automatique (GitHub Actions)

Chaque push sur `main` déclenche :
1. Tests unitaires (PHPUnit + Vitest)
2. Si OK → Déploiement automatique

**Secrets GitHub requis :**
| Secret | Description |
|--------|-------------|
| `SSH_PRIVATE_KEY` | Clé privée SSH pour se connecter au serveur |
| `SERVER_HOST` | IP ou hostname du serveur |
| `SERVER_USER` | Utilisateur SSH (deploy) |

### Manuel

```bash
ssh deploy@IP_SERVEUR
cd /opt/app
./infrastructure/deploy.sh --migrate
```

## Commandes utiles

```bash
# Logs
docker compose -f infrastructure/docker-compose.prod.yml logs -f

# Logs d'un service spécifique
docker compose -f infrastructure/docker-compose.prod.yml logs -f api

# Shell dans un container
docker exec -it sensea_api bash

# Redémarrer un service
docker compose -f infrastructure/docker-compose.prod.yml restart api

# Statut des services
docker compose -f infrastructure/docker-compose.prod.yml ps
```

## Structure

```
infrastructure/
├── docker-compose.prod.yml  # Stack de production
├── setup.sh                 # Setup initial du VPS
├── deploy.sh                # Déploiement manuel
├── .env.example             # Template des variables
└── README.md                # Cette documentation
```

## Backups

Les backups Hetzner créent un snapshot complet du VPS chaque jour (7 jours conservés).

**Données persistées :**
- `/opt/app/data/mysql/` → Base de données
- `/opt/app/data/uploads/` → Fichiers uploadés

## Restauration après panne

1. Créer un nouveau VPS depuis le snapshot Hetzner
2. L'application redémarre automatiquement (Docker restart: always)

Ou depuis zéro :
```bash
# 1. Nouveau VPS avec Docker CE
# 2. Setup
ssh root@NEW_IP 'bash -s' < infrastructure/setup.sh

# 3. Clone + config
ssh deploy@NEW_IP
git clone https://github.com/[REPO] /opt/app
cp /opt/app/infrastructure/.env.example /opt/app/.env
# Éditer .env avec les bonnes valeurs

# 4. Restaurer les données depuis backup externe (si applicable)

# 5. Lancer
cd /opt/app
docker compose -f infrastructure/docker-compose.prod.yml up -d
docker exec sensea_api php /var/www/html/migrations/migrate.php
sudo certbot --nginx -d suivi.sensea.cc
```

## Monitoring

Vérification santé des services :
```bash
# API
curl -s http://127.0.0.1:8080/health

# Frontend
curl -s http://127.0.0.1:3000

# Database
docker exec sensea_db mysqladmin ping -h localhost
```

## Coûts

| Élément | Prix/mois |
|---------|-----------|
| Hetzner CX22 | 4,35€ |
| Backups auto | 0,87€ |
| **Total** | **5,22€** |
