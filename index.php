<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Achetez et Vendez</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="window.location.href='index.php'">
                    <i class="fas fa-store"></i>
                    <h1>MarketPlace</h1>
                </div>
                
                <nav class="nav-menu">
                    <div class="user-actions" id="userActions">
                        <!-- Contenu dynamique injecté par JavaScript -->
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-wrapper">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher un produit, une marque, une catégorie...">
                    <button class="search-clear" id="clearSearch" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="filter-box">
                    <i class="fas fa-filter"></i>
                    <select id="categoryFilter">
                        <option value="all">🏪 Toutes catégories</option>
                        <optgroup label="🎮 Électronique & High-Tech">
                            <option value="smartphones">📱 Smartphones & Tablettes</option>
                            <option value="ordinateurs">💻 Ordinateurs & Laptops</option>
                            <option value="tv-audio">📺 TV, Audio & Photo</option>
                            <option value="accessoires-tech">🎧 Accessoires High-Tech</option>
                            <option value="jeux-video">🎮 Jeux Vidéo & Consoles</option>
                        </optgroup>
                        <optgroup label="👔 Mode & Beauté">
                            <option value="vetements-hommes">👔 Vêtements Hommes</option>
                            <option value="vetements-femmes">👗 Vêtements Femmes</option>
                            <option value="vetements-enfants">👶 Vêtements Enfants</option>
                            <option value="chaussures">👟 Chaussures</option>
                            <option value="sacs-accessoires">👜 Sacs & Accessoires</option>
                            <option value="montres-bijoux">⌚ Montres & Bijoux</option>
                            <option value="beaute-parfums">💄 Beauté & Parfums</option>
                        </optgroup>
                        <optgroup label="🏠 Maison & Jardin">
                            <option value="meubles">🛋️ Meubles</option>
                            <option value="electromenager">🔌 Électroménager</option>
                            <option value="decoration">🖼️ Décoration</option>
                            <option value="cuisine">🍳 Articles de Cuisine</option>
                            <option value="jardin">🌱 Jardin & Extérieur</option>
                            <option value="bricolage">🔨 Bricolage & Outils</option>
                        </optgroup>
                        <optgroup label="🚗 Véhicules">
                            <option value="voitures">🚗 Voitures</option>
                            <option value="motos">🏍️ Motos & Scooters</option>
                            <option value="pieces-auto">🔧 Pièces Auto & Moto</option>
                            <option value="velos">🚲 Vélos</option>
                        </optgroup>
                        <optgroup label="📚 Loisirs & Divertissement">
                            <option value="sport-fitness">⚽ Sport & Fitness</option>
                            <option value="livres">📚 Livres & Magazines</option>
                            <option value="musique">🎵 Instruments de Musique</option>
                            <option value="jouets">🧸 Jouets & Jeux</option>
                            <option value="collection">🎨 Collection & Art</option>
                        </optgroup>
                        <optgroup label="💼 Services & Emploi">
                            <option value="services-pro">💼 Services Professionnels</option>
                            <option value="cours">📖 Cours & Formations</option>
                            <option value="emploi">👔 Offres d'Emploi</option>
                            <option value="evenements">🎉 Événements</option>
                        </optgroup>
                        <optgroup label="🏢 Immobilier">
                            <option value="appartements">🏢 Appartements</option>
                            <option value="maisons">🏠 Maisons & Villas</option>
                            <option value="terrains">🌍 Terrains</option>
                            <option value="bureaux">🏢 Bureaux & Commerces</option>
                        </optgroup>
                        <optgroup label="🐾 Animaux">
                            <option value="chiens-chats">🐕 Chiens & Chats</option>
                            <option value="autres-animaux">🐦 Autres Animaux</option>
                            <option value="accessoires-animaux">🦴 Accessoires pour Animaux</option>
                        </optgroup>
                        <optgroup label="📦 Autres">
                            <option value="alimentation">🍔 Alimentation & Boissons</option>
                            <option value="bebe-puericulture">👶 Bébé & Puériculture</option>
                            <option value="sante">⚕️ Santé & Bien-être</option>
                            <option value="autres">📦 Autres</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="search-stats" id="searchStats" style="display: none;">
                <span id="statsText"></span>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <main class="main-content">
        <div class="container">
            <div id="productsGrid" class="products-grid">
                <!-- Produits chargés dynamiquement -->
            </div>
            <div id="noProducts" class="no-products" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <p>Aucun produit trouvé</p>
            </div>
        </div>
    </main>

    <!-- Modal Login -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>
            <form id="loginForm">
                <div class="form-group">
                    <input type="email" id="loginEmail" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" id="loginPassword" placeholder="Mot de passe" required>
                </div>
                <button type="submit" class="btn btn-primary">Se connecter</button>
                <p class="form-footer">Pas de compte? <a href="#" onclick="switchModal('loginModal', 'signupModal')">S'inscrire</a></p>
            </form>
        </div>
    </div>

    <!-- Modal Signup -->
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('signupModal')">&times;</span>
            <h2><i class="fas fa-user-plus"></i> Inscription</h2>
            <form id="signupForm">
                <div class="form-group">
                    <input type="text" id="signupName" placeholder="Nom complet" required>
                </div>
                <div class="form-group">
                    <input type="email" id="signupEmail" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="tel" id="signupPhone" placeholder="Téléphone" required>
                </div>
                <div class="form-group">
                    <input type="password" id="signupPassword" placeholder="Mot de passe" required>
                </div>
                <button type="submit" class="btn btn-primary">S'inscrire</button>
                <p class="form-footer">Déjà un compte? <a href="#" onclick="switchModal('signupModal', 'loginModal')">Se connecter</a></p>
            </form>
        </div>
    </div>

    <!-- Modal Publish Product -->
    <div id="publishModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeModal('publishModal')">&times;</span>
            <h2><i class="fas fa-plus-circle"></i> Publier un produit</h2>
            <form id="publishForm" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" id="productTitle" placeholder="Titre du produit" required>
                </div>
                <div class="form-group">
                    <textarea id="productDescription" placeholder="Description détaillée" rows="4" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <input type="number" id="productPrice" placeholder="Prix (GHS)" required>
                    </div>
                    <div class="form-group">
                        <select id="productCategory" required>
                            <option value="">Catégorie</option>
                            <option value="electronics">Électronique</option>
                            <option value="fashion">Mode</option>
                            <option value="home">Maison</option>
                            <option value="vehicles">Véhicules</option>
                            <option value="services">Services</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" id="productPhone" placeholder="Téléphone de contact" required>
                    </div>
                    <div class="form-group">
                        <input type="email" id="productEmail" placeholder="Email de contact" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="file-upload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Télécharger des images</span>
                        <input type="file" id="productImages" accept="image/*" multiple>
                    </label>
                    <div id="imagePreview" class="image-preview"></div>
                </div>
                <div class="form-group">
                    <label class="file-upload">
                        <i class="fas fa-video"></i>
                        <span>Télécharger des vidéos (facultatif)</span>
                        <input type="file" id="productVideos" accept="video/*" multiple>
                    </label>
                    <div id="videoPreview" class="video-preview"></div>
                </div>
                <div class="fee-info">
                    <p>Frais de publication: <span id="publishFee">0 FCFA</span></p>
                </div>
                <button type="submit" class="btn btn-primary">Publier le produit</button>
            </form>
        </div>
    </div>

    <!-- Modal Product Detail -->
    <div id="detailModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeModal('detailModal')">&times;</span>
            <div id="productDetail">
                <!-- Détails du produit chargés dynamiquement -->
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>