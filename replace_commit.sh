#!/bin/bash

if [ -f .dev_state ]; then
    echo "Already in dev state!"
    exit 1
fi

touch .dev_state
rm -f .commit_state

rm -f .htaccess
rm -f application/bootstrap.php
rm -f application/config/database.php

mv .htaccess.bak .htaccess
mv application/bootstrap.php.bak application/bootstrap.php
mv application/config/database.php.bak application/config/database.php
