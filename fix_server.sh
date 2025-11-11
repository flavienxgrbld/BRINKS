#!/bin/bash
#
# Script de correction pour le serveur BRINKS
# À exécuter sur srv-web-01
#

echo "=== Correction du serveur BRINKS ==="
echo ""

# 1. Voir les erreurs Apache
echo "1. Dernières erreurs Apache:"
tail -10 /var/log/apache2/error.log
echo ""

# 2. Sauvegarder l'ancien .htaccess
echo "2. Sauvegarde de l'ancien .htaccess..."
cp /var/www/html/BRINKS/.htaccess /var/www/html/BRINKS/.htaccess.backup.$(date +%Y%m%d_%H%M%S)

# 3. Créer un nouveau .htaccess simplifié et compatible Apache 2.4
echo "3. Création du nouveau .htaccess..."
cat > /var/www/html/BRINKS/.htaccess << 'HTACCESS_EOF'
# Configuration Apache pour BRINKS - Compatible Apache 2.4

# Activer le moteur de réécriture
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Ne PAS rediriger les fichiers PHP existants
    RewriteCond %{REQUEST_URI} \.php$ [OR]
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    
    # Rediriger tout le reste vers index.php
    RewriteRule ^ index.php [L]
</IfModule>

# Protection des fichiers sensibles (Apache 2.4)
<FilesMatch "\.(env|ini|log|sh|sql)$">
    Require all denied
</FilesMatch>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Cache pour les ressources statiques
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Headers de sécurité
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
HTACCESS_EOF

echo "✓ .htaccess créé"

# 4. Vérifier la configuration Apache
echo ""
echo "4. Vérification de la configuration Apache..."
if ! grep -q "AllowOverride All" /etc/apache2/sites-enabled/000-default.conf; then
    echo "⚠ AllowOverride n'est pas configuré. Ajout..."
    
    # Créer une sauvegarde
    cp /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default.conf.backup
    
    # Ajouter la configuration avant </VirtualHost>
    sed -i '/<\/VirtualHost>/i \
    <Directory /var/www/html>\
        Options Indexes FollowSymLinks\
        AllowOverride All\
        Require all granted\
    </Directory>' /etc/apache2/sites-enabled/000-default.conf
    
    echo "✓ Configuration Apache mise à jour"
else
    echo "✓ AllowOverride déjà configuré"
fi

# 5. Vérifier les permissions
echo ""
echo "5. Vérification des permissions..."
chown -R www-data:www-data /var/www/html/BRINKS
chmod -R 755 /var/www/html/BRINKS
echo "✓ Permissions corrigées"

# 6. Redémarrer Apache
echo ""
echo "6. Redémarrage d'Apache..."
systemctl restart apache2
sleep 2

if systemctl is-active --quiet apache2; then
    echo "✓ Apache redémarré avec succès"
else
    echo "❌ Erreur lors du redémarrage d'Apache"
    systemctl status apache2
    exit 1
fi

# 7. Tests
echo ""
echo "7. Tests de fonctionnement..."
echo ""

# Test 1: Fichier PHP simple
echo "Test 1: Fichier PHP simple"
RESULT=$(curl -s http://localhost/BRINKS/test_connection.php | head -c 50)
if [[ $RESULT == *"PHP"* ]] || [[ $RESULT == *"Connexion"* ]]; then
    echo "✓ PHP fonctionne"
else
    echo "❌ Problème avec PHP: $RESULT"
fi

# Test 2: API Login
echo ""
echo "Test 2: API Login"
RESULT=$(curl -s -X POST http://localhost/BRINKS/backend/api_login.php \
    -H "Content-Type: application/json" \
    -d '{"username":"admin","password":"password"}')

if [[ $RESULT == *"success"* ]] || [[ $RESULT == *"message"* ]]; then
    echo "✓ API répond en JSON: $RESULT"
else
    echo "❌ API ne répond pas correctement"
    echo "Réponse: $RESULT"
fi

# Test 3: Page d'accueil
echo ""
echo "Test 3: Page d'accueil"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/BRINKS/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✓ Page d'accueil accessible (HTTP $HTTP_CODE)"
else
    echo "⚠ Page d'accueil retourne HTTP $HTTP_CODE"
fi

echo ""
echo "=== Correction terminée ==="
echo ""
echo "Testez maintenant dans le navigateur:"
echo "http://srv-web-01/BRINKS/"
echo ""
echo "Identifiants:"
echo "  Username: admin"
echo "  Password: password"
echo ""
