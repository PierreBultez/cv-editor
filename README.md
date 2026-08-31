# CV Studio

Générateur de CV en ligne : on remplit ses expériences, ses formations et ses
compétences, on choisit deux couleurs et deux polices, on ajoute sa photo, et
l'aperçu au format A4 se met à jour à chaque frappe. Impression navigateur pour
obtenir le PDF, lien public pour partager.

Le projet est né d'un CV personnel statique, conservé dans [`legacy/`](legacy/) :
sa mise en page est devenue le template « Classique » de l'outil.

## Stack

| Couche | Choix |
|---|---|
| Back | Laravel 13, SQLite en dev |
| Pont | Inertia 3 |
| Front | Vue 3, Vite 8, TypeScript, Pinia |
| UI | Nuxt UI 4 en **mode Vue standalone** (pas de Nuxt), Tailwind CSS v4 |
| Images | Intervention Image 4 (GD) |
| Tests | Pest 4 |

> Nuxt n'est pas utilisé : Nuxt et Inertia se disputeraient le routage. Nuxt UI
> s'installe sans lui via `@nuxt/ui/vite` et `@nuxt/ui/vue-plugin`, configuré
> avec `router: 'inertia'`.

## Pas de comptes

Il n'y a ni inscription, ni connexion, ni table `users`. Un CV est identifié par
un `public_id` (ULID non devinable) pour la lecture, et protégé en écriture par
un `edit_token` de 48 caractères, **stocké haché** en base et conservé côté
visiteur dans le `localStorage`.

Conséquence assumée : effacer les données du site, changer de navigateur ou
naviguer en privé fait perdre l'accès en écriture. Le CV reste consultable par
son lien public.

## Démarrer

Prérequis : [Laravel Herd](https://herd.laravel.com) (fournit PHP 8.4, Composer,
nginx) et Node 20+.

```bash
composer install
```

```bash
npm install
```

```bash
php artisan migrate --seed
```

```bash
php artisan storage:link
```

Puis, dans deux terminaux :

```bash
npm run dev
```

```bash
php artisan serve
```

Le CV de démonstration est servi sur `/cv/01K0DEMXCV0000000000000000`.

## Tests

```bash
php artisan test
```

## Déploiement

Deux workflows GitHub Actions dans [`.github/workflows`](.github/workflows) :

- **`ci.yml`** — Pint, Pest, typage et build à chaque push. Un second job rejoue
  les migrations sur **MariaDB 11.8**, parce que la production n'a ni `sqlite3`
  ni `pdo_sqlite` : une incompatibilité de schéma se découvrirait sinon au
  déploiement.
- **`deploy.yml`** — se déclenche seulement si la CI est verte. Les dépendances
  PHP et les assets sont compilés dans la CI puis expédiés par `rsync`, ce qui
  évite d'exiger Composer ou Node sur le serveur.

### Secrets à définir

| Secret | Rôle |
|---|---|
| `SSH_HOST`, `SSH_USER`, `SSH_PORT` | Connexion au serveur |
| `SSH_PRIVATE_KEY` | Clé privée dédiée au déploiement |
| `DEPLOY_PATH` | Racine de l'application sur le serveur |
| `FPM_RELOAD_COMMAND` | Facultatif, ex. `sudo systemctl reload php8.4-fpm` |

### Prérequis serveur

PHP 8.4 avec `gd` (ou `imagick`), `pdo_mysql`, `fileinfo`, `intl`, `mbstring` et
`zip` ; MariaDB ou MySQL ; nginx dont la racine pointe sur `public/`.

À faire une fois, à la main :

```bash
cp .env.example .env && php artisan key:generate && php artisan storage:link
```

Dans `.env` : `APP_ENV=production`, **`APP_DEBUG=false`**, `APP_URL`, et les
identifiants `DB_*`. Le mode debug n'est pas qu'une question de confort — c'est
l'affichage des avertissements PHP qui rend les réponses JSON illisibles côté
client.

La purge des CV inactifs passe par l'ordonnanceur, à déclarer dans le cron de
l'utilisateur qui sert le site :

```bash
* * * * * cd /chemin/vers/le/site && php artisan schedule:run >> /dev/null 2>&1
```

### AVIF en production

L'AVIF dépend de la compilation de l'hôte, pas de la version de PHP : libavif
pour GD, libheif pour Imagick. Les deux ne vont pas ensemble.

**Sur le serveur de production, GD n'a pas l'AVIF mais Imagick l'a.** Le `.env`
de production doit donc porter :

```bash
CV_IMAGE_DRIVER=imagick
```

`gd` reste la valeur par défaut, adaptée au poste de développement. Une
extension demandée mais absente ne met rien en panne : le service enregistre un
avertissement, revient à GD, et l'AVIF est simplement omis du `<picture>`.

Pour vérifier ce dont dispose un hôte :

```bash
php -r 'var_dump(function_exists("imageavif"), extension_loaded("imagick") ? Imagick::queryFormats("AVIF") : "imagick absent");'
```

## Points d'architecture

**Le thème est du CSS, pas un build.** `resources/js/lib/palette.ts` dérive une
échelle de sept nuances à partir d'une seule couleur, en OKLCH : la teinte est
conservée, la luminosité de chaque palier est imposée. Les variables
`--cv-primary-*` sont posées en style inline sur la racine de l'aperçu, donc
changer de couleur ne recompile rien.

La luminosité étant imposée, une couleur très claire ressort plus foncée que
celle choisie — c'est ce qui garantit le contraste des titres sur blanc à
l'impression. Le sélecteur affiche les nuances obtenues pour éviter la surprise.

**Le contenu est une colonne JSON.** Les sections sont hétérogènes,
réordonnables et activables ; sept tables normalisées auraient triplé le travail
sans bénéfice, puisqu'on ne requête jamais entre CV.
[`UpdateCvRequest`](app/Http/Requests/UpdateCvRequest.php) est donc le seul
garant de la forme de `content`, et
[`CvDefaults`](app/Support/CvDefaults.php) la référence unique des valeurs
autorisées — côté serveur comme côté client.

**Les polices sont auto-hébergées au build**, déclarées dans
[`vite.config.ts`](vite.config.ts) via les providers `local` (Satoshi) et
`bunny` de `laravel-vite-plugin`. Chaque famille expose une variable
`--font-{alias}`. Le navigateur ne télécharge une famille qu'au moment où elle
est réellement appliquée.

**L'aperçu est la feuille.** La page fait 210 × 297 mm en dimensions réelles ;
[`A4Frame`](resources/js/components/preview/A4Frame.vue) ne fait que la réduire
par une transformation CSS, neutralisée à l'impression. Ce qui est à l'écran est
ce qui sort en PDF.

## Impression

Le bouton « Imprimer / PDF » masque l'interface et ne sort que la feuille. Dans
la boîte de dialogue du navigateur :

- Destination : **Enregistrer au format PDF**
- Papier : **A4**, marges **Aucune**, échelle **100 %**
- Activer **Graphiques d'arrière-plan**
- Désactiver en-têtes et pieds de page

## Données personnelles

Un service anonyme qui stocke des CV conserve des données personnelles sans que
personne ne puisse revenir les gérer. Trois garde-fous :

- les pages publiques sont en `noindex` par défaut, l'indexation est un choix
  explicite dans l'onglet « Réglages » ;
- un bouton de suppression définitive est disponible dans l'éditeur ;
- `cv:purge` supprime les CV et leurs photos après 12 mois d'inactivité,
  planifié chaque lundi à 3 h.

```bash
php artisan cv:purge --dry-run
```

## AVIF

Les photos sont réencodées en JPEG (repli garanti), WebP et AVIF. `imageavif()`
n'existe que si GD a été compilé avec libavif : le pipeline teste la présence de
l'encodeur et omet simplement la variante sinon, sans jamais faire échouer
l'upload. Le PHP de Herd la fournit.
