---
tags: [installer, panel, centralcorp, geoventure, index]
updated: 2026-05-28
version: 1.2.0
related: launcher
---

# Coffre — CentralCorp Installer

> Index principal du projet. Point d'entrée pour la navigation dans la documentation.

---

## Navigation rapide

### Documentation projet
- [[primer]] — Architecture, fichiers clés, workflow de dev
- [[hindsight]] — Décisions techniques, leçons apprises, TODO

### Code source
- `src/api.ts` — Interface TypeScript + appels HTTP vers PHP
- `src/views/CentralCorpInstaller.vue` — Page principale d'installation
- `src/views/RequirementsView.vue` — Vérification prérequis PHP
- `src/views/DownloadView.vue` — Téléchargement et extraction
- `backend/index.php` — Backend PHP standalone
- `scripts/build.js` — Packaging de l'archive déployable

---

## Contexte projet

| Paramètre | Valeur |
|---|---|
| Projet | CentralCorp Installer |
| Version | **1.2.0** |
| Stack frontend | Vue 3 + TypeScript + Vite |
| Backend | PHP standalone (`index.php`) |

---

## Relation avec le Launcher

```
[CentralCorp Installer]
        ↓  installe
[CentralCorp Panel]
        ↕  API REST  (/utils/api)
[Geoventure Launcher]
  env: "panel"  ← NE PAS CHANGER
  settings: https://launcher.bmeouchi.fr/
        ↓  lance
[Minecraft 1.20.1 · Forge 1.20.1-47.4.20]
```

### Serveurs gérés par le Launcher

| Serveur | Auth URL | Statut |
|---|---|---|
| Geoventure | `https://geoventure.bmeouchi.fr/` | ✅ Configuré |
| Elandor | TBD | ⚠️ À configurer |
| Pokeland | TBD | ⚠️ À configurer |

| Projet | Repo |
|---|---|
| Installer (ce repo) | [Geoventure-MC/Installer](https://github.com/Geoventure-MC/Installer) |
| Launcher | [Geoventure-MC/Launcher](https://github.com/Geoventure-MC/Launcher) |

---

## Versions & releases

| Version | Notes |
|---|---|
| 1.2.0 | Version actuelle — stable |

---

## Scripts utiles

```bash
# Dev
npm run dev

# Build frontend
npm run build

# Package complet (dist + backend PHP → archive)
npm run package

# Snapshot contexte (pour IA / debugging)
./memory.sh
./memory.sh --full --clip
```

---

## Checklist déploiement

- [ ] Bumper version dans `package.json`
- [ ] `npm run build` — vérifier pas d'erreurs TypeScript
- [ ] `npm run package` — générer l'archive
- [ ] Uploader l'archive sur le serveur cible
- [ ] Vérifier la page d'installation en navigateur
- [ ] Confirmer que le Launcher se connecte bien au panel après installation
- [ ] Créer la Release GitHub

---

## Ressources externes

| Ressource | Lien |
|---|---|
| Vue 3 | https://vuejs.org |
| Vite | https://vitejs.dev |
| Bootstrap 5 | https://getbootstrap.com |
| vue-i18n | https://vue-i18n.intlify.dev |
| Launcher (repo lié) | https://github.com/Geoventure-MC/Launcher |
| Discord Geoventure | https://discord.gg/VCmNXHvf77 |
