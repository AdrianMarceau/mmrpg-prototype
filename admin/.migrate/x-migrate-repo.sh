#!/bin/bash

source "$(dirname "$0")/x-common.sh"

REPO_PATH="${BASE_PATH}content/${1}/"

if test -d  "${REPO_PATH}"; then

    php "${ADMIN_PATH}/.migrate/migrate-objects.php" kind="${1}"

else

    echo "Unable to migrate objects to new directories!"
    echo "${REPO_PATH} does not exist!"
    echo ""
    echo "FAILED!"

fi