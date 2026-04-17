#!/bin/bash
set -e
set -x

mkdir -p /var/pheme/centrifugo
cp /bd_build/web/centrifugo/config.yaml.tmpl /var/pheme/centrifugo/config.yaml.tmpl
