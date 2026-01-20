.PHONY: help install start stop restart logs shell-api shell-frontend db-shell clean rebuild

# Default target
help:
	@echo "Sensea Snoezelen - Commandes disponibles:"
	@echo ""
	@echo "  make install    - Installation initiale (copie .env, build, start)"
	@echo "  make start      - Démarrer tous les conteneurs"
	@echo "  make stop       - Arrêter tous les conteneurs"
	@echo "  make restart    - Redémarrer tous les conteneurs"
	@echo "  make logs       - Voir les logs de tous les conteneurs"
	@echo "  make logs-api   - Voir les logs de l'API"
	@echo "  make logs-front - Voir les logs du frontend"
	@echo ""
	@echo "  make shell-api      - Shell dans le conteneur API"
	@echo "  make shell-frontend - Shell dans le conteneur frontend"
	@echo "  make db-shell       - Shell MySQL"
	@echo ""
	@echo "  make clean      - Supprimer les conteneurs et volumes"
	@echo "  make rebuild    - Rebuild complet des images"
	@echo ""
	@echo "URLs:"
	@echo "  Frontend:    http://localhost:5173"
	@echo "  API:         http://localhost:8080"
	@echo "  MailHog:     http://localhost:8025"
	@echo "  phpMyAdmin:  http://localhost:8081"

# Installation initiale
install:
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "✓ Fichier .env créé"; \
	fi
	@docker compose build
	@docker compose up -d
	@echo ""
	@echo "✓ Installation terminée!"
	@echo ""
	@echo "L'application est accessible sur:"
	@echo "  - Frontend:   http://localhost:5173"
	@echo "  - API:        http://localhost:8080"
	@echo "  - MailHog:    http://localhost:8025 (pour voir les emails)"
	@echo "  - phpMyAdmin: http://localhost:8081"
	@echo ""
	@echo "Compte admin par défaut: admin@sensea.cc"
	@echo "Demandez un magic link sur la page de login pour vous connecter."

# Démarrer
start:
	@docker compose up -d
	@echo "✓ Conteneurs démarrés"
	@echo "  Frontend: http://localhost:5173"
	@echo "  API:      http://localhost:8080"

# Arrêter
stop:
	@docker compose down
	@echo "✓ Conteneurs arrêtés"

# Redémarrer
restart: stop start

# Logs
logs:
	@docker compose logs -f

logs-api:
	@docker compose logs -f api

logs-front:
	@docker compose logs -f frontend

# Shells
shell-api:
	@docker compose exec api bash

shell-frontend:
	@docker compose exec frontend sh

db-shell:
	@docker compose exec db mysql -u snoezelen -psnoezelen_secret snoezelen_db

# Nettoyage
clean:
	@docker compose down -v --remove-orphans
	@echo "✓ Conteneurs et volumes supprimés"

# Rebuild complet
rebuild:
	@docker compose down
	@docker compose build --no-cache
	@docker compose up -d
	@echo "✓ Rebuild terminé"
