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
            case 'rimuovi':
                $prodotto_id = $_POST['prodotto_id'];
                if (isset($_SESSION['carrello'][$prodotto_id])) {
                    unset($_SESSION['carrello'][$prodotto_id]);
                    $success = "Prodotto rimosso dal carrello";
                }
                break;
            case 'conferma':
                try {
                    // Calcola il totale dell'ordine
                    $totale_ordine = 0;
                    foreach ($_SESSION['carrello'] as $item) {
                        $totale_ordine += $item['prezzo'] * $item['quantita'];
                    }

                    // Inizia la transazione
                    $conn->beginTransaction();

                    // Ottieni i dati dell'utente
                    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = :user_id");
                    $stmt->execute([':user_id' => $_SESSION['user_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Verifica se esiste già un cliente con questa email
                    $stmt = $conn->prepare("SELECT id FROM clienti WHERE email = :email");
                    $stmt->execute([':email' => $user['email']]);
                    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($cliente) {
                        // Se il cliente esiste, usa il suo ID
                        $cliente_id = $cliente['id'];
                    } else {
                        // Se il cliente non esiste, crea un nuovo record
                        $stmt = $conn->prepare("INSERT INTO clienti (nome, cognome, email, data_registrazione) 
                                             VALUES (:nome, :cognome, :email, NOW())");
                        $stmt->execute([
                            ':nome' => $user['username'],
                            ':cognome' => '',
                            ':email' => $user['email']
                        ]);
                        $cliente_id = $conn->lastInsertId();
                    }

                    // Crea l'ordine
                    $stmt = $conn->prepare("INSERT INTO ordini (cliente_id, data_ordine, totale) VALUES (:cliente_id, NOW(), :totale)");
                    $stmt->execute([
                        ':cliente_id' => $cliente_id,
                        ':totale' => $totale_ordine
                    ]);
                    $ordine_id = $conn->lastInsertId();

                    // Aggiungi i dettagli dell'ordine
                    $stmt = $conn->prepare("INSERT INTO dettagli_ordine (ordine_id, prodotto_id, quantita, prezzo_unitario) VALUES (:ordine_id, :prodotto_id, :quantita, :prezzo)");
                    foreach ($_SESSION['carrello'] as $prodotto_id => $item) {
                        $stmt->execute([
                            ':ordine_id' => $ordine_id,
                            ':prodotto_id' => $prodotto_id,
                            ':quantita' => $item['quantita'],
                            ':prezzo' => $item['prezzo']
                        ]);

                        // Aggiorna la quantità disponibile nel magazzino
                        $stmt_update = $conn->prepare("UPDATE prodotti SET quantita_disponibile = quantita_disponibile - :quantita WHERE id = :prodotto_id");
                        $stmt_update->execute([
                            ':quantita' => $item['quantita'],
                            ':prodotto_id' => $prodotto_id
                        ]);
                    }

                    // Aggiungi il movimento contabile
                    $stmt = $conn->prepare("INSERT INTO movimenti_contabili (tipo, importo, descrizione, riferimento_ordine) VALUES ('entrata', :importo, :descrizione, :ordine_id)");
                    $stmt->execute([
                        ':importo' => $totale_ordine,
                        ':descrizione' => "Vendita Ordine",
                        ':ordine_id' => $ordine_id
                    ]);

                    // Conferma la transazione
                    $conn->commit();

                    // Svuota il carrello
                    $_SESSION['carrello'] = [];
                    $success = "Ordine confermato con successo! Il carrello è stato svuotato.";
                } catch (PDOException $e) {
                    // In caso di errore, annulla la transazione
                    $conn->rollBack();
                    $error = "Errore durante la conferma dell'ordine: " . $e->getMessage();
                }
                break;
        }
    }
}

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
    <title>Carrello - Sistema ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .cart-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .cart-items {
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .cart-item:last-child {
            border-bottom: none;a
        }
        .cart-total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin: 20px 0;
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

                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="../contabilita/index.php">Contabilità</a></li>
                    <li><a href="../magazzino/index.php">Magazzino</a></li>
                    <li><a href="../clienti/index.php">Clienti</a></li>
                    <li><a href="../fornitori/index.php">Fornitori</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'user'): ?>
                    <li><a href="../ordini/prodotti.php">Prodotti</a></li>
                    <li><a href="../ordini/carrello.php">Carrello</a></li>
                <?php endif; ?>
                <li><a href="../../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Il tuo Carrello</h1>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="cart-container">
            <?php if (empty($_SESSION['carrello'])): ?>
                <div class="empty-cart">
                    <h2>Il carrello è vuoto</h2>
                    <p>Vai alla pagina prodotti per aggiungere articoli al carrello</p>
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['carrello'] as $id => $item): ?>
                    <div class="cart-item">
                        <div>
                            <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
                            <p>Quantità: <?php echo $item['quantita']; ?></p>
                            <p>Prezzo: €<?php echo number_format($item['prezzo'], 2); ?></p>
                            <p>Subtotale: €<?php echo number_format($item['prezzo'] * $item['quantita'], 2); ?></p>
                        </div>
                        <form method="POST" action="carrello.php">
                            <input type="hidden" name="action" value="rimuovi">
                            <input type="hidden" name="prodotto_id" value="<?php echo $id; ?>">
                            <button type="submit" class="btn" style="background-color: #e74c3c;">Rimuovi</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    Totale: €<?php echo number_format($totale_carrello, 2); ?>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <form method="POST" action="carrello.php">
                        <input type="hidden" name="action" value="conferma">
                        <button type="submit" class="btn" style="width: 200px;">Conferma Ordine</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>