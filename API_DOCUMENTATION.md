# DOCUMENTATION API - BRINKS

## Authentification

### POST /backend/api_login.php
Connexion d'un utilisateur

**Paramètres (JSON):**
```json
{
    "username": "admin",
    "password": "password"
}
```

**Réponse succès:**
```json
{
    "success": true,
    "message": "Connexion réussie",
    "user": {
        "id": 1,
        "username": "admin",
        "role": "ADMIN"
    }
}
```

**Réponse erreur:**
```json
{
    "success": false,
    "message": "Identifiants incorrects"
}
```

### GET /backend/api_logout.php
Déconnexion de l'utilisateur (redirige vers index.php)

---

## Gestion des utilisateurs

### GET /backend/api_users.php?action=list
Liste de tous les utilisateurs (ADMIN uniquement)

**Réponse:**
```json
{
    "success": true,
    "users": [
        {
            "id": 1,
            "employee_id": "EMP001",
            "username": "admin",
            "firstname": "Administrateur",
            "lastname": "Système",
            "email": "admin@brinks.com",
            "role": "ADMIN",
            "active": 1,
            "created_at": "2025-01-01 10:00:00"
        }
    ]
}
```

### GET /backend/api_users.php?action=get&id={id}
Récupérer un utilisateur spécifique (ADMIN uniquement)

**Réponse:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "employee_id": "EMP001",
        "username": "admin",
        "firstname": "Administrateur",
        "lastname": "Système",
        "email": "admin@brinks.com",
        "role": "ADMIN",
        "active": 1
    }
}
```

### POST /backend/api_users.php?action=create
Créer un nouvel utilisateur (ADMIN uniquement)

**Paramètres (JSON):**
```json
{
    "employee_id": "EMP006",
    "username": "nouveau",
    "password": "motdepasse123",
    "firstname": "Nouveau",
    "lastname": "Utilisateur",
    "email": "nouveau@brinks.com",
    "role": "USER",
    "active": 1
}
```

**Réponse:**
```json
{
    "success": true,
    "message": "Utilisateur créé avec succès"
}
```

### POST /backend/api_users.php?action=update
Mettre à jour un utilisateur (ADMIN uniquement)

**Paramètres (JSON):**
```json
{
    "id": 2,
    "firstname": "Jean",
    "lastname": "Dupont",
    "email": "j.dupont@brinks.com",
    "role": "ADMIN"
}
```

### GET /backend/api_users.php?action=delete&id={id}
Désactiver un utilisateur (ADMIN uniquement)

---

## Gestion des convois

### GET /backend/api_convoys.php?action=list
Liste des convois
- **USER**: Voit uniquement ses convois
- **ADMIN**: Voit tous les convois

**Réponse:**
```json
{
    "success": true,
    "convoys": [
        {
            "id": 1,
            "convoy_number": "CNV-2025-001",
            "start_datetime": "2025-01-15 08:00:00",
            "end_datetime": "2025-01-15 16:30:00",
            "pallets_recovered": 45,
            "pallets_stored": 40,
            "pallets_sold": 5,
            "departure_address": "123 Rue de la République, 75001 Paris",
            "arrival_address": "456 Avenue des Champs-Élysées, 75008 Paris",
            "status": "TERMINE",
            "validator_firstname": "Administrateur",
            "validator_lastname": "Système",
            "role_in_convoy": "CHEF"
        }
    ]
}
```

### GET /backend/api_convoys.php?action=get&id={id}
Détails complets d'un convoi

**Réponse:**
```json
{
    "success": true,
    "convoy": {
        "id": 1,
        "convoy_number": "CNV-2025-001",
        "start_datetime": "2025-01-15 08:00:00",
        "end_datetime": "2025-01-15 16:30:00",
        "pallets_recovered": 45,
        "pallets_stored": 40,
        "pallets_sold": 5,
        "departure_address": "123 Rue de la République, 75001 Paris",
        "arrival_address": "456 Avenue des Champs-Élysées, 75008 Paris",
        "notes": "Convoi standard",
        "incidents": null,
        "status": "TERMINE",
        "validator_firstname": "Administrateur",
        "validator_lastname": "Système",
        "duration": "0 jours 8 heures 30 minutes",
        "personnel": [
            {
                "id": 1,
                "user_id": 2,
                "role_in_convoy": "CHEF",
                "firstname": "Jean",
                "lastname": "Dupont",
                "employee_id": "EMP002",
                "email": "j.dupont@brinks.com"
            }
        ],
        "steps": [
            {
                "id": 1,
                "step_order": 1,
                "address": "200 Rue Saint-Honoré, 75001 Paris",
                "arrival_time": "2025-01-15 10:30:00",
                "departure_time": "2025-01-15 11:00:00",
                "notes": "Collecte documents bancaires"
            }
        ]
    }
}
```

### GET /backend/api_convoys.php?action=stats
Statistiques globales pour le dashboard

**Réponse:**
```json
{
    "success": true,
    "stats": {
        "total_convoys": 7,
        "total_pallets_recovered": 389,
        "total_pallets_stored": 346,
        "total_pallets_sold": 43,
        "convoys_in_progress": 2,
        "convoys_completed": 5
    }
}
```

### POST /backend/api_convoys.php?action=create
Créer un nouveau convoi (ADMIN uniquement)

**Paramètres (JSON):**
```json
{
    "convoy_number": "CNV-2025-008",
    "start_datetime": "2025-06-01 08:00:00",
    "end_datetime": null,
    "pallets_recovered": 0,
    "pallets_stored": 0,
    "pallets_sold": 0,
    "departure_address": "123 Rue Test, 75001 Paris",
    "arrival_address": "456 Avenue Test, 75008 Paris",
    "notes": "Nouveau convoi",
    "incidents": "",
    "status": "EN_COURS"
}
```

### GET /backend/api_convoys.php?action=filter&start_date={date}&end_date={date}&status={status}&user_id={id}&min_pallets={number}
Filtrer les convois (ADMIN uniquement)

**Paramètres optionnels:**
- `start_date`: Date de début (YYYY-MM-DD)
- `end_date`: Date de fin (YYYY-MM-DD)
- `status`: EN_COURS | TERMINE | ANNULE
- `user_id`: ID de l'utilisateur
- `min_pallets`: Nombre minimum de palettes

**Exemple:**
```
GET /backend/api_convoys.php?action=filter&start_date=2025-01-01&end_date=2025-12-31&status=TERMINE
```

---

## Export de données

### GET /backend/api_export.php?format=csv
Exporter les convois en CSV (ADMIN uniquement)

**Paramètres optionnels (filtres):**
- `start_date`: Date de début
- `end_date`: Date de fin
- `status`: Statut du convoi

**Exemple:**
```
GET /backend/api_export.php?format=csv&start_date=2025-01-01&status=TERMINE
```

### GET /backend/api_export.php?format=pdf
Exporter les convois en PDF (ADMIN uniquement)

---

## Codes de réponse HTTP

- **200 OK**: Requête réussie
- **400 Bad Request**: Paramètres manquants ou invalides
- **401 Unauthorized**: Non authentifié
- **403 Forbidden**: Accès refusé (permissions insuffisantes)
- **404 Not Found**: Ressource non trouvée
- **500 Internal Server Error**: Erreur serveur

---

## Authentification et sessions

Toutes les API (sauf login) nécessitent une session PHP active.

La session est créée lors de la connexion et contient :
- `user_id`: ID de l'utilisateur
- `username`: Nom d'utilisateur
- `firstname`: Prénom
- `lastname`: Nom
- `email`: Email
- `role`: ADMIN ou USER
- `employee_id`: ID employé

---

## Permissions

### Endpoints ADMIN uniquement:
- Gestion des utilisateurs (api_users.php)
- Création de convois (api_convoys.php?action=create)
- Filtres avancés (api_convoys.php?action=filter)
- Export de données (api_export.php)

### Endpoints USER:
- Voir ses propres convois (api_convoys.php?action=list)
- Voir les détails d'un convoi auquel il participe (api_convoys.php?action=get)
- Statistiques globales (api_convoys.php?action=stats)

---

## Format des dates

Toutes les dates sont au format: `YYYY-MM-DD HH:MM:SS`

Exemple: `2025-01-15 08:00:00`

---

## Gestion des erreurs

Toutes les réponses d'erreur suivent ce format:

```json
{
    "success": false,
    "message": "Description de l'erreur"
}
```

Les erreurs sont également loguées côté serveur dans les logs PHP.
