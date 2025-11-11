const express = require('express');
const bcrypt = require('bcryptjs');
const { pool } = require('../config/database');
const { isAuthenticated, isAdmin } = require('../middleware/auth');
const router = express.Router();

// Récupérer tous les utilisateurs (ADMIN seulement)
router.get('/', isAuthenticated, isAdmin, async (req, res) => {
    try {
        const [users] = await pool.query(
            'SELECT id, username, email, role, created_at, last_login, is_active FROM users ORDER BY created_at DESC'
        );
        res.json({ success: true, users });
    } catch (error) {
        console.error('Erreur lors de la récupération des utilisateurs:', error);
        res.status(500).json({ error: 'Erreur serveur' });
    }
});

// Récupérer un utilisateur par ID (ADMIN seulement)
router.get('/:id', isAuthenticated, isAdmin, async (req, res) => {
    try {
        const [users] = await pool.query(
            'SELECT id, username, email, role, created_at, last_login, is_active FROM users WHERE id = ?',
            [req.params.id]
        );

        if (users.length === 0) {
            return res.status(404).json({ error: 'Utilisateur non trouvé' });
        }

        res.json({ success: true, user: users[0] });
    } catch (error) {
        console.error('Erreur lors de la récupération de l\'utilisateur:', error);
        res.status(500).json({ error: 'Erreur serveur' });
    }
});

// Créer un nouvel utilisateur (ADMIN seulement)
router.post('/', isAuthenticated, isAdmin, async (req, res) => {
    try {
        const { username, email, password, role } = req.body;

        // Validation
        if (!username || !email || !password) {
            return res.status(400).json({ error: 'Tous les champs sont requis' });
        }

        // Vérifier si l'utilisateur existe déjà
        const [existing] = await pool.query(
            'SELECT id FROM users WHERE username = ? OR email = ?',
            [username, email]
        );

        if (existing.length > 0) {
            return res.status(400).json({ error: 'Nom d\'utilisateur ou email déjà utilisé' });
        }

        // Hasher le mot de passe
        const hashedPassword = await bcrypt.hash(password, 10);

        // Insérer l'utilisateur
        const [result] = await pool.query(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)',
            [username, email, hashedPassword, role || 'USER']
        );

        res.status(201).json({
            success: true,
            message: 'Utilisateur créé avec succès',
            userId: result.insertId
        });

    } catch (error) {
        console.error('Erreur lors de la création de l\'utilisateur:', error);
        res.status(500).json({ error: 'Erreur serveur' });
    }
});

// Mettre à jour un utilisateur (ADMIN seulement)
router.put('/:id', isAuthenticated, isAdmin, async (req, res) => {
    try {
        const { username, email, role, is_active } = req.body;
        const userId = req.params.id;

        // Vérifier si l'utilisateur existe
        const [users] = await pool.query('SELECT id FROM users WHERE id = ?', [userId]);

        if (users.length === 0) {
            return res.status(404).json({ error: 'Utilisateur non trouvé' });
        }

        // Construire la requête de mise à jour
        const updates = [];
        const values = [];

        if (username) {
            updates.push('username = ?');
            values.push(username);
        }
        if (email) {
            updates.push('email = ?');
            values.push(email);
        }
        if (role) {
            updates.push('role = ?');
            values.push(role);
        }
        if (is_active !== undefined) {
            updates.push('is_active = ?');
            values.push(is_active);
        }

        if (updates.length === 0) {
            return res.status(400).json({ error: 'Aucune modification fournie' });
        }

        values.push(userId);

        await pool.query(
            `UPDATE users SET ${updates.join(', ')} WHERE id = ?`,
            values
        );

        res.json({ success: true, message: 'Utilisateur mis à jour avec succès' });

    } catch (error) {
        console.error('Erreur lors de la mise à jour de l\'utilisateur:', error);
        res.status(500).json({ error: 'Erreur serveur' });
    }
});

// Supprimer un utilisateur (ADMIN seulement)
router.delete('/:id', isAuthenticated, isAdmin, async (req, res) => {
    try {
        const userId = req.params.id;

        // Ne pas permettre la suppression de son propre compte
        if (userId == req.session.userId) {
            return res.status(400).json({ error: 'Vous ne pouvez pas supprimer votre propre compte' });
        }

        const [result] = await pool.query('DELETE FROM users WHERE id = ?', [userId]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Utilisateur non trouvé' });
        }

        res.json({ success: true, message: 'Utilisateur supprimé avec succès' });

    } catch (error) {
        console.error('Erreur lors de la suppression de l\'utilisateur:', error);
        res.status(500).json({ error: 'Erreur serveur' });
    }
});

// Réinitialiser le mot de passe d'un utilisateur (ADMIN seulement)
router.post('/:id/reset-password', isAuthenticated, isAdmin, async (req, res) => {
    try {
        const { newPassword } = req.body;
        const userId = req.params.id;

        if (!newPassword) {
            return res.status(400).json({ error: 'Nouveau mot de passe requis' });
        }

        // Hasher le nouveau mot de passe
        const hashedPassword = await bcrypt.hash(newPassword, 10);

        await pool.query(
            'UPDATE users SET password = ? WHERE id = ?',
            [hashedPassword, userId]
        );

        res.json({ success: true, message: 'Mot de passe réinitialisé avec succès' });

    } catch (error) {
        console.error('Erreur lors de la réinitialisation du mot de passe:', error);
        res.status(500).json({ error: 'Erreur serveur' });
    }
});

module.exports = router;
