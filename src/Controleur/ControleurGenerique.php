<?php

namespace App\Trellotrolle\Controleur;

use App\Trellotrolle\Lib\Conteneur;
use App\Trellotrolle\Lib\MessageFlash;
use App\Trellotrolle\Service\Exception\ConnexionException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ControleurGenerique {


    /**
     * ControleurGenerique constructor.
     * @param ContainerInterface $container le conteneur de dépendances
     * fonction qui permet de construire le controleur generique
     */

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param string $cheminVue le chemin de la vue
     * @param array $parametres les parametres de la vue
     * @return Response la réponse
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

    /**
     * @param string $route le nom de route
     * @param array $parameters les parametres de la route
     * @return RedirectResponse
     * fonction qui permet de rediriger vers une route avec des parametres
     */
    protected function redirection(string $route = "accueil", array $parameters = []) : RedirectResponse
    {
        $generateurUrl= $this->container->get("url_generator");
        $url = $generateurUrl->generate($route, $parameters);
        var_dump($url);
        return new RedirectResponse($url);
    }

    /**
     * @param string $messageErreur le message d'erreur
     * @param string $controleur le nom du controleur
     * @return Response la page d'erreur
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
        return $this->afficherTwig('error.html.twig',["errorMessage" => $messageErreurVue]);
    }


    /**
     * @param array $requestParams les parametres de la requete
     * @return bool vrai si les parametres sont bien definis et non null
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
     * @param ConnexionException $e l'exception de connexion
     * @return Response la redirection vers la page de connexion avec un message flash
     *
     * fonction qui permet de rediriger vers la page de connexion avec un message flash
     */

    protected function redirectionConnectionFlash(ConnexionException $e): Response
    {
        MessageFlash::ajouter("info", $e->getMessage());
        return self::redirection("afficherFormulaireConnexion");
    }

    /**
     * @param string $cheminVue le chemin de la vue
     * @param array $parametres les parametres de la vue
     * @return Response la réponse
     * @throws LoaderError si le chargement de la vue a échoué
     * @throws RuntimeError si une erreur est survenue lors de l'exécution du code
     * @throws SyntaxError si une erreur de syntaxe est survenue
     *
     * fonction qui permet d'afficher une vue twig via le chemin de la vue et les parametres
     */
    protected function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = $this->container->get("twig");
        $corpsReponse = $twig->render($cheminVue, $parametres);
        return new Response($corpsReponse);
    }

}