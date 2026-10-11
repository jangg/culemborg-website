<?php
/**
 * aanmelden.php - aanmeldformulier zonder database
 *
 * De aanmelding gaat per mail naar de organisatie; de deelnemer krijgt een
 * bevestiging met agendabestand. Er wordt niets opgeslagen.
 */

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/t-config.php';
require_once __DIR__ . '/includes/t-functies.php';

// Token en tijdstempel voor deze formulierweergave
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
if (empty($_SESSION['formulier_geopend'])) {
    $_SESSION['formulier_geopend'] = time();
}
if (!isset($_SESSION['aantal_verzonden'])) {
    $_SESSION['aantal_verzonden'] = 0;
}

$fouten   = array();
$gelukt   = false;
$gegevens = array('naam' => '', 'email' => '', 'telefoon' => '', 'aantal' => 1, 'opmerking' => '');

// ---------------------------------------------------------------------------
// Verwerking
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $honey = isset($_POST['website']) ? trim($_POST['website']) : '';
    $duur  = time() - (int) $_SESSION['formulier_geopend'];

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $fouten['algemeen'] = 'Het formulier is verlopen. Vul het opnieuw in.';

    } elseif ($honey !== '') {
        // Bot: doe alsof het gelukt is, maar verstuur niets.
        $gelukt = true;

    } elseif ($duur < MIN_INVULTIJD) {
        $fouten['algemeen'] = 'Het formulier is te snel verzonden. Probeer het nog een keer.';

    } elseif ($_SESSION['aantal_verzonden'] >= MAX_PER_BEZOEK) {
        $fouten['algemeen'] = 'Er zijn al meerdere aanmeldingen verstuurd vanaf deze computer. '
                            . 'Bel ons even als er nog iemand bij moet.';

    } elseif (!aanmeldenKan()) {
        $fouten['algemeen'] = 'Aanmelden kan niet meer.';

    } else {
        $resultaat = leesFormulier($_POST);
        $gegevens  = $resultaat['gegevens'];
        $fouten    = $resultaat['fouten'];

        if (count($fouten) === 0) {
            schrijfNaarLogbestand($gegevens);

            if (stuurAanmeldingNaarOrganisatie($gegevens)) {
                stuurBevestiging($gegevens);   // mislukt deze, dan is de aanmelding er nog steeds
                $gelukt = true;
                $_SESSION['aantal_verzonden']++;
                unset($_SESSION['csrf_token'], $_SESSION['formulier_geopend']);
            } else {
                $fouten['algemeen'] = 'De aanmelding kon niet worden verstuurd. '
                    . 'Probeer het straks nog eens'
                    . (CONTACT_TELEFOON !== '' ? ', of bel ' . CONTACT_TELEFOON : '') . '.';
            }
        }
    }
}

/** Toont de foutmelding onder een veld. */
function fout($veld, $fouten)
{
    if (!isset($fouten[$veld])) {
        return '';
    }
    return '<div class="invalid-feedback d-block">' . h($fouten[$veld]) . '</div>';
}

$whatsappLink = 'https://wa.me/' . WHATSAPP_NUMMER . '?text=' . rawurlencode(WHATSAPP_TEKST);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<?php include_once 'includes/head.php'; ?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aanmelden - <?php echo h(BIJEENKOMST_TITEL); ?> | <?php echo h(SITE_NAAM); ?></title>
<meta name="description" content="Meld je aan voor <?php echo h(BIJEENKOMST_TITEL); ?>.">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Honeypot: onzichtbaar voor mensen, wel invulbaar voor bots. */
    .hp-veld { position: absolute; left: -5000px; top: -5000px; }
    /* Ruimere raakvlakken; veel bezoekers vullen dit op de telefoon in. */
    .form-control, .form-select { min-height: 48px; font-size: 1.05rem; }
    .btn-lg { min-height: 52px; }
</style>
</head>
<body class="bg-light">
<?php include_once ('includes/header.php'); ?>

<main class="container my-4 my-md-5" style="max-width: 640px;">

<?php if ($gelukt): ?>

    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5 text-center">
            <h1 class="h3 mb-3">Je bent aangemeld</h1>
            <p class="mb-3">
                Er staat een bevestiging in je mailbox, met een agendabestand erbij.
                Zie je hem niet? Kijk dan even in de map ongewenste e-mail.
            </p>
            <p class="mb-4">
                <strong><?php echo h(BIJEENKOMST_TITEL); ?></strong><br>
                <?php echo h(datumTekst()); ?><br>
                <?php echo h(locatieTekst()); ?>
            </p>
            <?php if (CONTACT_NAAM !== ''): ?>
                <p class="text-muted mb-0">
                    Vragen? Bel of mail <?php echo h(CONTACT_NAAM); ?><?php
                        if (CONTACT_TELEFOON !== '') { echo ' op ' . h(CONTACT_TELEFOON); } ?>.
                </p>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <div class="card shadow-lg">
        <div class="card-body p-4 p-md-5" style="background-color: rgba(219, 219, 219, 1);">

            <h1 class="h3 mb-2">Aanmelden</h1>
            <p class="lead mb-3"><?php echo h(BIJEENKOMST_TITEL); ?></p>

            <dl class="row mb-4">
                <dt class="col-4 col-sm-3">Wanneer</dt>
                <dd class="col-8 col-sm-9"><?php echo h(datumTekst()); ?></dd>
                <dt class="col-4 col-sm-3">Waar</dt>
                <dd class="col-8 col-sm-9"><?php echo h(locatieTekst()); ?></dd>
            </dl>

            <?php if (BIJEENKOMST_OMSCHRIJVING !== ''): ?>
                <p><?php echo nl2br(h(BIJEENKOMST_OMSCHRIJVING)); ?></p>
            <?php endif; ?>

            <?php if (!aanmeldenKan()): ?>

                <div class="alert alert-warning mb-0">
                    <strong>Aanmelden kan niet meer.</strong>
                    Kom je toch graag? Neem dan even contact op.
                    <?php if (CONTACT_TELEFOON !== ''): ?>
                        <br><?php echo h(CONTACT_NAAM); ?>, <?php echo h(CONTACT_TELEFOON); ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <?php if (isset($fouten['algemeen'])): ?>
                    <div class="alert alert-danger"><?php echo h($fouten['algemeen']); ?></div>
                <?php endif; ?>

                <form method="post" action="t-aanmelden.php" novalidate>

                    <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

                    <div class="hp-veld" aria-hidden="true">
                        <label for="website">Website (niet invullen)</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label for="naam" class="form-label fw-semibold">Naam</label>
                        <input type="text" class="form-control<?php echo isset($fouten['naam']) ? ' is-invalid' : ''; ?>"
                               id="naam" name="naam" maxlength="100" required
                               autocomplete="name" autocapitalize="words"
                               value="<?php echo h($gegevens['naam']); ?>">
                        <?php echo fout('naam', $fouten); ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mailadres</label>
                        <input type="email" class="form-control<?php echo isset($fouten['email']) ? ' is-invalid' : ''; ?>"
                               id="email" name="email" maxlength="150" required
                               autocomplete="email" inputmode="email"
                               value="<?php echo h($gegevens['email']); ?>">
                        <div class="form-text">Hier sturen we de bevestiging naartoe.</div>
                        <?php echo fout('email', $fouten); ?>
                    </div>

                    <div class="mb-3">
                        <label for="telefoon" class="form-label fw-semibold">
                            Telefoon <span class="fw-normal text-muted">(mag je overslaan)</span>
                        </label>
                        <input type="tel" class="form-control<?php echo isset($fouten['telefoon']) ? ' is-invalid' : ''; ?>"
                               id="telefoon" name="telefoon" maxlength="30"
                               autocomplete="tel" inputmode="tel"
                               value="<?php echo h($gegevens['telefoon']); ?>">
                        <?php echo fout('telefoon', $fouten); ?>
                    </div>

                    <div class="mb-3">
                        <label for="aantal" class="form-label fw-semibold">Met hoeveel personen kom je?</label>
                        <select class="form-select<?php echo isset($fouten['aantal']) ? ' is-invalid' : ''; ?>"
                                id="aantal" name="aantal">
                            <?php for ($i = 1; $i <= MAX_PERSONEN; $i++): ?>
                                <option value="<?php echo $i; ?>"
                                    <?php echo ((int) $gegevens['aantal'] === $i) ? ' selected' : ''; ?>>
                                    <?php echo $i; ?><?php echo $i === 1 ? ' persoon' : ' personen'; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <?php echo fout('aantal', $fouten); ?>
                    </div>

                    <div class="mb-4">
                        <label for="opmerking" class="form-label fw-semibold">
                            Wil je nog iets kwijt? <span class="fw-normal text-muted">(mag je overslaan)</span>
                        </label>
                        <textarea class="form-control<?php echo isset($fouten['opmerking']) ? ' is-invalid' : ''; ?>"
                                  id="opmerking" name="opmerking" rows="2" maxlength="500"><?php
                            echo h($gegevens['opmerking']); ?></textarea>
                        <div class="form-text">Bijvoorbeeld een vraag vooraf, of dat je slecht ter been bent.</div>
                        <?php echo fout('opmerking', $fouten); ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Meld mij aan</button>

                    <p class="form-text mt-3 mb-0">
                        We gebruiken je gegevens alleen voor deze bijeenkomst.
                        Je krijgt geen nieuwsbrief van ons.
                    </p>
                </form>

                <hr class="my-4">

                <p class="mb-2">Liever niet dit formulier?</p>
                <a class="btn btn-outline-success w-100 mb-2" href="<?php echo h($whatsappLink); ?>">
                    Aanmelden via WhatsApp
                </a>
                <?php if (CONTACT_TELEFOON !== ''): ?>
                    <a class="btn btn-outline-secondary w-100"
                       href="tel:<?php echo h(preg_replace('/[^0-9+]/', '', CONTACT_TELEFOON)); ?>">
                        Bel <?php echo h(CONTACT_TELEFOON); ?>
                    </a>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>

<?php endif; ?>

    <!-- <p class="text-center text-muted small mt-4 mb-0"><?php // echo h(SITE_NAAM); ?></p> -->

</main>
<?php include_once 'includes/footer.php'; ?>

</body>
</html>
