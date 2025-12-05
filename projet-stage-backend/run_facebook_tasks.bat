@echo off
cd /d "C:\Users\safa\Desktop\stage\Nouveau dossier (2)\LinkedIn_projet\projet-stage-backend"

C:\xampp\php\php.exe artisan facebook:check-fan-count

C:\xampp\php\php.exe artisan app:facebook-save-my-posts 712621068610746

C:\xampp\php\php.exe artisan app:facebook-save-my-posts 113075436909745

C:\xampp\php\php.exe artisan app:facebook-save-my-stats 712621068610746
C:\xampp\php\php.exe artisan app:facebook-save-my-stats 113075436909745

C:\xampp\php\php.exe artisan app:facebook-save-my-posts-reactions 4213512118886237 712621068610746

C:\xampp\php\php.exe artisan app:facebook-save-my-posts-reactions 4213512118886237 113075436909745



C:\xampp\php\php.exe artisan app:update-other-pages-details 4213512118886237 SopraHR 1400925603534421

REM get other pages details.
C:\xampp\php\php.exe artisan app:facebook-save-pages-posts-reactions 4213512118886237 1400925603534421


