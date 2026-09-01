# Première mise en production — cv.pierrebultez.com

À faire une seule fois. Les déploiements suivants passent par GitHub Actions.

| | |
|---|---|
| Domaine | `cv.pierrebultez.com` |
| Chemin | `/var/www/cv` |
| Utilisateur SSH | `pierre`, port `9438` |
| PHP | 8.4.11 (FPM), pilote image **imagick** |
| Base | MariaDB 11.8 |

## 1. Clé de déploiement

Depuis votre poste, pas depuis le serveur :

```bash
ssh-keygen -t ed25519 -C "github-actions-cv-studio" -f ~/.ssh/cv_studio_deploy -N ""
```

Autorisez la clé publique sur le serveur :

```bash
ssh-copy-id -i ~/.ssh/cv_studio_deploy.pub -p 9438 pierre@cv.pierrebultez.com
```

La clé **privée** (`~/.ssh/cv_studio_deploy`, contenu entier, en-têtes compris)
devient le secret `SSH_PRIVATE_KEY`.

## 2. Secrets GitHub

`Settings` → `Secrets and variables` → `Actions` :

| Secret | Valeur |
|---|---|
| `SSH_HOST` | `cv.pierrebultez.com` — ou l'IP tant que le DNS n'a pas propagé |
| `SSH_USER` | `pierre` |
| `SSH_PORT` | `9438` |
| `SSH_PRIVATE_KEY` | contenu de `~/.ssh/cv_studio_deploy` |
| `DEPLOY_PATH` | `/var/www/cv` |
| `FPM_RELOAD_COMMAND` | facultatif, voir l'étape 7 |

## 3. Dossier

```bash
sudo mkdir -p /var/www/cv && sudo chown pierre:pierre /var/www/cv
```

Le déploiement écrit en tant que `pierre` ; PHP-FPM lit en tant que `www-data`.
Il faut donc que `www-data` puisse traverser le dossier et écrire dans `storage`
et `bootstrap/cache` :

```bash
sudo usermod -aG pierre www-data
sudo chmod 750 /var/www/cv
```

`storage/` est exclu du `rsync` — il porte les photos et les journaux, qui
appartiennent au serveur, pas au dépôt. Son squelette est donc créé par le
workflow de déploiement à chaque passage. C'est nécessaire : sans
`storage/framework/views`, `artisan optimize` échoue sur un « View path not
found » qui ne désigne pas `resources/views` mais `config('view.compiled')`.

## 4. Base de données

```bash
sudo mariadb
```

```sql
CREATE DATABASE cv_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cv_studio'@'localhost' IDENTIFIED BY 'UN_MOT_DE_PASSE_SOLIDE';
GRANT ALL PRIVILEGES ON cv_studio.* TO 'cv_studio'@'localhost';
FLUSH PRIVILEGES;
```

## 5. Premier déploiement

Poussez sur `main` : la CI puis le déploiement remplissent `/var/www/cv`.

Le `.env` appartient au serveur et n'est jamais transmis par `rsync`. Au premier
passage il n'existe donc pas encore, et le workflow saute ses commandes artisan
en le signalant — c'est attendu, pas un échec. Il reste à le créer :

```bash
cd /var/www/cv
cp .env.example .env
php artisan key:generate
```

Puis éditez `.env` :

```bash
APP_NAME="CV Studio"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cv.pierrebultez.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cv_studio
DB_USERNAME=cv_studio
DB_PASSWORD=UN_MOT_DE_PASSE_SOLIDE

SESSION_DRIVER=database
FILESYSTEM_DISK=local

# Le GD de cet hôte n'a pas été compilé avec libavif, son Imagick si.
CV_IMAGE_DRIVER=imagick
CV_RETENTION_MONTHS=12
```

`APP_DEBUG=false` n'est pas cosmétique : c'est l'affichage des avertissements
PHP qui rend les réponses JSON illisibles côté client, et qui exposerait les
traces d'exécution.

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Pour publier le CV d'exemple auquel renvoie la page d'accueil :

```bash
php artisan db:seed --force
```

Le contenu est fictif et le jeton d'édition est tiré au hasard à chaque
passage. La commande l'affiche une seule fois : conservez-le si vous comptez
retoucher l'exemple, sinon ignorez-le — il suffira de relancer le seeder.

## 6. nginx et TLS

```bash
sudo cp /var/www/cv/deploy/nginx/cv.pierrebultez.com.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/cv.pierrebultez.com.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Vérifiez au passage le chemin de la socket FPM, la configuration suppose
`/run/php/php8.4-fpm.sock` :

```bash
ls /run/php/
```

Une fois le DNS propagé :

```bash
sudo certbot --nginx -d cv.pierrebultez.com
```

Certbot ajoute lui-même le bloc TLS et la redirection depuis le port 80.

## 7. Ordonnanceur et OPcache

La purge des CV inactifs (12 mois) passe par l'ordonnanceur Laravel :

```bash
crontab -e -u pierre
```

```
* * * * * cd /var/www/cv && php artisan schedule:run >> /dev/null 2>&1
```

Si `opcache.validate_timestamps=0` dans votre configuration PHP, le code
déployé ne serait pris en compte qu'au redémarrage de FPM. Autorisez alors le
rechargement sans mot de passe :

```bash
echo 'pierre ALL=(ALL) NOPASSWD: /bin/systemctl reload php8.4-fpm' | sudo tee /etc/sudoers.d/cv-deploy
```

puis définissez le secret `FPM_RELOAD_COMMAND` à
`sudo systemctl reload php8.4-fpm`.

## 8. Vérifications

```bash
php -r 'require "/var/www/cv/vendor/autoload.php"; var_dump(extension_loaded("imagick"), in_array("AVIF", Imagick::queryFormats("AVIF"), true));'
```

Puis dans un navigateur : créer un CV, envoyer une photo, contrôler que
`storage/app/public/cv-photos/<id>/photo.avif` existe, ouvrir la page publique
et imprimer.
