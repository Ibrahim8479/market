# 🛍️ MarketPlace Niger - Plateforme de Vente en Ligne

Site de petites annonces avec système de paiement pour publication. Conçu spécialement pour le marché nigérien avec prix en **Franc CFA (FCFA)**.

## 📁 Structure du Projet

```
marketplace/
│
├── index.php              # Page principale
├── admin.php              # Dashboard administrateur
├── config.php             # Configuration de la base de données
├── database.sql           # Script de création de la base de données
│
├── css/
│   └── style.css          # Styles CSS (Orange, Noir, Gris)
│
├── js/
│   └── app.js             # JavaScript principal
│
├── api/                   # API Backend PHP
│   ├── auth.php           # Authentification
│   ├── products.php       # Gestion des produits
│   └── messages.php       # Messagerie
│
└── uploads/               # Dossier pour les images et vidéos (créé automatiquement)
```

## 🚀 Installation

### Prérequis
- **XAMPP** ou **WAMP** (Apache + MySQL + PHP 7.4+)
- Navigateur web moderne
- Connexion Internet (pour Font Awesome)

### Étapes d'installation

#### 1. Installer XAMPP
1. Télécharge XAMPP : https://www.apachefriends.org/
2. Installe XAMPP
3. Démarre **Apache** et **MySQL** depuis le panneau de contrôle XAMPP

#### 2. Créer le projet
1. Ouvre le dossier `htdocs` de XAMPP (généralement `C:\xampp\htdocs\`)
2. Crée un nouveau dossier nommé `marketplace`
3. Place tous les fichiers du projet dans ce dossier selon la structure ci-dessus

#### 3. Créer la base de données
1. Ouvre phpMyAdmin : http://localhost/phpmyadmin
2. Clique sur "Nouvelle base de données"
3. Nom: `marketplace`
4. Encodage: `utf8mb4_unicode_ci`
5. Clique sur "Créer"
6. Sélectionne la base de données `marketplace`
7. Clique sur l'onglet "SQL"
8. Copie-colle le contenu du fichier `database.sql`
9. Clique sur "Exécuter"

#### 4. Configuration
Ouvre le fichier `config.php` et vérifie les paramètres :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');  // Laisse vide pour XAMPP par défaut
```

#### 5. Créer les dossiers nécessaires
Assure-toi que ces dossiers existent dans ton projet :
```
marketplace/
├── css/
├── js/
├── api/
└── uploads/  (sera créé automatiquement au premier upload)
```

#### 6. Permissions (Linux/Mac uniquement)
Sur Linux/Mac, donne les permissions au dossier uploads :
```bash
chmod 755 uploads/
```

## 🎯 Utilisation

### Accéder au site
Ouvre ton navigateur et va sur : **http://localhost/marketplace/**

### Compte Administrateur par défaut
- **Email**: admin@marketplace.com
- **Mot de passe**: admin123

⚠️ **Important** : Change ce mot de passe après la première connexion !

### Fonctionnalités

#### Pour les utilisateurs :
1. **S'inscrire/Se connecter**
2. **Publier un produit** avec images et/ou vidéos (avec frais selon le prix)
3. **Rechercher des produits** (barre de recherche + filtres par catégorie)
4. **Contacter les vendeurs** (téléphone, email, message direct)
5. **Voir les détails des produits** avec galerie média complète

#### Pour l'administrateur :
1. **Accès au dashboard admin** (bouton "Admin" dans le header)
2. **Voir tous les produits** publiés
3. **Supprimer les contenus inappropriés** (nudité, illicite, etc.)
4. **Voir les statistiques** (revenus, produits actifs, utilisateurs)
5. **Gérer les revenus** en temps réel

### 💰 Tarification de publication (en FCFA)

Le système utilise une tarification à 2 niveaux pour être équitable :

#### **Tarif Réduit** (pour petits prix)
- **Prix < 5,000 FCFA** → **150 FCFA**
  - Exemples : 500, 1000, 2000, 3000, 4000 FCFA → 150 FCFA

#### **Tarif Normal** (prix moyens et élevés)
- **Prix ≥ 5,000 FCFA** → **300 FCFA par tranche de 5,000 FCFA**
  - 5,000 FCFA → 300 FCFA
  - 10,000 FCFA → 600 FCFA (2 tranches)
  - 15,000 FCFA → 900 FCFA (3 tranches)
  - 20,000 FCFA → 1,200 FCFA (4 tranches)
  - 50,000 FCFA → 3,000 FCFA (10 tranches)
  - 100,000 FCFA → 6,000 FCFA (20 tranches)

**Calcul automatique** : Les frais s'affichent en temps réel quand le vendeur saisit le prix !

## 🛠️ Technologies Utilisées

- **Frontend**: HTML5, CSS3, JavaScript ES6 (Vanilla - pas de framework)
- **Backend**: PHP 7.4+
- **Base de données**: MySQL 5.7+ / MariaDB
- **Design**: Responsive avec CSS Grid et Flexbox
- **Icônes**: Font Awesome 6.4.0 (CDN)
- **Sécurité**: PDO, Password Hashing, XSS Protection

## 🎨 Personnalisation

### Couleurs du site
Les couleurs principales sont définies dans `css/style.css` :
```css
:root {
    --orange: #ff6b35;      /* Orange principal */
    --orange-dark: #e55a2b; /* Orange foncé */
    --black: #1a1a1a;       /* Noir */
    --grey: #6b7280;        /* Gris */
    --grey-light: #f3f4f6;  /* Gris clair */
    --grey-dark: #374151;   /* Gris foncé */
}
```

### Logo
Remplace l'icône dans le header (`index.php` et `admin.php`) :
```html
<i class="fas fa-store"></i>  <!-- Change l'icône ici -->
```

Liste des icônes disponibles : https://fontawesome.com/icons

### Modifier les tarifs
Pour changer les frais de publication :

**Dans `js/app.js`** :
```javascript
if (priceNum < 5000) {
    return 150;  // Tarif réduit
}
const tranches = Math.ceil(priceNum / 5000);
return tranches * 300;  // Tarif normal
```

**Dans `api/products.php`** :
```php
if ($price < 5000) {
    return 150;  // Tarif réduit
}
$tranches = ceil($price / 5000);
return $tranches * 300;  // Tarif normal
```

## 🔒 Sécurité

Le site inclut plusieurs mesures de sécurité :

- ✅ **Protection SQL Injection** : PDO avec requêtes préparées
- ✅ **Hashage des mots de passe** : bcrypt (PASSWORD_DEFAULT)
- ✅ **Protection XSS** : htmlspecialchars() sur toutes les entrées
- ✅ **Validation des données** : Côté client et serveur
- ✅ **Gestion de sessions sécurisée** : session_start() avec configurations
- ✅ **Upload sécurisé** : Validation des types de fichiers
- ✅ **CSRF Protection** : Recommandé d'ajouter des tokens

### Recommandations supplémentaires
1. Change le mot de passe admin par défaut
2. Utilise HTTPS en production (certificat SSL)
3. Configure les limites d'upload dans `php.ini`
4. Sauvegarde régulièrement la base de données
5. Active les logs d'erreurs PHP

## 📱 Responsive Design

Le site est entièrement responsive et fonctionne sur :
- 💻 **Desktop** (1920px et plus)
- 💻 **Laptop** (1366px - 1920px)
- 📱 **Tablette** (768px - 1366px)
- 📱 **Mobile** (320px - 768px)

## 🎥 Fonctionnalités Média

### Images
- **Formats acceptés** : JPG, PNG, GIF, WebP
- **Upload multiple** : Plusieurs images par produit
- **Prévisualisation** : Avant publication
- **Galerie** : Affichage dans les détails du produit

### Vidéos ⭐ NOUVEAU
- **Formats acceptés** : MP4, AVI, MOV, WebM
- **Upload multiple** : Plusieurs vidéos par produit
- **Prévisualisation** : Avant publication
- **Lecture** : Contrôles vidéo intégrés
- **Badge** : Icône 🎬 sur les produits avec vidéo

### Configuration Upload
Modifie les limites dans `php.ini` (XAMPP) :
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20
```

Redémarre Apache après modification.

## 🐛 Dépannage

### Problème : "Cannot connect to database"
**Solution** : 
- Vérifie que MySQL est démarré dans XAMPP
- Vérifie les identifiants dans `config.php`
- Assure-toi que la base de données `marketplace` existe

### Problème : "Upload failed" ou images ne s'affichent pas
**Solution** : 
- Crée le dossier `uploads/` manuellement
- Vérifie les permissions (755 sur Linux/Mac)
- Augmente `upload_max_filesize` dans `php.ini`
- Vérifie que le chemin dans `config.php` est correct

### Problème : Page blanche
**Solution** : Active l'affichage des erreurs PHP. Ajoute au début de `config.php` :
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Problème : Les vidéos ne se lisent pas
**Solution** : 
- Vérifie le format vidéo (MP4 recommandé)
- Assure-toi que le navigateur supporte HTML5 video
- Vérifie la taille du fichier (< limite PHP)

### Problème : "Session already started"
**Solution** : 
- Vérifie qu'il n'y a pas d'espace avant `<?php` dans les fichiers
- Pas de `session_start()` en double

### Problème : Frais de publication incorrects
**Solution** : 
- Vide le cache du navigateur (Ctrl+F5)
- Vérifie que les fonctions de calcul sont identiques dans `app.js` et `products.php`

## 📧 Support et Maintenance

### Sauvegarde de la base de données
Dans phpMyAdmin :
1. Sélectionne la base `marketplace`
2. Clique sur "Exporter"
3. Choisis "Rapide" et "SQL"
4. Télécharge le fichier `.sql`

### Restauration
1. Crée une nouvelle base de données
2. Importe le fichier `.sql` sauvegardé

### Logs
Les erreurs PHP sont dans : `C:\xampp\apache\logs\error.log`

## 🌍 Adaptation pour le Niger

Ce site est spécialement conçu pour le marché nigérien :
- 💰 Prix en **FCFA** (Franc CFA)
- 📱 Numéros de téléphone format local
- 🎯 Tarification adaptée au marché local
- 🌐 Interface en français
- 📹 Support vidéo pour mieux montrer les produits

## 📄 Licence

Ce projet est open source. Tu peux l'utiliser, le modifier et le distribuer librement pour des projets personnels ou commerciaux.

## 🎓 Crédits

- **Design** : Couleurs personnalisées (Orange, Noir, Gris)
- **Icônes** : Font Awesome 6.4.0
- **Développement** : Site complet en PHP/MySQL/JavaScript

---

## 📞 Besoin d'aide ?

Pour toute question, problème technique ou demande de fonctionnalité supplémentaire, n'hésite pas à demander !

**Version** : 2.0
**Dernière mise à jour** : Décembre 2024
**Pays** : Niger 🇳🇪
**Devise** : Franc CFA (FCFA)

---

**Bon développement ! 🚀**

*Site développé pour faciliter le commerce en ligne au Niger avec un système équitable et accessible à tous.*