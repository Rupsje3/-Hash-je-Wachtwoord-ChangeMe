# Login- en registratiesysteem met wachtwoord-hashing (PHP)

Een klein systeem waarmee gebruikers zich kunnen registreren en inloggen.
Wachtwoorden worden nooit leesbaar opgeslagen, maar als hash met `password_hash()`.

## Hoe werkt het?

1. **Registreren** (`registreren.php`): de gebruiker vult e-mail en wachtwoord (2x) in.
   Na de controles wordt het wachtwoord gehashed met `password_hash()` en samen met
   het e-mailadres opgeslagen in een SQLite-database.
2. **Inloggen** (`inloggen.php`): de gebruiker vult opnieuw e-mail en wachtwoord in.
   `password_verify()` vergelijkt het ingevoerde wachtwoord met de opgeslagen hash.
3. **Foutafhandeling**: het programma controleert op:
   - lege invoer en een ongeldig e-mailformaat
   - een wachtwoord korter dan 8 tekens
   - twee wachtwoorden die niet overeenkomen
   - een e-mailadres dat al bestaat (controle in PHP én een `UNIQUE`-regel in de database)
   - fouten bij het hashen en de database (`try`-`catch`)
   - een onjuiste combinatie bij het inloggen (met één algemene melding)

## Installatie

Vereisten: PHP 8 of hoger met de extensie `pdo_sqlite` (staat meestal standaard aan).

```
git clone https://github.com/JOUWNAAM/wachtwoord-hashing.git
cd wachtwoord-hashing
php -S localhost:8000
```

Open daarna `http://localhost:8000/registreren.php`. De database wordt automatisch
aangemaakt in de map `data/`.

## Vragen

**Kun je het oorspronkelijke wachtwoord uit de hash achterhalen?**
Nee. Een hash is een eenrichtingsfunctie: je kunt er wel een hash van maken, maar
niet terugrekenen naar het wachtwoord. Een aanvaller kan alleen wachtwoorden
raden, hashen en kijken of de uitkomst gelijk is (brute force of woordenlijsten).
Bcrypt is daarom bewust langzaam (instelbare "cost"), zodat veel raden erg lang
duurt. Zwakke wachtwoorden zoals `welkom123` zijn alsnog snel te raden. Daarom
eisen we een minimale lengte.

**Maken `password_hash()` / `bcrypt` gebruik van een salt?**
Ja. `password_hash()` genereert automatisch een willekeurige salt en bewaart die
in de hash zelf. Een bcrypt-hash heeft dit formaat:

```
$2y$10$ + 22 tekens salt + 31 tekens hash
```

Daardoor krijgen twee gebruikers met hetzelfde wachtwoord toch een andere hash,
en werken kant-en-klare rainbow tables niet. `password_verify()` haalt de salt
zelf uit de hash. Zelf een salt toevoegen is dus niet nodig en wordt zelfs
afgeraden, omdat dat de veiligheid kan verslechteren.

## Bronnen

- https://www.php.net/manual/en/function.password-hash.php
- https://auth0.com/blog/hashing-in-action-understanding-bcrypt/