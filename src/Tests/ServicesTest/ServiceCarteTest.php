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
use App\Trellotrolle\Service\Exception\MiseAJourException;
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

    public function testSupprimerCarteValide()
    {
        $fakeTableau = $this->createFakeTableau();
        $this->carteRepository->method("supprimer")->willReturnCallback(function ($idCarte) {
            return true;
        });
        $this->carteRepository->method("recupererCartesTableau")->willReturn([$this->createFakeCarte()]);
        $cartes=$this->serviceCarte->supprimerCarte($fakeTableau, "1");
        self::assertEquals([$this->createFakeCarte()],$cartes);
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

    public function testMiseAJourCarteMembre()
    {
        $fakeUser=new Utilisateur("login","nom","prenom","email@e.com","mdp",null);
        $fakeTableau=$this->createFakeTableau($fakeUser);
        $fakeCarte=$this->createFakeCarte();
        $this->carteRepository->method("recupererCartesTableau")->willReturn([$fakeCarte]);
        $this->carteRepository->method("getAffectationsCarte")->willReturn([$fakeUser]);
        $this->carteRepository->method("setAffectationsCarte")->willReturnCallback(function ($affectations,$carte)use ($fakeCarte){
           self::assertEquals([],$affectations);
           self::assertEquals($fakeCarte,$carte);
        });
        $this->serviceCarte->miseAJourCarteMembre($fakeTableau,$fakeUser);
    }

    /** miseAJourCarte */

    public function testMiseAJourCarteMembreInexistant()
    {
        $attributs = [
            "titreCarte" =>"titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1","2"],
        ];
        $fakeTableau=$this->createFakeTableau();
        $fakeCarte=$this->createFakeCarte();
        $fakeColonne=$this->createFakeColonne();
        $this->expectExceptionCode(404);
        $this->expectException(CreationException::class);
        $this->expectExceptionMessage("Un des membres affecté à la tâche n'existe pas");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceCarte->miseAJourCarte($fakeTableau,$attributs,$fakeCarte,$fakeColonne);
    }
    public function testMiseAJourCarteMembrePasAffecte()
    {
        $attributs = [
            "titreCarte" =>"titre",
            "descriptifCarte" => "desc",
            "couleurCarte" => "couleur",
            "affectationsCarte" => ["1","2"],
        ];
        $fakeTableau=$this->createFakeTableau();
        $fakeCarte=$this->createFakeCarte();
        $fakeColonne=$this->createFakeColonne();
        $fakeUser=new Utilisateur("login","nom","prenom","email@email.com","mdp",null);
        $this->expectExceptionCode(403);
        $this->expectException(MiseAJourException::class);
        $this->expectExceptionMessage("Un des membres affecté à la tâche n'est pas affecté au tableau");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(false);
        $this->serviceCarte->miseAJourCarte($fakeTableau,$attributs,$fakeCarte,$fakeColonne);
    }
    public function testMiseAJourCarteValide()
    {
        $attributs = [
            "titreCarte" =>"titreCarte",
            "descriptifCarte" => "descr",
            "couleurCarte" => "coul",
            "affectationsCarte" => ["1","2"],
        ];
        $fakeTableau=$this->createFakeTableau();
        $fakeCarte=$this->createFakeCarte();
        $fakeColonne=$this->createFakeColonne();
        $fakeUser=new Utilisateur("login","nom","prenom","email@email.com","mdp",null);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        $this->carteRepository->method("mettreAJour")->willReturnCallback(function ($carte)use ($fakeCarte,$fakeColonne){
            self::assertEquals("titreCarte",$carte->getTitreCarte());
            self::assertEquals($fakeCarte->getIdCarte(),$carte->getIdCarte());
            self::assertEquals("descr",$carte->getDescriptifCarte());
            self::assertEquals("coul",$carte->getCouleurCarte());
            self::assertEquals($fakeColonne,$carte->getColonne());
        });
        $this->serviceCarte->miseAJourCarte($fakeTableau,$attributs,$fakeCarte,$fakeColonne);
    }

    /** verificationsMiseAJourCarte */

    public function testVerificationsMiseAJourCarteConflictTableau()
    {
        $attributs = [
            "titreCarte" =>"titreCarte",
            "descriptifCarte" => "descr",
            "couleurCarte" => "coul",
            "affectationsCarte" => ["1","2"],
        ];
        $this->expectExceptionCode(409);
        $this->expectException(CreationException::class);
        $this->expectExceptionMessage("Le tableau de cette colonne n'est pas le même que celui de la colonne d'origine de la carte!");
        $fakeUser=new Utilisateur("login","nom","prenom","email@l.com","mdp",null);
        $fakeTableau=new Tableau(1,"code","titre",$fakeUser);
        $fakeColonne=new Colonne(1,"titre",$fakeTableau);
        $fakeCarte=new Carte(1,"titre","desc","couleur",new Colonne(2,"titre",new Tableau(2,"code","titre",$fakeUser)));
        $this->carteRepository->method("getAllFromTable")->willReturn($fakeCarte);
        $this->serviceCarte->verificationsMiseAJourCarte(1,$fakeColonne,$attributs);
    }

    public function testVerificationsMiseAJourCarteValide()
    {
        $attributs = [
            "titreCarte" =>"titreCarte",
            "descriptifCarte" => "descr",
            "couleurCarte" => "coul",
            "affectationsCarte" => ["1","2"],
        ];
        $fakeUser=new Utilisateur("login","nom","prenom","email@l.com","mdp",null);
        $fakeTableau=new Tableau(1,"code","titre",$fakeUser);
        $fakeColonne=new Colonne(1,"titre",$fakeTableau);
        $fakeCarte=new Carte(1,"titre","desc","couleur",$fakeColonne);
        $this->carteRepository->method("getAllFromTable")->willReturn($fakeCarte);
        $carte=$this->serviceCarte->verificationsMiseAJourCarte(1,$fakeColonne,$attributs);
        self::assertEquals($fakeCarte,$carte);

    }

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
        $fakeUtilisateur = new Utilisateur("1", "nom", "prenom", "email", "mdp",null);
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
        $fakeUtilisateur = new Utilisateur("1", "nom", "prenom", "email", "mdp",null);
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


    /** getNextIdCarte */

    public function testGetNextIdCarte()
    {
        $this->carteRepository->method("getNextIdCarte")->willReturn(1);
        $id=$this->serviceCarte->getNextIdCarte();
        assertEquals(1,$id);
    }

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

    /** getAffectations */

    public function testGetAffectations()
    {
        $fakeCarte=$this->createFakeCarte();
        $this->carteRepository->method("getAffectationsCarte")->willReturn([$fakeCarte]);
        $affectations=$this->serviceCarte->getAffectations($fakeCarte);
        self::assertEquals([$fakeCarte],$affectations);
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