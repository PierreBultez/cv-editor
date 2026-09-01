# Civi — notes de reprise

*Faites votre CV, pas votre mise en page.*

Générateur de CV en ligne, **sans comptes**. On remplit un formulaire, l'aperçu
A4 se recompose à chaque frappe, le PDF sort par l'impression navigateur, et un
lien public permet de partager. Déployé sur `cv.pierrebultez.com`.

Ce fichier oriente une session qui reprend le projet. Le [README](README.md)
décrit le produit, [`deploy/SETUP.md`](deploy/SETUP.md) la mise en production.

---

## Faire tourner le projet

Le poste est sous **Windows avec Laravel Herd**. Deux particularités coûtent du
temps si on ne les connaît pas.

**PHP, Composer et Node ne sont pas dans le PATH par défaut** des shells de
l'agent. Préfixer chaque commande :

```powershell
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
```

**Ne pas lancer les serveurs par une tâche d'arrière-plan du harnais.** Son
environnement prive PHP de répertoire temporaire et *tout upload de photo
échoue*. Les lancer détachés depuis un shell au premier plan :

```powershell
Start-Process -FilePath "C:\Users\wendy\.config\herd\bin\php.bat" -ArgumentList "artisan","serve","--port=8000" -WindowStyle Hidden
Start-Process -FilePath "C:\Program Files\nodejs\npm.cmd" -ArgumentList "run","dev" -WindowStyle Hidden
```

Vérifications :

```powershell
./vendor/bin/pint --test    # style PHP
php artisan test            # 31 tests Pest
npm run typecheck           # vue-tsc
npm run build
```

**Vérifier sur l'arbre versionné, pas sur le dossier de travail.** Deux
défaillances de CI sont venues d'artefacts locaux absents du dépôt :

```powershell
git archive -o tree.tar HEAD   # puis extraire ailleurs, composer install, tester
```

---

## Décisions structurantes

Prises et validées ; ne pas les rejouer sans raison.

| Décision | Pourquoi |
|---|---|
| **Pas de comptes** | ULID non devinable pour lire, jeton haché SHA-256 pour écrire, gardé dans le `localStorage`. Le jeton voyage dans le fragment d'URL des liens de modification — jamais transmis au serveur. |
| **`content` en colonne JSON** | Sections hétérogènes, réordonnables, jamais requêtées entre CV. [`UpdateCvRequest`](app/Http/Requests/UpdateCvRequest.php) est le seul garant de la forme, [`CvDefaults`](app/Support/CvDefaults.php) la référence des valeurs. |
| **Nuxt UI en mode Vue standalone** | Nuxt et Inertia se disputeraient le routage. Configuré `router: 'inertia'`, enveloppé dans `UApp`. |
| **Le thème est du CSS, pas un build** | Échelle OKLCH dérivée d'une couleur, posée en variables inline sur la racine de l'aperçu. |
| **L'aperçu *est* la feuille** | 210 × 297 mm réels, seulement mis à l'échelle par `transform`, neutralisé à l'impression. |
| **Impression navigateur, pas de PDF serveur** | Zéro infrastructure, fidélité parfaite — même moteur de rendu que l'aperçu. |

---

## Invariants

- **Aucune donnée personnelle réelle dans le dépôt.** Le CV de démonstration est
  fictif (Camille Moreau). L'historique a été réécrit en un commit unique le
  1ᵉʳ septembre 2026 pour purger d'anciennes données ; le dépôt est public.
- **`noindex` par défaut.** Seules la page d'accueil et un CV dont l'auteur a
  coché la case sont référençables. Les métadonnées de partage suivent la même
  règle : le nom n'apparaît que si l'indexation est autorisée
  ([`SocialCard`](app/Support/SocialCard.php)).
- **La marque habille l'application, pas le CV.** Poppins/Satoshi et le violet
  Civi valent pour l'interface ; le CV garde le thème et les polices choisis par
  son auteur (`--cv-*`).
- **Ne jamais casser un CV enregistré.** La clé JSON `techLine` s'appelle encore
  ainsi bien que le champ soit devenu « Sous-titre ».

---

## Carte du code

```
app/Support/CvDefaults.php      valeurs autorisées, schéma de `content`
app/Support/SocialCard.php      métadonnées Open Graph
app/Services/PhotoProcessor.php recadrage + jpg/webp/avif, pilote configurable
app/Http/Middleware/            EnsureCvEditToken — seul contrôle en écriture

resources/js/lib/               palette (OKLCH), fonts, languages (CECR),
                                storage (trousseau local), sections, types
resources/js/stores/            useCvStore — état réactif + autosave 700 ms
resources/js/components/
  templates/                    ClassicSidebar, CompactHeader
  preview/                      A4Frame (mesure, coupes de page), CvPreview
  editor/                       formulaires, sélecteurs couleur/police/photo
  landing/                      AppPreview, BrandIcon
deploy/og-image.php             fabrique l'image de partage
```

---

## Pièges rencontrés

Chacun a coûté au moins un aller-retour. Le détail est en commentaire dans le
fichier concerné.

### `overflow: hidden` — trois fois

Il ne rogne pas, il change la nature de la boîte.

- En média paginé, il rend l'élément **monolithique** : au-delà de 297 mm le
  contenu *disparaissait* à l'impression. Neutralisé dans `@media print`.
- Sur un ancêtre, il devient le conteneur de défilement d'un `position: sticky`,
  qui cesse alors de coller. La landing utilise `overflow-x-clip`.

### Environnement et déploiement

- `artisan serve` ne transmet au processus `php -S` qu'une liste blanche de
  variables ; `TMP` et `TEMP` en sont absents, donc **tout upload échoue**.
  Complété dans [`AppServiceProvider`](app/Providers/AppServiceProvider.php).
- **Deux identités écrivent dans `storage/`** — PHP-FPM et l'utilisateur de
  déploiement. Le `setgid` transmet le groupe mais pas le droit d'écriture, que
  l'umask 022 refuse. Réponse : une **ACL par défaut** (`setfacl -d`).
- `artisan optimize` échoue sur « View path not found » quand
  `storage/framework/views` manque — le message désigne `config('view.compiled')`,
  pas `resources/views`.
- La production n'a **ni `sqlite3` ni `pdo_sqlite`** : un job de CI rejoue les
  migrations sur MariaDB. Son GD n'a pas l'AVIF, son Imagick si — d'où
  `CV_IMAGE_DRIVER=imagick`.

### Laravel et Vue

- `ConvertEmptyStringsToNull` transforme les `""` du JSON en `null`, que la
  validation rejette. La route `cv/*` en est exceptée.
- La table `sessions` a besoin de `user_id` même sans comptes.
- Un validateur imbriqué alimenté par une clé **contenant des points** la lit
  comme un chemin : toutes les règles passaient à vide.
- **`USlider` renvoie un tableau** à un élément dès qu'on le manipule.
  Normalisé au point de liaison.
- Tailwind v4 élague les variables de `@theme` non employées par un utilitaire :
  celles que Nuxt UI référence exigent **`@theme static`**.
- Les tests doivent appeler `withoutVite()`, sinon ils exigent un build.
- Les données Inertia sont dans le **contenu** d'un `<script data-page>`, pas
  dans l'attribut.

### Aperçus de partage

- **Ni WebP ni AVIF** : `og:image` ne désigne qu'une URL, sans négociation, et
  les robots de LinkedIn et X ne les lisent pas.
- À poids égal, la quantification PNG **dithère les aplats** en bruit visible.
  JPEG l'emporte sur ce visuel. Arbitrage à refaire pour une autre image.
- `fb:app_id` n'est pas requis pour l'aperçu, malgré ce qu'affirme le débogueur.

---

## Méthode qui a payé

**Mesurer plutôt que raisonner.** Trois erreurs de ma part venaient d'une
conclusion tirée du code source ou de la mémoire au lieu d'une observation :
`USlider` (lu comme déballant son tableau, ce qu'il ne fait pas),
`process.umask` (affirmé sans vérification, la directive n'existe pas), le choix
PNG/JPEG (prédit à l'envers). L'instrumentation — `fetch` intercepté, règles
d'impression rejouées à l'écran, agrandissements comparés — a tranché chaque
fois en une manipulation.

**Les captures du volet navigateur sont peu fiables** après un défilement
programmatique : elles reviennent blanches. Préférer les mesures DOM, qui sont
d'ailleurs plus probantes.

---

## État et points ouverts

Déployé et fonctionnel : landing, éditeur, page publique, photos (AVIF compris),
CI et déploiement automatiques.

| Ouvert | Nature |
|---|---|
| **Impression d'un CV de deux pages** | Jamais vérifiée par un œil humain. La géométrie est mesurée et juste, mais la fragmentation de la grille à deux colonnes par Chrome reste inconnue. Seul point touchant la promesse centrale. |
| `APP_NAME` du serveur | Vaut peut-être encore « CV Studio » : le `<title>` en dépend. `sed` puis `php artisan optimize`. |
| Open Analytics en production | Répond 403 depuis `localhost`, attendu. À confirmer sur le domaine réel. |
| Tests côté client | Délibérément différés : aucun lanceur JavaScript. Les deux bugs d'interface ont été trouvés à l'usage, pas par des tests. |
| Portée du script d'analyse | Il s'exécute aussi sur les CV publics, qui affichent des données personnelles. Le restreindre aux pages de présentation est une condition d'une ligne. |
