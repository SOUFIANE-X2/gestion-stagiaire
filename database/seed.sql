-- INARAIN — données de démonstration
-- Mot de passe du compte admin : inarain2026
-- Matricule : ADM-001

SET NAMES utf8mb4;

INSERT INTO administrators (id, matricule, password_hash, first_name, last_name, email, role, last_login_at) VALUES
(1, 'ADM-001', '$2y$10$HiuV.bwnOUL06oqLEEQTlOklUT/nDN0O6GFVbOVXfrau0rjGNgRka', 'Soufiane', 'El Amrani', 'admin@inarain.ma', 'admin', NULL);

INSERT INTO filieres (id, code, label) VALUES
(1, 'DD', 'Développement Digital'),
(2, 'ID', 'Infrastructure Digitale'),
(3, 'GE', 'Gestion des Entreprises');

INSERT INTO groupes (id, code, label, filiere_id, academic_year) VALUES
(1, 'DD-101', 'Développement Digital — G1', 1, '2025-2026'),
(2, 'DD-102', 'Développement Digital — G2', 1, '2025-2026'),
(3, 'ID-101', 'Infrastructure Digitale — G1', 2, '2025-2026'),
(4, 'ID-102', 'Infrastructure Digitale — G2', 2, '2025-2026'),
(5, 'GE-101', 'Gestion des Entreprises — G1', 3, '2025-2026');

INSERT INTO stagiaires (id, code, first_name, last_name, gender, email, phone, group_id, note, status, created_at) VALUES
(1, 'STG-0001', 'Salma', 'El Amrani', 'F', 'salma.elamrani@inarain.ma', '+212 661 234 501', 1, 16.75, 'active', DATE_SUB(NOW(), INTERVAL 180 DAY)),
(2, 'STG-0002', 'Youssef', 'Benali', 'M', 'youssef.benali@inarain.ma', '+212 662 234 502', 1, 14.25, 'active', DATE_SUB(NOW(), INTERVAL 173 DAY)),
(3, 'STG-0003', 'Aya', 'Lamrani', 'F', 'aya.lamrani@inarain.ma', '+212 663 234 503', 1, 17.50, 'active', DATE_SUB(NOW(), INTERVAL 166 DAY)),
(4, 'STG-0004', 'Othmane', 'El Fassi', 'M', 'othmane.elfassi@inarain.ma', '+212 664 234 504', 1, 12.00, 'active', DATE_SUB(NOW(), INTERVAL 159 DAY)),
(5, 'STG-0005', 'Nisrine', 'Bennis', 'F', 'nisrine.bennis@inarain.ma', '+212 665 234 505', 1, 15.25, 'active', DATE_SUB(NOW(), INTERVAL 152 DAY)),
(6, 'STG-0006', 'Mehdi', 'Ait Taleb', 'M', 'mehdi.aittaleb@inarain.ma', '+212 666 234 506', 2, 13.75, 'active', DATE_SUB(NOW(), INTERVAL 145 DAY)),
(7, 'STG-0007', 'Khadija', 'Ouazzani', 'F', 'khadija.ouazzani@inarain.ma', '+212 667 234 507', 2, 18.00, 'active', DATE_SUB(NOW(), INTERVAL 138 DAY)),
(8, 'STG-0008', 'Hamza', 'Chraibi', 'M', 'hamza.chraibi@inarain.ma', '+212 668 234 508', 2, 9.50, 'active', DATE_SUB(NOW(), INTERVAL 131 DAY)),
(9, 'STG-0009', 'Imane', 'Sabri', 'F', 'imane.sabri@inarain.ma', '+212 669 234 509', 2, 16.25, 'active', DATE_SUB(NOW(), INTERVAL 124 DAY)),
(10, 'STG-0010', 'Anas', 'Berrada', 'M', 'anas.berrada@inarain.ma', '+212 670 234 510', 2, 11.25, 'active', DATE_SUB(NOW(), INTERVAL 117 DAY)),
(11, 'STG-0011', 'Sara', 'El Idrissi', 'F', 'sara.elidrissi@inarain.ma', '+212 671 234 511', 3, 15.75, 'active', DATE_SUB(NOW(), INTERVAL 110 DAY)),
(12, 'STG-0012', 'Yassine', 'Amrani', 'M', 'yassine.amrani@inarain.ma', '+212 672 234 512', 3, 12.75, 'active', DATE_SUB(NOW(), INTERVAL 103 DAY)),
(13, 'STG-0013', 'Meryem', 'Tazi', 'F', 'meryem.tazi@inarain.ma', '+212 673 234 513', 3, 17.00, 'active', DATE_SUB(NOW(), INTERVAL 96 DAY)),
(14, 'STG-0014', 'Omar', 'Belkacem', 'M', 'omar.belkacem@inarain.ma', '+212 674 234 514', 3, 10.50, 'active', DATE_SUB(NOW(), INTERVAL 89 DAY)),
(15, 'STG-0015', 'Hajar', 'Naciri', 'F', 'hajar.naciri@inarain.ma', '+212 675 234 515', 4, 14.00, 'active', DATE_SUB(NOW(), INTERVAL 82 DAY)),
(16, 'STG-0016', 'Amine', 'Chakir', 'M', 'amine.chakir@inarain.ma', '+212 676 234 516', 4, 13.25, 'active', DATE_SUB(NOW(), INTERVAL 75 DAY)),
(17, 'STG-0017', 'Leila', 'Bouzidi', 'F', 'leila.bouzidi@inarain.ma', '+212 677 234 517', 5, 16.50, 'active', DATE_SUB(NOW(), INTERVAL 68 DAY)),
(18, 'STG-0018', 'Reda', 'Mansouri', 'M', 'reda.mansouri@inarain.ma', '+212 678 234 518', 5, 8.75, 'active', DATE_SUB(NOW(), INTERVAL 61 DAY)),
(19, 'STG-0019', 'Zineb', 'Kettani', 'F', 'zineb.kettani@inarain.ma', '+212 679 234 519', 5, 15.00, 'graduated', DATE_SUB(NOW(), INTERVAL 240 DAY)),
(20, 'STG-0020', 'Ilyas', 'Haddad', 'M', 'ilyas.haddad@inarain.ma', '+212 680 234 520', 4, 11.75, 'archived', DATE_SUB(NOW(), INTERVAL 300 DAY));

INSERT INTO absences (stagiaire_id, date, slot, justified, reason, justification, created_at) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 0, 'Maladie', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2, 1, 'Rendez-vous médical', 'Certificat médical remis', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 0, 'Retard répété', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 3, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(5, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 2, 1, 'Raison familiale', 'Justificatif signé', DATE_SUB(NOW(), INTERVAL 14 DAY)),
(6, DATE_SUB(CURDATE(), INTERVAL 21 DAY), 1, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 21 DAY)),
(7, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 4, 0, 'Absence non signalée', NULL, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(8, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 2, 1, 'Maladie', 'Ordonnance fournie', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(9, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 1, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(10, DATE_SUB(CURDATE(), INTERVAL 33 DAY), 3, 1, 'Transport', 'Grève des transports', DATE_SUB(NOW(), INTERVAL 33 DAY)),
(11, DATE_SUB(CURDATE(), INTERVAL 44 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 44 DAY)),
(12, DATE_SUB(CURDATE(), INTERVAL 51 DAY), 1, 1, 'Rendez-vous administratif', 'Convocation fournie', DATE_SUB(NOW(), INTERVAL 51 DAY)),
(13, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 4, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(14, DATE_SUB(CURDATE(), INTERVAL 27 DAY), 3, 0, 'Absence non signalée', NULL, DATE_SUB(NOW(), INTERVAL 27 DAY)),
(15, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 2, 1, 'Maladie', 'Certificat médical', DATE_SUB(NOW(), INTERVAL 75 DAY)),
(16, DATE_SUB(CURDATE(), INTERVAL 18 DAY), 1, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 18 DAY)),
(17, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 3, 1, 'Raison personnelle', 'Justificatif déposé', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(18, DATE_SUB(CURDATE(), INTERVAL 36 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 36 DAY)),
(19, DATE_SUB(CURDATE(), INTERVAL 90 DAY), 1, 0, 'Retard répété', NULL, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(20, DATE_SUB(CURDATE(), INTERVAL 47 DAY), 4, 1, 'Maladie', 'Certificat médical', DATE_SUB(NOW(), INTERVAL 47 DAY)),
(1, DATE_SUB(CURDATE(), INTERVAL 41 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 41 DAY)),
(2, DATE_SUB(CURDATE(), INTERVAL 66 DAY), 3, 0, 'Absence non signalée', NULL, DATE_SUB(NOW(), INTERVAL 66 DAY)),
(3, DATE_SUB(CURDATE(), INTERVAL 95 DAY), 1, 1, 'Rendez-vous médical', 'Certificat médical', DATE_SUB(NOW(), INTERVAL 95 DAY)),
(4, DATE_SUB(CURDATE(), INTERVAL 110 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 110 DAY)),
(5, DATE_SUB(CURDATE(), INTERVAL 120 DAY), 4, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 120 DAY)),
(6, DATE_SUB(CURDATE(), INTERVAL 78 DAY), 3, 1, 'Raison familiale', 'Justificatif signé', DATE_SUB(NOW(), INTERVAL 78 DAY)),
(7, DATE_SUB(CURDATE(), INTERVAL 130 DAY), 1, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 130 DAY)),
(8, DATE_SUB(CURDATE(), INTERVAL 140 DAY), 2, 1, 'Maladie', 'Ordonnance', DATE_SUB(NOW(), INTERVAL 140 DAY)),
(9, DATE_SUB(CURDATE(), INTERVAL 58 DAY), 3, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 58 DAY)),
(10, DATE_SUB(CURDATE(), INTERVAL 150 DAY), 1, 0, 'Absence non signalée', NULL, DATE_SUB(NOW(), INTERVAL 150 DAY)),
(11, DATE_SUB(CURDATE(), INTERVAL 88 DAY), 4, 1, 'Transport', 'Justificatif', DATE_SUB(NOW(), INTERVAL 88 DAY)),
(12, DATE_SUB(CURDATE(), INTERVAL 105 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 105 DAY)),
(13, DATE_SUB(CURDATE(), INTERVAL 160 DAY), 1, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 160 DAY)),
(14, DATE_SUB(CURDATE(), INTERVAL 70 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 70 DAY)),
(15, DATE_SUB(CURDATE(), INTERVAL 135 DAY), 3, 1, 'Rendez-vous médical', 'Certificat', DATE_SUB(NOW(), INTERVAL 135 DAY)),
(16, DATE_SUB(CURDATE(), INTERVAL 25 DAY), 4, 0, 'Retard répété', NULL, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(17, DATE_SUB(CURDATE(), INTERVAL 115 DAY), 1, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 115 DAY)),
(18, DATE_SUB(CURDATE(), INTERVAL 145 DAY), 3, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 145 DAY)),
(19, DATE_SUB(CURDATE(), INTERVAL 80 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 80 DAY)),
(20, DATE_SUB(CURDATE(), INTERVAL 100 DAY), 4, 1, 'Maladie', 'Certificat médical', DATE_SUB(NOW(), INTERVAL 100 DAY)),
(1, DATE_SUB(CURDATE(), INTERVAL 22 DAY), 4, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 22 DAY)),
(3, DATE_SUB(CURDATE(), INTERVAL 55 DAY), 2, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 55 DAY)),
(5, DATE_SUB(CURDATE(), INTERVAL 92 DAY), 3, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 92 DAY)),
(7, DATE_SUB(CURDATE(), INTERVAL 125 DAY), 4, 0, NULL, NULL, DATE_SUB(NOW(), INTERVAL 125 DAY));
