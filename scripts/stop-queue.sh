#!/bin/bash
pid=$(ps aux | grep -v "grep" | grep "php artisan" | awk '{print $2}')
sudo kill -9 $pid
