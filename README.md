---
lang: fr
---
# SAE4A - Trello-Trollé

Rendu du projet de SAE4A.

## Lien du site 

[]

## Liste des fonctionnalités améliorées 

- **Ajout de la template Twig** : Pour faciliter la programmation et améliorer la sécurité des différentes vues du site.
- **Ajout de la couche service** : Pour respecter le principe de single responsability et permettre de lever des exceptions.
- **Ajout d'un système de routage** : Pour gérer les différentes routes du site.
- **Ajout d'un conteneur YAML et utilisation de l'injection de dépendances** : Pour gérer les différentes dépendances du site.
- **Utilisation du JWT** : Pour gérer les sessions des utilisateurs.
- **Utilisation de requêtes préparées** : Pour éviter les injections SQL.
- **Correction de la fonction de d'oubli du mot de passe** : Pour préférer la modification du mot de passe à la récupération.
- **Correction des droits d'accès** : Pour éviter des accès non autorisés sur les tableaux.
- **Ajout de la fonctionnalité de drag and drop** : Pour déplacer les cartes d'une colonne à une autre plus aisément.
- **Dynamisation des pages via AJAX** : Pour éviter de recharger les pages à chaque action de façon à améliorer l'expérience utilisateur.
- **Ajout d'objets réactifs** : Pour que certains aspects soient synchronisés.
- **Refonte graphique** : Pour améliorer l'aspect visuel du site et le rendre responsive.

## Répartition des tâches

- **Maëlys BOISSEZON** : Passage au TWIG/Tests/échappement JS/Interfaces/Virtualisation/Mise en page Rapport
- **Romain TOUZÉ** : Passage à l'API/Routing/Conteneur/Tests/JWT/erreurs HTTP/Services
- **Lorick VERGNES** : Refactor de la BD/DataObject/Repositories/MDP oubliés par mailer/sécurité
- **Sascha LEVY** : Routing/PhpDoc
- **Thomas LOYE** : Objets réactifs/Utilisation API/Drag and Drop/JavaScript Doc/Refonte Graphique/MessagesFlash JS