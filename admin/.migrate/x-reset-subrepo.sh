#!/bin/bash

source "$(dirname "$0")/x-common.sh"

SUBREPO_PATH="${BASE_PATH}content/${1}/"
SUBREPO_BRANCH="master"

TEMP_SUBREPO_PATH="$(mktemp -d)"
TEMP_CONFIG_PATH="$(mktemp)"

echo "=================================="
echo "-- FACTORY RESET ${1^^} SUBREPO --"
echo "=================================="
echo ""

if test -d  "${SUBREPO_PATH}"; then

    echo "Checking out temp copy of sub-repo into separate path ${TEMP_SUBREPO_PATH} ..."
    echo ""
    git clone "${GITHUB_BASE}mmrpg-prototype_${1}.git" "${TEMP_SUBREPO_PATH}"

    echo "Making a copy of the temp sub-repo config before deleting ..."
    echo ""
    cp "${TEMP_SUBREPO_PATH}/.git/config" "${TEMP_CONFIG_PATH}"

    echo "Deleting the temp sub-repo directory and recreating as an empty one ..."
    echo ""
    rm -rf "${TEMP_SUBREPO_PATH}"
    mkdir "${TEMP_SUBREPO_PATH}"

    echo "Changing to the new empty sub-repo directory ..."
    echo ""
    cd "${TEMP_SUBREPO_PATH}"

    echo "Initializing .git in the new empty sub-repo directory ..."
    echo ""
    git init

    echo ""
    echo "Copying the temp config file back into temp sub-repo directory ..."
    echo ""
    rm -f "${TEMP_SUBREPO_PATH}/.git/config"
    cp "${TEMP_CONFIG_PATH}" "${TEMP_SUBREPO_PATH}/.git/config"

    echo "Copying over all folders from actual sub-repo into the temp sub-repo directory ..."
    echo ""
    find "${SUBREPO_PATH}" -type d ! -name .git ! -name pages -exec cp -rt "${TEMP_SUBREPO_PATH}" {} +
    cp "${SUBREPO_PATH}README.md" "${TEMP_SUBREPO_PATH}/README.md"

    echo "Adding all copied files and directories and commiting ...";
    echo ""
    git add .
    git commit -m "Initial commit"

    echo "Force-pushing the changes as this was the first commit ..."
    echo ""
    git push -u --force origin "${SUBREPO_BRANCH}"

    echo "Changing back to actual sub-repo directory ..."
    echo ""
    cd "${SUBREPO_PATH}"

    echo "Resetting all the unstaged changes and removing untracked files ..."
    echo ""
    git checkout .
    git clean -fd

    echo "Pulling down changes that were recently pushed from the temp sub-repo ..."
    echo ""
    git fetch --all
    git reset --hard "origin/${SUBREPO_BRANCH}"

    echo ""
    echo "Cleaning up temporary directories and files ..."
    echo ""
    rm -rf "${TEMP_SUBREPO_PATH}"
    rm -rf "${TEMP_CONFIG_PATH}"

    echo ""
    echo "DONE!"

    start chrome "${GITHUB_BASE}mmrpg-prototype_${1}.git"

else

    echo "${SUBREPO_PATH} does not exist!"
    echo ""
    echo "FAILED!"

fi

echo ""