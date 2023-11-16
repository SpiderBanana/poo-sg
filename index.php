<?php
session_start();

// Classe Joueur
class Joueur {
    public $name;
    public $marbles;
    public $loss;
    public $gain;
    public $screem_war;

    public function __construct($name, $marbles, $loss, $gain, $screem_war) {
        $this->name = $name;
        $this->marbles = $marbles;
        $this->loss = $loss;
        $this->gain = $gain;
        $this->screem_war = $screem_war;
    }
}

// Classe Joueur à affronter
class JoueurAffronter {
    public $name;
    public $marbles;
    public $age;

    public function __construct($name, $marbles, $age) {
        $this->name = $name;
        $this->marbles = $marbles;
        $this->age = $age;
    }
}

// Initialisation des joueurs
$joueurs = [
    new Joueur("Seong Gi-hun", 15, 2, 1, "J'ai gagné !"),
    new Joueur("Kang Sae-byeok", 25, 1, 2, "Je suis la meilleure !"),
    new Joueur("Cho Sang-woo", 35, 0, 3, "Je suis le meilleur !"),
];

// Création des joueurs à affronter
$joueurs_affronter = [];
for ($i = 0; $i < 20; $i++) {
    $joueurs_affronter[] = new JoueurAffronter("Joueur " . ($i + 1), rand(1, 20), rand(18, 60));
}

// Choix du joueur et du niveau de difficulté
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['start_game'])) {
    $personnage_choisi = $_POST['personnage'];
    foreach ($joueurs as $joueur) {
        if ($joueur->name == $personnage_choisi) {
            $_SESSION['joueur'] = serialize($joueur);
            break;
        }
    }

    $niveau_difficulte = $_POST['niveau_difficulte'];
    $_SESSION['niveau_difficulte'] = $niveau_difficulte;
    $_SESSION['niveau_atteint'] = 0;

    // Redirection pour commencer le jeu
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Logique du jeu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guess'])) {
    $joueur = unserialize($_SESSION['joueur']);
    $adversaire = $joueurs_affronter[$_SESSION['niveau_atteint']];
    $guess = $_POST['guess'];

    // Détermination de la victoire ou défaite
    if (($guess == 'pair' && $adversaire->marbles % 2 == 0) || ($guess == 'impair' && $adversaire->marbles % 2 != 0)) {
        echo "<p>Victoire ! " . $joueur->screem_war . "</p>";
        $joueur->marbles += $adversaire->marbles + $joueur->gain;
    } else {
        echo "<p>Défaite... Vous perdez " . $adversaire->marbles . " billes.</p>";
        $joueur->marbles -= $adversaire->marbles + $joueur->loss;
    }

    // Afficher combien de billes avait l'adversaire
    echo "<p>Le joueur affronté avait " . $adversaire->marbles . " billes.</p>";

    // Mise à jour du joueur et du niveau
    $_SESSION['joueur'] = serialize($joueur);
    $_SESSION['niveau_atteint']++;

    // Vérification de la fin du jeu
    if ($joueur->marbles <= 0) {
        echo "<p>Vous avez perdu toutes vos billes. Jeu terminé.</p>";
        session_destroy();
    } elseif ($_SESSION['niveau_atteint'] == $_SESSION['niveau_difficulte']) {
        echo "<p>Félicitations, vous avez gagné 45,6 milliards de Won sud-coréen !</p>";
        session_destroy();
    }
}


// Formulaire de démarrage du jeu
if (!isset($_SESSION['joueur'])) {
    ?>
    <form action="" method="post">
        <label>Choisissez votre personnage:</label><br>
        <select name="personnage">
            <?php foreach ($joueurs as $joueur) { ?>
                <option value="<?php echo $joueur->name; ?>"><?php echo $joueur->name; ?></option>
            <?php } ?>
        </select><br><br>

        <label>Choisissez votre niveau de difficulté:</label><br>
        <select name="niveau_difficulte">
            <option value="5">Facile</option>
            <option value="10">Difficile</option>
            <option value="20">Impossible</option>
        </select><br><br>

        <input type="submit" name="start_game" value="Commencer le jeu">
    </form>
    <?php
}

// Formulaire pour deviner le nombre de billes
if (isset($_SESSION['joueur']) && $_SESSION['niveau_atteint'] < $_SESSION['niveau_difficulte']) {
    $joueur = unserialize($_SESSION['joueur']);
    echo "<p>Vous avez " . $joueur->marbles . " billes.</p>";
    ?>
    <form action="" method="post">
        <label>Devinez si votre adversaire a un nombre de billes pair ou impair:</label><br>
        <input type="radio" name="guess" value="pair" id="pair"><label for="pair">Pair</label><br>
        <input type="radio" name="guess" value="impair" id="impair"><label for="impair">Impair</label><br>
        <input type="submit" value="Soumettre">
    </form>
    <?php
}
?>
