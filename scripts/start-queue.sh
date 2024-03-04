#!/bin/bash
cd /home/ubuntu/bigmelo-api/src/

php artisan migrate --seed
nohup php artisan queue:work --daemon >/dev/null 2>&1 &