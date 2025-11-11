# DONNÉES DE TEST POUR BRINKS

## Commandes SQL pour ajouter des données de test

Après avoir créé la structure de base de données, vous pouvez ajouter ces données de test :

```sql
-- ========================================
-- DONNÉES DE TEST - BRINKS
-- ========================================

USE brinks_db;

-- Ajouter des utilisateurs de test
INSERT INTO users (employee_id, username, password, firstname, lastname, email, role, active) VALUES
('EMP002', 'jdupont', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean', 'Dupont', 'j.dupont@brinks.com', 'USER', 1),
('EMP003', 'mmartin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Martin', 'm.martin@brinks.com', 'USER', 1),
('EMP004', 'pbernard', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pierre', 'Bernard', 'p.bernard@brinks.com', 'USER', 1),
('EMP005', 'sdurand', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie', 'Durand', 's.durand@brinks.com', 'ADMIN', 1);

-- Ajouter des convois de test
INSERT INTO convoys (convoy_number, start_datetime, end_datetime, pallets_recovered, pallets_stored, pallets_sold, departure_address, arrival_address, notes, incidents, status, validated_by) VALUES
('CNV-2025-001', '2025-01-15 08:00:00', '2025-01-15 16:30:00', 45, 40, 5, '123 Rue de la République, 75001 Paris', '456 Avenue des Champs-Élysées, 75008 Paris', 'Convoi standard sans incidents majeurs', NULL, 'TERMINE', 1),
('CNV-2025-002', '2025-02-20 07:30:00', '2025-02-20 18:00:00', 62, 55, 7, '789 Boulevard Saint-Germain, 75006 Paris', '321 Rue de Rivoli, 75004 Paris', 'Transport de haute valeur, escorte renforcée', 'Retard de 30 minutes dû au trafic', 'TERMINE', 1),
('CNV-2025-003', '2025-03-10 09:00:00', NULL, 38, 30, 8, '555 Avenue Montaigne, 75008 Paris', '888 Rue du Faubourg Saint-Honoré, 75008 Paris', 'Convoi en cours', NULL, 'EN_COURS', NULL),
('CNV-2025-004', '2025-03-25 06:00:00', '2025-03-25 14:45:00', 75, 68, 7, '111 Quai de Bercy, 75012 Paris', '222 Avenue de la Grande Armée, 75017 Paris', 'Grande opération, 3 véhicules', NULL, 'TERMINE', 5),
('CNV-2025-005', '2025-04-05 08:30:00', '2025-04-05 17:15:00', 52, 48, 4, '333 Rue Lafayette, 75009 Paris', '444 Boulevard Haussmann, 75009 Paris', NULL, NULL, 'TERMINE', 1),
('CNV-2025-006', '2025-04-18 10:00:00', NULL, 29, 25, 4, '666 Avenue Victor Hugo, 75016 Paris', '777 Rue de la Paix, 75002 Paris', 'En cours de livraison', NULL, 'EN_COURS', NULL),
('CNV-2025-007', '2025-05-02 07:00:00', '2025-05-02 19:30:00', 88, 80, 8, '999 Boulevard Raspail, 75006 Paris', '1111 Avenue Kléber, 75016 Paris', 'Opération complexe multi-sites', 'Incident mineur: crevaison réparée sur place', 'TERMINE', 5);

-- Assigner du personnel aux convois
INSERT INTO convoy_personnel (convoy_id, user_id, role_in_convoy) VALUES
-- CNV-2025-001
(1, 2, 'CHEF'),
(1, 3, 'CONVOYEUR'),
(1, 4, 'CONTROLEUR'),
-- CNV-2025-002
(2, 5, 'CHEF'),
(2, 2, 'CONVOYEUR'),
(2, 3, 'CONVOYEUR'),
-- CNV-2025-003
(3, 4, 'CHEF'),
(3, 2, 'CONVOYEUR'),
-- CNV-2025-004
(4, 5, 'CHEF'),
(4, 2, 'CONVOYEUR'),
(4, 3, 'CONVOYEUR'),
(4, 4, 'CONTROLEUR'),
-- CNV-2025-005
(5, 2, 'CHEF'),
(5, 3, 'CONVOYEUR'),
-- CNV-2025-006
(6, 3, 'CHEF'),
(6, 4, 'CONVOYEUR'),
-- CNV-2025-007
(7, 5, 'CHEF'),
(7, 2, 'CONVOYEUR'),
(7, 3, 'CONVOYEUR'),
(7, 4, 'CONTROLEUR');

-- Ajouter des étapes intermédiaires
INSERT INTO convoy_steps (convoy_id, step_order, address, arrival_time, departure_time, notes) VALUES
-- CNV-2025-001
(1, 1, '200 Rue Saint-Honoré, 75001 Paris', '2025-01-15 10:30:00', '2025-01-15 11:00:00', 'Collecte documents bancaires'),
(1, 2, '350 Rue de la Banque, 75002 Paris', '2025-01-15 12:15:00', '2025-01-15 12:45:00', 'Dépôt partiel'),
-- CNV-2025-002
(2, 1, '150 Boulevard Voltaire, 75011 Paris', '2025-02-20 09:45:00', '2025-02-20 10:30:00', 'Point de collecte principal'),
(2, 2, '500 Avenue Daumesnil, 75012 Paris', '2025-02-20 13:00:00', '2025-02-20 13:30:00', 'Pause déjeuner sécurisée'),
(2, 3, '225 Rue du Temple, 75003 Paris', '2025-02-20 15:15:00', '2025-02-20 15:45:00', 'Dernière collecte'),
-- CNV-2025-004
(4, 1, '180 Avenue de Wagram, 75017 Paris', '2025-03-25 08:30:00', '2025-03-25 09:00:00', NULL),
(4, 2, '420 Rue de Vaugirard, 75015 Paris', '2025-03-25 10:45:00', '2025-03-25 11:15:00', NULL),
-- CNV-2025-007
(7, 1, '650 Boulevard Saint-Michel, 75005 Paris', '2025-05-02 09:00:00', '2025-05-02 09:45:00', 'Site 1'),
(7, 2, '880 Rue de Rennes, 75006 Paris', '2025-05-02 11:30:00', '2025-05-02 12:15:00', 'Site 2'),
(7, 3, '1050 Avenue de la République, 75011 Paris', '2025-05-02 14:00:00', '2025-05-02 14:45:00', 'Site 3'),
(7, 4, '1200 Boulevard de la Villette, 75019 Paris', '2025-05-02 16:30:00', '2025-05-02 17:00:00', 'Site 4 - Dernier arrêt');
```

## Note importante

Le mot de passe pour tous les utilisateurs de test est : **password**

Liste des utilisateurs de test :
- **admin** / password (ADMIN) - EMP001
- **jdupont** / password (USER) - EMP002
- **mmartin** / password (USER) - EMP003
- **pbernard** / password (USER) - EMP004
- **sdurand** / password (ADMIN) - EMP005

## Statistiques avec les données de test

Avec ces données, vous aurez :
- **7 convois** au total
- **5 convois terminés**
- **2 convois en cours**
- **389 palettes récupérées** au total
- **346 palettes stockées** au total
- **43 palettes vendues** au total
- **5 utilisateurs** (2 admins, 3 users)
