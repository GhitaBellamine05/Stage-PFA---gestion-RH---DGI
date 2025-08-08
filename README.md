# Stage-PFA---gestion-RH---DGI

##  Description
Ce projet est un système de gestion des ressources humaines développé pour la **DGI** dans le cadre du **PFA**.  
Il utilise **PHP** et **MySQL**, et fonctionne en local grâce à **XAMPP**.

---

## 🚀 Installation et démarrage

###  Installer XAMPP
- Télécharger et installer [XAMPP](https://www.apachefriends.org/download.html) selon votre système d’exploitation.
- Lancer **Apache** et **MySQL** depuis le panneau de contrôle XAMPP.

---

###  Placer le projet dans `htdocs`
- Copier le dossier du projet "stage"  dans :   C:/xampp/htdocs/

###  Importer la base de données
1. Ouvrir le navigateur et accéder à :  http://localhost/phpmyadmin
2. Créer une nouvelle base de données `users`.
3. Dans cette base, cliquer sur **Importer**.
4. Sélectionner le fichier :   /sql/users.sql  qui se trouve dans le dossier du projet
5. Cliquer sur **Exécuter** pour importer la base de données.

---

###  Lancer le projet
- Dans le navigateur, aller à :  http://localhost/stage/home


