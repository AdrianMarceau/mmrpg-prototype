#!/bin/bash

# Get the script's directory
current_user="$USER"
current_script_path="$(readlink -f "$BASH_SOURCE")"
project_base_path="$(dirname "$(dirname "$(dirname "$current_script_path")")")"
log_base_path="$(dirname "$project_base_path")"

# Redirect stdout and stderr to log files in the script directory
exec >> "$log_base_path/_logs/cron.log" 2>> "$log_base_path/_logs/error.log"

# Set the path to the list files
PUSH_LIST_FILE="$project_base_path/.cache/admin/cron_git-push-pending.list"
PULL_LIST_FILE="$project_base_path/.cache/admin/cron_git-pull-pending.list"

#echo "=========="
echo "Running the '$BASH_SOURCE' script as '$current_user' on $(date '+%Y-%m-%d @ %l:%M%p')"
#echo "-----"

# Check if the list files exist and process them
for LIST_FILE in "$PUSH_LIST_FILE" "$PULL_LIST_FILE"; do
    if [ -f "$LIST_FILE" ]; then

        echo -e "(!) Found a list file: '$LIST_FILE'"
        echo "-----"

        # Read the directories from the list file and process them
        processed_successfully=false
        while IFS= read -r repo_path; do

            # Collect the relative path and action token
            repo_rel_path=$(echo "$repo_path" | sed "s|$project_base_path/||")
            filename="$(basename "$LIST_FILE")"
            action_token="$(echo "$filename" | sed 's/-pending.list//')"
            echo -e "Attempting to run '$action_token' on the '$repo_rel_path' repository directory..."

            # Perform the action based on the list file name
            cd "$repo_path"
            if [ "$action_token" = "cron_git-pull" ]; then
                echo "Output from git pull:"
                git_output=$(git pull -s recursive -X theirs --no-edit 2>&1)
                echo "$git_output"
                processed_successfully=true
            elif [ "$action_token" = "cron_git-push" ]; then
                echo "First, output from git pull:"
                git_output=$(git pull -s recursive -X theirs --no-edit 2>&1)
                echo "$git_output"
                echo "Then, output from git push:"
                git_output=$(git push origin master 2>&1)
                echo "$git_output"
                processed_successfully=true
            else
                echo "Cron Error (1/2): Something went wrong running the sudo-git-wrapper script as '$current_user' on $(date '+%Y-%m-%d @ %l:%M%p')" >&2
                echo "Cron Error (2/2): Unknown list file: $LIST_FILE" >&2
                echo "Unknown list file: $LIST_FILE" >&1
                break
            fi

            # If processed successfully, then print a message
            if [ "$processed_successfully" = true ]; then
                echo -e "Finished executing '$(basename "$LIST_FILE" | sed 's/-pending.list//')' on path\n"
            fi

        done < "$LIST_FILE"

        # Remove the list file if it was successfully processed
        if [ "$processed_successfully" = true ]; then
            rm "$LIST_FILE"
            echo -e "Deleted the list file: '$LIST_FILE'"
        fi
        echo "-----"

    fi
done

echo "Done."
exit 0
