@echo off
:: Se rendre dans le répertoire du projet Laravel
cd /d "C:\Users\safa\Desktop\stage\Nouveau dossier (2)\LinkedIn_projet\projet-stage-backend"

echo [INFO] Lancement de fetch:statistics
"C:\xampp\php\php.exe" artisan fetch:statistics >> logs.txt 2>&1

echo [INFO] Lancement de fetch:update-other-posts-data
"C:\xampp\php\php.exe" artisan fetch:update-other-posts-data >> logs.txt 2>&1

echo [INFO] Lancement de fetch:update-organization 
"C:\xampp\php\php.exe" artisan fetch:update-organization >> logs.txt 2>&1


echo [INFO] Lancement de fetch:statistics
"C:\xampp\php\php.exe" artisan fetch:statistics >> logs.txt 2>&1

echo [INFO] Lancement de fetch:update-my-posts-data 2
"C:\xampp\php\php.exe" artisan fetch:update-my-posts-data >> logs.txt 2>&1

:: Exécuter les commandes Artisan avec PHP
echo [INFO] Lancement de posts:check-thresholds
"C:\xampp\php\php.exe" artisan posts:check-thresholds >> logs.txt 2>&1



:: Afficher le contenu des logs dans la console
echo [INFO] Affichage des logs :
type logs.txt

:: Fin de l'exécution
echo [INFO] Exécution terminée
