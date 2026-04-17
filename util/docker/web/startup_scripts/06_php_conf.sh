#!/bin/bash

source /etc/php/.version

dockerize -template "/etc/php/${PHP_VERSION}/05-pheme.ini.tmpl:/etc/php/${PHP_VERSION}/cli/conf.d/05-pheme.ini"
