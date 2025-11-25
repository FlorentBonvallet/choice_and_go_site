<?php
session_start();

$page_title = "Profil — Choice&Go";
include __DIR__ . "/includes/db.php";
include __DIR__ . "/includes/header.php";

// Require logged-in user
if (empty($_SESSION['user_id'])) {
  $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  $location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';
  header('Location: ' . $location);
  exit;
}

$userId = (int) $_SESSION['user_id'];

// Fetch current user
$stmt = $pdo->prepare('SELECT utilisateur_id, prenom, nom, email, telephone FROM utilisateurs WHERE utilisateur_id = :id');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (! $user) {
  // Unknown user in session: logout and redirect to login
  session_unset();
  session_destroy();
  $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  $location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';
  header('Location: ' . $location);
  exit;
}

// Handle POST (update profile and/or change password)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstname = trim($_POST['firstname'] ?? '');
  $lastname = trim($_POST['lastname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $telephone = trim($_POST['telephone'] ?? '');

  // Basic profile validation
  if ($firstname === '' || $lastname === '' || $email === '') {
    $flash = '<p class="flash error">Tous les champs du profil sont requis.</p>';
  } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $flash = '<p class="flash error">Adresse e‑mail invalide.</p>';
  } else {
    // Check email uniqueness (exclude current user)
    $chk = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = :email AND utilisateur_id <> :id');
    $chk->execute([':email' => $email, ':id' => $userId]);
    if ($chk->fetchColumn() > 0) {
      $flash = '<p class="flash error">Cet e‑mail est déjà utilisé.</p>';
    } else {
      // Update profile fields
      $up = $pdo->prepare('UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email, telephone = :telephone WHERE utilisateur_id = :id');
      $up->execute([
        ':prenom' => $firstname,
        ':nom' => $lastname,
        ':email' => $email,
        ':telephone' => $telephone ?: null,
        ':id' => $userId,
      ]);
      $flash = '<p class="flash success">Profil mis à jour.</p>';

      // Update local $user vars for re-render
      $user['prenom'] = $firstname;
      $user['nom'] = $lastname;
      $user['email'] = $email;
      $user['telephone'] = $telephone;
    }
  }

  // Handle password change if requested
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $new_confirm = $_POST['new_password_confirm'] ?? '';

  if ($current !== '' || $new !== '' || $new_confirm !== '') {
    // must provide all fields
    if ($current === '' || $new === '' || $new_confirm === '') {
      $flash .= '<p class="flash error">Pour changer le mot de passe, remplissez tous les champs.</p>';
    } elseif ($new !== $new_confirm) {
      $flash .= '<p class="flash error">Le nouveau mot de passe et sa confirmation ne correspondent pas.</p>';
    } else {
      // Verify current password
      $pwStmt = $pdo->prepare('SELECT mot_de_passe FROM utilisateurs WHERE utilisateur_id = :id');
      $pwStmt->execute([':id' => $userId]);
      $row = $pwStmt->fetch();
      $hash = $row['mot_de_passe'] ?? '';

      if (! $hash || ! password_verify($current, $hash)) {
        $flash .= '<p class="flash error">Mot de passe actuel incorrect.</p>';
      } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = :pwd WHERE utilisateur_id = :id');
        $upd->execute([':pwd' => $newHash, ':id' => $userId]);
        $flash .= '<p class="flash success">Mot de passe mis à jour.</p>';
      }
    }
  }
}
?>
<section class="container auth">
  <h1>MON PROFIL</h1>


  <form class="auth-form" method="post" action="profile.php" novalidate>
    <label>Prénom :
      <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['prenom']); ?>" required />
    </label>

    <label>Nom :
      <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['nom']); ?>" required />
    </label>

    <label>Adresse mail :
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
    </label>

    <label>Téléphone :
      <input type="tel" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" />
    </label>

    <fieldset style="margin-top:1rem;border:1px solid #eee;padding:0.75rem;">
      <legend>Changer le mot de passe (optionnel)</legend>
      <label>Mot de passe actuel :
        <input type="password" name="current_password" autocomplete="current-password" />
      </label>
      <label>Nouveau mot de passe :
        <input type="password" name="new_password" autocomplete="new-password" />
      </label>
      <label>Confirmer le nouveau mot de passe :
        <input type="password" name="new_password_confirm" autocomplete="new-password" />
      </label>
    </fieldset>

    <div style="display:flex;gap:0.5rem;margin-top:1rem;">
      <button class="btn-primary" type="submit">Enregistrer</button>
      <a class="btn-secondary" href="logout.php" style="align-self:center;padding:0.5rem 0.75rem;text-decoration:none;">Déconnexion</a>
    </div>
  </form>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>