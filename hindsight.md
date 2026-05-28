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

### Packaging dist + backend en archive
**Pourquoi** : L'utilisateur final reçoit une archive zip autonome — drop sur le serveur, ça marche. Pas de pipeline CI côté serveur requis.

---

## Ce qui a bien fonctionné

- **itty-fetcher** : Abstraction HTTP légère, parfaite pour les 2 endpoints de l'API PHP
- **vue-i18n** : i18n dès le départ — facile d'ajouter des langues sans refactoring
- **Bootstrap 5** : UI responsive prête en quelques classes
- **Interface TypeScript `FetchedData`** : Contrat clair entre PHP et Vue — pas d'erreurs de typage surprises

---

## Ce qui a posé problème

### Test du flux complet en dev
**Problème** : `npm run dev` lance Vite mais sans le backend PHP — impossible de tester `baseFetch()` sans serveur PHP.  
**Contournement** : Développer sur un serveur local PHP (XAMPP/Laragon) en buildant et déployant le dist.  
**TODO** : Mocker le backend PHP via un proxy Vite en dev.

### Déploiement sur Windows Server
**Problème** : Le backend PHP doit détecter l'OS (`windows` dans `FetchedData`) car les chemins et les commandes d'extraction diffèrent.  
**Statut** : Géré dans `index.php` — à vérifier à chaque évolution.

### Dépendance sur la version du panel
**Problème** : Si le panel CentralCorp publie une version majeure, l'URL de téléchargement dans `index.php` peut casser silencieusement.  
**TODO** : Externaliser l'URL de téléchargement dans une config ou un endpoint de vérification de version.

---

## Relation avec le Launcher — Points d'attention

| Point | Statut |
|---|---|
| Panel installé → API consommée par Launcher | ✅ Flux documenté dans primer.md |
| Launcher `env: "panel"` (NE PAS CHANGER) | ✅ Documenté dans hindsight Launcher |
| API endpoint : `{settings}/utils/api` | ✅ Configuré dans Launcher `package.json` |
| Launcher `settings` URL : `https://launcher.bmeouchi.fr/` | ✅ En prod |
| Auth Elandor & Pokeland | ⚠️ URLs TBD — à configurer dans Launcher `package.json` |
| Schéma JSON de `/utils/api` | ⚠️ Non documenté formellement — à risque si le panel change |

---

## Ce qu'on ferait différemment aujourd'hui

1. **Mock du backend PHP en dev** — Un proxy Vite ou un serveur Express mock pour `?execute=php` éviterait le cycle build-deploy-test.
2. **Tests E2E** — Playwright pourrait simuler le flux complet d'installation (prérequis OK → download → succès).
3. **Documenter le schéma `/utils/api`** — Fichier JSON de référence pour le contrat API Installer/Launcher/Panel.
4. **Séparer index.php** — Découper en plusieurs fichiers PHP (Requirements.php, Downloader.php, Extractor.php) pour la maintenabilité.

---

## TODO techniques

- [ ] Proxy Vite pour mocker le backend PHP en dev
- [ ] Documenter le schéma JSON de `/utils/api` (consommé par le Launcher)
- [ ] Tester sur Windows Server (chemins, extraction)
- [ ] Vérifier la compatibilité avec les futures versions du panel
- [ ] Ajouter des tests E2E Playwright sur le flux d'installation
- [ ] Renseigner les URLs Elandor et Pokeland (coordonner avec Launcher)
