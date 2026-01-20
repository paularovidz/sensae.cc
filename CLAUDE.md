# Suivi Séances Snoezelen - Documentation Projet

## Concept

Application de dashboard pour le suivi de séances Snoezelen (thérapie sensorielle utilisée en milieu paramédical). Les données sont sensibles et nécessitent une sécurité maximale.

## Architecture

```
/
├── api/                    # Backend PHP
│   ├── config/            # Configuration (DB, mail, sécurité)
│   ├── src/
│   │   ├── Controllers/   # Contrôleurs API REST
│   │   ├── Models/        # Modèles de données
│   │   ├── Middleware/    # Authentification, CORS, Rate limiting
│   │   ├── Services/      # Logique métier (Mail, Auth)
│   │   └── Utils/         # Helpers, validation
│   ├── migrations/        # Scripts SQL
│   └── public/            # Point d'entrée (index.php)
│
└── frontend/              # Frontend VueJS 3
    ├── src/
    │   ├── components/    # Composants réutilisables
    │   ├── views/         # Pages
    │   ├── stores/        # Pinia stores
    │   ├── router/        # Vue Router
    │   ├── services/      # Appels API
    │   └── composables/   # Logique réutilisable
    └── public/
```

## Modèle de données

### Users (Comptes professionnels)
- `id` (UUID)
- `email` (unique, requis)
- `login` (unique, requis)
- `first_name` (requis)
- `last_name` (requis)
- `phone` (optionnel)
- `role` (enum: 'member', 'admin')
- `is_active` (boolean)
- `created_at`, `updated_at`

### Persons (Personnes suivies)
- `id` (UUID)
- `first_name`, `last_name`
- `birth_date`
- `notes` (texte chiffré - notes générales)
- `created_at`, `updated_at`

### UserPersons (Liaison N-N)
- `user_id`, `person_id`
- `created_at`

### SensoryProposals (Propositions sensorielles)
- `id` (UUID)
- `title` (ex: "Stimulation corps entier avec un foulard")
- `type` (enum: 'tactile', 'visual', 'olfactory', 'gustatory', 'auditory', 'proprioceptive')
- `description` (optionnel)
- `created_by` (FK user_id)
- `is_global` (boolean - visible par tous si true)
- `created_at`, `updated_at`

### Sessions (Séances Snoezelen)
- `id` (UUID)
- `person_id` (FK)
- `created_by` (FK user_id)
- `session_date` (datetime)
- `duration_minutes` (int)
- `sessions_per_month` (int - nombre de séances/mois)

#### Début de séance
- `behavior_start` (enum: 'calm', 'agitated', 'defensive', 'anxious', 'passive')
- `proposal_origin` (enum: 'person', 'relative')
- `attitude_start` (enum: 'accepts', 'indifferent', 'refuses')

#### Pendant la séance
- `position` (enum: 'standing', 'lying', 'sitting', 'moving')
- `communication` (JSON array: ['body', 'verbal', 'vocal'])

#### Fin de séance
- `session_end` (enum: 'accepts', 'refuses', 'interrupts')
- `behavior_end` (enum: 'calm', 'agitated', 'tired', 'defensive', 'anxious', 'passive')
- `wants_to_return` (boolean nullable - true=oui, false=non, null=non renseigné)

#### Notes privées (chiffrées)
- `professional_notes` (texte chiffré - impressions du professionnel)
- `person_expression` (texte chiffré - impressions et expression de la personne)

- `created_at`, `updated_at`

### SessionProposals (Propositions utilisées dans une séance)
- `id` (UUID)
- `session_id` (FK)
- `sensory_proposal_id` (FK)
- `appreciation` (enum: 'negative', 'neutral', 'positive')
- `order` (int - ordre d'affichage)
- `created_at`

### MagicLinks
- `id` (UUID)
- `user_id` (FK)
- `token` (hash sécurisé)
- `expires_at`
- `used_at`
- `ip_address`
- `created_at`

### AuditLogs
- `id` (UUID)
- `user_id`
- `action`
- `entity_type`, `entity_id`
- `old_values`, `new_values` (JSON)
- `ip_address`
- `user_agent`
- `created_at`

## Enums et valeurs

### Types de propositions sensorielles
| Valeur | Label FR |
|--------|----------|
| tactile | Tactile |
| visual | Visuelle |
| olfactory | Olfactive |
| gustatory | Gustative |
| auditory | Auditive |
| proprioceptive | Proprioceptive |

### Comportement (début/fin)
| Valeur | Label FR |
|--------|----------|
| calm | Calme |
| agitated | Agité |
| defensive | Défensif |
| anxious | Inquiet |
| passive | Passif (apathique) |
| tired | Fatigué (fin uniquement) |

### Origine de la proposition
| Valeur | Label FR |
|--------|----------|
| person | La personne elle-même |
| relative | Un proche |

### Attitude début
| Valeur | Label FR |
|--------|----------|
| accepts | Accepte la séance |
| indifferent | Indifférente |
| refuses | Refuse |

### Position pendant séance
| Valeur | Label FR |
|--------|----------|
| standing | Debout |
| lying | Allongée |
| sitting | Assise |
| moving | Se déplace |

### Communication
| Valeur | Label FR |
|--------|----------|
| body | Corporelle |
| verbal | Verbale |
| vocal | Vocale |

### Fin de séance
| Valeur | Label FR |
|--------|----------|
| accepts | Accepte |
| refuses | Refuse |
| interrupts | Interrompt la séance |

### Appréciation proposition
| Valeur | Label FR |
|--------|----------|
| negative | Apprécié négativement |
| neutral | Neutralité |
| positive | Apprécié positivement |

## Sécurité (CRITIQUE)

### Authentification
- Magic link uniquement (pas de mot de passe)
- Token JWT avec refresh token
- Expiration courte (15min access, 7j refresh)
- Rate limiting sur demande de magic link

### Protection API
- Toutes les routes protégées sauf `/auth/request-magic-link` et `/auth/verify`
- CORS strict (domaine frontend uniquement)
- Headers de sécurité (CSP, X-Frame-Options, etc.)
- Validation stricte des entrées
- Prepared statements (PDO)
- Chiffrement AES-256 des données sensibles (notes, observations)

### Sessions
- Tokens stockés en httpOnly cookies
- Rotation des tokens
- Invalidation sur changement IP significatif

## Rôles et Permissions

### Member
- Voir les personnes qui lui sont assignées
- CRUD séances pour ses personnes
- CRUD propositions sensorielles (les siennes + globales en lecture)
- Voir son profil

### Admin
- Tout ce que peut faire Member
- CRUD tous les utilisateurs
- Assigner personnes aux utilisateurs
- CRUD toutes les personnes
- CRUD toutes les propositions sensorielles
- Marquer propositions comme globales
- Voir audit logs
- Statistiques globales

## API Endpoints

### Auth
- `POST /auth/request-magic-link` - Demande magic link
- `GET /auth/verify/{token}` - Vérifie et connecte
- `POST /auth/refresh` - Rafraîchit le token
- `POST /auth/logout` - Déconnexion

### Users (Admin only sauf GET /me)
- `GET /users` - Liste
- `GET /users/{id}` - Détail
- `GET /users/me` - Profil connecté
- `POST /users` - Création
- `PUT /users/{id}` - Modification
- `DELETE /users/{id}` - Désactivation

### Persons
- `GET /persons` - Liste (filtrée selon rôle)
- `GET /persons/{id}` - Détail
- `POST /persons` - Création (admin)
- `PUT /persons/{id}` - Modification
- `DELETE /persons/{id}` - Suppression (admin)

### Sensory Proposals
- `GET /sensory-proposals` - Liste (globales + personnelles)
- `GET /sensory-proposals/{id}` - Détail
- `POST /sensory-proposals` - Création
- `PUT /sensory-proposals/{id}` - Modification
- `DELETE /sensory-proposals/{id}` - Suppression
- `GET /sensory-proposals/search?q=...&type=...` - Recherche

### Sessions
- `GET /sessions` - Liste (filtrée)
- `GET /sessions/{id}` - Détail
- `GET /persons/{id}/sessions` - Sessions d'une personne
- `POST /sessions` - Création
- `PUT /sessions/{id}` - Modification
- `DELETE /sessions/{id}` - Suppression

### Stats (Admin)
- `GET /stats/dashboard` - Statistiques globales

## Stack Technique

### Backend
- PHP 8.2+
- PDO MySQL/MariaDB
- Composer pour dépendances
- PHPMailer pour emails
- firebase/php-jwt pour JWT

### Frontend
- Vue 3 + Composition API
- Vite
- Pinia (store)
- Vue Router
- Tailwind CSS
- Axios

## Variables d'environnement

### API (.env)
```
DB_HOST=localhost
DB_NAME=snoezelen_db
DB_USER=
DB_PASS=
DB_CHARSET=utf8mb4

JWT_SECRET=
JWT_REFRESH_SECRET=
ENCRYPTION_KEY=

MAIL_HOST=
MAIL_PORT=587
MAIL_USER=
MAIL_PASS=
MAIL_FROM=noreply@sensea.cc

APP_URL=https://suivi.sensea.cc
FRONTEND_URL=https://suivi.sensea.cc

ENV=production
DEBUG=false
```

### Frontend (.env)
```
VITE_API_URL=https://suivi.sensea.cc/api
```

## Commandes utiles

```bash
# Backend
cd api && composer install
php migrations/migrate.php

# Frontend
cd frontend && npm install
npm run dev      # Développement
npm run build    # Production
```

## Notes importantes

1. **Pas d'inscription publique** - Seuls les admins créent les comptes
2. **Données chiffrées** - Notes privées chiffrées en base (professional_notes, person_expression)
3. **Audit complet** - Toutes les actions sont loguées
4. **RGPD** - Prévoir export et suppression données
5. **Snoezelen** - Thérapie sensorielle, environnements contrôlés (lumière, son, odeurs, textures)
6. **Propositions sensorielles** - Peuvent être créées à la volée lors d'une séance, recherche autocomplete
