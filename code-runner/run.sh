#!/bin/bash

# File: /code/run.sh
echo "$USER_CODE" > user_code.txt

# Based on the language, execute with test input
case "$LANGUAGE" in
    python)
        echo "$TEST_INPUT" | python3 user_code.txt
        ;;
    php)
        echo "$TEST_INPUT" | php user_code.txt
        ;;
    javascript)
        echo "$TEST_INPUT" | node user_code.txt
        ;;
    java)
        # Save as Main.java for Java
        echo "$USER_CODE" > Main.java
        javac Main.java && echo "$TEST_INPUT" | java Main
        ;;
    cpp)
        # Compile and run C++
        g++ user_code.txt -o output && echo "$TEST_INPUT" | ./output
        ;;
    c)
        # Compile and run C
        gcc user_code.txt -o output && echo "$TEST_INPUT" | ./output
        ;;
    csharp)
        # Compile and run C#
        mcs user_code.txt -out:output.exe && echo "$TEST_INPUT" | mono output.exe
        ;;
    *)
        echo "Error: Unsupported language: $LANGUAGE"
        exit 1
        ;;
esac
