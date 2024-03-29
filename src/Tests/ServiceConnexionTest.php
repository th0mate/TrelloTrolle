<?php

namespace App\Trellotrolle\Tests;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Modele\Repository\UtilisateurRepositoryInterface;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceConnexionInterface;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceTableauInterface;
use PHPUnit\Framework\TestCase;
class ServiceConnexionTest extends TestCase
{

    private ServiceConnexionInterface $serviceConnexion;
    private UtilisateurRepositoryInterface $utilisateurRepository;
    protected function setUp(): void
    {
        parent::setUp();
        $this->utilisateurRepository=$this->createMock(UtilisateurRepository::class);
        $this->serviceConnexion=new ServiceConnexion($this->utilisateurRepository);
    }
    /** pasConnecter */

    public function testPasConnecteTrue()
    {

    }
    public function testPasConnecteFalse()
    {

    }
    /** dejaConnecter */

    public function testDejaConnecterTrue()
    {

    }

    public function testDejaConnecteFalse()
    {

    }

    /** deconnecter */

    public function deconnecterNonConnecte()
    {

    }

    public function deconnecterValide()
    {

    }
    /** connecter */

    public function testConnecterAttributManquant()
    {

    }
    public function testConnecterLoginInconnu()
    {

    }

    public function testConnecterMdpIncorrect()
    {

    }
    public function testConnecterValide()
    {

    }
}