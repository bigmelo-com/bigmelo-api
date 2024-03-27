#!/bin/bash
cd /home/ubuntu/bigmelo-api
git stash
git stash drop
git pull origin --rebase
cd /home/ubuntu/bigmelo-api/src/
npm i
npm audit fix
composer install

php artisan migrate --seed
nohup php artisan queue:work --daemon >/dev/null 2>&1 &