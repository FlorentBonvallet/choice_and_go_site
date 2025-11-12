<?php
$page_title = "Connexion — Choice&Go";
include __DIR__ . "/includes/header.php";
?>
<section class="container auth">
  <h1>CONNEXION</h1>
  <form class="auth-form" method="post" action="/login.php">
    <label>Adresse mail :
      <input type="email" name="email" placeholder="Ex : martin.dupont@gmail.com" required />
    </label>
    <label>Mot de passe :
      <div class="password-field">
        <input type="password" name="password" placeholder="Ex : R95c@sd?4" required />
        <button type="button" class="toggle-eye" aria-label="Afficher le mot de passe">👁️</button>
      </div>
    </label>
    <p class="help"><a href="#">Mot de passe oublié ?</a></p>
    <button class="btn-primary" type="submit">Connexion</button>
  </form>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
