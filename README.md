<div align="center">

<img src="https://raw.githubusercontent.com/Geoventure-MC/Launcher/master/src/assets/images/icon.png" alt="Geoventure Panel" width="100"/>

# Geoventure Installer

**Installateur automatique du panneau Geoventure**

[![Dernière version](https://img.shields.io/github/v/release/Geoventure-MC/Installer?style=flat-square&label=version&color=6366f1)](https://github.com/Geoventure-MC/Installer/releases/latest)
[![Build](https://img.shields.io/github/actions/workflow/status/Geoventure-MC/Installer/release.yml?branch=master&style=flat-square&label=build)](https://github.com/Geoventure-MC/Installer/actions)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Discord](https://img.shields.io/badge/Discord-Geoventure-5865f2?style=flat-square&logo=discord&logoColor=white)](https://discord.gg/VCmNXHvf77)

[**Télécharger**](https://github.com/Geoventure-MC/Installer/releases/latest) · [Signaler un bug](https://github.com/Geoventure-MC/Installer/issues)

</div>

---

## Présentation

Le **Geoventure Installer** est un outil web qui installe automatiquement le **panneau Geoventure** sur ton serveur en quelques clics — pas besoin de ligne de commande ni de configuration manuelle.

Une fois installé, le panneau sert de backend au **[Nexus Launcher](https://github.com/Geoventure-MC/Launcher)** : authentification des joueurs, gestion des mods, configuration des serveurs Minecraft.

---

## Installation

### 1. Vérifier les prérequis

Ton serveur web doit avoir :

| Prérequis | Version minimale |
|---|---|
| PHP | **8.2 ou supérieur** |
| Extensions PHP | `bcmath` `ctype` `json` `mbstring` `openssl` `PDO` `tokenizer` `xml` `curl` `fileinfo` `zip` |
| Serveur web | Apache (avec `mod_rewrite`) ou Nginx |
| Droits | Écriture sur le répertoire d’installation |

> **Astuce Linux** : installe toutes les extensions en une commande :
> ```bash
> apt install php8.2 php8.2-mysql php8.2-pgsql php8.2-sqlite3 php8.2-bcmath \
>   php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd
> ```

### 2. Télécharger l’installer

**[Télécharger `installer.zip` →](https://github.com/Geoventure-MC/Installer/releases/latest)**

### 3. Déployer sur ton serveur

1. Extrais le contenu de `installer.zip` **à la racine de ton site web**
2. Assure-toi que les fichiers suivants sont présents à la racine :
   ```
   index.php
   .htaccess
   public/
   ```

### 4. Lancer l’installation

1. Ouvre ton navigateur et navigue vers ton domaine (ex : `https://ton-serveur.fr`)
2. L’installer vérifie tes prérequis automatiquement
3. Si tout est vert, clique **Continuer**
4. Le panneau est téléchargé et installé
5. Suis les étapes de configuration du panneau

> **Note** : une fois l’installation terminée, le fichier `index.php` de l’installer est remplacé par le panneau Geoventure. Tu n’as pas besoin de le supprimer manuellement.

---

## Nouveautes v1.2.11

### Protection contre le path traversal

L'extraction des fichiers ZIP valide désormais chaque chemin avant écriture. Les entrées contenant des séquences `../` ou des chemins absolus sont rejetées, empêchant toute attaque de type **directory traversal** qui pourrait écrire en dehors du répertoire d'installation.

### Health checks automatiques

Avant de lancer l'installation du panneau, l'installer vérifie automatiquement :

- **Version PHP** — PHP 8.2+ requis, détecté et affiché
- **Extensions PHP** — chaque extension nécessaire (`bcmath`, `curl`, `zip`, `mbstring`, etc.) est testée individuellement
- **Configuration serveur** — droits d'écriture, `mod_rewrite` (Apache), connectivité réseau

Si un prérequis manque, un message explicite indique la marche à suivre **avant** de télécharger quoi que ce soit.

### ZIP autonome

L'installer est entièrement **autonome** : tous les assets (CSS, JS, images, polices) sont embarqués localement dans le ZIP. Aucune dépendance CDN externe — l'installation fonctionne même sur un serveur sans accès internet sortant (hors téléchargement du panneau lui-même).

---

## Dépannage

### PHP n’est pas exécuté

Si tu vois le message « PHP is not executed » dans ton navigateur, PHP n’est pas configuré sur ton serveur web. Avec Apache :
```bash
apt install libapache2-mod-php
a2enmod php8.2
service apache2 restart
```

### Erreur cURL 60

Ton PHP ne peut pas vérifier les certificats SSL. Édite ton `php.ini` et renseigne le chemin vers un bundle CA :
```ini
curl.cainfo = /etc/ssl/certs/ca-certificates.crt
```

### Répertoire non accessible en écriture

```bash
chmod -R 755 /var/www/html
chown -R www-data:www-data /var/www/html
```

---

## Relation avec le Launcher

```
Geoventure Installer
       ↓ installe
Geoventure Panel  ←  backend API
       ↕
Nexus Launcher  →  Minecraft 1.20.1
```

Le panneau installé gère :
- L’authentification des joueurs (AZauth)
- La liste des mods et leur version
- La configuration des serveurs Minecraft
- Les news, le statut, les skins

---

## Développement

### Prérequis

- Node.js 22+
- npm 10+
- Un serveur PHP local pour tester (XAMPP, Laragon, php -S)

### Installation

```bash
git clone https://github.com/Geoventure-MC/Installer.git
cd Installer
npm install
```

### Lancer en développement

```bash
npm run dev       # Lance Vite (frontend uniquement)
npm run build     # Build de production
npm run package   # Crée installer.zip
```

> Le frontend seul est suffisant pour développer l’UI. Pour tester le flux complet (vérification PHP, téléchargement), déploie le build sur un serveur PHP local.

### Déployer une mise à jour

Pushe sur `master` — le CI bumpe la version et publie la release automatiquement.

---

## Pour les développeurs

| Fichier | Rôle |
|---|---|
| [primer.md](primer.md) | Architecture & guide de démarrage rapide |
| [hindsight.md](hindsight.md) | Décisions techniques & retrospective |
| [coffre.md](coffre.md) | Index Obsidian du projet |
| [memory.sh](memory.sh) | Snapshot contexte projet pour debugging/IA |

---

<div align="center">
Fait avec ❤️ pour <strong>Geoventure MC</strong>
</div>
