#!/bin/bash

source "$(dirname "$0")/x-common.sh"

REPO_PATH="${BASE_PATH}content/${1}/"

if test -d  "${REPO_PATH}"; then

    php "${ADMIN_PATH}/.migrate/migrate-objects.php" kind="${1}"

    cd "${REPO_PATH}"

    git add .
    git reset

    echo "DONE!"
    echo ""

else

    echo "Unable to migrate objects to new directories!"
    echo "${REPO_PATH} does not exist!"

    echo -n "Create the new directory right now? (yes/no): "
    read answer

    if [ $answer == "yes" ]; then
        mkdir "${REPO_PATH}"
        echo "New directory created!"
        echo "Cloning mmrpg-prototype_${1}.git into the new directory..."
        git clone "${GITHUB_BASE}mmrpg-prototype_${1}.git" "${REPO_PATH}"
        echo "Clone successful!  Restarting migration script..."
        exec "${ADMIN_PATH}/.migrate/x-migrate-subrepo.sh" "${1}"
    else
        echo "FAILED!"
    fi

    echo ""

fi