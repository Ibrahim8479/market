<?php
require_once 'config.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$totalProducts = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT balance FROM users WHERE role = 'admin'");
$adminBalance = $stmt->fetch()['balance'];

// Liste des produits
$stmt = $pdo->query("
    SELECT p.*, u.name as seller_name 
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    ORDER BY p.created_at DESC
");
$allProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - MarketPlace</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="window.location.href='index.php'">
                    <i class="fas fa-store"></i>
                    <h1>MarketPlace Admin</h1>
                </div>
                <nav class="nav-menu">
                    <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                        <i class="fas fa-home"></i> Accueil
                    </button>
                    <div class="user-info">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <button class="btn btn-danger" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <h1 style="margin-bottom: 30px; color: var(--black);">
                <i class="fas fa-chart-line"></i> Tableau de bord
            </h1>

            <div class="admin-stats">
                <div class="stat-card">
                    <h3>Revenus totaux</h3>
                    <p><?php echo number_format($adminBalance, 0, ',', ' '); ?> FCFA</p>
                </div>
                <div class="stat-card secondary">
                    <h3>Produits actifs</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Utilisateurs</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
            </div>

            <div class="admin-products">
                <h2 style="margin-bottom: 25px;">
                    <i class="fas fa-box"></i> Tous les produits
                </h2>

                <?php foreach ($allProducts as $product): ?>
                    <div class="admin-product-item">
                        <div>
                            <h3 style="font-size: 18px; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($product['title']); ?>
                            </h3>
                            <p style="color: var(--grey); font-size: 14px;">
                                Par: <?php echo htmlspecialchars($product['seller_name']); ?> | 
                                Prix: <?php echo number_format($product['price'], 0, ',', ' '); ?> FCFA | 
                                Statut: <?php echo $product['status']; ?>
                            </p>
                        </div>
                        <div class="admin-actions">
                            <button class="btn btn-primary" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($product['status'] === 'active'): ?>
                                <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($allProducts)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--grey);">
                        Aucun produit
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function viewProduct(id) {
            window.location.href = 'index.php?product=' + id;
        }

        async function deleteProduct(id) {
            showConfirm(
                'Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.',
                async () => {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);

                    try {
                        const response = await fetch('api/products.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            showToast('Produit supprimé avec succès', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Erreur: ' + data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        showToast('Erreur de suppression', 'error');
                    }
                },
                {
                    title: 'Supprimer le produit',
                    confirmText: 'Supprimer',
                    type: 'danger'
                }
            );
        }

        async function logout() {
            showConfirm(
                'Voulez-vous vraiment vous déconnecter ?',
                async () => {
                    try {
                        const response = await fetch('api/auth.php?action=logout');
                        const data = await response.json();

                        if (data.success) {
                            showToast('Déconnexion réussie', 'success');
                            setTimeout(() => window.location.href = 'index.php', 1000);
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                    }
                },
                {
                    title: 'Déconnexion'
                }
            );
        }

        // Système de notifications (copié depuis app.js)
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
    </script>
</body>
</html>