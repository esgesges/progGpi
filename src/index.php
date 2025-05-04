<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema ERP</title>
    <link rel="stylesheet" href="css/style.css">
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
        <h1>Benvenuto nel Sistema ERP</h1>
        <div class="dashboard">
            <div class="card">
                <h2>Contabilità</h2>
                <p>Gestisci i movimenti contabili e monitora le finanze</p>
                <a href="modules/contabilita/index.php" class="btn">Contabilità</a>
            </div>
            <div class="card">
                <h2>Magazzino</h2>
                <p>Controlla le giacenze e gestisci i prodotti</p>
                <a href="modules/magazzino/index.php" class="btn">Magazzino</a>
            </div>
            <div class="card">
                <h2>Clienti</h2>
                <p>Gestisci l'anagrafica e le relazioni con i clienti</p>
                <a href="modules/clienti/index.php" class="btn">Clienti</a>
            </div>
            <div class="card">
                <h2>Fornitori</h2>
                <p>Gestisci i rapporti con i fornitori</p>
                <a href="modules/fornitori/index.php" class="btn">Fornitori</a>
            </div>
            <div class="card">
                <h2>Prodotti</h2>
                <p>Gestisci ivisualizza tutti i prodotti disponibili</p>
                <a href="modules/ordini/prodotti.php" class="btn">Prodotti</a>
            </div>
        </div>
    </main>

</body>
</html>