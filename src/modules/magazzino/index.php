<?php
require_once '../../database/config.php';

// Funzione per ottenere tutti i prodotti
function getProdotti($conn) {
    $stmt = $conn->prepare("SELECT p.*, f.ragione_sociale as fornitore 
                          FROM prodotti p 
                          LEFT JOIN fornitori f ON p.fornitore_id = f.id");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Gestione dell'aggiunta di un nuovo prodotto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'aggiungi') {
        try {
            $stmt = $conn->prepare("INSERT INTO prodotti (nome, descrizione, prezzo, quantita_disponibile, fornitore_id) 
                                  VALUES (:nome, :descrizione, :prezzo, :quantita, :fornitore_id)");
            $stmt->execute([
                ':nome' => $_POST['nome'],
                ':descrizione' => $_POST['descrizione'],
                ':prezzo' => $_POST['prezzo'],
                ':quantita' => $_POST['quantita'],
                ':fornitore_id' => $_POST['fornitore_id']
            ]);
            header("Location: index.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Errore nell'aggiunta del prodotto: " . $e->getMessage();
        }
    }
}

// Ottenere la lista dei fornitori per il form di aggiunta
$stmt = $conn->prepare("SELECT id, ragione_sociale FROM fornitori");
$stmt->execute();
$fornitori = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ottenere tutti i prodotti
$prodotti = getProdotti($conn);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Magazzino - Sistema ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .table-container {
            overflow-x: auto;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sistema ERP</div>
            <ul class="menu">
                <li><a href="modules/contabilita/index.php">Contabilità</a></li>
                <li><a href="modules/magazzino/index.php">Magazzino</a></li>
                <li><a href="modules/clienti/index.php">Clienti</a></li>
                <li><a href="modules/fornitori/index.php">Fornitori</a></li>
                <li><a href="modules/ordini/prodotti.php">Prodotti</a></li>
                <li><a href="modules/ordini/carrello.php">Carrello</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Gestione Magazzino</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">Prodotto aggiunto con successo!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <h2>Prodotti in Magazzino</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        <th>Prezzo</th>
                        <th>Quantità</th>
                        <th>Fornitore</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prodotti as $prodotto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prodotto['id']); ?></td>
                        <td><?php echo htmlspecialchars($prodotto['nome']); ?></td>
                        <td><?php echo htmlspecialchars($prodotto['descrizione']); ?></td>
                        <td>€<?php echo number_format($prodotto['prezzo'], 2); ?></td>
                        <td><?php echo htmlspecialchars($prodotto['quantita_disponibile']); ?></td>
                        <td><?php echo htmlspecialchars($prodotto['fornitore']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <h2>Aggiungi Nuovo Prodotto</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="aggiungi">
                
                <div class="form-group">
                    <label for="nome">Nome Prodotto:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="descrizione">Descrizione:</label>
                    <textarea id="descrizione" name="descrizione" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="prezzo">Prezzo (€):</label>
                    <input type="number" id="prezzo" name="prezzo" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="quantita">Quantità Disponibile:</label>
                    <input type="number" id="quantita" name="quantita" required>
                </div>

                <div class="form-group">
                    <label for="fornitore_id">Fornitore:</label>
                    <select id="fornitore_id" name="fornitore_id" required>
                        <option value="">Seleziona un fornitore</option>
                        <?php foreach ($fornitori as $fornitore): ?>
                            <option value="<?php echo $fornitore['id']; ?>">
                                <?php echo htmlspecialchars($fornitore['ragione_sociale']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn">Aggiungi Prodotto</button>
            </form>
        </div>
    </main>
</body>
</html>