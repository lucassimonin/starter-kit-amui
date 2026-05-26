# Starter kit Amui studio


## Installation

1. **Cloner et installer les dépendances PHP**

   ```bash
   make install
   ```

2. **Base de données et migrations**

   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

3. **Jeu de données de démonstration (one page type `amui.html`)**

   ```bash
   php bin/console doctrine:fixtures:load --no-interaction
   ```

   Cela crée :
   - une page d’accueil publiée avec blocs `hero`, `gallery`, `about`, `contact` (ancres `#top`, `#projets`, `#agence`, `#contact`) ;
   - un compte admin **uniquement pour l’environnement de démo** : `admin@starter.kit` / `admin-admin` (changez ou supprimez ce compte avant toute mise en production).

4. **Front public**

   Après les fixtures, ouvrez `/` pour la démo. Les messages du formulaire de contact sont enregistrés dans l’entité `ContactMessage` (back-office EasyAdmin).

