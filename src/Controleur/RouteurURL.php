<?php

namespace App\Trellotrolle\Controleur;
use App\Trellotrolle\Modele\Repository\CarteRepository;
use App\Trellotrolle\Modele\Repository\ColonneRepository;
use App\Trellotrolle\Modele\Repository\TableauRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public static function traiterRequete()
    {
        $requete = Request::createFromGlobals();
        $contexteRequete = (new RequestContext())->fromRequest($requete);
        $routes = new RouteCollection();
        $conteneur = new ContainerBuilder();

        $conteneur->register('configuration_bdd_postgresql', ConfigurationBaseDeDonnees::class);

        $connexionBaseService = $conteneur->register('connexion_base_de_donnees', ConnexionBaseDeDonnees::class);
        $connexionBaseService->setArguments([new Reference('configuration_bdd_my_sql')]);

        $cartesRepositoryService = $conteneur->register('carte_repository',CarteRepository::class);
        $cartesRepositoryService->setArguments([new Reference('connexion_base_de_donnees')]);

        $utilisateurRepositoryService = $conteneur->register('utilisateur_repository',UtilisateurRepository::class);
        $utilisateurRepositoryService->setArguments([new Reference('connexion_base_de_donnees')]);

        $colonnesRepositoryService = $conteneur->register('colonne_repository',ColonneRepository::class);
        $colonnesRepositoryService->setArguments([new Reference('connexion_base_de_donnees')]);

        $tableauxRepositoryService = $conteneur->register('tableau_repository',TableauRepository::class);
        $tableauxRepositoryService->setArguments([new Reference('connexion_base_de_donnees')]);


// Route afficherListe

        $fileLocator = new FileLocator(__DIR__);
        $attrClassLoader = new AttributeRouteControllerLoader();
        $routes = (new AttributeDirectoryLoader($fileLocator, $attrClassLoader))->load(__DIR__);

        $twigLoader = new FilesystemLoader(__DIR__ . '/../vue/');
        $twig = new Environment(
            $twigLoader,
            [
                'autoescape' => 'html',
                'strict_variables' => true
            ]
        );
        $twig->addGlobal('messagesFlash', new MessageFlash());
        $twig->addGlobal('idUtilisateurConnecte', ConnexionUtilisateur::getLoginUtilisateurConnecte());
        Conteneur::ajouterService("twig", $twig);

        $generateurUrl = new UrlGenerator($routes, $contexteRequete);
        $assistantUrl = new UrlHelper(new RequestStack(), $contexteRequete);
        Conteneur::ajouterService("generateurUrl", $generateurUrl);
        Conteneur::ajouterService("assistantUrl", $assistantUrl);

        $twig->addFunction(new TwigFunction('asset', function ($url) use ($assistantUrl) {
            return $assistantUrl->getAbsoluteUrl($url);
        }));
        $twig->addFunction(new TwigFunction('route', function ($route, $params = []) use ($generateurUrl) {
            return $generateurUrl->generate($route, $params);
        }));


        try {
            $associateurUrl = new UrlMatcher($routes, $contexteRequete);
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);
            $controleur = $resolveurDeControleur->getController($requete);

            $resolveurDArguments = new ArgumentResolver();
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            $response = call_user_func_array($controleur, $arguments);
        }
        catch (BadRequestException $exception) {
            // Remplacez xxx par le bon code d'erreur
            $response = (new ControleurGenerique())->afficherErreur($exception->getMessage(), 400);
        } catch (NotFoundHttpException $exception) {
            // Remplacez xxx par le bon code d'erreur
            $response = (new ControleurGenerique())->afficherErreur($exception->getMessage(), 404);
        } catch (\Exception $exception) {
            $response = (new ControleurGenerique())->afficherErreur($exception->getMessage()) ;
        }
        $response->send();

    }
}