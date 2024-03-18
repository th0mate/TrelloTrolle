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
    <script type="text/javascript" src="../tests/tableaux.js" defer></script>
    <link rel="stylesheet" type="text/css" href="../tests/tests.css">
</head>
<body>

<div class="all">

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
                <div class="stockage" data-columns="<?= $colonnes[$i]->getIdColonne() ?>">
                    <?php
                    foreach ($data[$i] as $carte) {
                        ?>
                        <div class="card" draggable="true" data-columns="<?= $colonnes[$i]->getIdColonne() ?>"
                             data-carte="<?= $carte->getIdCarte() ?>">
                            <span style="border : 2px solid <?= $carte->getCouleurCarte() ?>"></span>
                            <?= htmlspecialchars($carte->getTitreCarte()) ?>
                            <div class="features">
                                <!-- futurs membres de la carte -->
                            </div>
                        </div>
                        <?php
                    }
                    ?>
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

</body>
</html>
