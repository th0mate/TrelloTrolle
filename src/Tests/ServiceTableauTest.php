<?php

namespace App\Trellotrolle\Tests;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\Exception\TableauException;
use App\Trellotrolle\Service\ServiceTableau;
use PHPUnit\Framework\TestCase;
class ServiceTableauTest extends TestCase
{

    private ServiceTableau $serviceTableau;

    private UtilisateurRepository $utilisateurRepository;
    private CarteRepository $carteRepository;
    private ColonneRepository $colonneRepository;
    private TableauRepository $tableauRepository;


    protected function setUp():void{
        parent::setUp();
        $this->utilisateurRepository=$this->createMock(UtilisateurRepository::class);
        $this->carteRepository=$this->createMock(CarteRepository::class);
        $this->colonneRepository=$this->createMock(ColonneRepository::class);
        $this->tableauRepository=$this->createMock(TableauRepository::class);
        $this->serviceTableau=new ServiceTableau();
    }

    /** supprimerTableau */

    /** creerTableau */

    /** mettreAJourTableau */

    /** quitterTableau */

    /** recupererCartesColonne */

    public function testRecupererCartesColonnes()
    {
        //TODO Pas fini
        $fakeColonne=new Colonne($this->creerTableauEtUtilisateurFake(),"-1","fake",);
        $this->colonneRepository->method("recupererColonnesTableau")->willReturn($fakeColonne);

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
        $this->tableauRepository->method("recupererParClePrimaire")->willReturn($fakeTableau);
        $this->serviceTableau->recupererTableauParCode("1");
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
        $fakeTableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("recupererParClePrimaire")->willReturn($fakeTableau);
        $this->serviceTableau->recupererTableauParId("1");
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
                "fake"
            );
        }
        return new Tableau(
            $utilisateur,
            $id,
            $id,
            "titre",
            []
        );
    }
}