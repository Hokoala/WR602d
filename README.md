# Docly — WR602D

Application SaaS de conversion et manipulation de fichiers PDF

## Liens

| Service | URL |
|---|---|
| Application | https://mmi23e10.mmi-troyes.fr/WR602d/ |
| phpMyAdmin | https://mmi23e10.mmi-troyes.fr/adminsql/ |
| Mailpit (emails) | http://mmi23e10.mmi-troyes.fr:8026/mailpit/ |

## Description

Docly permet aux utilisateurs de convertir, fusionner, compresser et manipuler des fichiers PDF directement depuis leur navigateur. L'accès aux outils est conditionné par un plan d'abonnement (FREE, BASIC, PREMIUM) avec un quota mensuel de générations. Les conversions sont réalisées via Gotenberg (conteneur Docker).

## Stack technique

- **Framework** : Symfony 7
- **Frontend** : React, Webpack Encore, Tailwind CSS
- **Base de données** : MySQL / Doctrine ORM
- **Paiement** : Stripe
- **Conversion PDF** : Gotenberg (port 3000)
- **Emails** : Symfony Mailer + Mailpit
- **Tests** : PHPUnit

## Outils disponibles par plan

### Plan FREE (4 générations / mois)
- URL vers PDF
- HTML vers PDF
- Fusionner des PDF
- Markdown vers PDF

### Plan BASIC (50 générations / mois)
- Tous les outils FREE
- Diviser un PDF
- Compresser un PDF
- Office vers PDF (Word, Excel, PowerPoint)
- Screenshot vers PDF
- WYSIWYG vers PDF

### Plan PREMIUM (générations illimitées)
- Tous les outils BASIC
- Image vers PDF (JPG/PNG)

## Installation

### 1. Cloner le repository et installer les dépendances

```bash
composer install
npm install
```

### 2. Copier le fichier `.env` en `.env.local`

```bash
cp .env .env.local
```

### 3. Variables à configurer dans `.env.local`

```
APP_ENV=prod
APP_SECRET=votre_secret
DATABASE_URL="mysql://user:password@localhost:3306/dbname"
STRIPE_SECRET_KEY=sk_...
STRIPE_PUBLISHABLE_KEY=pk_...
STRIPE_WEBHOOK_SECRET=whsec_...
MAILER_DSN=smtp://localhost:1025
URL_GOTENBERG=http://localhost:3000
```

### 4. Migrations et fixtures

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 5. Build des assets

```bash
npm run build
```

## Lancer en développement

```bash
symfony serve
npm run watch
```

## Tests

```bash
php bin/phpunit tests/Unit/
php bin/phpunit tests/Functional/
```

## Crontab

La commande `app:handle-queue` est exécutée automatiquement toutes les 10 minutes via crontab :

```
*/10 * * * * cd /home/mmi23e10/WR602d && APP_ENV=prod php bin/console app:handle-queue
```

## Compte de test

| Champ | Valeur |
|---|---|
| Email | test@test.com |
| Mot de passe | à créer via l'inscription |

## Carte Stripe (mode test)

| Champ | Valeur |
|---|---|
| Numéro | 4242 4242 4242 4242 |
| Date | N'importe quelle date future |
| CVC | N'importe quel 3 chiffres |

## Auteur

Jean-Michel Le — Étudiant MMI, Université de Troyes — Promotion 2020
