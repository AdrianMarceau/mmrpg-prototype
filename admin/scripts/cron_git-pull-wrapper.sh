#!/bin/bash

# Check if the required arguments are provided
if [ "$#" -ne 2 ]; then
    echo "Error: Missing arguments"
    echo "Usage: $0 <action_token> <user>"
    exit 1
fi

# Get the script's directory
current_script_path="$(readlink -f "$BASH_SOURCE")"
project_base_path="$(dirname "$(dirname "$(dirname "$current_script_path")")")"

# Set the path to the list file
LIST_FILE="$project_base_path/.cache/admin/cron_${1}-pending.list"

echo "================================"
echo "Running the $1 wrapper script..."
echo "================================"

# Check if the list file exists
if [ -f "$LIST_FILE" ]; then
    echo "The $1 list file exists, proceeding with the script."

    # Read the directories from the list file and process them
    while IFS= read -r repo_path; do
        echo "Entering the script as user $2"
        cd "$repo_path"
        echo -e "Changed directory to path: \n$repo_path"

        # Perform the action based on the first argument
        if [ "$1" = "git-pull" ]; then
            echo "Output from git pull:"
            git_output=$(sudo -u "$2" git pull -s recursive -X theirs --no-edit 2>&1)
            echo "$git_output"
        elif [ "$1" = "git-push" ]; then
            echo "Output from git pre-pull and push:"
            git_output=$(sudo -u "$2" git pull -s recursive -X theirs --no-edit 2>&1)
            echo "$git_output"
            git_output=$(sudo -u "$2" git push origin master 2>&1)
            echo "$git_output"
        else
            echo "Unknown action token: $1"
            break
        fi

        echo -e "Finished executing $1 on path"
    done < "$LIST_FILE"

    # Remove the list file
    rm "$LIST_FILE"
    echo -e "Deleted the $1 list file at: \n$LIST_FILE"

else
    echo -e "Could not find the $1 list file at: \n$LIST_FILE"
    echo "A pending $1 list file does not exist, aborting the script."
fi

echo "================================"
