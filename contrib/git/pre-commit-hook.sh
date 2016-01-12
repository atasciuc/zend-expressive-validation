#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_PATH=$DIR"/../../"
COMPOSER_PATH=$(which composer)

if [[ -f $DIR/contrib.env ]]
then
    source $DIR/contrib.env
fi

# establish project path
if [[ ! -f $PROJECT_PATH/composer.json && ! -f $PROJECT_PATH/.project_path && ! -f $DIR/contrib.env ]]
then
    read -r -p "Can not find composer.json in $PROJECT_PATH, is this the project root? [y/N] " RESPONSE
    case $RESPONSE in
        [yY][eE][sS]|[yY])
            touch $PROJECT_PATH/.project_path
            ;;
        [nN][oO]|[nN])
            read -r -p "Enter root project path: " RESPONSE
            if [[ ! -d $RESPONSE ]]
            then
                echo "Invalid project path"
                exit 1;
            else
               echo "PROJECT_PATH=$RESPONSE" >> $DIR/contrib.env
               PROJECT_PATH=$RESPONSE
            fi
            ;;
        *)
            echo "Invalid response"
            exit 1;
        ;;
    esac
fi

# find composer executable..
if [[ -z "$COMPOSER_PATH" ]]
then
    # may not be global so look in project for it
    if [[ -f $PROJECT_PATH/composer.phar ]]
    then
        COMPOSER_PATH=$PROJECT_PATH/composer.phar
    else
        read -r -p "Enter composer executable path: " RESPONSE
        if [[ -f $RESPONSE ]]
        then
            echo "COMPOSER_PATH=$RESPONSE" >> $DIR/contrib.env
            COMPOSER_PATH=$RESPONSE
        else
            echo "Could not find composer"
            exit 1;
        fi
    fi
fi

# execute tests
$COMPOSER_PATH --working-dir=$PROJECT_PATH test