#!/bin/bash

# SQL Execution Script - Behaves like MySQL Workbench
# Supports both SQLite (in-memory) and MySQL queries

cd /tmp/code-exec

# Determine SQL engine (default to SQLite for safety)
SQL_ENGINE="${SQL_ENGINE:-sqlite}"

if [ "$SQL_ENGINE" = "mysql" ]; then
    # MySQL execution (requires connection details)
    if [ -z "$MYSQL_HOST" ]; then
        echo "Error: MySQL host not configured"
        exit 1
    fi
    
    # Save SQL query to file
    echo "$USER_CODE" > query.sql
    
    # Execute query
    mysql -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -D"$MYSQL_DATABASE" < query.sql 2>&1
else
    # SQLite execution (in-memory or file-based)
    # Create temporary database
    DB_FILE="/tmp/sql-data/temp_${RANDOM}.db"
    
    # If there's initialization SQL (schema), run it first
    if [ -n "$SQL_INIT" ]; then
        echo "$SQL_INIT" | sqlite3 "$DB_FILE" 2>&1
    fi
    
    # Execute user's SQL query
    echo "$USER_CODE" | sqlite3 -header -column "$DB_FILE" 2>&1
    
    # Clean up
    rm -f "$DB_FILE"
fi
