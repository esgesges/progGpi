<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validazione
    if (empty($username)) {
        $errors[] = "Il nome utente è obbligatorio";
    }
    if (empty($cognome)) {
        $errors[] = "Il cognome è obbligatorio";
    }
    if (empty($email)) {
        $errors[] = "L'email è obbligatoria";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Formato email non valido";
    }
    if (empty($password)) {
        $errors[] = "La password è obbligatoria";
    } elseif (strlen($password) < 6) {
        $errors[] = "La password deve essere di almeno 6 caratteri";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Le password non coincidono";
    }

    // Verifica se l'username o l'email esistono già
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username o email già in uso";
        }
    }

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, cognome, email, password, role) VALUES (:username, :cognome, :email, :password, 'user')");
            $stmt->execute([
                ':username' => $username,
                ':cognome' => $cognome,
                ':email' => $email,
                ':password' => $hashed_password
            ]);
            $_SESSION['success'] = "Registrazione completata con successo! Ora puoi effettuare il login.";
            header("Location: login.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Errore durante la registrazione: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Sistema ERP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Registrazione</h1>
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nome:</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="cognome">Cognome:</label>
                    <input type="text" id="cognome" name="cognome" value="<?php echo isset($_POST['cognome']) ? htmlspecialchars($_POST['cognome']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Conferma Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn">Registrati</button>
            </form>
            <p class="auth-link">Hai già un account? <a href="login.php">Accedi</a></p>
        </div>
    </div>
</body>
</html> 