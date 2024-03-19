<?php


namespace App\Trellotrolle;
use App\Trellotrolle\Controleur\RouteurURL;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../vendor/autoload.php";
$requete=Request::createFromGlobals();
$response=RouteurURL::traiterRequete($requete);
$response->send();


