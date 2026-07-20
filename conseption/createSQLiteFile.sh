#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
db_name="${1:-test.db}"

case "$db_name" in
	*.db|*.sqlite3)
		;;
	*.*)
		printf 'Erreur: extension non valide pour %s\n' "$db_name" >&2
		exit 1
		;;
	*)
		db_name="$db_name.db"
		;;
esac

db_path="$script_dir/$db_name"

mkdir -p "$script_dir"

if command -v php >/dev/null 2>&1; then
	if [ ! -f "$db_path" ]; then
		php -r 'file_put_contents($argv[1], "");' -- "$db_path"
	fi
else
	if [ ! -f "$db_path" ]; then
		: > "$db_path"
	fi
fi

printf 'SQLite file ready: %s\n' "$db_path"

