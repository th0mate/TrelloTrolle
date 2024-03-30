<?php

namespace App\Trellotrolle\Tests\ServicesTest;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceCarteInterface;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ServiceCarteTest extends TestCase
{
    private ServiceCarteInterface $serviceCarte;

    private CarteRepositoryInterface $carteRepository;
    private UtilisateurRepositoryInterface $utilisateurRepository;
    private TableauRepositoryInterface $tableauRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carteRepository = $this->createMock(CarteRepository::class);
        $this->utilisateurRepository = $this->createMock(UtilisateurRepository::class);
        $this->tableauRepository = $this->createMock(TableauRepository::class);
        $this->serviceCarte = new ServiceCarte($this->carteRepository, $this->utilisateurRepository, $this->tableauRepository);

    }

    /** recupererCarte */

    public function testRecupererCarteManquante()
    {
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Code de carte manquant");
        $this->serviceCarte->recupererCarte(null);
    }

    public function testRecupererCarteInexistante()
    {
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Carte inexistante");
        $this->carteRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceCarte->recupererCarte(1);
    }

    public function testRecupererCarteValide()
    {
        $fakeCarte = $this->createFakeCarte();
        $this->carteRepository->method("recupererParClePrimaire")->willReturn($fakeCarte);
        $carte = $this->serviceCarte->recupererCarte(1);
        self::assertEquals($carte, $fakeCarte);
    }

    /** supprimerCarte */

    public function testSupprimerCarte()
    {
        $this->expectExceptionCode(400);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Erreur lors de la suppression de la carte");
        $fakeTableau = $this->createFakeTableau();
        $this->carteRepository->method("supprimer")->willReturnCallback(function ($idCarte) {
            return false;
        });
        $this->carteRepository->method("recupererCartesTableau")->willReturn([$this->createFakeCarte()]);
        $this->serviceCarte->supprimerCarte($fakeTableau, "1");
    }

    public function testSupprimerCarteValide()
    {
        $this->expectNotToPerformAssertions();
        $fakeTableau = $this->createFakeTableau();
        $this->carteRepository->method("supprimer")->willReturnCallback(function ($idCarte) {
            return true;
        });
        $this->carteRepository->method("recupererCartesTableau")->willReturn([$this->createFakeCarte()]);
        $this->serviceCarte->supprimerCarte($fakeTableau, "1");
    }

    /** recupererAttributs */

    public function testRecupererAtrributManquant()
    {
        $attributs = [
            "titreCarte" => "titre",
            "descriptifCarte" => null,
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1", "2"],
        ];
        $this->expectException(CreationException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Attributs manquants");
        $this->serviceCarte->recupererAttributs($attributs);
    }

    public function testRecupererAttributsValide()
    {
        $this->expectNotToPerformAssertions();
        $attributs = [
            "titreCarte" => "titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1", "2"],
        ];
        $this->serviceCarte->recupererAttributs($attributs);
    }

    /** miseAJourCarteMembre */



    /** carteUpdate */

    /** miseAJourCarte */

    /** verificationsMiseAJourCarte */

    /** creerCarte */

    public function testCreerCarteMembreInexistant()
    {
        $attributs = [
            "titreCarte" => "titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1", "2"],
        ];
        $fakeTableau = $this->createFakeTableau();
        $fakeColonne = $this->createFakeColonne();
        $this->expectException(CreationException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Un des membres affecté à la tâche n'existe pas");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceCarte->creerCarte($fakeTableau, $attributs, $fakeColonne);
    }

    public function testCreerCarteMembrePasDansTableau()
    {
        $attributs = [
            "titreCarte" => "titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1", "2"],
        ];
        $fakeTableau = $this->createFakeTableau();
        $fakeColonne = $this->createFakeColonne();
        $fakeUtilisateur = new Utilisateur("1", "nom", "prenom", "email", "mdp");
        $this->expectException(CreationException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Un des membres affecté à la tâche n'est pas affecté au tableau");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUtilisateur);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(false);
        $this->serviceCarte->creerCarte($fakeTableau, $attributs, $fakeColonne);
    }

    public function testCreerCarteValide()
    {
        $attributs = [
            "titreCarte" => "titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1", "2"],
        ];
        $fakeTableau = $this->createFakeTableau();
        $fakeColonne = $this->createFakeColonne();
        $fakeUtilisateur = new Utilisateur("1", "nom", "prenom", "email", "mdp");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUtilisateur);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        $this->carteRepository->method("getNextIdCarte")->willReturn(3);
        $this->carteRepository->method("ajouter")->willReturnCallback(function ($carte) use ($fakeColonne) {
            self::assertEquals("titre", $carte->getTitreCarte());
            self::assertEquals("desc", $carte->getDescriptifCarte());
            self::assertEquals("couleur", $carte->getCouleurCarte());
            self::assertEquals($fakeColonne, $carte->getColonne());
            return true;
        });
        $this->carteRepository->method("setAffectationsCarte")->willReturnCallback(function ($affectationsCarte) use ($fakeUtilisateur) {
            self::assertEquals([$fakeUtilisateur, $fakeUtilisateur], $affectationsCarte);
        });
        $this->serviceCarte->creerCarte($fakeTableau, $attributs, $fakeColonne);
    }

    public function testCreerCarteErreur()
    {
        $attributs = [
            "titreCarte" => "titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1", "2"],
        ];
        $fakeTableau = $this->createFakeTableau();
        $fakeColonne = $this->createFakeColonne();
        $this->expectException(CreationException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("Erreur lors de la création de la carte");
        $fakeUtilisateur = new Utilisateur("1", "nom", "prenom", "email", "mdp");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUtilisateur);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        $this->carteRepository->method("getNextIdCarte")->willReturn(3);
        $this->carteRepository->method("ajouter")->willReturnCallback(function ($carte) use ($fakeColonne) {
            self::assertEquals("titre", $carte->getTitreCarte());
            self::assertEquals("desc", $carte->getDescriptifCarte());
            self::assertEquals("couleur", $carte->getCouleurCarte());
            self::assertEquals($fakeColonne, $carte->getColonne());
            return false;
        });
        $this->carteRepository->method("setAffectationsCarte")->willReturnCallback(function ($affectationsCarte) use ($attributs) {
            self::assertEquals($attributs["affectationsCarte"], $affectationsCarte);
        });
        $this->serviceCarte->creerCarte($fakeTableau, $attributs, $fakeColonne);
    }

    /** getNextIdCarte */

    /** deplacerCarte */

    public function testDeplacerCarte()
    {
        $fakeCarte = $this->createFakeCarte();
        $fakeColonne = $this->createFakeColonne();
        $this->carteRepository->method("mettreAJour")->willReturnCallback(function ($carte) use ($fakeColonne, $fakeCarte) {
            self::assertEquals($fakeColonne, $carte->getColonne());
            self::assertEquals($fakeCarte->getIdCarte(), $carte->getIdCarte());
            self::assertEquals($fakeCarte->getCouleurCarte(), $carte->getCouleurCarte());
            self::assertEquals($fakeCarte->getDescriptifCarte(), $carte->getDescriptifCarte());
            self::assertEquals($fakeCarte->getTitreCarte(), $carte->getTitreCarte());
        });
        $this->serviceCarte->deplacerCarte($fakeCarte, $fakeColonne);
    }

    /** FONCTIONX UTILITAIRES */

    private function createFakeCarte($idCarte = 1): Carte
    {
        return new Carte($idCarte, "titre", "desc", "couleur", new Colonne(2, "titre", null));
    }

    private function createFakeColonne($idColonne = 1): Colonne
    {
        return new Colonne($idColonne, "titre", null);
    }

    private function createFakeTableau($utilisateur = null, $idTableau = 1): Tableau
    {
        return new Tableau($idTableau, "code", "titre", $utilisateur);
    }
}