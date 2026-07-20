# Déploiement — Dice Adventure sur o2switch

> Procédure Codapia v1 — rédigée pour la première MEP (juillet 2026).
> À corriger au fil de l'exécution : chaque écart entre ce document et la réalité
> est une correction à reporter ici. Ce fichier est la référence, pas la mémoire.

## 1. Préparation côté cPanel (une seule fois)

### 1.1 PHP
Outil **« Sélectionner une version de PHP »** : choisir **PHP 8.2** (ou plus récent),
vérifier que les modules `intl`, `mbstring`, `pdo_mysql`, `opcache` sont cochés.

### 1.2 Base de données
Outil **« Bases de données MySQL »** :
1. Créer la base (ex. `xxxx_diceadventure`).
2. Créer un utilisateur dédié — **mot de passe alphanumérique long, sans caractères
   spéciaux** (recommandation o2switch : les caractères spéciaux dans le `.env`
   causent des erreurs chronophages).
3. Donner tous les droits à cet utilisateur sur cette base.

La version serveur est **MariaDB 10.6** → à reporter dans `DATABASE_URL`.

### 1.3 Domaine → dossier public/
Outil **« Domaines configurés »** : associer le domaine au dossier
**`/home/<compte>/dice_adventure/public`** — modifier la « racine du document »,
la valeur pré-remplie est incorrecte.

⚠️ **C'est l'erreur n°1 sur mutualisé.** Si le domaine pointe sur la racine du
projet, tout le code source (`.env` compris) est exposé publiquement.

### 1.4 SSL
Vérifier que le certificat AutoSSL (Let's Encrypt) est actif sur le domaine
**avant** la première visite : l'application envoie du HSTS, le navigateur
mémorisera l'obligation de HTTPS pendant un an.

### 1.5 Accès SSH
Outil **« Whitelist SSH »** : ajouter ton adresse IP. Puis :
```bash
ssh <identifiant-cpanel>@<serveur>.o2switch.net
```
(identifiants = ceux de cPanel ; le nom du serveur est dans le mail
« bienvenue chez o2switch »)

## 2. Assets (à faire en local, avant chaque déploiement)

npm/node demandent une manipulation supplémentaire sur o2switch, et `public/build`
est ignoré par git. Le plus simple et le plus fiable : **compiler en local, téléverser
le résultat**.

```powershell
npm run build
scp -r public/build <identifiant>@<serveur>.o2switch.net:~/dice_adventure/public/
```
(ou via le gestionnaire de fichiers cPanel : téléverser le dossier `public/build`)

## 3. Premier déploiement

```bash
# connecté en SSH sur o2switch
cd ~
git clone <url-du-depot> dice_adventure
cd dice_adventure
```

### 3.1 Configuration
Créer `.env.local` à la racine (jamais commité) :
```bash
APP_ENV=prod
APP_SECRET=<64 caractères hexadécimaux aléatoires>
DATABASE_URL="mysql://<user>:<motdepasse>@localhost:3306/<base>?serverVersion=mariadb-10.6.0&charset=utf8mb4"
# MAILER_DSN inutile tant que le contact passe par Google Forms.
# Le jour où un envoi de mail revient : créer une adresse dans cPanel > Comptes de
# messagerie, puis (attention : @ et caractères spéciaux à encoder en %40 etc.) :
# MAILER_DSN=smtp://contact%40domaine.fr:motdepasse@<serveur>.o2switch.net:465?verify_peer=0
```
Générer le secret : `php -r "echo bin2hex(random_bytes(32));"`

### 3.2 Installation
```bash
composer install --no-dev --optimize-autoloader
composer dump-env prod          # fige la config en .env.local.php
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear && php bin/console cache:warmup
```
`--no-dev` est indispensable : sans lui, le profileur et les outils de débogage
sont installés en production.

**Jamais `doctrine:fixtures:load` en production** — la commande purge la base.

### 3.3 Compte administrateur
```bash
php bin/console app:admin:create <identifiant> <email-du-client>
```
(mot de passe demandé en saisie masquée, 12 caractères minimum)

### 3.4 Téléverser les assets compilés
Voir section 2 (le `git clone` ne les contient pas).

## 4. Vérifications avant d'annoncer l'ouverture

- [ ] `https://<domaine>/` s'affiche ; `http://` redirige vers `https://`
- [ ] `https://<domaine>/composer.json` et `/.env` → **404** (sinon : racine du
      domaine mal configurée, voir 1.3 — à corriger immédiatement)
- [ ] `/_profiler` → 404
- [ ] Connexion sur `/parlezAmi`, accès à `/ecranMJ`
- [ ] `/ecranMJ` en navigation privée → redirection vers la connexion
- [ ] Le formulaire Google Forms se charge après le clic de consentement,
      et un envoi de test arrive bien dans les réponses du formulaire
- [ ] Créer un tarif dans le back-office → il s'affiche sur l'accueil
- [ ] CSS/JS chargés (sinon : section 2 oubliée)

## 5. Sauvegardes (à faire dans la foulée, pas « plus tard »)

1. o2switch fournit des sauvegardes automatiques (JetBackup dans cPanel) :
   vérifier qu'elles couvrent la base ET les fichiers.
2. **Tester une restauration réelle une fois** : restaurer la base dans une base
   temporaire et vérifier son contenu. Une sauvegarde non testée n'existe pas.
3. En complément, tâche cron cPanel quotidienne :
   ```bash
   mysqldump -u <user> -p'<motdepasse>' <base> | gzip > ~/backups/dice_$(date +\%F).sql.gz
   ```
   (créer `~/backups/` d'abord ; garder ~15 jours, hors racine web)

## 6. Mises à jour ultérieures

```bash
# en local : npm run build + scp du dossier public/build (section 2)
# en SSH :
cd ~/dice_adventure
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear && php bin/console cache:warmup
```

## Historique

| Date | Événement |
|------|-----------|
| 2026-07-__ | Première mise en production (v___) |
