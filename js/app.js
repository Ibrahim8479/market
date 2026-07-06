// Configuration
const API_URL = 'api/';

// État de l'application
let currentUser = null;
let products = [];
let selectedProduct = null;

// Système de notifications professionnelles
function showToast(message, type = 'success', title = '') {
    const container = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };
    
    const titles = {
        success: title || 'Succès',
        error: title || 'Erreur',
        warning: title || 'Attention',
        info: title || 'Information'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">${icons[type]}</div>
        <div class="toast-content">
            <div class="toast-title">${titles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast(this)">×</button>
    `;
    
    container.appendChild(toast);
    
    // Auto-fermeture après 5 secondes
    setTimeout(() => {
        closeToast(toast.querySelector('.toast-close'));
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

function closeToast(button) {
    const toast = button.closest('.toast');
    if (toast) {
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
    }
}

// Modal de confirmation professionnel
function showConfirm(message, onConfirm, options = {}) {
    const modal = document.getElementById('confirmModal') || createConfirmModal();
    
    const icon = options.type === 'danger' ? 
        '<i class="fas fa-exclamation-triangle"></i>' : 
        '<i class="fas fa-question-circle"></i>';
    
    const iconClass = options.type === 'danger' ? 'danger' : 'warning';
    const title = options.title || 'Confirmation';
    const confirmText = options.confirmText || 'Confirmer';
    const cancelText = options.cancelText || 'Annuler';
    
    modal.innerHTML = `
        <div class="confirm-content">
            <div class="confirm-header">
                <div class="confirm-icon ${iconClass}">${icon}</div>
                <div class="confirm-header-text">
                    <h3>${title}</h3>
                </div>
            </div>
            <div class="confirm-body">${message}</div>
            <div class="confirm-footer">
                <button class="btn btn-cancel" onclick="closeConfirm()">
                    <i class="fas fa-times"></i> ${cancelText}
                </button>
                <button class="btn ${options.type === 'danger' ? 'btn-danger' : 'btn-primary'}" id="confirmBtn">
                    <i class="fas fa-check"></i> ${confirmText}
                </button>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
    
    document.getElementById('confirmBtn').onclick = () => {
        closeConfirm();
        onConfirm();
    };
    
    modal.onclick = (e) => {
        if (e.target === modal) closeConfirm();
    };
}

function createConfirmModal() {
    const modal = document.createElement('div');
    modal.id = 'confirmModal';
    modal.className = 'confirm-modal';
    document.body.appendChild(modal);
    return modal;
}

function closeConfirm() {
    const modal = document.getElementById('confirmModal');
    if (modal) modal.style.display = 'none';
}

// Bouton avec état de chargement
function setButtonLoading(button, loading) {
    if (loading) {
        button.classList.add('loading');
        button.disabled = true;
    } else {
        button.classList.remove('loading');
        button.disabled = false;
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    loadProducts();
    setupEventListeners();
});

// Vérifier l'authentification
async function checkAuth() {
    try {
        const response = await fetch(API_URL + 'auth.php?action=check');
        const data = await response.json();
        
        if (data.success && data.user) {
            currentUser = data.user;
            updateUI();
        } else {
            currentUser = null;
            updateUI();
        }
    } catch (error) {
        console.error('Erreur auth:', error);
    }
}

// Mettre à jour l'interface
function updateUI() {
    const userActions = document.getElementById('userActions');
    
    if (currentUser) {
        userActions.innerHTML = `
            <button class="btn btn-primary" onclick="openModal('publishModal')">
                <i class="fas fa-plus"></i> Publier
            </button>
            ${currentUser.role === 'admin' ? `
                <button class="btn btn-secondary" onclick="window.location.href='admin.php'">
                    <i class="fas fa-cog"></i> Admin
                </button>
            ` : ''}
            <div class="header-divider"></div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>${currentUser.name}</span>
            </div>
            <button class="btn btn-danger" onclick="logout()" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        `;
    } else {
        userActions.innerHTML = `
            <button class="btn btn-secondary" onclick="openModal('loginModal')">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </button>
            <button class="btn btn-primary" onclick="openModal('signupModal')">
                <i class="fas fa-user-plus"></i> Inscription
            </button>
        `;
    }
}

// Charger les produits
async function loadProducts() {
    try {
        const response = await fetch(API_URL + 'products.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            products = data.products;
            displayProducts();
        }
    } catch (error) {
        console.error('Erreur chargement produits:', error);
    }
}

// Afficher les produits
function displayProducts() {
    const grid = document.getElementById('productsGrid');
    const noProducts = document.getElementById('noProducts');
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const searchStats = document.getElementById('searchStats');
    const statsText = document.getElementById('statsText');
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value;
    
    // Afficher/cacher le bouton clear
    if (searchTerm) {
        clearBtn.style.display = 'flex';
    } else {
        clearBtn.style.display = 'none';
    }
    
    const filtered = products.filter(p => {
        const matchSearch = !searchTerm || 
                           p.title.toLowerCase().includes(searchTerm) || 
                           p.description.toLowerCase().includes(searchTerm) ||
                           p.category.toLowerCase().includes(searchTerm);
        const matchCategory = category === 'all' || p.category === category;
        return matchSearch && matchCategory && p.status === 'active';
    });
    
    // Afficher les statistiques de recherche
    if (searchTerm || category !== 'all') {
        searchStats.style.display = 'block';
        if (filtered.length > 0) {
            statsText.innerHTML = `<strong>${filtered.length}</strong> produit${filtered.length > 1 ? 's' : ''} trouvé${filtered.length > 1 ? 's' : ''}`;
        } else {
            statsText.innerHTML = 'Aucun produit ne correspond à votre recherche';
        }
    } else {
        searchStats.style.display = 'none';
    }
    
    if (filtered.length === 0) {
        grid.style.display = 'none';
        noProducts.style.display = 'flex';
        return;
    }
    
    grid.style.display = 'grid';
    noProducts.style.display = 'none';
    
    grid.innerHTML = filtered.map(product => {
        let mediaHtml = '<i class="fas fa-image"></i>';
        
        if (product.images && product.images.length > 0) {
            mediaHtml = `<img src="uploads/${product.images[0]}" alt="${product.title}">`;
        } else if (product.videos && product.videos.length > 0) {
            mediaHtml = `
                <video src="uploads/${product.videos[0]}" muted loop autoplay>
                    <i class="fas fa-video"></i>
                </video>
            `;
        }
        
        // Obtenir l'émoji de catégorie
        const categoryEmoji = getCategoryEmoji(product.category);
        
        return `
            <div class="product-card" onclick="showProductDetail(${product.id})">
                <div class="product-image">
                    ${mediaHtml}
                    ${product.videos && product.videos.length > 0 ? '<div class="video-badge"><i class="fas fa-play-circle"></i></div>' : ''}
                </div>
                <div class="product-info">
                    <h3 class="product-title">${product.title}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-footer">
                        <span class="product-price">${formatPrice(product.price)}</span>
                        <span class="product-category">${categoryEmoji} ${getCategoryName(product.category)}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Obtenir l'émoji de la catégorie
function getCategoryEmoji(category) {
    const emojis = {
        'smartphones': '📱', 'ordinateurs': '💻', 'tv-audio': '📺', 
        'accessoires-tech': '🎧', 'jeux-video': '🎮',
        'vetements-hommes': '👔', 'vetements-femmes': '👗', 'vetements-enfants': '👶',
        'chaussures': '👟', 'sacs-accessoires': '👜', 'montres-bijoux': '⌚',
        'beaute-parfums': '💄', 'meubles': '🛋️', 'electromenager': '🔌',
        'decoration': '🖼️', 'cuisine': '🍳', 'jardin': '🌱', 'bricolage': '🔨',
        'voitures': '🚗', 'motos': '🏍️', 'pieces-auto': '🔧', 'velos': '🚲',
        'sport-fitness': '⚽', 'livres': '📚', 'musique': '🎵', 'jouets': '🧸',
        'collection': '🎨', 'services-pro': '💼', 'cours': '📖', 'emploi': '👔',
        'evenements': '🎉', 'appartements': '🏢', 'maisons': '🏠', 'terrains': '🌍',
        'bureaux': '🏢', 'chiens-chats': '🐕', 'autres-animaux': '🐦',
        'accessoires-animaux': '🦴', 'alimentation': '🍔', 'bebe-puericulture': '👶',
        'sante': '⚕️', 'autres': '📦'
    };
    return emojis[category] || '📦';
}

// Obtenir le nom de la catégorie
function getCategoryName(category) {
    const names = {
        'smartphones': 'Smartphones', 'ordinateurs': 'Ordinateurs', 'tv-audio': 'TV & Audio',
        'accessoires-tech': 'Accessoires Tech', 'jeux-video': 'Jeux Vidéo',
        'vetements-hommes': 'Vêtements Hommes', 'vetements-femmes': 'Vêtements Femmes',
        'vetements-enfants': 'Vêtements Enfants', 'chaussures': 'Chaussures',
        'sacs-accessoires': 'Sacs & Accessoires', 'montres-bijoux': 'Montres & Bijoux',
        'beaute-parfums': 'Beauté', 'meubles': 'Meubles', 'electromenager': 'Électroménager',
        'decoration': 'Décoration', 'cuisine': 'Cuisine', 'jardin': 'Jardin',
        'bricolage': 'Bricolage', 'voitures': 'Voitures', 'motos': 'Motos',
        'pieces-auto': 'Pièces Auto', 'velos': 'Vélos', 'sport-fitness': 'Sport',
        'livres': 'Livres', 'musique': 'Musique', 'jouets': 'Jouets',
        'collection': 'Collection', 'services-pro': 'Services Pro', 'cours': 'Cours',
        'emploi': 'Emploi', 'evenements': 'Événements', 'appartements': 'Appartements',
        'maisons': 'Maisons', 'terrains': 'Terrains', 'bureaux': 'Bureaux',
        'chiens-chats': 'Animaux', 'autres-animaux': 'Animaux', 
        'accessoires-animaux': 'Accessoires Animaux', 'alimentation': 'Alimentation',
        'bebe-puericulture': 'Bébé', 'sante': 'Santé', 'autres': 'Autres'
    };
    return names[category] || 'Autre';
}

// Formater le prix en FCFA
function formatPrice(price) {
    return new Intl.NumberFormat('fr-NE', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).format(price);
}

// Afficher détail produit
async function showProductDetail(productId) {
    try {
        const response = await fetch(API_URL + `products.php?action=get&id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            selectedProduct = data.product;
            const detail = document.getElementById('productDetail');
            
            // Construire la galerie média
            let mediaGallery = '';
            
            if (selectedProduct.images && selectedProduct.images.length > 0) {
                mediaGallery += '<div class="media-gallery">';
                selectedProduct.images.forEach(img => {
                    mediaGallery += `<img src="uploads/${img}" alt="${selectedProduct.title}" class="gallery-item">`;
                });
                mediaGallery += '</div>';
            }
            
            if (selectedProduct.videos && selectedProduct.videos.length > 0) {
                mediaGallery += '<div class="video-gallery">';
                selectedProduct.videos.forEach(video => {
                    mediaGallery += `
                        <video controls class="gallery-item">
                            <source src="uploads/${video}" type="video/mp4">
                            Votre navigateur ne supporte pas la vidéo.
                        </video>
                    `;
                });
                mediaGallery += '</div>';
            }
            
            if (!mediaGallery) {
                mediaGallery = '<div class="detail-image"><i class="fas fa-image"></i></div>';
            }
            
            detail.innerHTML = `
                <div class="product-detail">
                    ${mediaGallery}
                    
                    <div class="detail-header">
                        <div class="detail-title">
                            <h1>${selectedProduct.title}</h1>
                            <span class="product-category">${selectedProduct.category}</span>
                        </div>
                        <div class="detail-price">${formatPrice(selectedProduct.price)}</div>
                    </div>
                    
                    <div class="detail-section">
                        <h2>Description</h2>
                        <p>${selectedProduct.description}</p>
                    </div>
                    
                    <div class="detail-section">
                        <h2>Informations du vendeur</h2>
                        <div class="seller-info">
                            <p><i class="fas fa-user"></i> <strong>Nom:</strong> ${selectedProduct.seller_name}</p>
                            <p><i class="fas fa-phone"></i> <strong>Téléphone:</strong> ${selectedProduct.phone}</p>
                            <p><i class="fas fa-envelope"></i> <strong>Email:</strong> ${selectedProduct.email}</p>
                        </div>
                    </div>
                    
                    ${currentUser && currentUser.id !== selectedProduct.seller_id ? `
                        <div class="detail-section">
                            <h2>Contacter le vendeur</h2>
                            <div class="message-form">
                                <textarea id="messageContent" placeholder="Votre message..." rows="4"></textarea>
                                <button class="btn btn-primary" onclick="sendMessage()">
                                    <i class="fas fa-paper-plane"></i> Envoyer
                                </button>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${currentUser && currentUser.role === 'admin' ? `
                        <div class="detail-section">
                            <button class="btn btn-danger" onclick="deleteProduct(${selectedProduct.id})">
                                <i class="fas fa-trash"></i> Supprimer ce produit
                            </button>
                        </div>
                    ` : ''}
                </div>
            `;
            
            openModal('detailModal');
        }
    } catch (error) {
        console.error('Erreur détail produit:', error);
    }
}

// Configuration des écouteurs d'événements
function setupEventListeners() {
    // Recherche avec debounce pour de meilleures performances
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            displayProducts();
        }, 300); // Attendre 300ms après la dernière saisie
    });
    
    // Bouton clear recherche
    document.getElementById('clearSearch').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        displayProducts();
    });
    
    // Filtre catégorie
    document.getElementById('categoryFilter').addEventListener('change', displayProducts);
    
    // Formulaire de connexion
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        setButtonLoading(submitBtn, true);
        
        try {
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            
            const response = await fetch(API_URL + 'auth.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                currentUser = data.user;
                updateUI();
                closeModal('loginModal');
                showToast('Connexion réussie ! Bienvenue sur MarketPlace', 'success');
            } else {
                showToast(data.message || 'Email ou mot de passe incorrect', 'error');
            }
        } catch (error) {
            console.error('Erreur login:', error);
            showToast('Erreur de connexion au serveur', 'error');
        } finally {
            setButtonLoading(submitBtn, false);
        }
    });
    
    // Formulaire d'inscription
    document.getElementById('signupForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        const formData = new FormData();
        formData.append('action', 'signup');
        formData.append('name', document.getElementById('signupName').value);
        formData.append('email', document.getElementById('signupEmail').value);
        formData.append('phone', document.getElementById('signupPhone').value);
        formData.append('password', document.getElementById('signupPassword').value);
        
        setButtonLoading(submitBtn, true);
        
        try {
            const response = await fetch(API_URL + 'auth.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                currentUser = data.user;
                updateUI();
                closeModal('signupModal');
                showToast('Inscription réussie ! Votre compte a été créé', 'success');
            } else {
                showToast(data.message || 'Erreur lors de l\'inscription', 'error');
            }
        } catch (error) {
            console.error('Erreur signup:', error);
            showToast('Erreur de connexion au serveur', 'error');
        } finally {
            setButtonLoading(submitBtn, false);
        }
    });
    
    // Formulaire de publication
    document.getElementById('publishForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!currentUser) {
            showToast('Vous devez être connecté pour publier', 'warning');
            return;
        }
        
        const price = document.getElementById('productPrice').value;
        const fee = calculateFee(price);
        
        showConfirm(
            `Le produit sera publié avec les frais suivants :<br><br>
            <strong>Prix du produit :</strong> ${formatPrice(price)}<br>
            <strong>Frais de publication :</strong> ${formatPrice(fee)}<br><br>
            Voulez-vous confirmer la publication ?`,
            async () => {
                const submitBtn = e.target.querySelector('button[type="submit"]');
                setButtonLoading(submitBtn, true);
                
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('title', document.getElementById('productTitle').value);
                formData.append('description', document.getElementById('productDescription').value);
                formData.append('price', price);
                formData.append('category', document.getElementById('productCategory').value);
                formData.append('phone', document.getElementById('productPhone').value);
                formData.append('email', document.getElementById('productEmail').value);
                
                // Images
                const imageFiles = document.getElementById('productImages').files;
                for (let i = 0; i < imageFiles.length; i++) {
                    formData.append('images[]', imageFiles[i]);
                }
                
                // Vidéos
                const videoFiles = document.getElementById('productVideos').files;
                for (let i = 0; i < videoFiles.length; i++) {
                    formData.append('videos[]', videoFiles[i]);
                }
                
                try {
                    const response = await fetch(API_URL + 'products.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        closeModal('publishModal');
                        document.getElementById('publishForm').reset();
                        document.getElementById('imagePreview').innerHTML = '';
                        document.getElementById('videoPreview').innerHTML = '';
                        loadProducts();
                        showToast('Produit publié avec succès ! Il est maintenant visible sur le site', 'success', 'Publication réussie');
                    } else {
                        showToast(data.message || 'Erreur lors de la publication', 'error');
                    }
                } catch (error) {
                    console.error('Erreur publication:', error);
                    showToast('Erreur de connexion au serveur', 'error');
                } finally {
                    setButtonLoading(submitBtn, false);
                }
            },
            {
                title: 'Confirmer la publication',
                confirmText: 'Publier',
                type: 'warning'
            }
        );
    });
    
    // Calcul des frais en temps réel
    document.getElementById('productPrice').addEventListener('input', (e) => {
        const fee = calculateFee(e.target.value);
        document.getElementById('publishFee').textContent = formatPrice(fee);
    });
    
    // Prévisualisation des images
    document.getElementById('productImages').addEventListener('change', (e) => {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        for (let file of e.target.files) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-image';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Prévisualisation des vidéos
    document.getElementById('productVideos').addEventListener('change', (e) => {
        const preview = document.getElementById('videoPreview');
        preview.innerHTML = '';
        
        for (let file of e.target.files) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.className = 'preview-video';
            video.controls = true;
            preview.appendChild(video);
        }
    });
}

// Calculer les frais de publication (en FCFA)
// Formule : 
// - Prix < 5,000 FCFA : 150 FCFA
// - Prix ≥ 5,000 FCFA : 300 FCFA par tranche de 5,000 FCFA
function calculateFee(price) {
    price = price || document.getElementById('productPrice')?.value || 0;
    const priceNum = parseFloat(price);
    
    if (priceNum <= 0) return 0;
    
    // Pour les prix inférieurs à 5,000 FCFA : frais fixe de 150 FCFA
    if (priceNum < 5000) {
        return 150;
    }
    
    // Pour les prix de 5,000 FCFA et plus : 300 FCFA par tranche de 5,000
    const tranches = Math.ceil(priceNum / 5000);
    return tranches * 300;
}

// Envoyer un message
async function sendMessage() {
    if (!currentUser) {
        showToast('Vous devez être connecté pour envoyer un message', 'warning');
        return;
    }
    
    const content = document.getElementById('messageContent').value;
    if (!content.trim()) {
        showToast('Veuillez écrire un message', 'warning');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'send');
        formData.append('product_id', selectedProduct.id);
        formData.append('receiver_id', selectedProduct.seller_id);
        formData.append('content', content);
        
        const response = await fetch(API_URL + 'messages.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Message envoyé avec succès au vendeur', 'success');
            document.getElementById('messageContent').value = '';
        } else {
            showToast('Erreur lors de l\'envoi du message', 'error');
        }
    } catch (error) {
        console.error('Erreur message:', error);
        showToast('Erreur de connexion au serveur', 'error');
    }
}

// Supprimer un produit
async function deleteProduct(productId) {
    showConfirm(
        'Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.',
        async () => {
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', productId);
                
                const response = await fetch(API_URL + 'products.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeModal('detailModal');
                    loadProducts();
                    showToast('Produit supprimé avec succès', 'success');
                } else {
                    showToast('Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur suppression:', error);
                showToast('Erreur de connexion au serveur', 'error');
            }
        },
        {
            title: 'Supprimer le produit',
            confirmText: 'Supprimer',
            cancelText: 'Annuler',
            type: 'danger'
        }
    );
}

// Déconnexion
async function logout() {
    showConfirm(
        'Voulez-vous vraiment vous déconnecter ?',
        async () => {
            try {
                const response = await fetch(API_URL + 'auth.php?action=logout');
                const data = await response.json();
                
                if (data.success) {
                    currentUser = null;
                    updateUI();
                    showToast('Déconnexion réussie. À bientôt !', 'info');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                }
            } catch (error) {
                console.error('Erreur logout:', error);
                showToast('Erreur lors de la déconnexion', 'error');
            }
        },
        {
            title: 'Déconnexion',
            confirmText: 'Déconnexion'
        }
    );
}

// Gestion des modals
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function switchModal(closeId, openId) {
    closeModal(closeId);
    openModal(openId);
}

// Fermer modal en cliquant à l'extérieur
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}