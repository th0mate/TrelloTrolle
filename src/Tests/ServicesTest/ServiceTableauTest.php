<?php

namespace App\Trellotrolle\Tests\ServicesTest;
use App\Trellotrolle\Modele\DataObject\Carte;
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
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ServiceTableauTest extends TestCase
{

    private ServiceTableauInterface $serviceTableau;

    private UtilisateurRepositoryInterface $utilisateurRepository;
    private CarteRepositoryInterface $carteRepository;
    private ColonneRepositoryInterface $colonneRepository;
    private TableauRepositoryInterface $tableauRepository;


    /**
     * @throws Exception
     */
    protected function setUp():void{
        parent::setUp();
        $this->utilisateurRepository=$this->createMock(UtilisateurRepository::class);
        $this->carteRepository=$this->createMock(CarteRepository::class);
        $this->colonneRepository=$this->createMock(ColonneRepository::class);
        $this->tableauRepository=$this->createMock(TableauRepository::class);
        $this->serviceTableau=new ServiceTableau($this->tableauRepository,$this->colonneRepository,$this->carteRepository,$this->utilisateurRepository);
    }

    /** supprimerTableau */

    public function test()
    {
        $tableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("supprimer")->willReturnCallback(function ($idTableau){
            self::assertEquals(1,$idTableau);
            return true;
        });
        $this->serviceTableau->supprimerTableau($tableau->getIdTableau());
    }

    /** creerTableau */

    public function testCreerTableauNomManquant()
    {
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Nom de tableau manquant");
        $this->expectExceptionCode(404);
        $this->serviceTableau->creerTableau(null,"loginConnecte");
    }

    public function testCreerTableauValide()
    {
        $utilisateur=new Utilisateur("loginConnecte","nom","prenom","test@test.fr",'mdp');
        $tableau=new Tableau(1, hash("sha256", $utilisateur->getLogin() ."1"),"nomTableau",$utilisateur);
        $colonne=new Colonne("1","TODO",$tableau,null);
        $this->utilisateurRepository->method("recupererParClePrimaire")->willReturn($utilisateur);
        $this->tableauRepository->method("getNextIdTableau")->willReturn(1);
        $this->colonneRepository->method("getNextIdColonne")->willReturn(1);
        $this->carteRepository->method("getNextIdCarte")->willReturn(1);
        $this->tableauRepository->method("ajouter")->willReturnCallback(function ($tableau2) use ($tableau){
            assertEquals($tableau,$tableau2);
            return true;
        });
        $this->colonneRepository->method("ajouter")->willReturnCallback(function ($colonne2)use ($colonne){
            self::assertEquals($colonne,$colonne2);
            return true;
        });
        $this->carteRepository->method("ajouter")->willReturnCallback(function ($carte)use ($colonne){
            self::assertEquals(new Carte("1","Exemple","Exemple de carte","#FFFFFF",$colonne),$carte);
            return true;
        });
        $this->serviceTableau->creerTableau("nomTableau","loginConnecte");
    }
    /** mettreAJourTableau */

    public function testMettreAJourTableauValide()
    {
        $tableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("mettreAJour")->willReturnCallback(function ($tableau2){
            $tableau=$this->creerTableauEtUtilisateurFake();
            self::assertEquals($tableau,$tableau2);
        });
        $this->serviceTableau->mettreAJourTableau($tableau);
    }

    /** quitterTableau */

    public function testQuitterTableauEstProprietaire()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous ne pouvez pas quitter ce tableau");
        $this->expectExceptionCode(403);
        $utilisateur=new Utilisateur("test","test","test",'test@t.com',"test");
        $tableau=new Tableau(1,"code","titre",$utilisateur);
        $this->tableauRepository->method("estProprietaire")->willReturn(true);
        $this->serviceTableau->quitterTableau($tableau,$utilisateur);
    }

    public function testQuitterTableauEstPasParticipant()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Vous n'appartenez pas à ce tableau");
        $this->expectExceptionCode(403);
        $utilisateur=new Utilisateur("test","test","test",'test@t.com',"test");
        $tableau=new Tableau(1,"code","titre",$utilisateur);
        $this->tableauRepository->method("estProprietaire")->willReturn(false);
        $this->tableauRepository->method("estParticipant")->willReturn(false);
        $this->serviceTableau->quitterTableau($tableau,$utilisateur);
    }

    public function testQuitterTableauValide()
    {
        $utilisateur=new Utilisateur("test","test","test",'test@t.com',"test");
        $tableau=new Tableau(1,"code","titre",$utilisateur);
        $carte=new Carte("1","titre","desc","couleur",new Colonne("1","titre",$tableau,null));
        $this->tableauRepository->method("estProprietaire")->willReturn(false);
        $this->tableauRepository->method("estParticipant")->willReturn(true);
        $this->tableauRepository->method("getParticipants")->willReturn([$utilisateur]);
        $this->tableauRepository->method("setParticipants")->willReturnCallback(function ($participants,$tableau){
            assertEquals([],$participants);
        });
        $this->carteRepository->method("recupererCartesTableau")->willReturn([$carte]);
        $this->carteRepository->method("getAffectationsCarte")->willReturn([$utilisateur]);
        $this->carteRepository->method("setAffectationsCarte")->willReturnCallback(function ($affectations,$carte){
           assertEquals([],$affectations);

        });
        $this->serviceTableau->quitterTableau($tableau,$utilisateur);
    }

    /** recupererCartesColonne */

    public function testRecupererCartesColonnes()
    {
        $tableau=$this->creerTableauEtUtilisateurFake();
        $fakeColonne=new Colonne("-1","fake",$tableau,null);
        $carte=new Carte("1","titre","desc","couleur",$fakeColonne);
        $this->colonneRepository->method("recupererColonnesTableau")->willReturn([$fakeColonne]);
        $this->carteRepository->method("recupererCartesColonne")->willReturn([$carte]);
        $this->carteRepository->method("getAffectationsCarte")->willReturn([$tableau->getUtilisateur()]);
        $colonnes=$this->serviceTableau->recupererCartesColonnes($tableau);
        $recuperer=["data"=>[[$carte]],"colonnes"=>[$fakeColonne],"participants"=>[1=>[$tableau->getUtilisateur()]]];
        self::assertEquals($recuperer,$colonnes);
    }

    /** recupererTableauEstMembre */

    public function testRecupererTableauEstMembre()
    {
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
        $fakeTableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("recupererParClePrimaire")->willReturn($fakeTableau);
        $tableau=$this->serviceTableau->recupererTableauParId("1");
        self::assertEquals($tableau,$fakeTableau);
    }

    /** estParticipant */

    public function testEstParticipantTrue()
    {
        $tableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(true);
        self::assertTrue($this->serviceTableau->estParticipant($tableau,"loginConnecte"));
    }
    public function testEstParticipantFalse()
    {
        $fakeTableau=$this->creerTableauEtUtilisateurFake();
        $this->tableauRepository->method("estParticipantOuProprietaire")->willReturn(false);
        self::assertFalse($this->serviceTableau->estParticipant($fakeTableau,"loginConnecte"));
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