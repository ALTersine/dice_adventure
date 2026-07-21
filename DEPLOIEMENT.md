# Déploiement — Dice Adventure sur o2switch

> Procédure Codapia v1 — rédigée pour la première MEP (juillet 2026).
> À corriger au fil de l'exécution : chaque écart entre ce document et la réalité
> est une correction à reporter ici. Ce fichier est la référence, pas la mémoire.

## 1. Préparation côté cPanel (une seule fois)

### 1.1 PHP

Outil **« Sélectionner une version de PHP »** : choisir **PHP 8.4**,
vérifier que les modules `intl`, `mbstring`, `opcache` sont cochés
(la sélection peut être réinitialisée après un changement de version).

⚠️ **La version du serveur doit être identique à celle du poste de développement.**
`composer.lock` fige des paquets qui exigent une version PHP minimale : si la prod
est en dessous, `composer install` refuse de s'exécuter.

**Ne jamais lancer `composer update` sur le serveur** pour contourner ce refus — cela
rétrograderait les dépendances et déploierait du code jamais testé en local.
Le `.lock` se met à jour en local, se teste en local, puis se commite.

Vérifier que la ligne de commande utilise bien la même version que le web :
```bash
php -v
```
Si elle diffère, appeler explicitement le binaire :
`/opt/cpanel/ea-php84/root/usr/bin/php bin/console ...`
(versions disponibles : `ls /opt/cpanel/ | grep ea-php`)

⚠️ `pdo_mysql` peut être **impossible à cocher** : o2switch utilise `nd_pdo_mysql`
(native driver), exclusif avec `pdo_mysql`. C'est normal, ne pas forcer.
Vérification : `php -m | grep -i pdo` doit afficher `pdo_mysql`.

### 1.2 Base de données
Outil **« Bases de données MySQL »** :
1. Créer la base (ex. `xxxx_diceadventure`) : gual2900__diceadventure.
2. Créer un utilisateur dédié — **mot de passe alphanumérique long, sans caractères
   spéciaux** (recommandation o2switch : les caractères spéciaux dans le `.env`
   causent des erreurs chronophages).
3. Donner tous les droits à cet utilisateur sur cette base.

La version serveur est **MariaDB 11.4.12** → à reporter dans `DATABASE_URL`.
Version PHP du server DB 8.4.22.

### 1.3 Domaine → dossier public/
Outil **« Domaines configurés »** : modifier la « racine du document » (la valeur
pré-remplie est incorrecte) et y mettre :

```
001/dice_adventure/public
```

**Convention Codapia :** un dossier numéroté par projet (`001/`, `002/`…) dans le
dossier personnel, pour les ranger. Ici : `/home2/gual2900/001/dice_adventure`.
⚠️ Ne jamais pointer un domaine sur `001/` lui-même : cela exposerait tous les projets.

⚠️ **Le chemin est relatif au dossier personnel.** Saisir `/public` renverrait vers
`/home2/<compte>/public`, qui n'existe pas. Vérifier avant :
`ls -d ~/001/dice_adventure/public`

Note : le compte est sur `/home2/` et non `/home/` — sans effet avec `~`, mais à
respecter dans tout chemin absolu (cron, sauvegardes).

À faire **après** le `git clone` : cPanel peut refuser un dossier inexistant.

**Cas du domaine principal :** cPanel force parfois `public_html` sans permettre de
le modifier. Deux contournements :
- déclarer le site en domaine additionnel (racine libre), ou
- remplacer `public_html` par un lien symbolique :
  `rm -rf ~/public_html && ln -s ~/dice_adventure/public ~/public_html`

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
scp -r public/build <identifiant>@<serveur>.o2switch.net:~/001/dice_adventure/public/
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
# serverVersion DOIT correspondre à la version réelle du serveur (voir 1.2) :
# Doctrine s'en sert pour générer son SQL.
DATABASE_URL="mysql://<user>:<motdepasse>@localhost:3306/gual2900__diceadventure?serverVersion=mariadb-11.4.12&charset=utf8mb4"
# MAILER_DSN inutile tant que le contact passe par Google Forms.
# Le jour où un envoi de mail revient : créer une adresse dans cPanel > Comptes de
# messagerie, puis (attention : @ et caractères spéciaux à encoder en %40 etc.) :
# MAILER_DSN=smtp://contact%40domaine.fr:motdepasse@<serveur>.o2switch.net:465?verify_peer=0
```
Générer le secret : `php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"`

Puis restreindre les droits (le fichier contient le mot de passe de la base) :
```bash
chmod 600 .env.local
```

⚠️ **Ne lancer aucune commande `bin/console` avant le `composer install` de l'étape
suivante** : `vendor/` est gitignoré, donc absent du clone. Sans lui, PHP plante sur
`vendor/autoload_runtime.php` — et comme `display_errors` est désactivé sur
l'hébergement, la commande ne renvoie **strictement aucun message**.
Pour voir l'erreur en cas de doute : `php -d display_errors=1 bin/console about`

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

⚠️ **Prérequis à valider EN LOCAL avant tout déploiement : la chaîne de migrations
doit être rejouable depuis une base vide.**
```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```
Des migrations auto-générées successivement contre une base qui dérive produisent
des chaînes non rejouables : instructions `DROP TABLE` sur des tables jamais créées,
mêmes `ADD CONSTRAINT` répétés dans plusieurs migrations (erreur MySQL 1005 /
errno 121, les noms de clés étrangères étant uniques pour toute la base).
Ces défauts sont **invisibles en local** et n'apparaissent qu'au premier déploiement.

Correction : écraser les migrations en une seule, régénérée depuis les entités.
Possible **uniquement tant qu'aucune base en production ne les a jouées**.

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
