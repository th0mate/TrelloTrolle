<?php

namespace App\Trellotrolle\Tests\TestsRepository;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonneesInterface;
use App\Trellotrolle\Tests\ConfigurationBDDTestUnitaires;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ColonneRepositoryTest extends TestCase
{
    private static ColonneRepositoryInterface  $colonneRepository;

    private static ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$connexionBaseDeDonnees = new ConnexionBaseDeDonnees(new ConfigurationBDDTestUnitaires());
        self::$colonneRepository = new ColonneRepository(self::$connexionBaseDeDonnees);
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
                                                              utilisateur (login,nom,prenom,email,mdphache,nonce)
                                                              VALUES ('bob560','zeblouse','agathe','agathe.zeblouze@jfiu.com','mdpAg','aaa')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              utilisateur (login,nom,prenom,email,mdphache,nonce)
                                                              VALUES ('alTE','terrieur','alain','alain.terrieur@terrieur.com','mdpAl','aaa')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              tableau (idtableau,codetableau,titretableau,login) 
                                                              VALUES (1, 'test', 'test','bob69')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              tableau (idtableau,codetableau,titretableau,login) 
                                                              VALUES (2, 'test2', 'test2','bob69')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              tableau (idtableau,codetableau,titretableau,login) 
                                                              VALUES (3, 'test3', 'test3','bib420')");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              colonne (idcolonne,titrecolonne,idtableau,ordre) 
                                                              VALUES (1, 'colonne1', 1,0)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              colonne (idcolonne,titrecolonne,idtableau,ordre) 
                                                              VALUES (2, 'colonne2', 1,1)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              colonne (idcolonne,titrecolonne,idtableau,ordre) 
                                                              VALUES (3, 'colonne3', 3,0)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              participant (login, idTableau)
                                                              VALUES ('bob69',3)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              participant (login, idTableau)
                                                              VALUES ('bob560',3)");
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM colonne");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM participant");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM tableau");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM utilisateur");

    }
    /**Test récupererColonnesTableau prends idTableau retourne array*/

    public function testRecupererColonnesTableauEnA(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeColonne1= new Colonne(1, 'colonne1',$fakeTab1,0);
        $fakeColonne2= new Colonne(2, 'colonne2',$fakeTab1,1);
        $this->assertEquals([$fakeColonne1,$fakeColonne2],self::$colonneRepository->recupererColonnesTableau(1));
    }
    public function testRecupererColonnesTableauEnAPAS(){
        $this->assertEquals([],self::$colonneRepository->recupererColonnesTableau(2));
    }
    public function testRecupererColonnesTableauInexistant(){
        $this->assertNull(self::$colonneRepository->recupererColonnesTableau(12));
    }

    /**Test getNextIdColonne retourne int*/

    public function testGetNextIdColonne(){
        $this->assertEquals(4,self::$colonneRepository->getNextIdColonne());
    }

    /**Test getNombreColonnesTotalTableau prend idTableau retourne int*/

    public function testgetNombreColonnesTotalTableauEnA(){
        $this->assertEquals(2,self::$colonneRepository->getNombreColonnesTotalTableau(1));
    }

    public function testgetNombreColonnesTotalTableauEnAPas(){
        $this->assertEquals(0,self::$colonneRepository->getNombreColonnesTotalTableau(2));
    }

    public function testgetNombreColonnesTotalTableauInexistant(){
        $this->assertEquals(0,self::$colonneRepository->getNombreColonnesTotalTableau(12));
    }

    /**Test inverserOrdreColonnes prends : idColonne, idColonne  */

    public function testInverserOrdreColonnes2(){
        self::$colonneRepository->inverserOrdreColonnes(1,2);
            $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
            $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
            $fakeColonne1= new Colonne(1, 'colonne1',$fakeTab1,1);
            $fakeColonne2= new Colonne(2, 'colonne2',$fakeTab1,0);
        $this->assertEqualsCanonicalizing([$fakeColonne1,$fakeColonne2],self::$colonneRepository->recupererColonnesTableau(1));
    }


    /**Test getAllFromColonne prends idColonne retourne array*/

    public function testGetAllFromColonne(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeColonne1= new Colonne(1, 'colonne1',$fakeTab1,0);
        assertEquals($fakeColonne1,self::$colonneRepository->getAllFromTable(1));

    }

    /**Test récupérer*/

    public function testRecuperer(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeTab2 = new Tableau(3, 'test3', 'test3',$fakeUser2);
        $fakeColonne1= new Colonne(1, 'colonne1',$fakeTab1,0);
        $fakeColonne2= new Colonne(2, 'colonne2',$fakeTab1,1);
        $fakeColonne3= new Colonne(3, 'colonne3',$fakeTab2,0);
        $this->assertEquals([$fakeColonne1,$fakeColonne2,$fakeColonne3], self::$colonneRepository->recuperer());
    }

    /**Test récupererParClePrimaire*/

    public function testRecupererParClePrimaireExistant(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeColonne1= new Colonne(1, 'colonne1',$fakeTab1,0);
        self::assertEquals($fakeColonne1,self::$colonneRepository->recupererParClePrimaire(1));
    }

    public function testRecupererParClePrimaireInexistant(){
        self::assertNull(self::$colonneRepository->recupererParClePrimaire(12));
    }


    /**Test ajouter*/

    public function testAjouter(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakecolonneAADD = new Colonne(5,'colonne5',$fakeTab1,2);
        $fakeColonne1= new Colonne(1, 'colonne1',$fakeTab1,0);
        $fakeColonne2= new Colonne(2, 'colonne2',$fakeTab1,1);
        self::$colonneRepository->ajouter($fakecolonneAADD);
        $this->assertEquals([$fakeColonne1,$fakeColonne2,$fakecolonneAADD],self::$colonneRepository->recupererColonnesTableau(1));
    }

    /**Test mettre a jour*/

    public function testMettreAJour(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeColonne1= new Colonne(1, 'coloooooonne1',$fakeTab1,0);
        self::$colonneRepository->mettreAJour($fakeColonne1);
        $this->assertEquals('coloooooonne1', self::$colonneRepository->recupererParClePrimaire(1)->getTitreColonne());
    }

    /**Test supprimer*/

    public function testSupprimer(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeColonne2= new Colonne(2, 'colonne2',$fakeTab1,1);
        self::$colonneRepository->supprimer(1);
        $this->assertEquals([$fakeColonne2], self::$colonneRepository->recupererColonnesTableau(1));
    }
}