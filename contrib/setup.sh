#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
GIT_PATH=$DIR"/../.git"
if [[ ! -d $GIT_PATH ]]
then
    read -r -p "Enter project git path: " RESPONSE
    if [[ ! -d $RESPONSE ]]
    then
        echo "Could not find .git folder"
        exit 1;
    fi
fi

ln -s $DIR/git/pre-commit-hook.sh $GIT_PATH/hooks/pre-commit