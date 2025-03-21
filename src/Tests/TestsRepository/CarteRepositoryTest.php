<?php

namespace App\Trellotrolle\Tests\TestsRepository;

use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonneesInterface;
use App\Trellotrolle\Tests\ConfigurationBDDTestUnitaires;
use PHPUnit\Framework\TestCase;

class CarteRepositoryTest extends TestCase
{

    private static CarteRepositoryInterface  $carteRepository;

    private static ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$connexionBaseDeDonnees = new ConnexionBaseDeDonnees(new ConfigurationBDDTestUnitaires());
        self::$carteRepository = new CarteRepository(self::$connexionBaseDeDonnees);
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO
                                                              utilisateur (login,nom,prenom,email,mdphache,nonce)
                                                              VALUES ('bob69','bobby','bob','bob.bobby@bob.com','mdpBob','aaa')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              utilisateur (login,nom,prenom,email,mdphache,nonce)
                                                              VALUES ('bib420','bibby','bib','bib.bibby@bob.com','mdpBib','aaa')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              tableau (idtableau,codetableau,titretableau,login) 
                                                              VALUES (1, 'test', 'test','bob69')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              colonne (idcolonne,titrecolonne,idtableau,ordre) 
                                                              VALUES (2, 'test2', 1,0)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              carte (idcarte,titrecarte,descriptifcarte,couleurcarte,idcolonne) 
                                                              VALUES (3, 'carte1', 'carte1', 'c est une carte1', 2)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              carte (idcarte,titrecarte,descriptifcarte,couleurcarte,idcolonne) 
                                                              VALUES (4, 'carte2', 'carte2', 'c est une carte2', 2)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO
                                                              affectationCarte(idcarte,login )
                                                              VALUES (3,'bob69')");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM affectationCarte");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM carte");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM colonne");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM tableau");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM utilisateur");

    }

    /** Test récupererCartesColonne, prend en argument: idColonne */

    public function testRecupererCartesColonneExistante(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $array = [new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne), new Carte(4, 'carte2', 'carte2', 'c est une carte2', $colonne)];
        $this->assertEquals($array, self::$carteRepository->recupererCartesColonne(2));
    }
    public function testRecupererCartesColonneNonExistante(){
        $this->assertEquals([], self::$carteRepository->recupererCartesColonne(4));
    }

    /** Test récupererCartesTableau, prend en argument: idTableau */

    public function testRecupererCartesTableauExistant(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $array = [new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne),
            new Carte(4, 'carte2', 'carte2', 'c est une carte2', $colonne)];
        $this->assertEquals($array, self::$carteRepository->recupererCartesTableau(1));
    }
    public function testRecupererCartesTableauNonExistant(){
        $this->assertEquals([], self::$carteRepository->recupererCartesTableau(-1));
    }

    /** Test récupererCartesUtilisateur, prend en argument: login */

    public function testRecupererCartesUtilisateurExistant(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $array = [new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne)];
        $this->assertEquals($array, self::$carteRepository->recupererCartesUtilisateur('bob69'));
    }
    public function testRecupererCartesUtilisateurNonExistant(){
        $this->assertEquals([], self::$carteRepository->recupererCartesUtilisateur('george'));
    }

    /** Test getNombreCartesTotalUtilisateur, prend en argument: login */

    public function testgetNombreCartesTotalUtilisateurAvecCarte(){
        $this->assertEquals(1,self::$carteRepository->getNombreCartesTotalUtilisateur('bob69'));
    }

    public function testgetNombreCartesTotalUtilisateurSansCarte(){
        $this->assertEquals(1,self::$carteRepository->getNombreCartesTotalUtilisateur('bob69'));
    }

    public function testgetNombreCartesTotalUtilisateurInexistant(){
        $this->assertEquals(0,self::$carteRepository->getNombreCartesTotalUtilisateur('george'));
    }
    /** Test getNextIdCarte */

    public function testGetNextIdCarte(){
        $this->assertEquals(5, self::$carteRepository->getNextIdCarte());
    }

    /** Test getAffectatonsCarte, prend en argument: une carte idcle */

    public function testGetAffectatonsCarteAlogin(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte = new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne);
        $this->assertEquals([$utilisateur],self::$carteRepository->getAffectationsCarte($carte));
    }

    public function testGetAffectatonsCarteAPaslogin(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte = new Carte(4, 'carte2', 'carte2', 'c est une carte2', $colonne);
        $this->assertEquals([],self::$carteRepository->getAffectationsCarte($carte));
    }

    public function testGetAffectatonsCarteInexistante(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte = new Carte(8, 'carte8', 'carte8', 'c est une carte8', $colonne);
        $this->assertNull(self::$carteRepository->getAffectationsCarte($carte));
    }

    /**Test getAllFromCarte*/
    public function testGetALLFromCarte(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte = new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne);
        $this->assertEquals($carte,self::$carteRepository->getAllFromTable(3));
    }

    /** Test setAffectationCarte, prend en argument: array affectationCarte, carte instance */
    public function testSetAffectatonsCarteAlogin(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte = new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne);
        self::$carteRepository->setAffectationsCarte([$utilisateur],$carte);
        $this->assertEquals([$utilisateur],self::$carteRepository->getAffectationsCarte($carte));
    }

    /** Test récuperer */

    public function testRecuperer(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte1 = new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne);
        $carte2 = new Carte(4, 'carte2', 'carte2', 'c est une carte2', $colonne);
        $this->assertEquals([$carte1,$carte2], self::$carteRepository->recuperer());

    }

    /** Test récupérerparcléprimaire */
    public function testRecupererParClePrimaire(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte1 = new Carte(3, 'carte1', 'carte1', 'c est une carte1', $colonne);
        $this->assertEquals($carte1,self::$carteRepository->recupererParClePrimaire(3));
    }

    /** Test ajouter */
    public function testAjouter(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte1 = new Carte(8, 'carte8', 'carte8', 'c est une carte8', $colonne);
        self::$carteRepository->ajouter($carte1);
        $this->assertEquals($carte1, self::$carteRepository->recupererParClePrimaire(8));
    }


    /** Test mettreAJour */

    public function testMettreAJour(){
        $utilisateur = new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $tableau = new Tableau(1, 'test', 'test',$utilisateur);
        $colonne = new Colonne(2, 'test2',  $tableau,0);
        $carte1 = new Carte(3, 'CAAAAAARTE', 'carte1', 'c est une carte1', $colonne);
        self::$carteRepository->mettreAJour($carte1);
        $this->assertEquals('CAAAAAARTE',self::$carteRepository->recupererParClePrimaire(3)->getTitreCarte());
    }

    /** Test supprimer */

    public function testSupprimer(){
        self::$carteRepository->supprimer(3);
        $this->assertNull(self::$carteRepository->recupererParClePrimaire(3));
    }

}