Prérequis:
- Node.js 18+ (ou équivalent)
- Un serveur MySQL accessible (local ou distant)

Configuration MySQL

Les informations de connexion se lisent depuis les variables d'environnement. Exemple (.env) :

```
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=yourpassword
MYSQL_DATABASE=brinks_db
PORT=3000
```

Installation et démarrage:

```powershell
# depuis la racine du projet
npm install
npm start
```

Le serveur écoute par défaut sur http://localhost:3000

Notes:
- La base MySQL doit exister (`MYSQL_DATABASE`) ; le code créera les tables si elles n'existent pas.
- C'est un prototype léger — pour production, ajoutez authentification, validations, tests et un vrai design.

Améliorations possibles:
- Ajout d'authentification
- Edition des lignes de facture
- Export PDF/CSV

# BRINKS

