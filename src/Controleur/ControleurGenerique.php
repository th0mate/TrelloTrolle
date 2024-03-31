<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\Conteneur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ControleurGenerique {


    /**
     * ControleurGenerique constructor.
     * @param ContainerInterface $container
     * fonction qui permet de construire le controleur generique
     */

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param string $cheminVue
     * @param array $parametres
     * @return Response
     *
     * fonction qui permet d'afficher une vue via le chemin de la vue et les parametres
     */

    protected function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        extract($parametres);
//        $messagesFlash = $_REQUEST["messagesFlash"] ?? [];
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require __DIR__ . "/../vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    // https://stackoverflow.com/questions/768431/how-do-i-make-a-redirect-in-php
    protected function redirection(string $route = "accueil", array $parameters = []) : RedirectResponse
    {
//        $queryString = [];
//        if ($action != "") {
//            $queryString[] = "action=$action";
//        }c
//        if ($controleur != "") {
//            $queryString[] = "controleur=$controleur";
//        }
//        foreach ($query as $name => $value) {
//            $name = rawurlencode($name);
//            $value = rawurlencode($value);
//            $queryString[] = "$name=$value";
//        }
//        $url = "Location: ./controleurFrontal.php?" . join("&", $queryString);
//        header($url);
        $generateurUrl= $this->container->get("url_generator");
        $url = $generateurUrl->generate($route, $parameters);
        var_dump($url);
        return new RedirectResponse($url);
    }

    /**
     * @param string $messageErreur
     * @param string $controleur
     * @return Response
     *
     * fonction qui permet d'afficher une erreur en indiquant le message d'erreur et le controleur
     */

    public function afficherErreur($messageErreur = "", $controleur = ""): Response
    {
        $messageErreurVue = "Problème";
        if ($controleur !== "")
            $messageErreurVue .= " avec le contrôleur $controleur";
        if ($messageErreur !== "")
            $messageErreurVue .= " : $messageErreur";

        /*return ControleurGenerique::afficherVue('vueGenerale.php', [
            "pagetitle" => "Problème",
            "cheminVueBody" => "erreur.php",
            "messageErreur" => $messageErreurVue
        ]);*/
        return $this->afficherTwig('error.html.twig',["errorMessage" => $messageErreurVue]);
    }

    /**
     * @param array $requestParams
     * @return bool
     *
     * fonction qui permet de verifier si les parametres de la requete sont bien definis et non null
     */

    public function issetAndNotNull(array $requestParams) : bool {
        foreach ($requestParams as $param) {
            if(!(isset($_REQUEST[$param]) && $_REQUEST[$param] != null)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param ConnexionException $e
     * @return Response
     *
     * fonction qui permet de rediriger vers la page de connexion avec un message flash
     */

    protected function redirectionConnectionFlash(ConnexionException $e): Response
    {
        MessageFlash::ajouter("info", $e->getMessage());
        return self::redirection("afficherFormulaireConnexion");
    }
    protected function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

}