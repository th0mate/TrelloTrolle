<?php
/** @var Tableau $tableau */
/** @var Colonne[] $colonnes */
/** @var Carte[][] $data */

/** @var array $participants */

use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Tableau;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" type="text/css" href="../tests/tests.css">
    <script type="text/javascript" src="../tests/tableaux.js" defer></script>
    <script type="module" src="../ressources/javaScript/colonne.js" defer></script>
    <script type="module" src="../ressources/javaScript/formulaireAjoutCarte.js" defer></script>
</head>
<body>

<div class="all">

    <div class="infos">
        <h3><?= htmlspecialchars($tableau->getTitreTableau()) ?></h3>
        <div class="allUsers">
            <h4>Collaborateurs :</h4>
            <?php
            echo'<span class="user">' . ($tableau->getUtilisateur()->getPrenom())[0] . ($tableau->getUtilisateur()->getNom())[0] . '</span>';
            foreach ($tableau->getParticipants() as $participant) {
                echo '<span class="user">' . ($participant->getPrenom())[0] . ($participant->getNom())[0] . '</span>';
            }
            ?>
            <div class="invite">Partager <img src="../tests/inviter.png" alt=""></div>
        </div>
    </div>

    <div class="ul">
        <?php
        for ($i = 0;
             $i < count($data);
             $i++) {
            ?>
            <div class="draggable" data-columns="<?= $colonnes[$i]->getIdColonne() ?>" draggable="true">
                <!-- Entete de la colonne avec son titre et ses bullets -->
                <div class="entete">
                    <h5 class="main" draggable="true"><?= htmlspecialchars($colonnes[$i]->getTitreColonne()) ?></h5>
                    <div class="bullets"><img src="../tests/bullets.png" alt=""></div>
                </div>

                <!-- Stockage des cartes -->
                <div class="stockage"  data-columns="<?= $colonnes[$i]->getIdColonne() ?>">
                    <?php
                    foreach ($data[$i] as $carte) {
                        ?>
                        <div class="card">
                            <span class="color" style="border : 5px solid <?= $carte->getCouleurCarte() ?>"></span>
                            <?= htmlspecialchars($carte->getTitreCarte()) ?>
                            <div class="features">
                                <?php
                                foreach ($carte->getAffectationsCarte() as $utilisateur) {
                                    echo '<span class="user">' . ($utilisateur->getPrenom())[0] . ($utilisateur->getNom())[0] . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="add" data-columns="<?= $colonnes[$i]->getIdColonne() ?>">
                    <img src="../tests/plus.png" alt="">
                    Ajouter une carte
                </div>
            </div>
            <?php
        }

        ?>

        <!-- Pour ajouter de nouvelles colonnes -->
        <div class="adder">
            <label>
                <input type="text" class="input" placeholder="Ajouter une colonne"/>
            </label>
            <span class="addCard">OK</span>
        </div>
    </div>

    <!-- Menu pour modifier/supprimer une colonne (appelé depuis les bullets) -->
    <div class="menuColonnes">
        <img src="../tests/close.png" alt="" class="close">
        <div class="deleteColumn">
            <h5>Supprimer</h5>
            <img src="../tests/bin.png" alt="">
        </div>
        <div class="updateColumn">
            <h5>Modifier</h5>
            <img src="../tests/edition.png" alt="">
        </div>
    </div>

</div>

<div class="formulaireCreationCarte">
    <div class="wrap"><h2>Création d'une carte</h2><img class="closeCard" src="../tests/close.png" alt=""></div>
    <input type="hidden" class="idColonne" data-reactiveInput="formulaireAjoutCarte.idColonne">
    <div class="content"><h4>Titre de la carte :</h4>
        <input maxlength="50" required
               data-reactiveInput="formulaireAjoutCarte.titre" type="text"
               class="inputCreationCarte"
               placeholder="Entrez le titre de la carte">
    </div>
    <div class="content"><h4>Description de la carte :</h4>
        <textarea maxlength="255"
                  data-reactiveInput="formulaireAjoutCarte.description"
                  class="desc"
                  placeholder="Description de la carte...">
        </textarea>
    </div>
    <div class="content"><h4>Couleur de la carte :</h4>
        <input required type="color"
               data-reactiveInput="formulaireAjoutCarte.couleur">
        </div>
        <div class="content"><h4>Membres affectés :</h4>
            <input type="text">
        </div>
        <div class="boutonCreation" data-onclick="formulaireAjoutCarte.envoyerFormulaire">Créer</div>
    </div>



</body>
</html>
