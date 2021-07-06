source "./scripts/defaults.sh"

rsync -azP ./www/uploads $SSH_REMOTE_USER@$SSH_REMOTE_HOST:checkout/$1/shared/www
