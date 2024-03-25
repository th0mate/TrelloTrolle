<?php

namespace App\Trellotrolle\Tests;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceTableauInterface;
use PHPUnit\Framework\TestCase;
class ServiceTableauTest extends TestCase
{

    private ServiceTableauInterface $serviceTableau;

    private UtilisateurRepositoryInterface $utilisateurRepository;
    private CarteRepositoryInterface $carteRepository;
    private ColonneRepositoryInterface $colonneRepository;
    private TableauRepositoryInterface $tableauRepository;


    protected function setUp():void{
        parent::setUp();
        $this->utilisateurRepository=$this->createMock(UtilisateurRepository::class);
        $this->carteRepository=$this->createMock(CarteRepository::class);
        $this->colonneRepository=$this->createMock(ColonneRepository::class);
        $this->tableauRepository=$this->createMock(TableauRepository::class);
        $this->serviceTableau=new ServiceTableau($this->tableauRepository,$this->colonneRepository,$this->carteRepository,$this->utilisateurRepository);
    }

    /** supprimerTableau */

    /** creerTableau */

    /** mettreAJourTableau */

    /** quitterTableau */

    /** recupererCartesColonne */

    public function testRecupererCartesColonnes()
    {
        //TODO Pas fini
        $tableau=$this->creerTableauEtUtilisateurFake();
        $fakeColonne=new Colonne("-1","fake",$tableau);
        $this->colonneRepository->method("recupererColonnesTableau")->willReturn([$fakeColonne]);
        $colonnes=$this->serviceTableau->recupererCartesColonnes($tableau);
        self::assertEquals([$fakeColonne],$colonnes);
    }

    /** recupererTableauEstMembre */

    public function testRecupererTableauEstMembre()
    {
        //TODO sans doute à revoir
        $utilisateur=new Utilisateur("test","test","test","test@test.fr","test","test");
        $fakesTableaux=[$this->creerTableauEtUtilisateurFake($utilisateur),$this->creerTableauEtUtilisateurFake($utilisateur,2)];
        $this->tableauRepository->method("recupererTableauxOuUtilisateurEstMembre")->willReturn($fakesTableaux);
        self::assertCount(2,$this->serviceTableau->recupererTableauEstMembre("kk"));
    }

    /** isNotNullNomTableau */

    public function testIsNotNullNomTableauNull()
    {
        $this->expectException(TableauException::class);
        $this->expectExceptionMessage("Nom de tableau manquant");
        $nomTableau=null;
        $this->serviceTableau->isNotNullNomTableau($nomTableau,$this->creerTableauEtUtilisateurFake());
    }
    public function testIsNotNullNomTableauValide()
    {
        $this->expectNotToPerformAssertions();
        $this->serviceTableau->isNotNullNomTableau("Bonjour",$this->creerTableauEtUtilisateurFake());
    }

    /** recupererTableauParCode */

    public function testRecupererTableauParCodeNull()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Code de tableau manquant");
        $this->serviceTableau->recupererTableauParCode(null);
    }

    public function testRecupererTableauParCodeInexistant()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Tableau inexistant");
        $this->tableauRepository->method("recupererParCodeTableau")->willReturn(null);
        $this->serviceTableau->recupererTableauParCode("-1");
    }

    public function testRecupererTableauParCodeValide()
    {

        $fakeTableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("recupererParCodeTableau")->willReturn($fakeTableau);
        $tableau=$this->serviceTableau->recupererTableauParCode("1");
        self::assertEquals($fakeTableau,$tableau);
    }

    /** recupererTableauParId */

    public function testRecupererTableauParIdNull()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Identifiant du tableau manquant");
        $this->serviceTableau->recupererTableauParId(null);
    }

    public function testRecupererTableauParIdInexistant()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Tableau inexistant");
        $this->tableauRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceTableau->recupererTableauParId("-1");
    }

    public function testRecupererTableauIdValide()
    {
        $this->expectNotToPerformAssertions();
        $fakeTableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("recupererParClePrimaire")->willReturn($fakeTableau);
        $tableau=$this->serviceTableau->recupererTableauParId("1");
        self::assertEquals($tableau,$fakeTableau);
    }


    /** Fonctions utilitaires */
    private function creerTableauEtUtilisateurFake($utilisateur = "",$id=1): Tableau
    {
        if ($utilisateur==""){
            $utilisateur=new Utilisateur(
                "fake",
                "fake",
                "fake",
                "fake@fake.fr",
                "fake",
            );
        }
        return new Tableau(
            $id,
            $id,
            "Titre",
            $utilisateur
        );
    }
}