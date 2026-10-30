<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Achetez et vendez des produits près de chez vous au Niger avec MarketPlace.">
    <title>MarketPlace Niger | Achetez et vendez au Niger</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
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

    <section class="marketplace-intro">
        <div class="container marketplace-intro-content">
            <div>
                <p class="intro-eyebrow">Le commerce local, simplement</p>
                <h2>Achetez et vendez au Niger</h2>
                <p>Découvrez des annonces près de chez vous ou donnez une nouvelle vie à vos objets.</p>
            </div>
            <button class="btn btn-primary" onclick="openModal(currentUser ? 'publishModal' : 'signupModal')">
                <i class="fas fa-plus"></i> Déposer une annonce
            </button>
        </div>
    </section>

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
                        <option value="all">Toutes catégories</option>
                        <?php foreach (getProductCategoryGroups(true) as $group => $categories): ?>
                            <optgroup label="<?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php foreach ($categories as $value => $label): ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
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
            <div class="listing-heading">
                <div>
                    <p class="intro-eyebrow">À découvrir</p>
                    <h2>Annonces récentes</h2>
                </div>
            </div>
            <div id="productsGrid" class="products-grid">
                <!-- Produits chargés dynamiquement -->
            </div>
            <div id="noProducts" class="no-products" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <p id="noProductsText">Aucun produit trouvé</p>
            </div>
        </div>
    </main>

    <!-- Modal Login -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <button class="close" type="button" aria-label="Fermer la fenêtre de connexion" onclick="closeModal('loginModal')">
                <i class="fas fa-times"></i><span>Fermer</span>
            </button>
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
            <button class="close" type="button" aria-label="Fermer la fenêtre d’inscription" onclick="closeModal('signupModal')">
                <i class="fas fa-times"></i><span>Fermer</span>
            </button>
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
                    <input type="password" id="signupPassword" placeholder="Mot de passe (8 caractères minimum)" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary">S'inscrire</button>
                <p class="form-footer">Déjà un compte? <a href="#" onclick="switchModal('signupModal', 'loginModal')">Se connecter</a></p>
            </form>
        </div>
    </div>

    <!-- Modal Publish Product -->
    <div id="publishModal" class="modal">
        <div class="modal-content modal-large">
            <button class="close" type="button" aria-label="Fermer la fenêtre de publication" onclick="closeModal('publishModal')">
                <i class="fas fa-times"></i><span>Fermer</span>
            </button>
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
                        <input type="number" id="productPrice" placeholder="Prix (FCFA)" min="1" step="1" required>
                    </div>
                    <div class="form-group">
                        <select id="productCategory" required>
                            <option value="">Catégorie</option>
                            <?php foreach (getProductCategoryGroups() as $group => $categories): ?>
                                <optgroup label="<?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php foreach ($categories as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
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
                        <span>Images (8 maximum, 5 Mo chacune)</span>
                        <input type="file" id="productImages" accept=".jpg,.jpeg,.png,.gif,.webp" multiple>
                    </label>
                    <div id="imagePreview" class="image-preview"></div>
                </div>
                <div class="form-group">
                    <label class="file-upload">
                        <i class="fas fa-video"></i>
                        <span>Vidéos (2 maximum, 25 Mo chacune)</span>
                        <input type="file" id="productVideos" accept=".mp4,.webm,.mov,.avi" multiple>
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
            <button class="close" type="button" aria-label="Retour aux annonces" onclick="closeModal('detailModal')">
                <i class="fas fa-arrow-left"></i><span>Retour</span>
            </button>
            <div id="productDetail">
                <!-- Détails du produit chargés dynamiquement -->
            </div>
        </div>
    </div>

    <div id="messagesModal" class="modal">
        <div class="modal-content modal-large">
            <button class="close" type="button" aria-label="Fermer la fenêtre des messages" onclick="closeModal('messagesModal')">
                <i class="fas fa-times"></i><span>Fermer</span>
            </button>
            <h2><i class="fas fa-envelope"></i> Mes messages</h2>
            <div id="messagesList" class="messages-list" aria-live="polite">
                <p class="messages-empty">Chargement de vos messages...</p>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container">
            <span>MarketPlace Niger</span>
            <span>Achetez local. Vendez simplement.</span>
        </div>
    </footer>

    <script src="js/app.js?v=<?php echo filemtime(__DIR__ . '/js/app.js'); ?>"></script>
</body>
</html>