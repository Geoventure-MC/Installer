# Primer — CentralCorp Installer

> Guide de démarrage rapide pour comprendre et travailler sur le projet.  
> Vue 3 · TypeScript · Vite · PHP backend

---

## Ce que fait ce projet

Le CentralCorp Installer est une interface web qui installe automatiquement le **panneau CentralCorp** sur un serveur PHP.

**Relation avec le Launcher** : ce panneau installé sert de backend au Geoventure Launcher.  
Le Launcher utilise `env: "panel"` et consomme l'API via `{settings}/utils/api`.

Flux utilisateur :
1. L'admin ouvre l'installer dans son navigateur
2. L'interface vérifie les prérequis PHP (version, extensions)
3. Télécharge et extrait les fichiers du panel
4. Le panneau CentralCorp est opérationnel

---

## Architecture en 30 secondes

```
Installer/
├── src/                      ← Frontend Vue 3 (interface d'installation)
│   ├── main.ts               ← Point d'entrée Vue
│   ├── App.vue               ← Shell principal, gestion des étapes
│   ├── api.ts                ← Appels vers le backend PHP
│   ├── views/
│   │   ├── CentralCorpInstaller.vue  ← Page d'accueil / étape principale
│   │   ├── RequirementsView.vue      ← Vérification des prérequis PHP
│   │   └── DownloadView.vue          ← Téléchargement & extraction
│   ├── locales/              ← i18n (vue-i18n)
│   └── assets/               ← Styles, images
│
├── backend/                  ← Backend PHP
│   ├── index.php             ← API PHP : check prérequis, download, extract
│   ├── .htaccess             ← Routing Apache
│   └── public/               ← Assets publics backend
│
├── build/                    ← Artefacts de build (dist compilé)
├── scripts/                  ← Script de packaging (archiver)
└── package.json              ← v1.2.0 — deps Vue/Vite/Bootstrap
```

**Communication frontend ↔ backend :**
```
Vue (api.ts)
 └─ GET  /?execute=php   →  index.php  →  FetchedData (prérequis, version PHP…)
 └─ POST /?execute=php   →  index.php  →  action: 'download' (télécharge le panel)
```

---

## Fichiers clés à connaître

| Fichier | Ce qu'il fait |
|---|---|
| `src/api.ts` | Interface TypeScript vers le backend PHP — `baseFetch()` + `download()` |
| `src/views/CentralCorpInstaller.vue` | Page principale — config initiale et lancement |
| `src/views/RequirementsView.vue` | Affiche la checklist des prérequis PHP |
| `src/views/DownloadView.vue` | Barre de progression du téléchargement |
| `backend/index.php` | Toute la logique serveur : check PHP, curl, extraction |
| `scripts/build.js` | Package le dist Vue + backend PHP en archive déployable |
| `package.json` | Version `1.2.0` — scripts dev/build/package |

---

## Stack technique

| Couche | Technologie |
|---|---|
| Frontend | Vue 3 + TypeScript + Vite |
| UI | Bootstrap 5 + Bootstrap Icons |
| i18n | vue-i18n v9 |
| HTTP client | itty-fetcher |
| Backend | PHP (index.php standalone) |
| Packaging | archiver (zip du dist + backend) |

---

## API PHP — Interface `FetchedData`

```typescript
interface FetchedData {
  installerVersion: string   // version de l'installer
  minPhpVersion: string      // version PHP minimale requise
  phpVersion: string         // version PHP détectée
  phpFullVersion: string     // version complète avec patch
  phpIniPath: string         // chemin vers php.ini
  path: string               // répertoire d'installation
  file: string               // archive du panel à télécharger
  htaccess: boolean          // .htaccess supporté (Apache)
  requirements: Record<string, boolean>  // extensions PHP
  compatible: boolean        // prérequis OK ?
  extracted?: boolean        // extraction terminée ?
  windows?: boolean          // serveur Windows ?
}
```

---

## Relation Installer ↔ Launcher

```
[CentralCorp Installer]  →  installe  →  [CentralCorp Panel]
                                                ↕  API REST
                                       [Geoventure Launcher]
                                       env: "panel"
                                       settings: https://launcher.bmeouchi.fr/
                                       API: /utils/api
```

Le Launcher consomme :
- `GET  {settings}/utils/api` — config (version MC, mods, serveurs, auth URLs…)
- Auth AZauth via l'URL retournée par l'API (`config.azauth`)

**Serveurs gérés par le Launcher :**
| Serveur | Auth URL |
|---|---|
| Geoventure | `https://geoventure.bmeouchi.fr/` |
| Elandor | TBD |
| Pokeland | TBD |

---

## Workflow de développement

```bash
# 1. Cloner et installer
git clone https://github.com/Geoventure-MC/Installer.git && cd Installer
npm install

# 2. Dev (frontend uniquement — Vite HMR)
npm run dev
# NB: pour tester avec le PHP backend, déployer sur un serveur PHP local (XAMPP/Laragon)

# 3. Build de production
npm run build          # Compile le frontend dans dist/

# 4. Packager (frontend + backend → archive déployable)
npm run package        # Crée l'archive dans build/
```

---

## Liens utiles

- [Geoventure Launcher](https://github.com/Geoventure-MC/Launcher) — Client connecté au panel
- [Vue 3 Docs](https://vuejs.org)
- [Vite Docs](https://vitejs.dev)
- [Discord Geoventure](https://discord.gg/VCmNXHvf77)
