#!/usr/bin/env bash
# memory.sh — Capture le contexte du projet CentralCorp Installer
# Usage : ./memory.sh [--full] [--clip]
#   --full  : inclut le contenu des fichiers clés
#   --clip  : copie la sortie dans le presse-papier (xclip/pbcopy)

set -euo pipefail

FULL=false
CLIP=false

for arg in "$@"; do
  case $arg in
    --full) FULL=true ;;
    --clip) CLIP=true ;;
  esac
done

OUTPUT=""

snapshot() {
  OUTPUT+="$1\n"
}

# ── En-tête ────────────────────────────────────────────────────────────────────
snapshot "# CentralCorp Installer — Snapshot contexte"
snapshot "Date      : $(date '+%Y-%m-%d %H:%M:%S')"
snapshot "Répertoire: $(pwd)"
snapshot ""

# ── Git ────────────────────────────────────────────────────────────────────────
snapshot "## Git"
snapshot "Branche   : $(git branch --show-current 2>/dev/null || echo 'N/A')"
snapshot "Dernier commit : $(git log -1 --oneline 2>/dev/null || echo 'N/A')"
snapshot "Statut    :"
snapshot "$(git status --short 2>/dev/null || echo '  (pas de repo git)')"
snapshot ""

# ── Version ───────────────────────────────────────────────────────────────────
if [ -f package.json ]; then
  VERSION=$(node -p "require('./package.json').version" 2>/dev/null || echo "?")
  snapshot "## Package"
  snapshot "Version   : $VERSION"
  snapshot "Nom       : CentralCorp Installer"
  snapshot "Stack     : Vue 3 + TypeScript + Vite + PHP backend"
  snapshot ""
fi

# ── Node/npm ──────────────────────────────────────────────────────────────────
snapshot "## Environnement"
snapshot "Node      : $(node --version 2>/dev/null || echo 'non installé')"
snapshot "npm       : $(npm --version 2>/dev/null || echo 'non installé')"
snapshot "PHP       : $(php --version 2>/dev/null | head -1 || echo 'non installé')"
snapshot ""

# ── Structure du projet ───────────────────────────────────────────────────────
snapshot "## Structure"
snapshot "src/views : $(ls src/views/ 2>/dev/null | tr '\n' ' ' || echo 'N/A')"
snapshot "locales   : $(ls src/locales/ 2>/dev/null | tr '\n' ' ' || echo 'N/A')"
snapshot ""

# ── Fichiers modifiés récemment ───────────────────────────────────────────────
snapshot "## Fichiers modifiés (7 derniers jours)"
snapshot "$(find . -not -path './.git/*' -not -path './node_modules/*' -not -path './dist/*' -not -path './build/*' -newer package-lock.json -type f 2>/dev/null | head -20 || echo '  aucun')"
snapshot ""

# ── Fichiers clés (mode --full) ───────────────────────────────────────────────
if [ "$FULL" = true ]; then
  snapshot "## package.json"
  snapshot "\`\`\`json"
  snapshot "$(cat package.json 2>/dev/null || echo 'introuvable')"
  snapshot "\`\`\`"
  snapshot ""

  if [ -f src/api.ts ]; then
    snapshot "## src/api.ts"
    snapshot "\`\`\`typescript"
    snapshot "$(cat src/api.ts)"
    snapshot "\`\`\`"
    snapshot ""
  fi

  if [ -f primer.md ]; then
    snapshot "## primer.md (extrait)"
    snapshot "$(head -60 primer.md)"
    snapshot ""
  fi

  if [ -f hindsight.md ]; then
    snapshot "## hindsight.md (extrait)"
    snapshot "$(head -40 hindsight.md)"
    snapshot ""
  fi
fi

# ── Relation avec le Launcher ─────────────────────────────────────────────────
snapshot "## Contexte projet"
snapshot "Ce projet installe le backend (CentralCorp Panel / Azuriom)"
snapshot "que le Geoventure Launcher consomme via API REST."
snapshot "Panel URL prod : https://conflictura.eu"
snapshot "Launcher repo  : https://github.com/Geoventure-MC/Launcher"
snapshot ""

# ── Sortie ─────────────────────────────────────────────────────────────────────
printf "%b" "$OUTPUT"

if [ "$CLIP" = true ]; then
  if command -v pbcopy &>/dev/null; then
    printf "%b" "$OUTPUT" | pbcopy
    echo "(copié dans le presse-papier via pbcopy)"
  elif command -v xclip &>/dev/null; then
    printf "%b" "$OUTPUT" | xclip -selection clipboard
    echo "(copié dans le presse-papier via xclip)"
  elif command -v xsel &>/dev/null; then
    printf "%b" "$OUTPUT" | xsel --clipboard --input
    echo "(copié dans le presse-papier via xsel)"
  else
    echo "(aucun outil clipboard disponible : pbcopy/xclip/xsel)"
  fi
fi
