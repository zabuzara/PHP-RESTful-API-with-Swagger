#!/bin/bash

case $1 in
  PUBLIC)
    php ./RESTful/classes/set_api_access_to_public.php
    ;;
  LOCAL)
    php ./RESTful/classes/set_api_access_to_local.php
    ;;
  *)
    echo -n "unknown access type"
    ;;
esac