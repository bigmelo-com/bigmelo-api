#!/bin/bash
cd src/

nombre="php artisan"
pid=$(ps -ux | grep $nombre | grep -v grep | awk '{print $2}') # Busca el pid del proceso por su nombre
kill -9 $pid