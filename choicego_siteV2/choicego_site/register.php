<?php
session_start();

$page_title = "Inscription — Choice&Go";
include __DIR__ . "/includes/db.php";
include __DIR__ . "/includes/header.php";

// Generate a simple math captcha for GET (and regenerate after failed POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  // choose operator randomly
  $op = random_int(0, 1) === 0 ? '+' : '-';
  $a = random_int(1, 9);
  $b = random_int(1, 9);
  if ($op === '+') {
    $_SESSION['captcha_answer'] = $a + $b;
    $_SESSION['captcha_question'] = "{$a} + {$b} = ?";
  } else {
    // subtraction: a and b are independent so result can be negative or positive
    $_SESSION['captcha_answer'] = $a - $b;
    $_SESSION['captcha_question'] = "{$a} - {$b} = ?";
  }
}

// Compute relative path for redirects
function getRelativePath($target) {
  $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  return ($baseDir === '' || $baseDir === '.') ? '/' . $target : $baseDir . '/' . $target;
}
?>
<section class="container auth">
  <h1>INSCRIPTION</h1>
  <form class="auth-form" method="post" action="register.php" novalidate>
    <label>Adresse mail :
      <input type="email" name="email" placeholder="Ex : martin.dupont@gmail.com" required />
    </label>
    <label>Prénom :
      <input type="text" name="firstname" placeholder="Ex : martin" required />
    </label>
    <label>Nom :
      <input type="text" name="lastname" placeholder="Ex : dupont" required />
    </label>

    <label>Téléphone :
      <input type="tel" name="telephone" placeholder="Ex : 0601020304" />
    </label>

    <label>Date de naissance :
      <!-- Native date input (shown when supported) -->
      <input type="date" id="birthdate-native" class="birthdate-native" />

      <!-- Text fallback (shown when native date not supported) -->
      <input type="text" id="birthdate-text" class="birthdate-text" placeholder="JJ/MM/AAAA" inputmode="numeric" />

      <!-- Hidden ISO value that will be submitted as 'birthdate' -->
      <input type="hidden" name="birthdate" id="birthdate-hidden" />
    </label>

    <label>Mot de passe :
      <div class="password-field">
        <input type="password" name="password" placeholder="Ex : R95c@sd?4" required />
        <button type="button" class="toggle-eye" aria-label="Afficher le mot de passe">👁️</button>
      </div>
    </label>

    <label class="checkbox">
      <input type="checkbox" name="tos" required />
      J'accepte les <a href="#" id="show-terms">conditions d'utilisation</a>
    </label>

    <!-- simple math captcha (server-validated) -->
    <label>
      <span id="captcha-question"><?php echo htmlspecialchars($_SESSION['captcha_question'] ?? ''); ?></span>
      <input type="text" name="captcha" placeholder="Réponse" inputmode="numeric" required />
    </label>

    <button class="btn-primary" type="submit">Valider</button>

    <?php
    // POST handling with captcha validation
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $email = trim($_POST['email'] ?? '');
      $firstname = trim($_POST['firstname'] ?? '');
      $lastname = trim($_POST['lastname'] ?? '');
      $telephone = trim($_POST['telephone'] ?? '');
      $password = $_POST['password'] ?? '';
      $birthdate = $_POST['birthdate'] ?? ''; // ISO YYYY-MM-DD if provided by JS
      $captcha = trim($_POST['captcha'] ?? '');

      // validate captcha — accept negative answers as well
      $captcha_val = null;
      if (preg_match('/^-?\d+$/', $captcha)) {
        $captcha_val = (int)$captcha;
      }
      $captcha_ok = isset($_SESSION['captcha_answer']) && $captcha_val !== null && $captcha_val === $_SESSION['captcha_answer'];

      if (! $captcha_ok) {
        echo '<p class="flash error">Captcha incorrect — veuillez réessayer.</p>';
        // regenerate captcha for the next try
        $op = random_int(0, 1) === 0 ? '+' : '-';
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        if ($op === '+') {
          $_SESSION['captcha_answer'] = $a + $b;
          $_SESSION['captcha_question'] = "{$a} + {$b} = ?";
        } else {
          $_SESSION['captcha_answer'] = $a - $b;
          $_SESSION['captcha_question'] = "{$a} - {$b} = ?";
        }
        echo '<script>document.getElementById("captcha-question").textContent = ' . json_encode($_SESSION['captcha_question']) . ';</script>';
      } else {
        // basic validation
        if ($email && $firstname && $lastname && $password) {
          // check unique email
          $stmt = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = :email');
          $stmt->execute([':email' => $email]);
          if ($stmt->fetchColumn() == 0) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Insert with optional phone number
            $ins = $pdo->prepare('INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe, telephone) VALUES (:prenom, :nom, :email, :pwd, :telephone)');
            $ins->execute([
              ':prenom' => $firstname,
              ':nom'   => $lastname,
              ':email' => $email,
              ':pwd'   => $hash,
              ':telephone' => $telephone ?: null
            ]);

            // Log the user in and redirect to profile
            $newUserId = (int) $pdo->lastInsertId();
            if ($newUserId > 0) {
              $_SESSION['user_id'] = $newUserId;
            }

            // clear used captcha
            unset($_SESSION['captcha_answer'], $_SESSION['captcha_question']);

            // Redirect to profile page with relative path to preserve subfolder
            $profilePath = getRelativePath('profile.php');
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $fullUrl = $scheme . '://' . $host . $profilePath;
            
            echo '<script>location.href = ' . json_encode($fullUrl) . ';</script>';
            // In case JS is disabled, show success message with a link
            echo '<noscript><p class="flash success">Compte créé. <a href="' . htmlspecialchars($profilePath, ENT_QUOTES) . '">Accéder au profil</a></p></noscript>';
            // stop further output
            exit;
          } else {
            echo '<p class="flash error">Cet e‑mail est déjà utilisé.</p>';
            // regenerate captcha to avoid replay
            $op = random_int(0, 1) === 0 ? '+' : '-';
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            if ($op === '+') {
              $_SESSION['captcha_answer'] = $a + $b;
              $_SESSION['captcha_question'] = "{$a} + {$b} = ?";
            } else {
              $_SESSION['captcha_answer'] = $a - $b;
              $_SESSION['captcha_question'] = "{$a} - {$b} = ?";
            }
            echo '<script>document.getElementById("captcha-question").textContent = ' . json_encode($_SESSION['captcha_question']) . ';</script>';
          }
        }
      }
    }
    ?>
  </form>
</section>

<!-- TERMS modal -->
<div id="terms-modal" class="modal" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;">
  <div class="modal-overlay" data-close="true" style="position:fixed;inset:0;background:rgba(0,0,0,0.5)"></div>
  <div class="modal-content" role="document" style="position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);max-width:720px;background:#fff;padding:1.5rem;border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.2);">
    <button class="modal-close" aria-label="Fermer" style="float:right">✕</button>
    <h2>Conditions d'utilisation</h2>
    <p>
      <!-- Short placeholder terms; replace with the real terms -->
      En vous inscrivant, vous acceptez les présentes conditions d'utilisation de Choice&Go. Les données saisies sont
      utilisées pour la gestion de votre compte. Veuillez lire attentivement.
    </p>
    <div style="max-height:50vh;overflow:auto;margin-top:1rem;">
      <p>— Exemple d'article 1</p>
      <p>— Exemple d'article 2</p>
      <p>Remplacez ce texte par vos mentions légales complètes.</p>
    </div>
    <div style="text-align:right;margin-top:1rem;">
      <button class="btn-primary modal-close modal-accept" data-accept="true">J'ai lu et j'accepte</button>
    </div>
  </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
