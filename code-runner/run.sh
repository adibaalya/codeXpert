#!/bin/bash

# Enhanced multi-language code execution script
# Supports Java, Python, C++, C, JavaScript, PHP, and C#
# Uses FILE_EXTENSION to determine execution strategy

# Set stack size limit (8MB)
ulimit -s 8192

# Determine file name based on extension
case "$FILE_EXTENSION" in
    java)
        FILENAME="Main.java"
        ;;
    cpp)
        FILENAME="main.cpp"
        ;;
    c)
        FILENAME="main.c"
        ;;
    py)
        FILENAME="main.py"
        ;;
    js)
        FILENAME="main.js"
        ;;
    php)
        FILENAME="main.php"
        ;;
    cs)
        FILENAME="Program.cs"
        ;;
    *)
        FILENAME="code.txt"
        ;;
esac

# Write user code to file (using printf for better handling of special characters)
cd /tmp/code-exec || exit 1
printf "%s" "$USER_CODE" > "$FILENAME"

# Execute based on file extension (compile vs interpret)
case "$FILE_EXTENSION" in
    py)
        # Python: Interpreted language
        if [ -n "$TEST_INPUT" ]; then
            echo "$TEST_INPUT" | python3 -u "$FILENAME" 2>&1
        else
            python3 -u "$FILENAME" 2>&1
        fi
        ;;
        
    php)
        # PHP: Interpreted language
        if [ -n "$TEST_INPUT" ]; then
            echo "$TEST_INPUT" | php "$FILENAME" 2>&1
        else
            php "$FILENAME" 2>&1
        fi
        ;;
        
    js)
        # JavaScript: Interpreted language (Node.js)
        if [ -n "$TEST_INPUT" ]; then
            echo "$TEST_INPUT" | node "$FILENAME" 2>&1
        else
            node "$FILENAME" 2>&1
        fi
        ;;
        
    java)
        # Java: Compiled language
        # Compile
        javac "$FILENAME" 2>&1
        if [ $? -eq 0 ]; then
            # Extract class name from filename (remove .java extension)
            CLASS_NAME="${FILENAME%.java}"
            # Run
            if [ -n "$TEST_INPUT" ]; then
                echo "$TEST_INPUT" | java "$CLASS_NAME" 2>&1
            else
                java "$CLASS_NAME" 2>&1
            fi
        else
            exit 1
        fi
        ;;
        
    cpp)
        # C++: Compiled language
        # Compile with C++17 standard and safety flags
        g++ -std=c++17 -O2 -Wall -Wextra -o main "$FILENAME" 2>&1
        COMPILE_EXIT=$?
        if [ $COMPILE_EXIT -eq 0 ]; then
            # Check if binary was created
            if [ ! -f "./main" ]; then
                echo "Error: Compilation succeeded but binary was not created"
                exit 1
            fi
            
            if [ -n "$TEST_INPUT" ]; then
                echo "$TEST_INPUT" | timeout 15s ./main 2>&1
            else
                timeout 15s ./main 2>&1
            fi
            EXIT_CODE=$?
            
            # Check for timeout or segfault
            if [ $EXIT_CODE -eq 124 ]; then
                echo "Error: Program timed out after 15 seconds"
                exit 1
            elif [ $EXIT_CODE -eq 139 ]; then
                echo "Error: Segmentation fault - check your memory access (arrays, pointers, stack usage)"
                exit 1
            fi
        else
            exit $COMPILE_EXIT
        fi
        ;;
        
    c)
        # C: Compiled language
        # Compile with C11 standard and safety flags (matching C++ approach)
        gcc -std=c11 -O2 -Wall -Wextra -o main "$FILENAME" 2>&1
        COMPILE_EXIT=$?
        if [ $COMPILE_EXIT -eq 0 ]; then
            # Check if binary was created
            if [ ! -f "./main" ]; then
                echo "Error: Compilation succeeded but binary was not created"
                exit 1
            fi
            
            if [ -n "$TEST_INPUT" ]; then
                echo "$TEST_INPUT" | timeout 15s ./main 2>&1
            else
                timeout 15s ./main 2>&1
            fi
            EXIT_CODE=$?
            
            # Check for timeout or segfault
            if [ $EXIT_CODE -eq 124 ]; then
                echo "Error: Program timed out after 15 seconds"
                exit 1
            elif [ $EXIT_CODE -eq 139 ]; then
                echo "Error: Segmentation fault - check your memory access (arrays, pointers, stack usage)"
                exit 1
            fi
        else
            exit $COMPILE_EXIT
        fi
        ;;
        
    cs)
        # C#: Compiled language (Mono)
        mcs "$FILENAME" -out:program.exe 2>&1
        if [ $? -eq 0 ]; then
            if [ -n "$TEST_INPUT" ]; then
                echo "$TEST_INPUT" | mono program.exe 2>&1
            else
                mono program.exe 2>&1
            fi
        else
            exit 1
        fi
        ;;
        
    *)
        # Fallback: Try to use LANGUAGE variable if FILE_EXTENSION is not set
        if [ -n "$LANGUAGE" ]; then
            echo "Warning: FILE_EXTENSION not set, falling back to LANGUAGE variable"
            case "$LANGUAGE" in
                python)
                    python3 -u code.txt 2>&1
                    ;;
                php)
                    php code.txt 2>&1
                    ;;
                javascript)
                    node code.txt 2>&1
                    ;;
                *)
                    echo "Error: Unsupported language or extension: $FILE_EXTENSION / $LANGUAGE"
                    exit 1
                    ;;
            esac
        else
            echo "Error: Neither FILE_EXTENSION nor LANGUAGE is set"
            exit 1
        fi
        ;;
esac
