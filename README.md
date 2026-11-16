# INARAIN — Gestion des stagiaires & absences

Application web de gestion de centre de formation : stagiaires, filières, groupes, absences, justifications, notes et rapports imprimables. Projet PHP 8 + MySQL sans framework, avec une interface d'administration en français et un thème clair/sombre.

> Projet portfolio réalisé autour du domaine métier **INARAIN** (suivi des stagiaires et des absences).

---

## Sommaire

- [Aperçu](#aperçu)
- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation locale (WAMP / XAMPP)](#installation-locale-wamp--xampp)
- [Lancer l'application](#lancer-lapplication)
- [Compte de démonstration](#compte-de-démonstration)
- [Règles métier](#règles-métier)
- [Sécurité](#sécurité)
- [Rapports et exports](#rapports-et-exports)
- [Déploiement et limites de GitHub Pages](#déploiement-et-limites-de-github-pages)
- [Pistes d'évolution](#pistes-dévolution)
- [Licence](#licence)

---

## Aperçu

INARAIN centralise le suivi administratif d'un centre de formation :

- un **tableau de bord** avec indicateurs (stagiaires actifs, absences du mois, taux de justification, moyenne générale) et un historique des absences sur 6 mois ;
- un **répertoire de stagiaires** filtrable, paginé, avec fiche détaillée, note et historique ;
- la **gestion des absences** par créneau horaire, avec justification et impact automatique sur la note ;
- l'organisation par **filières et groupes** ;
- des **rapports imprimables** par filière et des **exports CSV** ;
- un **espace compte** (profil, mot de passe, activité).

L'interface est entièrement en français, responsive, avec thème clair/sombre mémorisé dans le navigateur.

---

## Fonctionnalités

### Authentification

- Connexion par matricule + mot de passe.
- Création de compte administrateur (désactivable via `app.allow_registration`).
- Mots de passe hachés avec `password_hash()` / vérifiés avec `password_verify()`.
- Limitation des tentatives de connexion : 5 échecs par session sur 10 minutes.
- Régénération de l'identifiant de session après connexion et changement de mot de passe.
- Déconnexion en `POST` protégée par CSRF.

### Stagiaires

- Création, consultation, modification et suppression.
- Code stagiaire généré automatiquement (`STG-0001`, `STG-0002`, …).
- Recherche par nom, prénom ou code ; filtres par filière, groupe, statut et note.
- Pagination (12 lignes par page).
- Statuts : `active`, `graduated`, `archived`.
- Note sur 20 avec code couleur (acquis / à surveiller / insuffisant).
- Suppression en cascade de l'historique d'absences.

### Absences

- Enregistrement d'une absence par date et créneau (4 créneaux définis en configuration).
- Une seule absence par stagiaire, date et créneau (contrainte d'unicité en base).
- Absences futures et doublons refusés.
- Justification avec motif, puis restauration de la note.
- Suppression avec restauration de la note si l'absence était non justifiée.
- Filtres par recherche, mois et statut de justification.

### Filières et groupes

- Création et suppression de filières et de groupes.
- Suppression protégée : impossible si des stagiaires ou des groupes y sont rattachés.
- Affichage des effectifs et de la moyenne par filière.

### Rapports

- Fiche filière imprimable (mise en page dédiée, bouton « Imprimer / PDF » via le navigateur).
- Export CSV des stagiaires et des absences.
- CSV en UTF-8 avec BOM et séparateur `;` pour une ouverture correcte dans Excel.

### Interface

- Design system CSS maison (aucun framework front).
- Thème clair/sombre avec préférence système et mémorisation locale.
- Navigation latérale rétractable sur mobile.
- Modales natives `<dialog>`, confirmations de suppression, messages flash.
- Accessibilité : lien d'évitement, labels, `aria-*`, focus visible, sprite SVG partagé.

---

## Stack technique

| Élément | Choix |
| --- | --- |
| Langage | PHP 8.1+ (testé avec PHP 8.3) |
| Base de données | MySQL 8.0+ / MariaDB 10.4+ |
| Accès aux données | PDO, requêtes préparées |
| Architecture | MVC léger maison, front controller |
| Front-end | HTML sémantique, CSS personnalisé, JavaScript vanilla |
| Dépendances | Aucune (pas de Composer, pas de npm, pas de build) |

---

## Architecture

```text
inarain-app/
├── app/
│   ├── Controllers/        # Auth, Dashboard, Stagiaires, Absences, Filieres, Reports, Profile
│   ├── Core/               # Router, Database, Auth, Csrf, Flash, View
│   ├── Models/             # Admin, Stagiaire, Absence, Filiere
│   ├── Views/              # Layouts, pages, partials, erreurs
│   ├── bootstrap.php       # Amorçage, autoload, helpers, session
│   └── helpers.php         # e(), url(), asset(), csrf_field(), icon(), …
├── config/
│   ├── config.php          # Configuration par défaut
│   ├── config.example.php  # Modèle de configuration locale
│   └── config.local.php    # Identifiants MySQL locaux (ignoré par Git)
├── database/
│   ├── schema.sql          # Schéma des tables
│   ├── seed.sql            # Jeu de données de démonstration
│   └── install.php         # Installateur CLI (création / réinitialisation)
├── public/
│   ├── index.php           # Front controller
│   ├── .htaccess           # Réécriture Apache + en-têtes de sécurité
│   └── assets/             # CSS et JS
└── .gitignore
```

Le point d'entrée public est `public/index.php`. La configuration locale contenant les identifiants MySQL n'est jamais versionnée.

---

## Prérequis

- PHP **8.1 ou supérieur** avec les extensions `pdo_mysql`, `mbstring`, `openssl`.
- MySQL **8.0+** ou MariaDB **10.4+**.
- Un serveur local : WAMP, XAMPP, Laragon, ou le serveur intégré de PHP.
- Git (facultatif, pour cloner le dépôt).

---

## Installation locale (WAMP / XAMPP)

### 1. Récupérer le projet

```bash
git clone https://github.com/<votre-compte>/inarain-app.git
```

Ou placez le dossier dans le répertoire web de votre serveur, par exemple :

```text
C:\wamp64\www\inarain-app
```

### 2. Configurer la base de données

Copiez le modèle de configuration :

```powershell
Copy-Item config/config.example.php config/config.local.php
```

Éditez `config/config.local.php` avec vos identifiants MySQL :

```php
<?php
declare(strict_types=1);

return [
    'app' => [
        'debug' => true,
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'inarain',
        'user' => 'root',
        'pass' => 'votre-mot-de-passe',
    ],
];
```

> `config/config.local.php` est listé dans `.gitignore` : ne commitez jamais vos identifiants réels.

### 3. Créer la base et les données de démonstration

Depuis la racine du projet :

```powershell
C:\wamp64\bin\php\php8.3.28\php.exe database/install.php --fresh
```

Ou, si `php` est dans le `PATH` :

```bash
php database/install.php --fresh
```

L'installateur :

1. supprime la base `inarain` si elle existe (`--fresh`) ;
2. crée la base en `utf8mb4` ;
3. exécute `database/schema.sql` puis `database/seed.sql` ;
4. affiche le récapitulatif et le compte de démonstration.

Sans `--fresh`, l'installateur refuse d'écraser une base contenant déjà des tables.

---

## Lancer l'application

### Option A — Serveur intégré de PHP (recommandé pour le développement)

```powershell
C:\wamp64\bin\php\php8.3.28\php.exe -S 127.0.0.1:8093 -t public public/index.php
```

Puis ouvrez <http://127.0.0.1:8093>.

### Option B — Apache / WAMP

Le dossier `public/` doit être la racine du site :

- soit via un VirtualHost pointant sur `C:\wamp64\www\inarain-app\public` ;
- soit en accédant à `http://localhost/inarain-app/public/` (la détection de sous-dossier est gérée par le front controller).

L'extension Apache `mod_rewrite` doit être activée pour que `.htaccess` fonctionne.

---

## Compte de démonstration

| Champ | Valeur |
| --- | --- |
| Matricule | `ADM-001` |
| Mot de passe | `inarain2026` |

Le jeu de données contient 3 filières, 5 groupes, 20 stagiaires et 44 absences.

> Ces identifiants sont publics et destinés uniquement à la démonstration locale.

---

## Règles métier

- Chaque stagiaire commence avec une note de **20/20**.
- Une absence **non justifiée** retire **0,25 point** à la note (plancher : 0).
- Une **justification** ajoute **0,25 point** (plafond : 20).
- La suppression d'une absence non justifiée restaure **0,25 point**.
- La note ne peut jamais dépasser 20 ni descendre sous 0 (contrainte SQL + logique applicative).
- Les créneaux horaires et les pénalités sont configurables dans `config/config.php`.

Les opérations qui modifient la note et l'absence associée sont exécutées dans une **transaction SQL** afin d'éviter tout déséquilibre.

---

## Sécurité

Mesures mises en place :

- requêtes préparées PDO partout (pas de concaténation SQL) ;
- échappement systématique des sorties via `e()` ;
- jetons **CSRF** sur tous les formulaires `POST` ;
- validation serveur des entrées (longueurs, formats, valeurs autorisées) ;
- hachage des mots de passe avec l'algorithme par défaut de PHP ;
- cookies de session `HttpOnly`, `SameSite=Lax`, `Secure` automatiquement en HTTPS ;
- limitation des tentatives de connexion ;
- en-têtes `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` via `.htaccess` ;
- dossier `config/` et fichiers sensibles hors du répertoire public ;
- `config/config.local.php` exclu du dépôt Git.

---

## Rapports et exports

| Ressource | URL | Description |
| --- | --- | --- |
| Rapport filière | `/reports/filieres/{id}` | Fiche imprimable : effectifs, notes, absences |
| Export stagiaires | `/reports/stagiaires.csv` | CSV UTF-8 (BOM), séparateur `;` |
| Export absences | `/reports/absences.csv` | CSV UTF-8 (BOM), séparateur `;` |

Les rapports utilisent une feuille de style d'impression dédiée : le bouton « Imprimer / PDF » ouvre la boîte de dialogue du navigateur, qui permet d'enregistrer en PDF.

---

## Déploiement et limites de GitHub Pages

**GitHub Pages ne peut pas exécuter PHP.** Il ne sert que des fichiers statiques : le dépôt peut donc être hébergé sur GitHub, mais l'application doit tourner sur un hébergement PHP.

Options possibles :

1. **Hébergement mutualisé PHP/MySQL** (OVH, Infomaniak, Hostinger, PlanetHoster, etc.) : déployer le contenu du dossier `public/` comme racine web, ou tout le projet avec une racine pointant vers `public/`.
2. **Plateforme avec PHP + MySQL managé** (Railway, Render via Docker, Fly.io, etc.) : ajouter un `Dockerfile` PHP-Apache et une base MySQL.
3. **Démonstration locale** : WAMP + capture vidéo ou rapport de stage pour le portfolio.

Pour un déploiement public :

- passer `app.debug` à `false` ;
- restreindre ou désactiver `app.allow_registration` après création du compte ;
- utiliser un utilisateur MySQL dédié avec des droits limités ;
- servir le site en **HTTPS** ;
- définir un mot de passe robuste pour le compte administrateur.

---

## Pistes d'évolution

- Rôles et permissions plus fins (lecture seule, gestion par filière).
- Import/export Excel (`.xlsx`).
- Notifications e-mail pour les absences répétées.
- Historique d'audit des modifications.
- Tests automatisés (PHPUnit) et intégration continue.
- API JSON pour un futur front-end mobile.

---

## Licence

Projet distribué sous licence MIT. Voir le fichier [LICENSE](LICENSE).
