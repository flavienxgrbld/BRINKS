#!/bin/bash
# Script de déploiement BRINKS sur srv-web-01
# À exécuter en tant que root

set -e  # Arrêter en cas d'erreur

echo "========================================"
echo "  DÉPLOIEMENT BRINKS SUR SRV-WEB-01"
echo "========================================"
echo ""

# Configuration
REPO_URL="https://github.com/flavienxgrbld/BRINKS.git"
WEB_DIR="/var/www/html"
APP_DIR="$WEB_DIR/BRINKS"
DB_NAME="brinks_db"
DB_USER="root"
DB_PASS="@Dmin_password"

# Étape 1: Nettoyer l'ancien déploiement
echo "1. Nettoyage de l'ancien déploiement..."
rm -rf "$APP_DIR"
echo "   ✓ Ancien dossier supprimé"

# Étape 2: Cloner le dépôt
echo ""
echo "2. Clonage du dépôt GitHub..."
cd "$WEB_DIR"
git clone "$REPO_URL"
echo "   ✓ Dépôt cloné"

# Étape 3: Configuration des permissions
echo ""
echo "3. Configuration des permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
echo "   ✓ Permissions configurées"

# Étape 4: Nettoyer les fichiers de développement
echo ""
echo "4. Nettoyage des fichiers de développement..."
rm -f "$APP_DIR/regenerate.py"
rm -f "$APP_DIR/clean_backend.bat"
rm -f "$APP_DIR/.htaccess.disabled" 2>/dev/null
echo "   ✓ Fichiers de dev supprimés"

# Étape 5: Vérifier la base de données
echo ""
echo "5. Vérification de la base de données..."
if mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
    echo "   ✓ Base de données existe"
else
    echo "   ⚠ Base de données n'existe pas. Création..."
    mysql -u "$DB_USER" -p"$DB_PASS" < "$APP_DIR/COMMANDES_SQL.txt"
    echo "   ✓ Base de données créée"
fi

# Étape 6: Tester la connexion PHP
echo ""
echo "6. Test de la connexion PHP à MySQL..."
php -r "
\$dsn = 'mysql:host=localhost;dbname=$DB_NAME';
try {
    \$pdo = new PDO(\$dsn, '$DB_USER', '$DB_PASS');
    echo '   ✓ Connexion PHP-MySQL OK' . PHP_EOL;
} catch (PDOException \$e) {
    echo '   ✗ Erreur: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

# Étape 7: Tester l'API
echo ""
echo "7. Test de l'API backend..."
API_TEST=$(curl -s http://localhost/BRINKS/backend/api_login.php | head -c 10)
if [[ "$API_TEST" == *"{"* ]] || [[ "$API_TEST" == *"success"* ]]; then
    echo "   ✓ API répond correctement (JSON)"
else
    echo "   ⚠ API retourne: $API_TEST"
    echo "   Si vous voyez '<?php', PHP n'est pas exécuté!"
fi

# Étape 8: Afficher les informations de connexion
echo ""
echo "========================================"
echo "  DÉPLOIEMENT TERMINÉ"
echo "========================================"
echo ""
echo "Application accessible à:"
echo "  URL: http://srv-web-01/BRINKS/"
echo ""
echo "Identifiants par défaut:"
echo "  Username: admin"
echo "  Password: password"
echo ""
echo "⚠ IMPORTANT: Changez le mot de passe admin!"
echo ""

# Étape 9: Vérifications finales
echo "Vérifications finales:"
echo ""

# Vérifier Apache
if systemctl is-active --quiet apache2; then
    echo "✓ Apache est actif"
else
    echo "✗ Apache n'est PAS actif"
fi

# Vérifier MySQL
if systemctl is-active --quiet mysql || systemctl is-active --quiet mariadb; then
    echo "✓ MySQL/MariaDB est actif"
else
    echo "✗ MySQL/MariaDB n'est PAS actif"
fi

# Vérifier mod_php
if apache2ctl -M | grep -q php; then
    echo "✓ Module PHP chargé dans Apache"
else
    echo "✗ Module PHP NON chargé"
fi

echo ""
echo "Commandes utiles:"
echo "  Voir logs Apache: tail -f /var/log/apache2/error.log"
echo "  Redémarrer Apache: systemctl restart apache2"
echo "  Tester l'API: curl http://localhost/BRINKS/backend/api_login.php"
echo ""
