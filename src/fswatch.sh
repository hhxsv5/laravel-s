#!/usr/bin/env bash
WORK_DIR=$1
if [ ! -n "${WORK_DIR}" ] ;then
    WORK_DIR="."
fi

echo "fswatching..."

fswatch ${WORK_DIR} | while read file
do
   echo "File ${file} A has been modified"
   php artisan laravels reload
done
exit 0