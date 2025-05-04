<?php
require_once '../../database/config.php';

// Funzione per ottenere tutti i movimenti contabili
function getMovimentiContabili($conn) {
    $stmt = $conn->prepare("SELECT m.*, o.id as ordine_numero 
                          FROM movimenti_contabili m 
                          LEFT JOIN ordini o ON m.riferimento_ordine = o.id 
                          ORDER BY data_movimento DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Gestione dell'aggiunta di un nuovo movimento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'aggiungi') {
        try {
            $stmt = $conn->prepare("INSERT INTO movimenti_contabili (tipo, importo, descrizione, riferimento_ordine) 
                                  VALUES (:tipo, :importo, :descrizione, :riferimento_ordine)");
            $stmt->execute([
                ':tipo' => $_POST['tipo'],
                ':importo' => $_POST['importo'],
                ':descrizione' => $_POST['descrizione'],
                ':riferimento_ordine' => !empty($_POST['riferimento_ordine']) ? $_POST['riferimento_ordine'] : null
            ]);
            header("Location: index.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Errore nella registrazione del movimento: " . $e->getMessage();
        }
    }
}

// Ottenere la lista degli ordini per il riferimento
$stmt = $conn->prepare("SELECT id, data_ordine, totale FROM ordini ORDER BY data_ordine DESC");
$stmt->execute();
$ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ottenere tutti i movimenti
$movimenti = getMovimentiContabili($conn);

// Calcolare il totale delle entrate e uscite
$totale_entrate = 0;
$totale_uscite = 0;
foreach ($movimenti as $movimento) {
    if ($movimento['tipo'] === 'entrata') {
        $totale_entrate += $movimento['importo'];
    } else {
        $totale_uscite += $movimento['importo'];
    }
}
$saldo = $totale_entrate - $totale_uscite;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Contabilità - Sistema ERP</title>
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
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .summary-card h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .summary-card .amount {
            font-size: 1.5em;
            font-weight: bold;
        }
        .entrata {
            color: #2ecc71;
        }
        .uscita {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Sistema ERP</div>
            <ul class="menu">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="index.php">Contabilità</a></li>
                <li><a href="../magazzino/index.php">Magazzino</a></li>
                <li><a href="../clienti/index.php">Clienti</a></li>
                <li><a href="../fornitori/index.php">Fornitori</a></li>
                <li><a href="../ordini/carrello.php">Carrello</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Gestione Contabilità</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">Movimento registrato con successo!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="summary-cards">
            <div class="summary-card">
                <h3>Totale Entrate</h3>
                <div class="amount entrata">€<?php echo number_format($totale_entrate, 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Totale Uscite</h3>
                <div class="amount uscita">€<?php echo number_format($totale_uscite, 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Saldo</h3>
                <div class="amount" style="color: <?php echo $saldo >= 0 ? '#2ecc71' : '#e74c3c'; ?>">
                    €<?php echo number_format($saldo, 2); ?>
                </div>
            </div>
        </div>

        <div class="table-container">
            <h2>Movimenti Contabili</h2>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Importo</th>
                        <th>Descrizione</th>
                        <th>Riferimento Ordine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimenti as $movimento): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($movimento['data_movimento'])); ?></td>
                        <td><?php echo ucfirst($movimento['tipo']); ?></td>
                        <td class="<?php echo $movimento['tipo']; ?>">
                            €<?php echo number_format($movimento['importo'], 2); ?>
                        </td>
                        <td><?php echo htmlspecialchars($movimento['descrizione']); ?></td>
                        <td><?php echo $movimento['ordine_numero'] ? "Ordine #" . $movimento['ordine_numero'] : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <h2>Registra Nuovo Movimento</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="aggiungi">
                
                <div class="form-group">
                    <label for="tipo">Tipo Movimento:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="entrata">Entrata</option>
                        <option value="uscita">Uscita</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="importo">Importo (€):</label>
                    <input type="number" id="importo" name="importo" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="descrizione">Descrizione:</label>
                    <textarea id="descrizione" name="descrizione" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="riferimento_ordine">Riferimento Ordine (opzionale):</label>
                    <select id="riferimento_ordine" name="riferimento_ordine">
                        <option value="">Seleziona un ordine</option>
                        <?php foreach ($ordini as $ordine): ?>
                            <option value="<?php echo $ordine['id']; ?>">
                                Ordine #<?php echo $ordine['id']; ?> - 
                                <?php echo date('d/m/Y', strtotime($ordine['data_ordine'])); ?> - 
                                €<?php echo number_format($ordine['totale'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn">Registra Movimento</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Sistema ERP. Tutti i diritti riservati.</p>
    </footer>
</body>
</html>