# CV HTML + Tailwind CSS 4

Template A4 inspiré du mockup généré dans la conversation.

## Le plus rapide

Ouvre directement `index.html` dans Chrome/Edge. Il utilise le Play CDN Tailwind CSS v4.

Pour exporter :

1. Cliquer sur **Imprimer / PDF**.
2. Destination : **Enregistrer au format PDF**.
3. Papier : **A4**.
4. Marges : **Aucune**.
5. Échelle : **100 %**.
6. Activer **Graphiques d'arrière-plan**.
7. Désactiver les en-têtes et pieds de page du navigateur.

## Build Tailwind local

```bash
npm install
npm run dev
```

Puis, dans `index.html` :

- supprimer le script Play CDN ;
- déplacer/supprimer le bloc `<style type="text/tailwindcss">...</style>` ;
- ajouter :

```html
<link rel="stylesheet" href="./dist/output.css">
```

Build minifié :

```bash
npm run build
```

## Modifier rapidement

- Identité : bloc `<header>` dans la colonne principale.
- Coordonnées : section `Coordonnées` dans `<aside>`.
- Compétences : modifier les libellés et les largeurs `w-[XX%]`.
- Expériences : dupliquer un `<article class="experience">...</article>`.
- Couleur principale : modifier les variables `--color-cv-*` dans `@theme`.
- Largeur de la sidebar : classe `grid-cols-[61mm_1fr]` sur `<main>`.

## Remarque impression

Chaque `.experience` utilise `break-inside: avoid` pour éviter qu'un poste soit coupé entre deux pages. Si le contenu devient trop long, le navigateur pourra créer une deuxième page lors de l'impression.
