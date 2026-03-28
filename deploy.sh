#!/bin/bash
# Script de déploiement — serveur MMI Troyes
# Usage: bash deploy.sh
# Requis: git, composer, node/npm installés sur le serveur
set -e

REPO_URL="git@github.com:Hokoala/WR602d.git"
BRANCH="develop"
APP_DIR="$HOME/apps/WR602d"
WWW_LINK="$HOME/www/WR602d"

echo "╔══════════════════════════════╗"
echo "║   Déploiement WR602d         ║"
echo "╚══════════════════════════════╝"

# ── 1. Clone ou mise à jour ──────────────────────────────
if [ -d "$APP_DIR/.git" ]; then
    echo "→ Mise à jour du code (branch: $BRANCH)..."
    git -C "$APP_DIR" fetch origin
    git -C "$APP_DIR" checkout "$BRANCH"
    git -C "$APP_DIR" reset --hard "origin/$BRANCH"
else
    echo "→ Premier déploiement — clonage du dépôt..."
    mkdir -p "$(dirname "$APP_DIR")"
    git clone "$REPO_URL" "$APP_DIR" --branch "$BRANCH"
fi

cd "$APP_DIR"

# ── 2. Vérification .env.local ───────────────────────────
if [ ! -f ".env.local" ]; then
    echo ""
    echo "⚠️  ATTENTION : .env.local manquant !"
    echo "   Crée le fichier $APP_DIR/.env.local avec le contenu suivant"
    echo "   (adapte les valeurs à ton serveur) :"
    echo ""
    cat << 'ENVTEMPLATE'
APP_ENV=prod
APP_SECRET=CHANGE_MOI_32_CHARS_MINIMUM
DATABASE_URL="mysql://LOGIN:MOT_DE_PASSE@localhost:3306/NOM_BASE?serverVersion=10.8.8-MariaDB&charset=utf8mb4"
ENCORE_PUBLIC_PATH=/WR602d/build
DEFAULT_URI=https://mmi23e10.mmi-troyes.fr/WR602d
MAILER_DSN=smtp://localhost:25
URL_GOTENBERG=http://localhost:3000
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLISHABLE_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
ENVTEMPLATE
    echo ""
    echo "   Puis relance ce script."
    exit 1
fi

# ── 3. Dépendances PHP ───────────────────────────────────
echo "→ Installation des dépendances PHP (prod)..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# ── 4. Assets Webpack ────────────────────────────────────
echo "→ Installation des dépendances Node..."
npm ci --silent

echo "→ Build des assets (public path: /WR602d/build)..."
ENCORE_PUBLIC_PATH=/WR602d/build npm run build -- --mode production

# ── 5. Cache Symfony ─────────────────────────────────────
echo "→ Nettoyage et préchauffage du cache..."
APP_ENV=prod php bin/console cache:clear --no-warmup
APP_ENV=prod php bin/console cache:warmup

# ── 6. Migrations ────────────────────────────────────────
echo "→ Exécution des migrations..."
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction

# ── 7. Permissions ───────────────────────────────────────
echo "→ Correction des permissions..."
chmod -R 775 var/
chmod -R 775 public/

# ── 8. Lien symbolique ───────────────────────────────────
if [ ! -d "$HOME/www" ]; then
    echo "⚠️  Le dossier ~/www n'existe pas. Adapte WWW_LINK dans ce script."
else
    echo "→ Lien symbolique : $WWW_LINK → $APP_DIR/public"
    ln -sfn "$APP_DIR/public" "$WWW_LINK"
fi

echo ""
echo "✓ Déploiement terminé !"
echo "  URL : https://mmi23e10.mmi-troyes.fr/WR602d/"
