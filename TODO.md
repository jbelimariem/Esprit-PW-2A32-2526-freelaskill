# TODO - TalentBridge

## Priorite haute

- [ ] Verifier la connexion a la base de donnees dans `config.php`.
- [ ] Nettoyer les scripts temporaires de correction de base de donnees (`db_check.php`, `fix_db.php`, `fix_fk.php`, `patch_db.php`) apres validation.
- [ ] Securiser toutes les requetes SQL avec des requetes preparees.
- [ ] Ajouter une validation cote serveur pour les formulaires produit, categorie et commande.
- [ ] Gerer les messages d'erreur et de succes de maniere uniforme dans le frontoffice et le backoffice.

## Produits et categories

- [ ] Finaliser l'ajout, la modification, la suppression et l'affichage des produits.
- [ ] Finaliser l'ajout, la modification, la suppression et l'affichage des categories.
- [ ] Verifier l'upload des images produit et bloquer les fichiers non autorises.
- [ ] Ajouter une confirmation avant suppression d'un produit ou d'une categorie.
- [ ] Tester le workflow de validation des produits en attente dans le backoffice.

## Fonctionnalites metier simples

- [ ] Ajouter le tri des produits par prix, nom, categorie et date d'ajout.
- [ ] Ajouter le tri des categories par nom et nombre de produits.
- [ ] Ajouter le tri des commandes par date, total et statut.
- [ ] Ajouter une recherche produit par nom, description ou categorie.
- [ ] Ajouter une recherche categorie par nom.
- [ ] Ajouter une recherche commande par client, reference ou statut.
- [ ] Ajouter des filtres simples dans les listes backoffice.
- [ ] Ajouter des statistiques globales dans `Views/Backoffice/dashboard.php`.
- [ ] Afficher le nombre total de produits, categories, commandes, ventes et achats.
- [ ] Afficher le chiffre d'affaires total des commandes validees.
- [ ] Integrer Chart.js dans `Views/Backoffice/dashboard.php`.
- [ ] Ajouter un graphique Chart.js pour les ventes par mois.
- [ ] Ajouter un graphique Chart.js pour les produits par categorie.
- [ ] Ajouter un graphique Chart.js pour les commandes par statut.
- [ ] Verifier que les statistiques se mettent a jour depuis la base de donnees.

## Fonctionnalites CRUD

- [ ] CRUD produits: ajouter un produit.
- [ ] CRUD produits: afficher la liste des produits.
- [ ] CRUD produits: afficher le detail d'un produit.
- [ ] CRUD produits: modifier un produit.
- [ ] CRUD produits: supprimer un produit.
- [ ] CRUD categories: ajouter une categorie.
- [ ] CRUD categories: afficher la liste des categories.
- [ ] CRUD categories: modifier une categorie.
- [ ] CRUD categories: supprimer une categorie.
- [ ] CRUD commandes: afficher la liste des commandes.
- [ ] CRUD commandes: afficher le detail d'une commande.
- [ ] CRUD commandes: modifier le statut d'une commande.
- [ ] CRUD commandes: supprimer ou annuler une commande.
- [ ] Ajouter des messages de confirmation apres chaque action CRUD.
- [ ] Ajouter une validation des champs avant chaque creation ou modification.

## Commandes et panier

- [ ] Tester l'ajout au panier depuis la page detail produit.
- [ ] Verifier le calcul du total panier et du total commande.
- [ ] Finaliser la creation de commande depuis `api/create_order.php`.
- [ ] Afficher clairement l'historique des achats et des ventes.
- [ ] Ajouter une page de confirmation fiable apres chaque commande.

## Interface utilisateur

- [ ] Harmoniser le style entre `Views/assets/style.css` et `Views/Backoffice/css.css`.
- [ ] Verifier l'affichage responsive des pages frontoffice.
- [ ] Verifier l'affichage responsive des pages backoffice.
- [ ] Corriger les textes, labels et messages en francais.
- [ ] Ajouter des etats vides utiles pour les listes sans donnees.
