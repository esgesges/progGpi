<?php
require_once '../../database/config.php';

session_start();

// Inizializza il carrello se non esiste
if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

// Gestione delle azioni del carrello
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'aggiungi':
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
                break;
                
            case 'rimuovi':
                $prodotto_id = $_POST['prodotto_id'];
                if (isset($_SESSION['carrello'][$prodotto_id])) {
                    unset($_SESSION['carrello'][$prodotto_id]);
                    $success = "Prodotto rimosso dal carrello";
                }
                break;
                
            case 'conferma':
                if (!empty($_SESSION['carrello'])) {
                    try {
                        $conn->beginTransaction();
                        
                        // Crea l'ordine
                        $totale = 0;
                        foreach ($_SESSION['carrello'] as $id => $item) {
                            $totale += $item['prezzo'] * $item['quantita'];
                        }
                        
                        $stmt = $conn->prepare("INSERT INTO ordini (cliente_id, totale, stato) VALUES (:cliente_id, :totale, 'in attesa')");
                        $stmt->execute([
                            ':cliente_id' => $_POST['cliente_id'],
                            ':totale' => $totale
                        ]);
                        
                        $ordine_id = $conn->lastInsertId();
                        
                        // Inserisci i dettagli dell'ordine e aggiorna il magazzino
                        foreach ($_SESSION['carrello'] as $id => $item) {
                            // Inserisci dettaglio ordine
                            $stmt = $conn->prepare("INSERT INTO dettagli_ordine (ordine_id, prodotto_id, quantita, prezzo_unitario) 
                                                  VALUES (:ordine_id, :prodotto_id, :quantita, :prezzo)");
                            $stmt->execute([
                                ':ordine_id' => $ordine_id,
                                ':prodotto_id' => $id,
                                ':quantita' => $item['quantita'],
                                ':prezzo' => $item['prezzo']
                            ]);
                            
                            // Aggiorna quantità magazzino
                            $stmt = $conn->prepare("UPDATE prodotti SET quantita_disponibile = quantita_disponibile - :quantita 
                                                  WHERE id = :id");
                            $stmt->execute([':quantita' => $item['quantita'], ':id' => $id]);
                        }
                        
                        $conn->commit();
                        $_SESSION['carrello'] = [];
                        $success = "Ordine confermato con successo!";
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $error = "Errore durante la conferma dell'ordine: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Ottieni lista prodotti disponibili
$stmt = $conn->prepare("SELECT * FROM prodotti WHERE quantita_disponibile > 0");
$stmt->execute();
$prodotti = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ottieni lista clienti per la selezione
$stmt = $conn->prepare("SELECT id, nome, cognome FROM clienti");
$stmt->execute();
$clienti = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcola totale carrello
$totale_carrello = 0;
foreach ($_SESSION['carrello'] as $item) {
    $totale_carrello += $item['prezzo'] * $item['quantita'];
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrello Acquisti - Sistema ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .product-list, .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .quantity-input {
            width: 60px;
            padding: 5px;
            margin-right: 10px;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
        }
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sistema ERP</div>
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
        <h1>Carrello Acquisti</h1>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="cart-container">
            <div class="product-list">
                <h2>Prodotti Disponibili</h2>
                <?php foreach ($prodotti as $prodotto): ?>
                    <div class="product-card">
                        <h3><?php echo htmlspecialchars($prodotto['nome']); ?></h3>
                        <p><?php echo htmlspecialchars($prodotto['descrizione']); ?></p>
                        <p>Prezzo: €<?php echo number_format($prodotto['prezzo'], 2); ?></p>
                        <p>Disponibili: <?php echo $prodotto['quantita_disponibile']; ?></p>
                        
                        <form method="POST" action="carrello.php" style="display: inline-block;">
                            <input type="hidden" name="action" value="aggiungi">
                            <input type="hidden" name="prodotto_id" value="<?php echo $prodotto['id']; ?>">
                            <input type="number" name="quantita" value="1" min="1" max="<?php echo $prodotto['quantita_disponibile']; ?>" class="quantity-input">
                            <button type="submit" class="btn">Aggiungi al Carrello</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Riepilogo Carrello</h2>
                <?php if (empty($_SESSION['carrello'])): ?>
                    <p>Il carrello è vuoto</p>
                <?php else: ?>
                    <?php foreach ($_SESSION['carrello'] as $id => $item): ?>
                        <div class="cart-item">
                            <div>
                                <h4><?php echo htmlspecialchars($item['nome']); ?></h4>
                                <p>Quantità: <?php echo $item['quantita']; ?></p>
                                <p>Prezzo: €<?php echo number_format($item['prezzo'], 2); ?></p>
                            </div>
                            <form method="POST" action="carrello.php">
                                <input type="hidden" name="action" value="rimuovi">
                                <input type="hidden" name="prodotto_id" value="<?php echo $id; ?>">
                                <button type="submit" class="btn" style="background-color: #e74c3c;">Rimuovi</button>
                            </form>
                        </div>
                    <?php endforeach; ?>

                    <div class="total">
                        Totale: €<?php echo number_format($totale_carrello, 2); ?>
                    </div>

                    <form method="POST" action="carrello.php" style="margin-top: 20px;">
                        <input type="hidden" name="action" value="conferma">
                        <div class="form-group">
                            <label for="cliente_id">Seleziona Cliente:</label>
                            <select id="cliente_id" name="cliente_id" required>
                                <option value="">Scegli un cliente</option>
                                <?php foreach ($clienti as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>">
                                        <?php echo htmlspecialchars($cliente['nome'] . ' ' . $cliente['cognome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Conferma Ordine</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Sistema ERP. Tutti i diritti riservati.</p>
    </footer>
</body>
</html>