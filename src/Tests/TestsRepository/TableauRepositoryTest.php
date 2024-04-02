<?php

namespace App\Trellotrolle\Tests\TestsRepository;


use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonneesInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Tests\ConfigurationBDDTestUnitaires;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class TableauRepositoryTest extends TestCase
{
    private static TableauRepositoryInterface  $tableauRepository;
    private static UtilisateurRepositoryInterface  $utilisateurRepository;

    private static ConnexionBaseDeDonneesInterface $connexionBaseDeDonnees;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$connexionBaseDeDonnees = new ConnexionBaseDeDonnees(new ConfigurationBDDTestUnitaires());
        self::$tableauRepository = new TableauRepository(self::$connexionBaseDeDonnees);
        self::$utilisateurRepository = new UtilisateurRepository(self::$connexionBaseDeDonnees);
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
                                                              participant (login, idTableau)
                                                              VALUES ('bob69',3)");
        self::$connexionBaseDeDonnees->getPdo()->query("INSERT INTO 
                                                              participant (login, idTableau)
                                                              VALUES ('bob560',3)");
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM participant");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM tableau");
        self::$connexionBaseDeDonnees->getPdo()->query("DELETE FROM utilisateur");

    }

    /**  Test recupererTableauxUtilisateur prends : string $login retourne : array*/



    /**  Test recupererParCodeTableau prends :string $codeTableau retourne: ?AbstractDataObject*/

    public function testrecupererParCodeTableauExistant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals($fakeTab3,self::$tableauRepository->recupererParCodeTableau('test3'));

    }
    public function testrecupererParCodeTableauInexistant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertNull(self::$tableauRepository->recupererParCodeTableau('alalalala'));
    }

    /**  Test recupererTableauxOuUtilisateurEstMembre prends :string $login retourne array*/

    public function testrecupererTableauxOuUtilisateurEstMembre(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeTab2 = new Tableau(2, 'test2', 'test2',$fakeUser);
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals([$fakeTab1,$fakeTab2, $fakeTab3], self::$tableauRepository->recupererTableauxOuUtilisateurEstMembre('bob69'));

    }

    public function testrecupererTableauxOuUtilisateurEstMembreSeulementParticipant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals([ $fakeTab3], self::$tableauRepository->recupererTableauxOuUtilisateurEstMembre('bob560'));
    }
    public function testrecupererTableauxOuUtilisateurEstMembrePasDeTableau(){
        $this->assertEquals([], self::$tableauRepository->recupererTableauxOuUtilisateurEstMembre('alTE'));
    }
    public function testrecupererTableauxOuUtilisateurEstMembreInexistant(){
        $this->assertEquals([], self::$tableauRepository->recupererTableauxOuUtilisateurEstMembre('george'));
    }


    /**  Test recupererTableauxParticipeUtilisateur prends : string $login retourne : array*/

    public function testRecupererTableauxParticipeUtilisateurEstParticipants(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals([ $fakeTab3], self::$tableauRepository->recupererTableauxParticipeUtilisateur('bob69'));
    }

    public function testRecupererTableauxParticiperUtilisateurEstPasParticipants(){
        $this->assertEquals([], self::$tableauRepository->recupererTableauxParticipeUtilisateur('bib64'));
    }

    public function testRecupererTableauxParticiperUtilisateurInexistant(){
        $this->assertEquals([ ],self::$tableauRepository->recupererTableauxParticipeUtilisateur('georges'));
    }

    /**  Test getNextIdTableau retourne: int*/

    public function testGetNextIdTableau(){
        $this->assertEquals(4, self::$tableauRepository->getNextIdTableau());
    }

    /**  Test getNombreTableauxTotalUtilisateur prends : string $login returne: int*/

    public function testGetNombreTableauxTotalUtilisateurEnA(){
        $this->assertEquals(2, self::$tableauRepository->getNombreTableauxTotalUtilisateur('bob69'));
    }

    public function testGetNombreTableauxTotalUtilisateurEnQueParticipant(){
        $this->assertEquals(0, self::$tableauRepository->getNombreTableauxTotalUtilisateur('bob560'));
    }
    public function testGetNombreTableauxTotalUtilisateurEnAPas(){
        $this->assertEquals(0, self::$tableauRepository->getNombreTableauxTotalUtilisateur('alTE'));
    }

    public function testGetNombreTableauxTotalUtilisateurInexistant(){
        $this->assertEquals(0, self::$tableauRepository->getNombreTableauxTotalUtilisateur('george'));
    }

    /**  Test estParticipant prends : string $login, Tableau $tableau retourne: bool*/

    public function testEstParticipantUtilisateurEstParticipantDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertTrue(self::$tableauRepository->estParticipant('bob69',$fakeTab3));
    }

    public function testEstParticipantUtilisateurEstPasParticipantDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertFalse(self::$tableauRepository->estParticipant('alTE',$fakeTab3));
    }

    public function testEstParticipantUtilisateurInexistant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertFalse(self::$tableauRepository->estParticipant('george',$fakeTab3));
    }


    /**  Test estProprietaire prends : $login, Tableau $tableau retourne: bool*/

    public function testEstProprietaireUtilisateurEstProprietaireDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertTrue(self::$tableauRepository->estProprietaire('bib420',$fakeTab3));
    }

    public function testEstProprietaireUtilisateurEstPasProprietaireDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertFalse(self::$tableauRepository->estProprietaire('bob69',$fakeTab3));
    }

    public function testEstProprietaireUtilisateurInexistant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab4 = new Tableau(4,'test4', 'test4',$fakeUser2);
        $this->assertFalse(self::$tableauRepository->estProprietaire('bib420',$fakeTab4));
    }

    /**  Test estParticipantOuProprietaire prends: string $login, Tableau $tableau retourne: bool*/

    public function testEstParticipantOuProprietaireUtilisateurEstProprietaireDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertTrue(self::$tableauRepository->estParticipantOuProprietaire('bib420',$fakeTab3));
    }

    public function testEstParticipantOuProprietaireUtilisatecurEstParticipantDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertTrue(self::$tableauRepository->estParticipantOuProprietaire('bob69',$fakeTab3));
    }

    public function testEstParticipantOuProprietaireUtilisatecurEstPasProprietaireOuParticipantDuTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertFalse(self::$tableauRepository->estParticipantOuProprietaire('alTE',$fakeTab3));
    }

    public function testEstParticipantOuProprietaireUtilisateurInexistant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab4 = new Tableau(4,'test4', 'test4',$fakeUser2);
        $this->assertFalse(self::$tableauRepository->estParticipantOuProprietaire('bib420',$fakeTab4));
    }


    /**  Test getParticipants prends Tableau $tableau retourne : ?array*/

    public function testGetParticipantsEnA(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeUser3 = new Utilisateur('bob560','zeblouse','agathe','agathe.zeblouze@jfiu.com','mdpAg',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals([$fakeUser3,$fakeUser], self::$tableauRepository->getParticipants($fakeTab3));
    }

    public function testGetParticipantsEnAPas(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $this->assertEquals([], self::$tableauRepository->getParticipants($fakeTab1));
    }

    public function testGetParticipantsTableauInexistant(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab4 = new Tableau(4,'test4', 'test4',$fakeUser2);
        $this->assertEquals([], self::$tableauRepository->getParticipants($fakeTab4));
    }

    /**  Test setParticipants prends : ?array $participants, Tableau $tableau retourne : void*/

    public function testSetParticipantTableauSansParticipant(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeUser3 = new Utilisateur('bob560','zeblouse','agathe','agathe.zeblouze@jfiu.com','mdpAg',"aaa");
        self::$tableauRepository->setParticipants([$fakeUser2,$fakeUser3],$fakeTab1);
        $this->assertEquals([$fakeUser2,$fakeUser3], self::$tableauRepository->getParticipants($fakeTab1));
    }

    public function testSetParticipantTableauAvecParticipant(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $fakeUser3 = new Utilisateur('alTE','terrieur','alain','alain.terrieur@terrieur.com','mdpAl',"aaa");
        self::$tableauRepository->setParticipants([$fakeUser,$fakeUser3],$fakeTab3);
        $this->assertEquals([$fakeUser3,$fakeUser], self::$tableauRepository->getParticipants($fakeTab3));
    }

    public function testSetParticipantTableauInexistant(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(4, 'test4', 'test4',$fakeUser);
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeUser3 = new Utilisateur('bob560','zeblouse','agathe','agathe.zeblouze@jfiu.com','mdpAg',"aaa");
        self::$tableauRepository->setParticipants([$fakeUser2,$fakeUser3],$fakeTab1);
        $this->assertEquals([], self::$tableauRepository->getParticipants($fakeTab1));
    }

    /**  Test getProprietaire prends : Tableau $tableau retourne: Utilisateur*/

    public function testGetProprietaire(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals($fakeUser2, self::$tableauRepository->getProprietaire($fakeTab3));
    }

    /**  Test getAllFromTableau prends : int $idTableau retounre : array*/

    public function testGetAllFromTableau(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals($fakeTab3, self::$tableauRepository->getAllFromTable(3));
    }

    /** Test  récuperer */

    public function testRecuperer(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $fakeTab2 = new Tableau(2, 'test2', 'test2',$fakeUser);
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        $this->assertEquals([$fakeTab1,$fakeTab2,$fakeTab3], self::$tableauRepository->recuperer());
    }

    /** Test récupererParCléPrimaire */

    public function testRecupererParClePrimaireExistant(){
        $fakeUser= new Utilisateur('bob69','bobby','bob','bob.bobby@bob.com','mdpBob',"aaa");
        $fakeTab1 = new Tableau(1, 'test', 'test',$fakeUser);
        $this->assertEquals($fakeTab1, self::$tableauRepository->recupererParClePrimaire(1));
    }

    public function testRecupererParClePrimaireInexistant(){
        $this->assertNull( self::$tableauRepository->recupererParClePrimaire(4));
    }

    /** Test ajouter */

    public function testAjouter(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab4 = new Tableau(4,'test4', 'test4',$fakeUser2);
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        self::$tableauRepository->ajouter($fakeTab4);
        $this->assertEquals([$fakeTab3, $fakeTab4], self::$tableauRepository->recupererTableauxUtilisateur('bib420'));
    }

    /** Test mettre à jour */

    public function testMettreAjour(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");
        $fakeTab1 = new Tableau(1, 'test1', 'test',$fakeUser2);
        $fakeTab3 = new Tableau(3,'test3', 'test3',$fakeUser2);
        self::$tableauRepository->mettreAJour($fakeTab1);
        $this->assertEquals([$fakeTab1,$fakeTab3], self::$tableauRepository->recupererTableauxUtilisateur('bib420'));
        $this->assertEquals('test1', self::$tableauRepository->recupererTableauxUtilisateur('bib420')[0]->getCodeTableau());
    }

    /** Test supprimer */
    public function testSupprimer(){
        $fakeUser2 = new Utilisateur('bib420','bibby','bib','bib.bibby@bob.com','mdpBib',"aaa");

        self::$tableauRepository->supprimer(3);
        $this->assertEquals([], self::$tableauRepository->recupererTableauxUtilisateur('bib420'));

    }

    /**Test supprimer utilisateur, supprime tableau aussi*/

    public function testSupprimerUtilisateurSupprimeTableau(){
        self::$utilisateurRepository->supprimer('bib420');
        $this->assertNull(self::$tableauRepository->recupererParClePrimaire(3));
    }

}