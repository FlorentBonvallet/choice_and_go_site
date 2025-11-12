<?php
$page_title = "Inscription — Choice&Go";
include __DIR__ . "/includes/header.php";
?>
<section class="container auth">
  <h1>INSCRIPTION</h1>
  <form class="auth-form" method="post" action="/register.php">
    <label>Adresse mail :
      <input type="email" name="email" placeholder="Ex : martin.dupont@gmail.com" required />
    </label>
    <label>Prénom :
      <input type="text" name="firstname" placeholder="Ex : martin" required />
    </label>
    <label>Nom :
      <input type="text" name="lastname" placeholder="Ex : dupont" required />
    </label>
    <label>Date de naissance :
      <input type="date" name="birthdate" required />
    </label>
    <label>Mot de passe :
      <input type="password" name="password" placeholder="Ex : R95c@sd?4" required />
    </label>
    <label class="checkbox">
      <input type="checkbox" name="tos" required /> J'accepte les <a href="#">conditions d'utilisation</a>
    </label>
    <button class="btn-primary" type="submit">Valider</button>

    <?php if (!empty($_POST)): ?>
      <p class="flash success">Compte créé (démo). Bienvenue, <?php echo htmlspecialchars($_POST['firstname']); ?> !</p>
    <?php endif; ?>
  </form>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
