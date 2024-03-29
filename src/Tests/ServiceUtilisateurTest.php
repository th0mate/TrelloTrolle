<?php

namespace App\Trellotrolle\Tests;

use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceUtilisateur;
use App\Trellotrolle\Service\ServiceUtilisateurInterface;
use PHPUnit\Framework\TestCase;

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

    /** RECUPER COMPTE */

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
        $fakeTableau=$this->createFakeTableau();
        $this->serviceUtilisateur->isNotNullLogin("test", $fakeTableau, "ajouter");
    }

    /** AJOUTER MEMBRE */

    /** VERIFICATIONS MEMBRE */

    /** UTILISATEUR EXISTANT*/

    public function testUtilisateurNonExistant()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage("Utilisateur inexistant");
        $fakeTableau=$this->createFakeTableau();
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceUtilisateur->utilisateurExistant("JENEXISTEPAS",$fakeTableau);
    }

    public function testUtilisateurExistantValide()
    {
        $fakeUser=$this->createFakeUser();
        $fakeTableau=$this->createFakeTableau($fakeUser);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceUtilisateur->utilisateurExistant("test",$fakeTableau);
    }

    /** EST PARTICIPANT */
    //TODO utilise connexionUtilisateur
    public function testEstPasParticipant()
    {

    }

    public function testEstParticipantValide()
    {

    }

    /** CREER UTILISATEUR */

    /** SUPPRIMER UTILISATEUR */

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


    /** FONCTIONS UTILITAIRES */

    public function createFakeUser($login = "test"): Utilisateur
    {
        return new Utilisateur($login, 'test', "test", 'test@t.t', "test");
    }

    public function createFakeTableau($idTableau = 1, $utilisateur = null): Tableau
    {
        if (is_null($utilisateur)) {
            $utilisateur = $this->createFakeUser();
        }
        return new Tableau($idTableau, "code" . $idTableau, "titre", $utilisateur);
    }
}