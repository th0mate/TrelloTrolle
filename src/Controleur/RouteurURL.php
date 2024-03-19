<?php

namespace App\Trellotrolle\Controleur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use App\Trellotrolle\Service\ServiceCarte;
use App\Trellotrolle\Service\ServiceColonne;
use App\Trellotrolle\Service\ServiceConnexion;
use App\Trellotrolle\Service\ServiceTableau;
use App\Trellotrolle\Service\ServiceUtilisateur;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use App\Trellotrolle\Controleur\ControleurUtilisateur;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlHelper;
use App\Trellotrolle\Lib\ConnexionUtilisateur;
use App\Trellotrolle\Lib\Conteneur;
use App\Trellotrolle\Lib\MessageFlash;
use TheFeed\Lib\ConnexionUtilisateurSession;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use App\Trellotrolle\Modele\Repository\ConnexionBaseDeDonnees;
use App\Trellotrolle\Modele\Repository\UtilisateurRepository;
use App\Trellotrolle\Configuration\ConfigurationBaseDeDonnees;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use App\Trellotrolle\Lib\AttributeRouteControllerLoader;


class RouteurURL
{

    public static function traiterRequete(Request $requete) : Response
    {
        $conteneur = new ContainerBuilder();
        $conteneur->setParameter('project_root', __DIR__.'/../..');

        $loader = new YamlFileLoader($conteneur, new FileLocator(__DIR__."/../Configuration"));
        //On remplit le conteneur avec les données fournies dans le fichier de configuration
        $loader->load("conteneur.yml");


        //Après l'instanciation de l'objet $request
        $conteneur->set('request_context', (new RequestContext())->fromRequest($requete));

        //Après que les routes soient récupérées
        $fileLocator = new FileLocator(__DIR__);
        $attrClassLoader = new AttributeRouteControllerLoader();
        $routes = (new AttributeDirectoryLoader($fileLocator, $attrClassLoader))->load(__DIR__);
        $conteneur->set('routes', $routes);


        $contexteRequete = $conteneur->get('request_context');
        $conteneur->set("container",$conteneur);

        $assistantUrl = $conteneur->get("url_helper");
        $generateurUrl = $conteneur->get("url_generator");

        //$twigLoader = new FilesystemLoader(__DIR__ . '/../vue/');
        $twig = $conteneur->get('twig');
        $twig->addFunction(new TwigFunction("route",function ($name,$parameters=[]) use ($generateurUrl){
            return $generateurUrl->generate($name,$parameters);
        }));
        $twig->addFunction(new TwigFunction("asset",function ($path) use ($assistantUrl){
            return $assistantUrl->getAbsoluteUrl($path);
        }));
        $twig->addGlobal('idUtilisateurConnecte', ConnexionUtilisateur::getLoginUtilisateurConnecte());
        $twig->addGlobal("messagesFlash",new MessageFlash());


        $controleurGenerique=$conteneur->get("controleur_generique");
        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            /*
             * NoConfigurationException
             * MethodNotAllowedException
             * RessourceNotFoundException
             * InvalidArgumentException
             * RunTimeException
             */

            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
            $controleur = $resolveurDeControleur->getController($requete);
            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            $reponse = call_user_func_array($controleur, $arguments);
        } catch ( ResourceNotFoundException $exception) {
            $reponse = $controleurGenerique->afficherErreur($exception->getMessage(),404) ;
        } catch(MethodNotAllowedException $exception){
            $reponse = $controleurGenerique->afficherErreur($exception->getMessage(),405) ;
        }catch ( \Exception $exception){
            $reponse = $controleurGenerique->afficherErreur($exception->getMessage()) ;
        }
        return $reponse;

    }
}