---
tags: [installer, panel, centralcorp, geoventure, index]
updated: 2026-05-28
version: auto (CI bump)
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
- `scripts/build.js` — Packaging de l'archive déployable (CDN mode)

### CI/CD
- `.github/workflows/release.yml` — Auto-bump + build CDN + GitHub Release (branch `master`)
- `.github/workflows/tests.yml` — Lint + build sur chaque push/PR

---

## Contexte projet

| Paramètre | Valeur |
|---|---|
| Projet | CentralCorp Installer |
| Version | auto (CI bump à chaque push master) |
| Stack frontend | Vue 3 + TypeScript + Vite |
| Backend | PHP standalone (`index.php`) |
| Assets | jsDelivr CDN (branche `dist`) |

---

## CI/CD — Workflow auto-release

```
git push master
    ↓
job: version-bump
    → npm version patch  (ex: 1.2.3 → 1.2.4)
    → sed sync $installerVersion dans index.php
    → git push [skip ci]
    ↓
job: build-and-release
    → npm run build-only  (Vite)
    → push dist/assets/ → branche dist (jsDelivr)
    → npm run package --CDN_BASE_URL  → installer.zip
    → GitHub Release v1.2.4
```

**Ne jamais bumper manuellement** — le CI le fait.

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

## Scripts utiles

```bash
# Dev
npm run dev

# Build frontend seulement
npm run build

# Package complet (dist + backend PHP → archive)
npm run package

# Package CDN (assets sur jsDelivr)
CDN_BASE_URL=https://cdn.jsdelivr.net/gh/Geoventure-MC/Installer@dist/backend/public npm run package

# Snapshot contexte (pour IA / debugging)
./memory.sh
./memory.sh --full --clip
```

---

## Checklist déploiement

- [ ] Coder la feature / fix
- [ ] Tester en local (`npm run dev` + serveur PHP)
- [ ] `git commit + push master`
- [ ] CI bumpe la version automatiquement
- [ ] Vérifier la Release sur [GitHub Releases](https://github.com/Geoventure-MC/Installer/releases)
- [ ] Vérifier que `installer.zip` fonctionne sur un serveur test
- [ ] Attendre la propagation jsDelivr (~1-2 min) si premiers assets

---

## Ressources externes

| Ressource | Lien |
|---|---|
| Vue 3 | https://vuejs.org |
| Vite | https://vitejs.dev |
| Bootstrap 5 | https://getbootstrap.com |
| vue-i18n | https://vue-i18n.intlify.dev |
| jsDelivr | https://www.jsdelivr.com |
| Launcher (repo lié) | https://github.com/Geoventure-MC/Launcher |
| Discord Geoventure | https://discord.gg/VCmNXHvf77 |
