# Hindsight — CentralCorp Installer

> Retrospective technique, décisions, et leçons apprises.  
> Document vivant — mis à jour à chaque cycle de dev significatif.

---

## Décisions d'architecture

### Vue 3 + TypeScript pour le frontend
**Pourquoi** : L'installer est une SPA légère avec 3 étapes max. Vue 3 + Vite donne un DX excellent et un bundle minimal. TypeScript garantit que l'interface `FetchedData` reste cohérente avec ce que retourne le PHP.  
**Inconvénient accepté** : Nécessite Node.js pour builder — le serveur de destination n'a besoin que de PHP.

### PHP standalone (index.php) pour le backend
**Pourquoi** : L'installer tourne sur l'hébergement cible — pas question d'exiger Composer ou Node. Un seul fichier PHP portable = déploiement universel.  
**Inconvénient** : Tout le code serveur dans un seul fichier, difficile à tester unitairement.

### Communication via `?execute=php`
**Pourquoi** : Paramètre query string qui permet de distinguer les requêtes XHR des navigations directes — simple et sans routing complexe.  
**Inconvénient** : Couplage fort frontend/backend sur ce paramètre.

### CDN mode (jsDelivr) pour les assets
**Pourquoi** : Les assets Vue compilés (JS/CSS hashés) sont poussés sur une branche `dist` et servis via `cdn.jsdelivr.net`. L'`installer.zip` ne contient que `index.php` + `.htaccess` → archive ultra-légère.  
**Comment** : `CDN_BASE_URL=https://cdn.jsdelivr.net/gh/Geoventure-MC/Installer@dist/backend/public` dans le CI.  
**Inconvénient** : jsDelivr peut avoir un délai de cache lors du premier déploiement.

### Auto-bump version CI
**Pourquoi** : Éviter d'oublier de bumper (comme sur le Launcher). Le job `version-bump` sync automatiquement `package.json` ET `backend/index.php` (`$installerVersion`).  
**Règle** : Ne jamais bumper manuellement — le CI le fait.

---

## Ce qui a bien fonctionné

- **itty-fetcher** : Abstraction HTTP légère, parfaite pour les 2 endpoints de l'API PHP
- **vue-i18n** : i18n dès le départ — facile d'ajouter des langues sans refactoring
- **Bootstrap 5** : UI responsive prête en quelques classes
- **Interface TypeScript `FetchedData`** : Contrat clair entre PHP et Vue — pas d'erreurs de typage surprises
- **CDN mode** : `installer.zip` léger, assets globalement cachés

---

## Ce qui a posé problème

### Test du flux complet en dev
**Problème** : `npm run dev` lance Vite mais sans le backend PHP — impossible de tester `baseFetch()` sans serveur PHP.  
**Contournement** : Développer sur un serveur local PHP (XAMPP/Laragon) en buildant et déployant le dist.  
**TODO** : Proxy Vite pour mocker le backend PHP en dev.

### Double source de vérité pour la version
**Problème** : La version était définie dans `package.json` ET `backend/index.php` (`$installerVersion`) et pouvait diverger.  
**Fix** : Le job `version-bump` du CI fait un `sed` sur `index.php` pour sync automatiquement.

### Déploiement sur Windows Server
**Problème** : Le backend PHP doit détecter l'OS (`windows` dans `FetchedData`) car les chemins diffèrent.  
**Statut** : Géré dans `index.php` — à vérifier à chaque évolution.

---

## Relation avec le Launcher — Points d'attention

| Point | Statut |
|---|---|
| Panel installé → API consommée par Launcher | ✅ Flux documenté dans primer.md |
| Launcher `env: "panel"` (NE PAS CHANGER) | ✅ Documenté dans hindsight Launcher |
| API endpoint : `{settings}/utils/api` | ✅ Configuré dans Launcher `package.json` |
| Launcher `settings` URL : `https://launcher.bmeouchi.fr/` | ✅ En prod |
| Auth Elandor & Pokeland | ⚠️ URLs TBD — à configurer dans Launcher `package.json` |
| Schéma JSON de `/utils/api` | ⚠️ Non documenté formellement |

---

## Idées d'améliorations futures

1. **Proxy Vite → PHP mock en dev** — Tester `baseFetch()` sans serveur PHP réel
2. **Page de succès post-installation** — `window.location.reload()` donne zéro feedback; afficher une page de confirmation
3. **Vérification de mise à jour de l'installer** — Comparer `installerVersion` avec le GitHub release pour alerter si l'installer est obsolète
4. **web.config pour Windows IIS** — Détecté dans `FetchedData` mais pas généré automatiquement
5. **Tests E2E Playwright** — Flux complet : check → download → succès
6. **Séparer index.php** — Requirements / Downloader / Extractor → maintenabilité

---

## TODO techniques

- [ ] Proxy Vite pour mocker le backend PHP en dev
- [ ] Page de succès post-installation (remplacer `window.location.reload()`)
- [ ] Documenter le schéma JSON de `/utils/api` (consommé par le Launcher)
- [ ] Tester sur Windows Server (chemins, extraction)
- [ ] Ajouter des tests E2E Playwright sur le flux d'installation
- [ ] Renseigner les URLs Elandor et Pokeland (coordonner avec Launcher)
- [ ] Vérifier délai cache jsDelivr lors du premier build
