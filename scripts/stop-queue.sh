#!/bin/bash
pid=$(ps aux | grep -v "grep" | grep "php artisan" | awk '{print $2}')
if [ -n "$pid" ]; then
  kill -9 $pid 
fi