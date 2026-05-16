#!/bin/bash

ENABLE_REDIS=${ENABLE_REDIS:-true}
export ENABLE_REDIS

dockerize -template "/var/pheme/centrifugo/config.yaml.tmpl:/var/pheme/centrifugo/config.yaml"
