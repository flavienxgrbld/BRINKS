// Middleware pour vérifier si l'utilisateur est authentifié
function isAuthenticated(req, res, next) {
    if (req.session && req.session.userId) {
        return next();
    }
    res.status(401).json({ error: 'Non authentifié. Veuillez vous connecter.' });
}

// Middleware pour vérifier si l'utilisateur est administrateur
function isAdmin(req, res, next) {
    if (req.session && req.session.userId && req.session.role === 'ADMIN') {
        return next();
    }
    res.status(403).json({ error: 'Accès refusé. Droits administrateur requis.' });
}

// Middleware pour vérifier si l'utilisateur est actif
function isActive(req, res, next) {
    if (req.session && req.session.isActive) {
        return next();
    }
    res.status(403).json({ error: 'Compte désactivé. Contactez un administrateur.' });
}

module.exports = { isAuthenticated, isAdmin, isActive };
