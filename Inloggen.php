<?php
require "db.php";

$fouten = [];
$succes = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $wachtwoord = $_POST["wachtwoord"] ?? "";

    // Lege invoer
    if ($email === "") {
        $fouten[] = "Vul je e-mailadres in.";
    }
    if ($wachtwoord === "") {
        $fouten[] = "Vul je wachtwoord in.";
    }

    if (empty($fouten)) {
        try {
            $db = getDb();
            $stmt = $db->prepare("SELECT wachtwoord_hash FROM gebruikers WHERE email = :email");
            $stmt->execute([":email" => $email]);
            $gebruiker = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifiëren: vergelijk het ingevoerde wachtwoord met de hash
            if ($gebruiker && password_verify($wachtwoord, $gebruiker["wachtwoord_hash"])) {
                $succes = "Welkom terug! Je bent ingelogd.";
            } else {
                // Bewust één melding: we verklappen niet of het e-mailadres bestaat
                $fouten[] = "E-mailadres of wachtwoord is onjuist.";
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $fouten[] = "Er ging iets mis bij het inloggen. Probeer het later opnieuw.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen</title>
</head>
<body>
    <h1>Inloggen</h1>

    <?php foreach ($fouten as $fout): ?>
        <p style="color:red"><?= htmlspecialchars($fout) ?></p>
    <?php endforeach; ?>

    <?php if ($succes): ?>
        <p style="color:green"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <form method="post">
        <p><label>E-mail:<br>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
        </label></p>
        <p><label>Wachtwoord:<br>
            <input type="password" name="wachtwoord">
        </label></p>
        <button type="submit">Inloggen</button>
    </form>

    <p>Nog geen account? <a href="registreren.php">Registreren</a></p>
</body>
</html>