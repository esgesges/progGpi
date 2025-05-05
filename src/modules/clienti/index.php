<?php
require_once '../../database/config.php';

// Funzione per ottenere tutti i clienti
function getClienti($conn) {
    $stmt = $conn->prepare("SELECT * FROM clienti ORDER BY data_registrazione DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Gestione dell'aggiunta di un nuovo cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'aggiungi') {
        try {
            $stmt = $conn->prepare("INSERT INTO clienti (nome, cognome, email, telefono, indirizzo) 
                                  VALUES (:nome, :cognome, :email, :telefono, :indirizzo)");
            $stmt->execute([
                ':nome' => $_POST['nome'],
                ':cognome' => $_POST['cognome'],
                ':email' => $_POST['email'],
                ':telefono' => $_POST['telefono'],
                ':indirizzo' => $_POST['indirizzo']
            ]);
            header("Location: index.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Errore nella registrazione del cliente: " . $e->getMessage();
        }
    }
}

// Ottenere la lista dei clienti
$clienti = getClienti($conn);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Clienti - Sistema ERP</title>
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
            <div class="logo">
                <img src="../../content/logo.png" alt="Logo Sistema ERP" class="logo-img">
            </div>
            <ul class="menu">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="../contabilita/index.php">Contabilità</a></li>
                <li><a href="../magazzino/index.php">Magazzino</a></li>
                <li><a href="../fornitori/index.php">Fornitori</a></li>
                <li><a href="../ordini/prodotti.php">Prodotti</a></li>
                <li><a href="../ordini/carrello.php">Carrello</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1>Gestione Clienti</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">Cliente registrato con successo!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <h2>Lista Clienti</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Indirizzo</th>
                        <th>Data Registrazione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clienti as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['cognome']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['indirizzo']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($cliente['data_registrazione'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <h2>Registra Nuovo Cliente</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="aggiungi">
                
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="cognome">Cognome:</label>
                    <input type="text" id="cognome" name="cognome" required>
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

                <button type="submit" class="btn">Registra Cliente</button>
            </form>
        </div>
    </main>
</body>
</html>