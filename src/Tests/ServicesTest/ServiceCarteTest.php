<?php

namespace App\Trellotrolle\Tests\ServicesTest;
use App\Trellotrolle\Modele\DataObject\Carte;
use App\Trellotrolle\Modele\DataObject\Colonne;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\CarteRepositoryInterface;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Modele\Repository\TableauRepositoryInterface;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\Exception\ServiceException;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceCarteInterface;
use PHPUnit\Framework\TestCase;

class ServiceCarteTest extends TestCase
{
    private ServiceCarteInterface $serviceCarte;

    private CarteRepositoryInterface $carteRepository;
    private UtilisateurRepositoryInterface $utilisateurRepository;
    private TableauRepositoryInterface $tableauRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carteRepository=$this->createMock(CarteRepository::class);
        $this->utilisateurRepository=$this->createMock(UtilisateurRepository::class);
        $this->tableauRepository=$this->createMock(TableauRepository::class);
        $this->serviceCarte=new ServiceCarte($this->carteRepository,$this->utilisateurRepository,$this->tableauRepository);

    }

    /** recupererCarte */

    public function testRecupererCarteManquante()
    {
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Code de carte manquant");
        $this->serviceCarte->recupererCarte(null);
    }

    public function testRecupererCarteInexistante()
    {
        $this->expectExceptionCode(404);
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Carte inexistante");
        $this->carteRepository->method("recupererParClePrimaire")->willReturn(null);
        $this->serviceCarte->recupererCarte(1);
    }

    public function testRecupererCarteValide()
    {
        $fakeCarte=$this->createFakeCarte();
        $this->carteRepository->method("recupererParClePrimaire")->willReturn($fakeCarte);
        $carte=$this->serviceCarte->recupererCarte(1);
        self::assertEquals($carte,$fakeCarte);
    }

    /** supprimerCarte */

    public function testSupprimerCarte()
    {

    }

    /** recupererAttributs */

    /** newCarte */

    /** miseAJourCarteMembre */

    /** carteUpdate */

    /** miseAJourCarte */

    /** verificationsMiseAJourCarte */

    /** creerCarte */

    /** getNextIdCarte */

    /** deplacerCarte */

    public function testDeplacerCarte()
    {
        $fakeCarte=$this->createFakeCarte();
        $fakeColonne=$this->createFakeColonne();
        $this->carteRepository->method("mettreAJour")->willReturnCallback(function ($carte) use($fakeColonne,$fakeCarte){
            self::assertEquals($fakeColonne,$carte->getColonne());
            self::assertEquals($fakeCarte->getIdCarte(),$carte->getIdCarte());
            self::assertEquals($fakeCarte->getCouleurCarte(),$carte->getCouleurCarte());
            self::assertEquals($fakeCarte->getDescriptifCarte(),$carte->getDescriptifCarte());
            self::assertEquals($fakeCarte->getTitreCarte(),$carte->getTitreCarte());
        });
        $this->serviceCarte->deplacerCarte($fakeCarte,$fakeColonne);
    }

    /** FONCTIONX UTILITAIRES */

    private function createFakeCarte($idCarte=1):Carte
    {
        return new Carte($idCarte,"titre","desc","couleur",new Colonne(2,"titre",null));
    }

    private function createFakeColonne($idColonne=1):Colonne
    {
        return new Colonne($idColonne,"titre",null);
    }
}