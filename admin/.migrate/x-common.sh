#!/usr/bin/env bash

CURRENT_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )/"
CONTENT_PATH="$(dirname "${CURRENT_PATH}")/"
BASE_PATH="$(dirname "${CONTENT_PATH}")/"
ADMIN_PATH="${BASE_PATH}admin/"
GITHUB_BASE="https://github.com/AdrianMarceau/"

source "${BASE_PATH}/content/index.sh"
