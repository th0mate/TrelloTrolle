<?php

namespace App\Trellotrolle\Tests\ServicesTest;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
use App\Trellotrolle\Modele\DataObject\Utilisateur;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepositoryInterface;
use App\Trellotrolle\Service\Exception\CreationException;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceColonneInterface;
use PHPUnit\Framework\TestCase;

class ServiceColonneTest extends TestCase
{

    private ServiceColonneInterface $serviceColonne;

    private ColonneRepositoryInterface $colonneRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->colonneRepository=$this->createMock(ColonneRepository::class);
        $this->serviceColonne=new ServiceColonne($this->colonneRepository);
    }

    /** recupererColonne */

    public function testRecupererColonneManquant()
    {
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Identifiant de colonne manquant");
        $this->serviceColonne->recupererColonne(null);
    }

    public function testRecupererColonneInexistante()
    {
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Colonne inexistante");
        $this->colonneRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceColonne->recupererColonne(1);
    }

    public function testRecupererColonneValide()
    {
        $fakeColonne=$this->createFakeColonne();
        $this->colonneRepository->method("recupererParClePrimaire")->willReturn($fakeColonne);
        $colonne=$this->serviceColonne->recupererColonne(1);
        self::assertEquals($fakeColonne,$colonne);
    }
    /** recupererColonneTableau */

    public function testRecupererColonnesTableau()
    {
        $fakeTableau=new Tableau(1,"code","titre",null);
        $fakeColonne=new Colonne("1","titre",$fakeTableau,null);
        $fakeColonne2=new Colonne("2","titre",$fakeTableau,null);
        $this->colonneRepository->method("recupererColonnesTableau")->willReturn([$fakeColonne,$fakeColonne2]);
        $colonnes=$this->serviceColonne->recupererColonnesTableau(1);
        self::assertEquals([$fakeColonne,$fakeColonne2],$colonnes);
    }

    /** supprimerColonne */

    public function testSupprimerColonneValide()
    {
        $fakeTableau=new Tableau(1,"code","titre",null);
        $fakeColonne=new Colonne("1","titre",$fakeTableau,null);
        $this->colonneRepository->method("supprimer")->willReturn(true);
        $this->colonneRepository->method("recupererColonnesTableau")->willReturn([$fakeColonne]);
        $colonnes=$this->serviceColonne->supprimerColonne($fakeTableau,"2");
        self::assertEquals([$fakeColonne],$colonnes);
    }

    /** isSetNomColonne */

    public function testIsNotSetNomColonne()
    {
        $this->expectExceptionCode(404);
        $this->expectException(CreationException::class);
        $this->expectExceptionMessage("Nom de colonne manquant");
        $this->serviceColonne->isSetNomColonne(null);
    }

    public function testIsSetNomColonne()
    {
        $this->expectNotToPerformAssertions();
        $this->serviceColonne->isSetNomColonne("test");
    }

    /** recupererColonneAndNomColonne */

public function testRecupererColonneManquantAndNomColonne()
    {
        $this->expectExceptionMessage("Identifiant de colonne manquant");
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(404);
        $this->serviceColonne->recupererColonneAndNomColonne(null,"nomColonne");
    }
    public function testRecupererColonneInexistanteAndNomColonne()
    {
        $this->expectExceptionMessage("Colonne inexistante");
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(404);
        $this->colonneRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceColonne->recupererColonneAndNomColonne(1,null);
    }
    public function testRecupererColonneAndNomColonneManquant()
    {
        $this->expectExceptionMessage("Nom de colonne manquant");
        $this->expectException(CreationException::class);
        $this->expectExceptionCode(404);
        $this->colonneRepository->method("recupererParClePrimaire")->willReturn($this->createFakeColonne());
        $this->serviceColonne->recupererColonneAndNomColonne(1,null);
    }
    public function testRecupererColonneAndNomColonneValide()
    {
        $this->colonneRepository->method("recupererParClePrimaire")->willReturn($this->createFakeColonne());
        $colonne=$this->serviceColonne->recupererColonneAndNomColonne(1,"nomColonne");
        self::assertEquals($this->createFakeColonne(),$colonne);
    }

    /** creerColonne */

    public function testCreerColonne()
    {
        $fakeTableau=new Tableau("1","code","titre",null);
        $this->colonneRepository->method("getNextIdColonne")->willReturn(2);
        $this->colonneRepository->method("ajouter")->willReturnCallback(function ($colonne)use ($fakeTableau){
            self::assertEquals(2,$colonne->getIdColonne());
            self::assertEquals("nomColonne",$colonne->getTitreColonne());
            self::assertEquals($fakeTableau,$colonne->getTableau());
            return true;
        });
        $this->serviceColonne->creerColonne($fakeTableau,"nomColonne");
    }

    /** miseAJourColonne */

    public function testMiseAJourColonne()
    {
        $fakeColonne=$this->createFakeColonne();
        $this->colonneRepository->method("mettreAJour")->willReturnCallback(function ($colonne)use ($fakeColonne){
            self::assertEquals($fakeColonne,$colonne);
        });
        $colonne=$this->serviceColonne->miseAJourColonne($fakeColonne);
        self::assertEquals($fakeColonne,$colonne);
    }

    /** getNextIdColonne */

    public function testGetNextIdColonne()
    {
        $this->colonneRepository->method("getNextIdColonne")->willReturn(0);
        $id=$this->serviceColonne->getNextIdColonne();
        self::assertEquals("0",$id);
    }

    /** inverserOrdreColonnes */

    /** FONCTIONS UTILITAIRES */

    private function createFakeColonne($idColonne=1):Colonne
    {
        return  new Colonne($idColonne,"titre",null,null);
    }
}