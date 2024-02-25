#!/bin/bash
cd /home/ubuntu/bigmelo-api/src/

nohup php artisan queue:work --daemon &