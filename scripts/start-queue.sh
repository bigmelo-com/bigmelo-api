#!/bin/bash
cd /home/ubuntu/bigmelo-api
git stash
git stash drop
cd /home/ubuntu/bigmelo-api/src/
npm i
npm audit fix
composer install

cd /home/ubuntu/bigmelo-api
git stash
git stash drop
git fetch origin
sudo git reset --hard origin/main
git pull --rebase origin main
cd /home/ubuntu/bigmelo-api/src/
php artisan migrate --seed
nohup php artisan queue:work --daemon >/dev/null 2>&1 &