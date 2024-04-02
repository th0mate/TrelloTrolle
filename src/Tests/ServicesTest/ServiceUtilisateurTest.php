<?php

namespace App\Trellotrolle\Tests\ServicesTest;

use App\Trellotrolle\Lib\MotDePasse;
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
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ServiceUtilisateurTest extends TestCase
{

    private ServiceUtilisateurInterface $serviceUtilisateur;

    private UtilisateurRepositoryInterface $utilisateurRepository;
    private TableauRepositoryInterface $tableauRepository;
    private CarteRepositoryInterface $carteRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->utilisateurRepository = $this->createMock(UtilisateurRepository::class);
        $this->tableauRepository = $this->createMock(TableauRepository::class);
        $this->carteRepository = $this->createMock(CarteRepository::class);
        $this->serviceUtilisateur = new ServiceUtilisateur($this->utilisateurRepository, $this->tableauRepository, $this->carteRepository);
    }

    /**MISE A JOUR UTILISATEUR*/

    public function testMettreAJourUtilisateurAttributNull()
    {
        $attributs = [
            "login" => "test",
            "nom" => "nom",
            "prenom" => "prenom",
            "email" => "email@email.com",
            "mdp" => "mdp",
            "mdp2" => "mdp",
            "mdpAncien" => null
        ];
        $this->expectException(MiseAJourException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Login, nom, prenom, email ou mot de passe manquant.");
        $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
    }

    public function testMettreAJourUtilisateurInexistant()
    {
        $attributs = [
            "login" => "test",
            "nom" => "nom",
            "prenom" => "prenom",
            "email" => "email@email.com",
            "mdp" => "mdp",
            "mdp2" => "mdp",
            "mdpAncien" => "email"
        ];
        $this->expectException(MiseAJourException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("L'utilisateur n'existe pas");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
    }

    public function testMettreAJourUtilisateurEmailNonValide()
    {
        $attributs = [
            "login" => "test",
            "nom" => "nom",
            "prenom" => "prenom",
            "email" => "email.com",
            "mdp" => "mdp",
            "mdp2" => "mdp",
            "mdpAncien" => "mdpAncien",

        ];
        $this->expectException(MiseAJourException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Email non valide");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($this->createFakeUser());
        $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
    }

    public function testMettreAJourUtilisateurMdpAncienErronne()
    {
        $attributs = [
            "login" => "test",
            "nom" => "nom",
            "prenom" => "prenom",
            "email" => "email@email.com",
            "mdp" => "mdp",
            "mdp2" => "mdp",
            "mdpAncien" => "mdpAncien",

        ];
        $this->expectException(MiseAJourException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage("Ancien mot de passe erroné.");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($this->createFakeUser());
        $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
    }

    public function testMettreAJourUtilisateurMdpsDistincs()
    {
        $attributs = [
            "login" => "test",
            "nom" => "nom",
            "prenom" => "prenom",
            "email" => "email@email.com",
            "mdp" => "mdp",
            "mdp2" => "mdp2",
            "mdpAncien" => "test",

        ];
        $this->expectException(MiseAJourException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage("Mots de passe distincts");
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($this->createFakeUser());
        $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
    }

    public function testMettreAJourUtilisateurValide()
    {
        $attributs = [
            "login" => "test",
            "nom" => "nom",
            "prenom" => "prenom",
            "email" => "email@email.com",
            "mdp" => "mdp",
            "mdp2" => "mdp",
            "mdpAncien" => "test",

        ];
        $fakeUser = $this->createFakeUser();
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->utilisateurRepository->method("mettreAJour")->willReturnCallback(function ($utilisateur) use ($fakeUser) {
            self::assertEquals($fakeUser->getLogin(), $utilisateur->getLogin());
            self::assertEquals("nom", $utilisateur->getNom());
            self::assertEquals("prenom", $utilisateur->getPrenom());
            self::assertEquals("email@email.com", $utilisateur->getEmail());
            self::assertEquals(MotDePasse::hacher("mdp"), $utilisateur->getMdpHache());
        });
        $this->serviceUtilisateur->mettreAJourUtilisateur($attributs);
    }

    /** RECUPER COMPTE */

    public function testRecupererCompteEmailManquant()
    {
        $this->expectExceptionMessage("Adresse email manquante");
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->serviceUtilisateur->recupererCompte(null);
    }

    public function testRecupererCompteInexistant()
    {
        $this->expectExceptionMessage("Aucun compte associé à cette adresse email");
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->utilisateurRepository->method("recupererUtilisateursParEmail")->willReturn([]);
        $this->serviceUtilisateur->recupererCompte("1");
    }

    public function testRecupererCompte()
    {
        $fakeUtilisateur = $this->createFakeUser();
        $this->utilisateurRepository->method("recupererUtilisateursParEmail")->willReturn([$fakeUtilisateur]);
        $user = $this->serviceUtilisateur->recupererCompte("1");
        assertEquals([$fakeUtilisateur], $user);
    }

    /** IS NOT NULL LOGIN */

    public function testIsNullLogin()
    {
        $fakeTableau = $this->createFakeTableau();
        $this->expectException(TableauException::class);
        $this->expectExceptionMessage("Login du membre à ajouter manquant");
        $this->expectExceptionCode(404);
        $this->serviceUtilisateur->isNotNullLogin(null, $fakeTableau, "ajouter");
    }

    public function testIsNotNullLoginValide()
    {
        $this->expectNotToPerformAssertions();
        $fakeTableau = $this->createFakeTableau();
        $this->serviceUtilisateur->isNotNullLogin("test", $fakeTableau, "ajouter");
    }

    /** AJOUTER MEMBRE */

    public function testAjouterMembre()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage("Ce membre est déjà membre du tableau");
        $fakesUsers = ["1", "2"];
        $fakeUser = $this->createFakeUser();
        $fakeTableau = $this->createFakeTableau();
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->serviceUtilisateur->ajouterMembre($fakeTableau, $fakesUsers, "loginConnecte");
    }

    public function testAjouterMembreValide()
    {
        $fakesUsers = ["1", "2"];
        $fakeUser1 = $this->createFakeUser();
        $fakeUser2 = $this->createFakeUser();
        $fakesUtilisateurs = [$fakeUser1, $fakeUser2];
        $fakeTableau = $this->createFakeTableau();
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(false);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser1);
        $this->tableauRepository->method("getParticipants")->willReturn([]);
        $this->tableauRepository->method("setParticipants")->willReturnCallback(function ($participants) use ($fakesUtilisateurs) {
            self::assertEquals($fakesUtilisateurs, $participants);
        });
        $this->serviceUtilisateur->ajouterMembre($fakeTableau, $fakesUsers, "loginConnecte");
    }

    /** VERIFICATIONS MEMBRE */

    public function testVerificationMembreUtilisateurEmpty()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage("Il n'est pas possible d'ajouter plus de membre à ce tableau.");
        $fakeUser = $this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->utilisateurRepository->method("recupererUtilisateursOrderedPrenomNom")->willReturn([$fakeUser]);
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        $this->serviceUtilisateur->verificationsMembre($fakeTableau, $fakeUser->getLogin());
    }

    public function testVerificationMembreValide()
    {
        $fakeUser = $this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->utilisateurRepository->method("recupererUtilisateursOrderedPrenomNom")->willReturn([$fakeUser]);
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(false);
        $results = $this->serviceUtilisateur->verificationsMembre($fakeTableau, $fakeUser->getLogin());
        assertEquals([$fakeUser], $results);
    }


    /** UTILISATEUR EXISTANT*/

    public function testUtilisateurNonExistant()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Utilisateur inexistant");
        $fakeTableau = $this->createFakeTableau();
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceUtilisateur->utilisateurExistant("JENEXISTEPAS", $fakeTableau);
    }

    public function testUtilisateurExistantValide()
    {
        $this->expectNotToPerformAssertions();
        $fakeUser = $this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->serviceUtilisateur->utilisateurExistant("test", $fakeTableau);
    }

    /** EST PARTICIPANT */
    public function testEstPasParticipant()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Vous n'avez pas de droits d'éditions sur ce tableau");
        $tableau = $this->createFakeTableau();
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(false);
        $this->serviceUtilisateur->estParticipant($tableau, "NULL");
    }

    public function testEstParticipantValide()
    {
        $this->expectNotToPerformAssertions();
        $tableau = $this->createFakeTableau();
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        $this->serviceUtilisateur->estParticipant($tableau, "test");
    }

    /** CREER UTILISATEUR */

    public function testcreerUtilisateurManquants()
    {
        $this->expectException(CreationException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Login, nom, prenom, email ou mot de passe manquant.");
        $attributs = ["login" => "test", "nom" => "nom", "prenom" => "prenom", "mdp" => "ee", "mdp2" => "jj", "email" => null];
        $this->serviceUtilisateur->creerUtilisateur($attributs);
    }

    public function testcreerUtilisateurMdpDistincts()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage("Mots de passe distincts");
        $attributs = ["login" => "test", "nom" => "nom", "prenom" => "prenom", "mdp" => "ee", "mdp2" => "jj", "email" => "email"];
        $this->serviceUtilisateur->creerUtilisateur($attributs);
    }

    public function testcreerUtilisateurEmailNonValide()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Email non valide");
        $attributs = ["login" => "test", "nom" => "nom", "prenom" => "prenom", "mdp" => "ee", "mdp2" => "ee", "email" => "null"];
        $this->serviceUtilisateur->creerUtilisateur($attributs);
    }

    public function testcreerUtilisateurLoginDejaPris()
    {
        $this->expectException(ServiceException::class);
        $fakeUser = $this->createFakeUser();
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Le login est déjà pris");
        $attributs = ["login" => "test", "nom" => "nom", "prenom" => "prenom", "mdp" => "ee", "mdp2" => "ee", "email" => "null@test.fr"];
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->serviceUtilisateur->creerUtilisateur($attributs);
    }

    public function testcreerUtilisateurErreur()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("Une erreur est survenue lors de la création de l'utilisateur.");
        $attributs = ["login" => "test", "nom" => "nom", "prenom" => "prenom", "mdp" => "ee", "mdp2" => "ee", "email" => "null@test.fr"];
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->utilisateurRepository->method("ajouter")->willReturnCallback(function () {
            return false;
        });
        $this->serviceUtilisateur->creerUtilisateur($attributs);
    }

    public function testcreerUtilisateurValide()
    {
        $utilisateur = new Utilisateur("test", "nom", "prenom", "null@test.fr", MotDePasse::hacher("ee"));
        $attributs = ["login" => "test", "nom" => "nom", "prenom" => "prenom", "mdp" => "ee", "mdp2" => "ee", "email" => "null@test.fr"];
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->utilisateurRepository->method("ajouter")->willReturnCallback(function ($userACreer) use ($utilisateur) {
            self::assertEquals($utilisateur, $userACreer);
            return true;
        });
        $this->serviceUtilisateur->creerUtilisateur($attributs);
    }


    /** SUPPRIMER UTILISATEUR */


    public function testSupprimerUtilisateurManquant()
    {
        $this->expectExceptionMessage("Login manquant");
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->serviceUtilisateur->supprimerUtilisateur(null);
    }

    public function testSupprimerUtilisateurValide()
    {
        $cartes = [$this->createFakeCarte(), $this->createFakeCarte(2)];
        $fakeParticipants = [$this->createFakeUser()];
        $fakeUser = $this->createFakeUser();
        $this->carteRepository->method("recupererCartesUtilisateur")->willReturn($cartes);
        $this->carteRepository->method("getAffectationsCarte")->willReturn($fakeParticipants);
        $this->tableauRepository->method("recupererTableauxParticipeUtilisateur")->willReturn([$this->createFakeTableau()]);
        $this->tableauRepository->method("getParticipants")->willReturn($fakeParticipants);
        $this->utilisateurRepository->method("supprimer")->willReturnCallback(function ($login) use ($fakeUser) {
            self::assertEquals($login, $fakeUser->getLogin());
            return true;
        });
        $this->serviceUtilisateur->supprimerUtilisateur("test");

    }

    /** EST PROPRIETAIRE */

    public function testEstPasProprietaire()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionMessage("Vous n'êtes pas propriétaire de ce tableau");
        $this->expectExceptionCode(403);
        $tableau = $this->createFakeTableau();
        $this->tableauRepository->method("estProprietaire")->willReturn(false);
        $this->serviceUtilisateur->estProprietaire($tableau, "test");
    }

    public function testEstProprietairerValide()
    {
        $this->expectNotToPerformAssertions();
        $tableau = $this->createFakeTableau();
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->serviceUtilisateur->estProprietaire($tableau, "test");
    }

    /** SUPPRIMER MEMBRE */

    public function testSupprimerMembreProprietaire()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Vous ne pouvez pas vous supprimer du tableau");
        $fakeUser=$this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->serviceUtilisateur->supprimerMembre($fakeTableau, "login", "login");
    }

    public function testSupprimerMembreNonParticipant()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(403);
        $this->expectExceptionMessage("Cet utilisateur n'est pas membre du tableau");
        $fakeUser=$this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->tableauRepository->method("estParticipant")->willReturn(false);
        $this->serviceUtilisateur->supprimerMembre($fakeTableau, "login", "loginConnecte");
    }

    public function testSupprimerMembreValide()
    {
        $fakeUser=$this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($fakeUser);
        $this->tableauRepository->method("estParticipant")->willReturn(true);
        $this->tableauRepository->method("getParticipants")->willReturn([$fakeUser]);
        $this->tableauRepository->method("setParticipants")->willReturnCallback(function ($participants,$tableau){
            self::assertEmpty($participants);
        });
        $this->serviceUtilisateur->supprimerMembre($fakeTableau, "login", "loginConnecte");
    }

    /** RECUPERER UTILISATEUR PAR CLE */

    public function testRecupererUtilisateurParCle()
    {
        $user = $this->createFakeUser();
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($user);
        $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle("test");
        self::assertEquals($user, $utilisateur);
    }

    public function testRecupererUtilisateurParCleNull()
    {
        $user = $this->createFakeUser();
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $utilisateur = $this->serviceUtilisateur->recupererUtilisateurParCle("test");
        self::assertEquals(null, $utilisateur);
    }

    /** RECHERCHE */

    public function testRechercheUtilisateurNull()
    {
        $this->expectExceptionMessage("La recherche est nulle");
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->serviceUtilisateur->rechercheUtilisateur(null);
    }

    public function testRechercheUtilisateurValide()
    {
        $fakesUser = [$this->createFakeUser(), $this->createFakeUser()];
        $this->utilisateurRepository->method("recherche")->willReturn($fakesUser);
        $results = $this->serviceUtilisateur->rechercheUtilisateur("test");
        assertEquals($fakesUser, $results);
    }

    /** GET PARTICIPANTS */
    public function testGetParticipants()
    {
        $fakeUser1 = $this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser1);
        $fakeUsers = [$this->createFakeUser(), $this->createFakeUser()];
        $this->tableauRepository->method("getParticipants")->willReturn($fakeUsers);
        assertEquals($fakeUsers, $this->serviceUtilisateur->getParticipants($fakeTableau));
    }

    /** GET PROPRIETAIRE TABLEAU */

    public function testGetProprietaireTableau()
    {
        $fakeUser = $this->createFakeUser();
        $fakeTableau = $this->createFakeTableau($fakeUser);
        $this->tableauRepository->method("getProprietaire")->willReturn($fakeUser);
        self::assertEquals($fakeUser, $this->serviceUtilisateur->getProprietaireTableau($fakeTableau));
    }

    /** FONCTIONS UTILITAIRES */

    private function createFakeUser($login = "test"): Utilisateur
    {
        return new Utilisateur($login, 'test', "test", 'test@t.t', MotDePasse::hacher("test"));
    }

    private function createFakeTableau($utilisateur = null, $idTableau = 1): Tableau
    {
        if (is_null($utilisateur)) {
            $utilisateur = $this->createFakeUser();
        }
        return new Tableau($idTableau, "code", "titre", $utilisateur);
    }

    private function createFakeCarte($idCarte = 1): Carte
    {
        return new Carte($idCarte, "titre", "descriptif", "bleu", new Colonne(null, null, null));
    }
}