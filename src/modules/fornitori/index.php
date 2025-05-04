<?php
require_once '../../database/config.php';

// Funzione per ottenere tutti i fornitori
function getFornitori($conn) {
    $stmt = $conn->prepare("SELECT * FROM fornitori ORDER BY data_registrazione DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Gestione dell'aggiunta di un nuovo fornitore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'aggiungi') {
        try {
            $stmt = $conn->prepare("INSERT INTO fornitori (ragione_sociale, partita_iva, email, telefono, indirizzo) 
                                  VALUES (:ragione_sociale, :partita_iva, :email, :telefono, :indirizzo)");
            $stmt->execute([
                ':ragione_sociale' => $_POST['ragione_sociale'],
                ':partita_iva' => $_POST['partita_iva'],
                ':email' => $_POST['email'],
                ':telefono' => $_POST['telefono'],
                ':indirizzo' => $_POST['indirizzo']
            ]);
            header("Location: index.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Errore nella registrazione del fornitore: " . $e->getMessage();
        }
    }
}

// Ottenere la lista dei fornitori
$fornitori = getFornitori($conn);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Fornitori - Sistema ERP</title>
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
        .form-group input, .form-group textarea {
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
        <h1>Gestione Fornitori</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">Fornitore registrato con successo!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <h2>Lista Fornitori</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ragione Sociale</th>
                        <th>Partita IVA</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Indirizzo</th>
                        <th>Data Registrazione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fornitori as $fornitore): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fornitore['id']); ?></td>
                        <td><?php echo htmlspecialchars($fornitore['ragione_sociale']); ?></td>
                        <td><?php echo htmlspecialchars($fornitore['partita_iva']); ?></td>
                        <td><?php echo htmlspecialchars($fornitore['email']); ?></td>
                        <td><?php echo htmlspecialchars($fornitore['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($fornitore['indirizzo']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($fornitore['data_registrazione'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <h2>Registra Nuovo Fornitore</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="aggiungi">
                
                <div class="form-group">
                    <label for="ragione_sociale">Ragione Sociale:</label>
                    <input type="text" id="ragione_sociale" name="ragione_sociale" required>
                </div>

                <div class="form-group">
                    <label for="partita_iva">Partita IVA:</label>
                    <input type="text" id="partita_iva" name="partita_iva" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Telefono:</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>

                <div class="form-group">
                    <label for="indirizzo">Indirizzo:</label>
                    <textarea id="indirizzo" name="indirizzo" rows="3"></textarea>
                </div>

                <button type="submit" class="btn">Registra Fornitore</button>
            </form>
        </div>
    </main>
</body>
</html>