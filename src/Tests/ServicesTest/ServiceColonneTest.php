<?php

namespace App\Trellotrolle\Tests\ServicesTest;

use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\DataObject\Tableau;
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

    /** supprimerColonne */

    public function testSupprimerColonneErreur()
    {
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("Erreur lors de la suppression d'une carte");
        $fakeTableau=new Tableau(1,"code","titre",null);
        $this->colonneRepository->method("supprimer")->willReturn(false);
        $this->serviceColonne->supprimerColonne($fakeTableau,"2");
    }
    public function testSupprimerColonneValide()
    {
        $this->expectNotToPerformAssertions();
        $fakeTableau=new Tableau(1,"code","titre",null);
        $this->colonneRepository->method("supprimer")->willReturn(true);
        $this->serviceColonne->supprimerColonne($fakeTableau,"2");
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

    /** creerColonne */

    /** miseAJourColonne */

    /** getNextIdColonne */

    /** inverserOrdreColonnes */

    /** FONCTIONS UTILITAIRES */

    private function createFakeColonne($idColonne=1):Colonne
    {
        return  new Colonne($idColonne,"titre",null);
    }
}