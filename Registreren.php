<?php
require "db.php";

$fouten = [];
$succes = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $wachtwoord = $_POST["wachtwoord"] ?? "";
    $herhaling = $_POST["wachtwoord_herhaling"] ?? "";

    // Validatie: lege invoer en formaat
    if ($email === "") {
        $fouten[] = "Vul een e-mailadres in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fouten[] = "Dit is geen geldig e-mailadres.";
    }

    if ($wachtwoord === "") {
        $fouten[] = "Vul een wachtwoord in.";
    } elseif (strlen($wachtwoord) < 8) {
        $fouten[] = "Het wachtwoord moet minimaal 8 tekens lang zijn.";
    }

    if ($herhaling === "") {
        $fouten[] = "Vul het wachtwoord een tweede keer in.";
    } elseif ($wachtwoord !== $herhaling) {
        $fouten[] = "De twee wachtwoorden komen niet overeen.";
    }

    if (empty($fouten)) {
        try {
            $db = getDb();

            // Dubbele gegevens: bestaat dit e-mailadres al?
            $stmt = $db->prepare("SELECT id FROM gebruikers WHERE email = :email");
            $stmt->execute([":email" => $email]);

            if ($stmt->fetch()) {
                $fouten[] = "Dit e-mailadres is al geregistreerd.";
            } else {
                // Hashen (salt wordt automatisch toegevoegd)
                $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
                if ($hash === false) {
                    throw new RuntimeException("Het hashen van het wachtwoord is mislukt.");
                }

                $stmt = $db->prepare(
                    "INSERT INTO gebruikers (email, wachtwoord_hash) VALUES (:email, :hash)"
                );
                $stmt->execute([":email" => $email, ":hash" => $hash]);

                $succes = "Registratie gelukt! Je kunt nu inloggen.";
                $email = "";
            }
        } catch (PDOException $e) {
            // 23000 = unieke sleutel geschonden (twee gelijktijdige registraties)
            if ($e->getCode() === "23000") {
                $fouten[] = "Dit e-mailadres is al geregistreerd.";
            } else {
                error_log($e->getMessage());
                $fouten[] = "Er ging iets mis met de database. Probeer het later opnieuw.";
            }
        } catch (Throwable $e) {
            // Vangt o.a. hashfouten (ValueError, RuntimeException)
            error_log($e->getMessage());
            $fouten[] = "Er ging iets mis bij het opslaan van je wachtwoord. Probeer het opnieuw.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren</title>
</head>
<body>
    <h1>Registreren</h1>

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
        <p><label>Wachtwoord herhalen:<br>
            <input type="password" name="wachtwoord_herhaling">
        </label></p>
        <button type="submit">Registreren</button>
    </form>

    <p>Al een account? <a href="inloggen.php">Inloggen</a></p>
</body>
</html>