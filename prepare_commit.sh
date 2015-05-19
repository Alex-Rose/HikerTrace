#!/bin/bash

if [ -f .commit_state ]; then
    echo "Already in commit state!"
    exit 1
fi

touch .commit_state
rm -f .dev_state

cp -f .htaccess .htaccess.bak
cp -f application/bootstrap.php application/bootstrap.php.bak
cp -f application/config/database.php application/config/database.php.bak

git add application/classes/*
git add application/views/*
git add assets/*

git checkout .htaccess
git checkout application/bootstrap.php
git checkout application/config/database.php
