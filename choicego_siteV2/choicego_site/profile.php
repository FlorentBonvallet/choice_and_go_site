<?php
session_start();

// Define the secret pepper value (same as in register.php and login.php)
define('PASSWORD_PEPPER', 's3cureP3pp3r!@#');

// Hash a password by adding the pepper before hashing
function hashPasswordWithPepper($password)
{
    return password_hash($password . PASSWORD_PEPPER, PASSWORD_DEFAULT);
}

// Verify a password by adding the pepper before checking
function verifyPasswordWithPepper($password, $hash)
{
    return password_verify($password . PASSWORD_PEPPER, $hash);
}

$flash = '';
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

if (!$user) {
    session_unset();
    session_destroy();
    $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';
    header('Location: ' . $location);
    exit;
}

// Fetch user vehicles
$vehiclesStmt = $pdo->prepare('SELECT vehicule_id, marque, modele, couleur, immatriculation FROM vehicules WHERE utilisateur_id = :uid');
$vehiclesStmt->execute([':uid' => $userId]);
$vehicles = $vehiclesStmt->fetchAll();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- UPDATE PROFILE ---
    if (isset($_POST['update_profile'])) {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        if ($firstname === '' || $lastname === '' || $email === '') {
            $flash = '<p class="flash error">Tous les champs du profil sont requis.</p>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $flash = '<p class="flash error">Adresse e‑mail invalide.</p>';
        } else {
            $chk = $pdo->prepare('SELECT COUNT(*) FROM utilisateurs WHERE email = :email AND utilisateur_id <> :id');
            $chk->execute([':email' => $email, ':id' => $userId]);
            if ($chk->fetchColumn() > 0) {
                $flash = '<p class="flash error">Cet e‑mail est déjà utilisé.</p>';
            } else {
                $up = $pdo->prepare('UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email, telephone = :telephone WHERE utilisateur_id = :id');
                $up->execute([
                    ':prenom' => $firstname,
                    ':nom' => $lastname,
                    ':email' => $email,
                    ':telephone' => $telephone ?: null,
                    ':id' => $userId,
                ]);
                $flash = '<p class="flash success">Profil mis à jour.</p>';

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
            if ($current === '' || $new === '' || $new_confirm === '') {
                $flash .= '<p class="flash error">Pour changer le mot de passe, remplissez tous les champs.</p>';
            } elseif ($new !== $new_confirm) {
                $flash .= '<p class="flash error">Le nouveau mot de passe et sa confirmation ne correspondent pas.</p>';
            } else {
                $pwStmt = $pdo->prepare('SELECT mot_de_passe FROM utilisateurs WHERE utilisateur_id = :id');
                $pwStmt->execute([':id' => $userId]);
                $row = $pwStmt->fetch();
                $hash = $row['mot_de_passe'] ?? '';

                // Use pepper for password verification
                if (!$hash || !verifyPasswordWithPepper($current, $hash)) {
                    $flash .= '<p class="flash error">Mot de passe actuel incorrect.</p>';
                } else {
                    // Use pepper for password hashing
                    $newHash = hashPasswordWithPepper($new);
                    $upd = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = :pwd WHERE utilisateur_id = :id');
                    $upd->execute([':pwd' => $newHash, ':id' => $userId]);
                    $flash .= '<p class="flash success">Mot de passe mis à jour.</p>';
                }
            }
        }
    }

    // --- ADD VEHICLE ---
    if (isset($_POST['add_vehicle'])) {
        $marque = trim($_POST['marque'] ?? '');
        $modele = trim($_POST['modele'] ?? '');
        $couleur = trim($_POST['couleur'] ?? '');
        $immatriculation = trim($_POST['immatriculation'] ?? '');

        if ($marque === '' || $modele === '' || $immatriculation === '') {
            $flash .= '<p class="flash error">Tous les champs obligatoires du véhicule doivent être remplis.</p>';
        } else {
            // Vérification d'immatriculation existante
            $chk = $pdo->prepare('SELECT COUNT(*) FROM vehicules WHERE immatriculation = :imm');
            $chk->execute([':imm' => $immatriculation]);
            if ($chk->fetchColumn() > 0) {
                $flash .= '<p class="flash error">Un véhicule avec cette immatriculation existe déjà.</p>';
            } else {
                $stmt = $pdo->prepare('INSERT INTO vehicules (utilisateur_id, marque, modele, couleur, immatriculation) VALUES (:uid, :marque, :modele, :couleur, :imm)');
                $stmt->execute([
                    ':uid' => $userId,
                    ':marque' => $marque,
                    ':modele' => $modele,
                    ':couleur' => $couleur ?: null,
                    ':imm' => $immatriculation
                ]);
                $flash .= '<p class="flash success">Véhicule ajouté avec succès.</p>';

                // Recharger la liste des véhicules après insertion
                $vehiclesStmt = $pdo->prepare('SELECT vehicule_id, marque, modele, couleur, immatriculation FROM vehicules WHERE utilisateur_id = :uid');
                $vehiclesStmt->execute([':uid' => $userId]);
                $vehicles = $vehiclesStmt->fetchAll();
            }
        }
    }

    // --- DELETE VEHICLE ---
    if (isset($_POST['delete_vehicle'])) {
        $vehiculeId = (int) ($_POST['vehicule_id'] ?? 0);

        // Verify the vehicle belongs to the current user
        $checkStmt = $pdo->prepare('SELECT utilisateur_id, marque, modele FROM vehicules WHERE vehicule_id = :id');
        $checkStmt->execute([':id' => $vehiculeId]);
        $vehicule = $checkStmt->fetch();

        if ($vehicule && $vehicule['utilisateur_id'] == $userId) {
            try {
                // Check if vehicle is used in any active rides
                $checkRidesStmt = $pdo->prepare('
                    SELECT COUNT(*) as nb_rides
                    FROM trajets
                    WHERE vehicule_id = :vid AND date_heure_depart > NOW()
                ');
                $checkRidesStmt->execute([':vid' => $vehiculeId]);
                $nbRides = (int) $checkRidesStmt->fetchColumn();

                if ($nbRides > 0) {
                    $flash .= '<p class="flash error">Impossible de supprimer ce véhicule : il est utilisé dans ' . $nbRides . ' trajet(s) futur(s).</p>';
                } else {
                    // Safe to delete
                    $deleteStmt = $pdo->prepare('DELETE FROM vehicules WHERE vehicule_id = :id');
                    $deleteStmt->execute([':id' => $vehiculeId]);
                    $flash .= '<p class="flash success">Véhicule supprimé avec succès.</p>';

                    // Recharger la liste des véhicules
                    $vehiclesStmt->execute([':uid' => $userId]);
                    $vehicles = $vehiclesStmt->fetchAll();
                }
            } catch (Exception $e) {
                $flash .= '<p class="flash error">Erreur lors de la suppression : ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            $flash .= '<p class="flash error">Véhicule non trouvé ou accès non autorisé.</p>';
        }
    }
}
?>

<section class="container auth">
    <h1>MON PROFIL</h1>

    <?php if (!empty($flash))
        echo $flash; ?>

    <form class="auth-form" method="post" action="profile.php" novalidate>
        <input type="hidden" name="update_profile" value="1" />

        <label>
            Prénom :
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['prenom']); ?>" required />
        </label>

        <label>
            Nom :
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['nom']); ?>" required />
        </label>

        <label>
            Adresse mail :
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
        </label>

        <label>
            Téléphone :
            <input type="tel" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" />
        </label>

        <fieldset style="margin-top:1rem;border:1px solid #eee;padding:0.75rem;">
            <legend>Changer le mot de passe (optionnel)</legend>
            <label>
                Mot de passe actuel :
                <input type="password" name="current_password" autocomplete="current-password" />
            </label>
            <label>
                Nouveau mot de passe :
                <input type="password" name="new_password" autocomplete="new-password" />
            </label>
            <label>
                Confirmer le nouveau mot de passe :
                <input type="password" name="new_password_confirm" autocomplete="new-password" />
            </label>
        </fieldset>

        <div style="display:flex;gap:0.5rem;margin-top:1rem;">
            <button class="btn-primary" type="submit">Enregistrer</button>
            <a class="btn-secondary" href="logout.php" style="align-self:center;padding:0.5rem 0.75rem;text-decoration:none;">Déconnexion</a>
        </div>
    </form>

    <!-- Vehicles section -->
    <form class="auth-form" method="post" action="profile.php" novalidate style="margin-top:5rem">
        <section style="margin-top:2rem;">
            <h1>Mes véhicules</h1>

            <?php if (count($vehicles) === 0): ?>
                <p>Aucun véhicule enregistré.</p>
            <?php else: ?>
                <div class="table-scroll">
                    <table class="vehicles-table">
                        <thead>
                            <tr>
                                <th>Marque</th>
                                <th>Modèle</th>
                                <th>Couleur</th>
                                <th>Immatriculation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicles as $v): ?>
                                <tr>
                                    <td><?= htmlspecialchars($v['marque']) ?></td>
                                    <td><?= htmlspecialchars($v['modele']) ?></td>
                                    <td><?= htmlspecialchars($v['couleur']) ?></td>
                                    <td><?= htmlspecialchars($v['immatriculation']) ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn-delete"
                                            data-type="vehicle"
                                            data-id="<?= $v['vehicule_id'] ?>"
                                            data-message="Voulez-vous vraiment supprimer ce véhicule ?"> Supprimer
                                        </button>

                                        <div class="popup-supr" id="confirm-popup">
                                            <div class="popup-content-supr">
                                                <p id="popup-message"></p>

                                                <div class="popup-actions">
                                                    <button type="button" id="popup-confirm">Oui</button>
                                                    <button type="button" id="popup-cancel">Annuler</button>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <form method="post" action="profile.php" style="margin-top:1rem;">
                <input type="hidden" name="add_vehicle" value="1" />

                <label>Marque : <input type="text" name="marque" required /></label>
                <label>Modèle : <input type="text" name="modele" required /></label>
                <label>Couleur : <input type="text" name="couleur" /></label>
                <label>Immatriculation : <input type="text" name="immatriculation" required /></label>

                <button class="btn-primary" type="submit" name="add_vehicle" value="1" style="margin-top:0.5rem;">Ajouter le véhicule</button>
            </form>
        </section>
    </form>
</section>



<!-- Hidden form for vehicle deletion -->
<form method="post" id="delete-vehicle-form" style="display: none;">
    <input type="hidden" name="delete_vehicle" value="1" />
    <input type="hidden" name="vehicule_id" id="delete-vehicle-id" value="" />
</form>



<?php include __DIR__ . "/includes/footer.php"; ?>
