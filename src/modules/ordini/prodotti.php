<?php
require_once '../../database/config.php';
session_start();

// Inizializza il carrello se non esiste
if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

// Gestione dell'aggiunta al carrello
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aggiungi') {
    $prodotto_id = $_POST['prodotto_id'];
    $quantita = $_POST['quantita'];
    
    // Verifica disponibilità
    $stmt = $conn->prepare("SELECT * FROM prodotti WHERE id = :id AND quantita_disponibile >= :quantita");
    $stmt->execute([':id' => $prodotto_id, ':quantita' => $quantita]);
    $prodotto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prodotto) {
        if (isset($_SESSION['carrello'][$prodotto_id])) {
            $_SESSION['carrello'][$prodotto_id]['quantita'] += $quantita;
        } else {
            $_SESSION['carrello'][$prodotto_id] = [
                'nome' => $prodotto['nome'],
                'prezzo' => $prodotto['prezzo'],
                'quantita' => $quantita
            ];
        }
        $success = "Prodotto aggiunto al carrello";
    }
}

// Ottieni lista prodotti disponibili
$stmt = $conn->prepare("SELECT * FROM prodotti WHERE quantita_disponibile > 0");
$stmt->execute();
$prodotti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prodotti - Sistema ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .product-card h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .product-card p {
            color: #666;
            margin-bottom: 15px;
        }
        .product-card .price {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .add-to-cart-form {
            display: flex;
            gap: 10px;
        }
        .add-to-cart-form input[type="number"] {
            width: 60px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="../../content/logo.png" alt="Logo Sistema ERP" class="logo-img">
            </div>
            <ul class="menu">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="../contabilita/index.php">Contabilità</a></li>
                <li><a href="../magazzino/index.php">Magazzino</a></li>
                <li><a href="../clienti/index.php">Clienti</a></li>
                <li><a href="../fornitori/index.php">Fornitori</a></li>
                <li><a href="carrello.php">Carrello</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Catalogo Prodotti</h1>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="products-grid">
            <?php foreach ($prodotti as $prodotto): ?>
                <div class="product-card">
                    <h3><?php echo htmlspecialchars($prodotto['nome']); ?></h3>
                    <p><?php echo htmlspecialchars($prodotto['descrizione']); ?></p>
                    <p>Prezzo: €<?php echo number_format($prodotto['prezzo'], 2); ?></p>
                    <p>Disponibili: <?php echo $prodotto['quantita_disponibile']; ?></p>
                    
                    <form method="POST" action="prodotti.php">
                        <input type="hidden" name="action" value="aggiungi">
                        <input type="hidden" name="prodotto_id" value="<?php echo $prodotto['id']; ?>">
                        <input type="number" name="quantita" value="1" min="1" max="<?php echo $prodotto['quantita_disponibile']; ?>" class="quantity-input">
                        <button type="submit" class="btn">Aggiungi al Carrello</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>