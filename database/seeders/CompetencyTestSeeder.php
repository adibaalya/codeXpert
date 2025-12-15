<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class CompetencyTestSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
           // Java MCQ Questions
            [
                'content' => 'What is a correct syntax to output "Hello World" in Java?',
                'answersData' => 'System.out.println("Hello World");',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Console.WriteLine("Hello World");', 'System.out.println("Hello World");', 'echo("Hello World");', 'print ("Hello World");'],
            ],

            [
                'content' => 'Which method can be used to find the length of a string?',
                'answersData' => 'length()',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['length()', 'getLength()', 'len()', 'getSize()'],
            ],

            [
                'content' => 'Which operator can be used to compare two values?',
                'answersData' => '==',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['<>', '><', '=', '=='],
            ],

            [
                'content' => 'What will be the output of the following program?

            ```java
            int[][] matrix = {
                {1, 2, 3},
                {4, 5, 6},
                {7, 8, 9}
            };
            System.out.println(matrix[1][2]);
            ```',
                'answersData' => '6',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['4', '5', '6', '9'],
            ],

            [
                'content' => 'What will be the output of the following Java code?

            ```java
            class Test {
                int x;
                Test() {
                    x = 10;
                }
            }
            public class Main {
                public static void main(String[] args) {
                    Test obj = new Test();
                    System.out.println(obj.x);
                }
            }
            ```',
                'answersData' => '10',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Compilation error', '0', '10', 'Null'],
            ],

            [
                'content' => 'What is the correct way to create an object called myObj of MyClass?',
                'answersData' => 'MyClass myObj = new MyClass();',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['MyClass myObj = new MyClass);', 'new myObj = MyClass);', 'class MyClass = new myObj();', 'MyClass myObj = new MyClass();'],
            ],

            [
                'content' => 'What will be the output of this code?

            ```java
            class Animal {
                void makeSound() {
                    System.out.println("Animal makes a sound");
                }
            }
            class Dog extends Animal {
                void makeSound() {
                    System.out.println("Dog barks");
                }
            }
            public class Main {
                public static void main(String[] args) {
                    Animal a = new Dog();
                    a.makeSound();
                }
            }
            ```',
                'answersData' => 'Dog barks',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Animal makes a sound', 'Dog barks', 'Compilation error', 'Runtime error'],
            ],

            [
                'content' => 'Predict the output of the following program.

            ```java
            abstract class demo
            {
                public int a;
                demo()
                {
                    a = 10;
                }

                abstract public void set();
                
                abstract final public void get();

            }

            class Test extends demo
            {

                public void set(int a)
                {
                    this.a = a;
                }

                final public void get()
                {
                    System.out.println("a = " + a);
                }

                public static void main(String[] args)
                {
                    Test obj = new Test();
                    obj.set(20);
                    obj.get();
                }
            }
            ```',
                'answersData' => 'Compilation error',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['a = 10', 'a = 20', 'Compilation error'],
            ],

            [
                'content' => 'Consider the following Java code fragment:

            ```java
            1   public class While
            2   {
            3    public void loop()
            4      {
            5      int x = 0;
            6      while(1)
            7        {
            8        System.out.println("x plus one is" +(x+1));
            9        }
            10     }
            11  }
            ```',
                'answersData' => 'There is syntax error in line no. 6',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['There is syntax error in line no. 1', 'There is syntax errors in line nos. 1 & 6', 'There is syntax error in line no. 8', 'There is syntax error in line no. 6'],
            ],

            [
                'content' => 'Can a Java constructor be private?',
                'answersData' => 'Yes, but the class cannot be instantiated outside the class.',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['No, it must be public.', 'Yes, but the class cannot be instantiated outside the class.', 'Yes, and it can be accessed from anywhere.', 'No, Java does not allow private constructors.'],
            ],

            [
                'content' => 'What is the output of the following code?

            ```java
            class Test {
            public static void swap(Integer i, Integer j) {
                Integer temp = new Integer(i);
                i = j;
                j = temp;
            }
            public static void main(String[] args) {
                Integer i = new Integer(10);
                Integer j = new Integer(20);
                swap(i, j);
                System.out.println("i = " + i + ", j = " + j);
            }
            }
            ```',
                'answersData' => 'i = 10, j = 20',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['i = 10, j = 20', 'i = 20, j = 10', 'i = 10, j = 10', 'i = 20, j = 20'],
            ],

            [
                'content' => 'How can you correctly initialize a 2D array in Java?',
                'answersData' => 'int arr[][] = {{1,2}, {3,4}};',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['int[][] arr = new int(3,3);', 'int arr[][] = {{1,2}, {3,4}};', 'int arr[][] = new int[2][2]{{1,2}, {3,4}};', 'int arr[2][2] = {1,2,3,4};'],
            ],

            [
                'content' => 'Study the following program:

            ```java
            // precondition: x>=0
            public void demo(int x)
            {
                System.out.print(x % 10);
                if (x / 10 != 0) {
                    demo(x / 10);
                }
                System.out.print(x % 10);
            }
            ```

            Which of the following is printed as a result of the call `demo(1234)`? (Note: The original code condition `x % 10 != 0` for recursion is likely a typo and should be `x / 10 != 0` for typical reverse/palindrome logic. Assuming `x / 10 != 0`.)',
                'answersData' => '43211234',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['1441', '3443', '43211234', '4321001234'],
            ],

            // Java Coding Questions
            [
                'content' => 'How do you reverse a string in Java?',
                'answersData' => 'public class StringPrograms {
            public static String reverse(String in) {
                if (in == null)
                throw new IllegalArgumentException("Null is not valid input");
                StringBuilder out = new StringBuilder();
                char[] chars = in.toCharArray();
                for (int i = chars.length - 1; i >= 0; i--)
                out.append(chars[i]);
                return out.toString();
            }
            }',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use the StringBuilder class or iterate backwards over the character array.',
                'testCases' => [
                    ['input' => ['in' => 'hello'], 'output' => 'olleh'],
                    ['input' => ['in' => '123'], 'output' => '321'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Given an employee\'s age, write a program using Java 17 to categorize them as "Youth (15-25)", "Mid Level(25-50)", "Senior (50-65)", or "Invalid Age (other age)".',
                'answersData' => 'public class EmployeeCategorizer {
                public static String categorizeEmployeeAge(Number age) {
                    return switch (age) {
                        case Integer i when i >= 15 && i < 25 -> "Youth Employee";
                        case Integer i when i >= 25 && i <= 50 -> "Mid level Employee";
                        case Integer i when i > 50 && i <= 65 -> "Senior Employee";
                        default -> "Invalid Age for Employment";
                    };
                }
            }',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Utilize Java 17\'s pattern matching for switch expressions to handle ranges and types.',
                'testCases' => [
                    ['input' => ['age' => 22], 'output' => 'Youth Employee'],
                    ['input' => ['age' => 35], 'output' => 'Mid level Employee'],
                    ['input' => ['age' => 60], 'output' => 'Senior Employee'],
                    ['input' => ['age' => 80], 'output' => 'Invalid Age for Employment'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a program that determines whether a year is a leap year or a common year.',
                'answersData' => 'public class LeapYearChecker {
                public static String checkYear(Number year) {
                    return switch (year) {
                        case Integer y -> (y % 4 == 0 && y % 100 != 0) || (y % 400 == 0) ?
                                    "Leap Year" : "Common Year";
                        default -> "Invalid Year";
                    };
                }
            }',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'A year is a leap year if it is divisible by 4, unless it is divisible by 100 but not by 400.',
                'testCases' => [
                    ['input' => ['year' => 2000], 'output' => 'Leap Year'],
                    ['input' => ['year' => 1900], 'output' => 'Common Year'],
                    ['input' => ['year' => 2024], 'output' => 'Leap Year'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a program to parse inputs into categories like "Valid Email" or "Phone Number". A valid email must have \'@\' character and a valid phone number must have 10 digits (using regex for simplicity).',
                'answersData' => 'public class InputParser {
                public static String parseInput(Object input) {
                    return switch (input) {
                        case String s when s.contains("@") && s.contains(".") -> "Valid Email";
                        case String s when s.matches("\\\\d{10}") -> "Valid Phone Number";
                        default -> "Invalid Email or Phone Number";
                    };
                }
            }',
                'status' => 'Approved',
                'language' => 'Java',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use String methods like `contains()` and `matches()` with a regular expression for the phone number check.',
                'testCases' => [
                    ['input' => ['input' => 'user@example.com'], 'output' => 'Valid Email'],
                    ['input' => ['input' => '1234567890'], 'output' => 'Valid Phone Number'],
                    ['input' => ['input' => 'invalid_input'], 'output' => 'Invalid Email or Phone Number'],
                ],
                'options' => [], // Added for consistency
            ], // <-- COMMA ADDED HERE

            // C MCQ Questions
            [
                'content' => 'Which of the following is not a logical operator?',
                'answersData' => '|',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['&&', '!', '||', '|'],
            ],

            [
                'content' => 'What is the output of the following C program?

            ```c
            #include "stdio.h"

            int main()  
            {
            int x, y = 5, z = 5;
            x = y == z;
            printf("%d", x);
            getchar();
            return 0;
            }
            ```',
                'answersData' => '1',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['0', '1', '5', 'Compiler Error'],
            ],

            [
                'content' => 'What does the following C statement mean?

            `scanf("%4s", str);`',
                'answersData' => 'Read maximum 4 characters from console.',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Read exactly 4 characters from console.', 'Read maximum 4 characters from console.', 'Read a string str in multiples of 4', 'Nothing'],
            ],

            [
                'content' => 'Assume that a character takes 1 byte. Output of following program?

            ```c
            #include<stdio.h>
            int main()
            {
                char str[20] = "GeeksQuiz";
                printf ("%d", sizeof(str));
                return 0;
            }
            ```',
                'answersData' => '20',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['9', '10', '20', 'Garbage Value'],
            ],

            [
                'content' => 'The value printed by the following program is

            ```c
            void f(int* p, int m)
            {
                m = m + 5;
                *p = *p + m;
                return;
            }
            void main()
            {
                int i=5, j=10;
                f(&i, j);
                printf("%d", i+j);
            }
            ```',
                'answersData' => '30',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['10', '20', '30', '40'],
            ],

            [
                'content' => 'In C, parameters are always',
                'answersData' => 'Passed by value',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Passed by value', 'Passed by reference', 'Non-pointer variables are passed by value and pointers are passed by reference', 'Passed by value result'],
            ],

            [
                'content' => 'If n has 3, then the statement `a[++n]=n++;`',
                'answersData' => 'what is assigned is compiler dependent',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['assigns 3 to a[5]', 'assigns 4 to a[5]', 'assigns 4 to a[4]', 'what is assigned is compiler dependent'],
            ],

            [
                'content' => 'What is the output of the following program?

            ```c
            #include <stdio.h>
            // Assume base address of "GeeksQuiz" to be 1000
            int main()
            {
            printf(5 + "GeeksQuiz");
            return 0;
            }
            ```',
                'answersData' => 'Quiz',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['GeeksQuiz', 'Quiz', '1005', 'Compile-time error'],
            ],

            [
                'content' => 'Consider the following C declaration 

            ```c
            struct
            {
                short s[5];
                union
                {
                    float y;
                    long z;
                }
                u;
            }t;
            ```

            Assume that the objects of the type short, float and long occupy 2 bytes, 4 bytes and 8 bytes, respectively. The memory requirement for variable t, ignoring alignment consideration, is',
                'answersData' => '18 bytes',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['22 bytes', '18 bytes', '14 bytes', '10 bytes'],
            ],

            [
                'content' => 'What is the output of the above program?

            ```c
            #include <stdio.h>
            #if X == 3
                #define Y 3
            #else
                #define Y 5
            #endif

            int main()
            {
                printf("%d", Y);
                return 0;
            }
            ```',
                'answersData' => '5',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['3', '5', '3 or 5 depending on value of X', 'Compile time error'],
            ],

            [
                'content' => 'What does the following program print?

            ```c
            #include <stdio.h>
            void f(int *p, int *q)
            {
            p = q;
            *p = 2;
            }
            int i = 0, j = 1;
            int main()
            {
            f(&i, &j);
            printf("%d %d \\n", i, j);
            return 0;
            }
            ```',
                'answersData' => '0 2',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['2 2', '2 1', '0 1', '0 2'],
            ],

            [
                'content' => 'What is the output of the following program?

            ```c
            #include <stdio.h>
            int tmp=20;
            main( )
            {
            printf("%d ",tmp);
            func( );
            printf("%d ",tmp);
            }
            func( )
            {
            static int tmp=10;
            printf("%d ",tmp);
            }
            ```',
                'answersData' => '20 10 20',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['20 10 10', '20 10 20', '20 20 20', '10 10 10'],
            ],

            [
                'content' => 'What does the following expression means ?

            `char *(*(a[N]) ()) ();`',
                'answersData' => 'an array of n pointers to function returning pointers to functions returning pointers to characters.',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['a pointer to a function returning array of n pointers to function returning character pointers.', 'a function return array of N pointers to functions returning pointers to characters', 'an array of n pointers to function returning pointers to characters', 'an array of n pointers to function returning pointers to functions returning pointers to characters.'],
            ],

            // C Coding Questions
            [
                'content' => 'Check whether a number is a palindrome.',
                'answersData' => '// C Program for
            // Checking Palindrome
            #include <stdio.h>
            void check_palindrome(int N)
            {
                int T = N;
                int rev = 0; 
                while (T != 0) {
                    rev = rev * 10 + T % 10;
                    T = T / 10;
                }
                if (rev == N)
                    printf("%d is palindrome\\n", N);
                else
                    printf("%d is not a palindrome\\n", N);
            }',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Reverse the number and compare it with the original number.',
                'testCases' => [
                    ['input' => ['N' => 13431], 'output' => '13431 is palindrome'],
                    ['input' => ['N' => 12345], 'output' => '12345 is not a palindrome'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a Program to create a pyramid pattern using C.',
                'answersData' => '// C Program print Pyramid pattern
            #include <stdio.h>
            int main()
            {
                int N = 5;
                for (int i = 1; i <= N; i++) {
                    for (int j = 1; j <= N - i; j++)
                        printf(" ");
                    for (int j = 1; j < 2 * i; j++)
                        printf("*");
                    printf("\\n");
                }
                return 0;
            }',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use nested loops to control spaces and stars in each row.',
                'testCases' => [],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a C Program to search elements in an array.',
                'answersData' => '// C code to Search elements in array
            #include <stdio.h;
            int search(int arr[], int N, int x)
            {
                int i;
                for (i = 0; i < N; i++)
                    if (arr[i] == x)
                        return i;
                return -1;
            }',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Iterate through the array and compare each element with the target value.',
                'testCases' => [
                    ['input' => ['arr' => [9, 3, 2, 1, 10, 4], 'N' => 6, 'x' => 10], 'output' => 4],
                    ['input' => ['arr' => [9, 3, 2, 1, 10, 4], 'N' => 6, 'x' => 5], 'output' => -1],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write code to Calculate frequency of characters in a string',
                'answersData' => '#include<stdio.h>
            int main() {
            char str[100];
            int i;
            int freq[256] = {
                0
            };
            printf("Enter the string: ");
            gets(str);
            for (i = 0; str[i] != \'\\0\'; i++) {
                freq[str[i]]++;
            }
            for (i = 0; i < 256; i++) {
                if (freq[i] != 0) {
                printf("The frequency of %c is %d\\n", i, freq[i]);
                }
            }
            return 0;
            }',
                'status' => 'Approved',
                'language' => 'C',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use an array (e.g., size 256 for ASCII) to store the count for each character, using the character\'s ASCII value as the index.',
                'testCases' => [
                    ['input' => ['str' => 'aabbc'], 'output' => 'The frequency of a is 2\nThe frequency of b is 2\nThe frequency of c is 1'],
                ],
                'options' => [], // Added for consistency
            ], // <-- COMMA ADDED HERE

            // C++ MCQ Questions
            [
                'content' => 'What is the file extension for a C++ source file?',
                'answersData' => '.cpp',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['.c', '.cpp', '.h', '.exe'],
            ],

            [
                'content' => 'What is the output of the following C++ code?

            ```cpp
            #include <iostream>
            using namespace std;

            int main()
            {
                cout << "Hello, World!" << endl;
                return 0;
            }
            ```',
                'answersData' => 'Hello, World!',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Hello, World', 'Hello, World!', 'Hello World!', 'Hello World'],
            ],

            [
                'content' => 'Which among the following loop is guaranteed to execute at least once?',
                'answersData' => 'do-while',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['for', 'while', 'do-while', 'none of the above'],
            ],

            [
                'content' => 'How do you access the third element of an array arr in C++?',
                'answersData' => 'arr[2]',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['arr(3)', 'arr{3}', 'arr[3]', 'arr[2]'],
            ],

            [
                'content' => 'What are the four main principles of Object-Oriented Programming (OOP)?',
                'answersData' => 'Inheritance, Polymorphism, Encapsulation, Abstraction',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Inheritance, Polymorphism, Abstraction, Composition', 'Inheritance, Polymorphism, Encapsulation, Abstraction', 'Inheritance, Encapsulation, Aggregation, Composition', 'Polymorphism, Encapsulation, Composition, Aggregation'],
            ],

            [
                'content' => 'How to declare a function in C++ that returns an integer and takes two integer parameters?',
                'answersData' => 'int function(int, int);',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['int function(int, int);', 'int function(a, b);', 'int function(int a, int b);', 'int function();'],
            ],

            [
                'content' => 'What will be the output of the following code?

            ```cpp
            #include <iostream>
            using namespace std;

            void modify(int& a) { a = 10; }

            int main()
            {
                int x = 5;
                modify(x);
                cout << x;
                return 0;
            }
            ```',
                'answersData' => '10',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['5', '10', '0', '15'],
            ],

            [
                'content' => 'What will be the output of the following code?

            ```cpp
            #include <iostream>
            using namespace std;

            class MyClass {
            public:
                MyClass() { cout << "Constructor called" << endl; }
                ~MyClass() { cout << "Destructor called" << endl; }
            };
            int main()
            {
                MyClass obj;
                return 0;
            }
            ```',
                'answersData' => 'Constructor called and Destructor called',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Constructor called', 'Destructor called', 'Constructor called and Destructor called', 'No output'],
            ],

            [
                'content' => 'How to declare a pointer to an integer in C++?',
                'answersData' => 'int* p;',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['int* p;', 'int p*;', 'int& p;', 'int p;'],
            ],

            [
                'content' => 'What will be the output of the following code?

            ```cpp
            #include <iostream>
            using namespace std;

            int main(){
                int *p = new int(42);
                cout << *p;
                delete p;
            }
            ```',
                'answersData' => '42',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['0', 'null', '42', 'error'],
            ],

            [
                'content' => 'How to implement a class in C++ to prevent it from being inherited?',
                'answersData' => 'By using the final keyword.',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['By declaring the class as sealed.', 'By using the final keyword.', 'By making all methods private.', 'By making all constructors private'],
            ],

            [
                'content' => 'Consider the following code. What will happen if the getter method is not declared as const?

            ```cpp
            class MyClass {
            private:
                int value;
            public:
                int getValue() const { return value; }
            };
            ```',
                'answersData' => 'The method cannot be called on const instances of MyClass.',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['The method can modify the class\'s internal state.', 'The method will automatically become a const method.', 'The method will return a reference to the internal state.', 'The method cannot be called on const instances of MyClass.'],
            ],

            [
                'content' => 'What does the following code do?

            ```cpp
            #include <iostream>
            using namespace std;
            int main() {
            int* p = new int[10];
            delete[] p;
            }
            ```',
                'answersData' => 'Allocates and then deallocates an array of 10 integers',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Allocates and then deallocates a single integer', 'Deallocates a single integer', 'Causes a compilation error', 'Allocates and then deallocates an array of 10 integers'],
            ],

            // C++ Coding Questions
            [
                'content' => 'Write a Program to Count the Number of Vowels.',
                'answersData' => '// C++ Program to count the number of vowels
            #include <iostream>
            using namespace std;

            int main()
            {
                string str = "GeeksforGeeks to the moon";
                int vowels = 0;

                for (int i = 0; str[i] != \'\\0\'; i++) {
                    if (str[i] == \'a\' || str[i] == \'e\' || str[i] == \'i\' || str[i] == \'o\' || str[i] == \'u\' || str[i] == \'A\' || str[i] == \'E\' || str[i] == \'I\' || str[i] == \'O\' || str[i] == \'U\') {
                        vowels++;
                    }
                }

                cout << "Number of vowels in the string: " << vowels << endl;

                return 0;
            }',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Iterate through the string and check if each character is one of the vowels (case-sensitive).',
                'testCases' => [
                    ['input' => ['str' => 'Hello'], 'output' => 'Number of vowels in the string: 2'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a Program to Find a Leap Year or Not.',
                'answersData' => '// C++ program to check if a given
            // year is leap year or not
            #include <iostream>
            using namespace std;

            bool checkYear(int year)
            {
                if (year % 400 == 0)
                    return true;

                if (year % 100 == 0)
                    return false;

                if (year % 4 == 0)
                    return true;

                return false;
            }',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'A year is a leap year if it is divisible by 4, unless it is divisible by 100 but not by 400.',
                'testCases' => [
                    ['input' => ['year' => 2000], 'output' => 'Leap Year'],
                    ['input' => ['year' => 1900], 'output' => 'Not a Leap Year'],
                    ['input' => ['year' => 2024], 'output' => 'Leap Year'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a Program to Calculate the Frequency of Each Word in the Given String.',
                'answersData' => '// C++ program to calculate
            // frequency of each word
            // in given string
            #include <iostream>
            #include <map>
            #include <string>
            #include <sstream>
            using namespace std;

            void printFrequency(string str)
            {
                map<string, int> M;
                stringstream ss(str);
                string word;

                while (ss >> word) {
                    M[word]++;
                }

                for (auto& it : M) {
                    cout << it.first << " - " << it.second << endl;
                }
            }
            ',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use a map or hash table to store word counts. `stringstream` is useful for splitting the string into words.',
                'testCases' => [
                    ['input' => ['str' => 'Geeks For Geeks is for Geeks'], 'output' => 'For - 1\nGeeks - 3\nis - 1\nfor - 1'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a Program to Iterate Over a Queue Without Removing the Element.',
                'answersData' => '// C++ program to iterate a
            // STL Queue by Creating
            // copy of given queue
            #include <iostream>
            #include <queue>
            using namespace std;
            int main()
            {
                queue<int> q;
                q.push(1);
                q.push(2);
                q.push(3);
                queue<int> copy_queue = q;

                cout << "Queue elements :\\n";

                while (!copy_queue.empty()) {
                    cout << copy_queue.front() << " ";

                    copy_queue.pop();
                }
                return 0;
            }',
                'status' => 'Approved',
                'language' => 'C++',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Since an STL queue does not directly support iteration, create a copy and iterate by repeatedly accessing the front and popping the element from the copy.',
                'testCases' => [
                    ['input' => [], 'output' => 'Queue elements :\n1 2 3 4 5'],
                ],
                'options' => [], // Added for consistency
            ], // <-- COMMA ADDED HERE

            // Python MCQ Questions
            [
                'content' => 'What does the following Python code print?

            ```python
            print(2 + 3 * 5)
            ```',
                'answersData' => '17',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['25', '17', '7', '10'],
            ],

            [
                'content' => 'Which of the following is the correct way to create a variable in Python?',
                'answersData' => 'x = 5',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['variable x = 5', 'x = 5', 'int x = 5', 'create x and assign 5 to it'],
            ],

            [
                'content' => 'What is the correct way to define a function in Python?',
                'answersData' => 'def add(a, b):',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['function add(a, b):', 'def add(a, b):', 'define add(a, b):', 'define_function add(a, b):'],
            ],

            [
                'content' => 'Which statement is used to exit a loop prematurely in Python?',
                'answersData' => 'break',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['break', 'exit', 'return', 'continue'],
            ],

            [
                'content' => 'Which of the following data types is mutable in Python?',
                'answersData' => 'list',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['int', 'float', 'tuple', 'list'],
            ],

            [
                'content' => 'What does the following Python code do?

            ```python
            x = 1
            while x < 6:
            print(x, end=" ")
            x += 1
            if x == 4:
                continue
            ```',
                'answersData' => 'Prints numbers in the range [1, 6) with a skip for 4.',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Prints numbers in the range [1, 6) with a skip for 4.', 'Prints numbers in the range [1, 6] without a skip.', 'Raises a SyntaxError.', 'Enters into an infinite loop.'],
            ],

            [
                'content' => 'What is the output of the following code snippet?

            ```python
            for i in range(5):
            if i == 3:
                continue
            print(i)
            ```',
                'answersData' => '0 1 2 4',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['0 1 2 3 4', '0 1 2 4', '0 1 2', '0 1 2 3'],
            ],

            [
                'content' => 'What is the output of the following code?

            ```python
            my_string = "Hello World"
            print(my_string[::-1])
            ```',
                'answersData' => 'dlroW olleH',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Hello World', 'dlroW olleH', 'olleH dlroW', 'Error'],
            ],

            [
                'content' => 'Suppose li is `[3, 4, 5, 20, 5, 25, 1, 3]`, what is li after `li.pop(1)`?',
                'answersData' => '[3, 5, 20, 5, 25, 1, 3]',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['[3, 4, 5, 20, 5, 25, 1, 3]', '[1, 3, 3, 4, 5, 5, 20, 25]', '[3, 5, 20, 5, 25, 1, 3]', '[1, 3, 4, 5, 20, 5, 25]'],
            ],

            [
                'content' => 'What is the correct way to check if a key exists in a dictionary?',
                'answersData' => 'key in dictionary',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['key in dictionary', 'dictionary.exists(key)', 'dictionary.has_key(key)', 'key.exists(dictionary)'],
            ],

            [
                'content' => 'What will be the output of the following code snippet?

            ```python
            def solve(a, b):
            return b if a == 0 else solve(b % a, a)
            print(solve(20, 50))
            ```',
                'answersData' => '10',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['10', '20', '5', '1'],
            ],

            [
                'content' => 'What will be the output of the following program?

            ```python
            tuple = {}

            tuple[(1,2,4)] = 8
            tuple[(4,2,1)] = 10
            tuple[(1,2)] = 12
            _sum = 0
            for k in tuple:
            _sum += tuple[k]
            print(len(tuple) + _sum)
            ```',
                'answersData' => '33',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['34', '12', '31', '33'],
            ],

            [
                'content' => 'What is the output of the following program?

            ```python
            value = [1, 2, 3, 4]

            data = 0
            try:
            data = value[4]
            except IndexError:
            print(\'GFG\', end = \'\')
            except:
            print(\'GeeksforGeeks \', end = \'\')
            ```',
                'answersData' => 'GFG',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['GeeksforGeeks', 'GFG', 'GFG GeeksforGeeks', 'Compilation error'],
            ],

            // Python Coding Questions
            [
                'content' => 'Checking for Palindrome Using Extended Slicing Technique',
                'answersData' => 'str1 = "Kayak".lower()
            str2 = "kayak".lower()

            if(str1 == str2[::-1]):
                print("True")
            else:
                print("False")
            ',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use slicing with a step of -1 to reverse a string for comparison.',
                'testCases' => [
                    ['input' => ['str' => 'Kayak'], 'output' => 'True'],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Given a string, find the length of the longest substring without repeating characters.',
                'answersData' => 'def length_of_longest_substring(s: str) -> int:
                char_set = set()
                left = 0
                max_len = 0

                for right in range(len(s)):
                    while s[right] in char_set:
                        char_set.remove(s[left])
                        left += 1
                    char_set.add(s[right])
                    max_len = max(max_len, right - left + 1)

                return max_len',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use a sliding window approach with a set to keep track of characters in the current window.',
                'testCases' => [
                    ['input' => ['s' => 'abcabcbb'], 'output' => 3],
                    ['input' => ['s' => 'bbbbb'], 'output' => 1],
                    ['input' => ['s' => 'pwwkew'], 'output' => 3],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Find the length of the longest word in a given sentence.',
                'answersData' => 'def longest_word_length(sentence):
                words = sentence.split()
                if not words:
                    return 0
                max_length = max(len(word) for word in words)
                return max_length',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Split the sentence into words and use the `max()` function with a generator expression to find the maximum length.',
                'testCases' => [
                    ['input' => ['sentence' => 'The quick brown fox jumped'], 'output' => 6],
                    ['input' => ['sentence' => 'a b ccc dd'], 'output' => 3],
                ],
                'options' => [], // Added for consistency
            ],

            [
                'content' => 'Write a Python program to convert a decimal number to binary.',
                'answersData' => 'def decimal_to_binary(decimal):
                return bin(decimal)[2:]',
                'status' => 'Approved',
                'language' => 'Python',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'Code_Solution',
                'hint' => 'Use Python\'s built-in `bin()` function and slice the result.',
                'testCases' => [
                    ['input' => ['decimal' => 10], 'output' => '1010'],
                    ['input' => ['decimal' => 5], 'output' => '101'],
                ],
                'options' => [], // Added for consistency
            ],

            // SQL MCQ Questions
            [
                'content' => 'Which SQL keyword is used to retrieve data from a database?',
                'answersData' => 'SELECT',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['FETCH', 'EXTRACT', 'SELECT', 'RECOVER'],
            ],

            [
                'content' => 'What is the purpose of the WHERE clause in SQL?',
                'answersData' => 'To filter rows based on a specified condition',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['To specify which columns to select', 'To specify which table to select from', 'To filter rows based on a specified condition', 'To order the results'],
            ],

            [
                'content' => 'Which SQL statement is used to delete data from a database?',
                'answersData' => 'DELETE',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['TRUNCATE', 'DELETE', 'DROP', 'REMOVE'],
            ],

            [
                'content' => 'Which SQL operator is used to compare two values for equality?',
                'answersData' => '=',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'beginner',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['EQUALS', 'COMPARE', '=', 'IS'],
            ],

            [
                'content' => 'Write an SQL query to retrieve the names of employees whose salary is greater than 50000.',
                'answersData' => 'SELECT name FROM employees WHERE salary > 50000;',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['SELECT name FROM employees HAVING salary > 50000;', 'SELECT name FROM employees WHERE salary > 50000;', 'SELECT name FROM employees WHERE salary > \'50000\';', 'SELECT name FROM employees GROUP BY salary HAVING salary > 50000;'],
            ],

            [
                'content' => 'Which SQL constraint is used to ensure that all values in a column are unique?',
                'answersData' => 'UNIQUE',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['PRIMARY KEY', 'UNIQUE', 'CHECK', 'FOREIGN KEY'],
            ],

            [
                'content' => 'What does the SQL operator "LIKE" do?',
                'answersData' => 'Performs pattern matching on a string value',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Checks if a value is NULL', 'Checks if a value is within a specified range', 'Performs pattern matching on a string value', 'Compares two values for equality'],
            ],

            [
                'content' => 'Write an SQL query to retrieve the names of employees whose names start with \'A\'.',
                'answersData' => 'SELECT name FROM employees WHERE name LIKE \'A%\';',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['SELECT name FROM employees WHERE name LIKE \'A%\';', 'SELECT name FROM employees WHERE name = \'A%\';', 'SELECT name FROM employees WHERE name LIKE \'%A\';', 'SELECT name FROM employees WHERE name = \'%A%\';'],
            ],

            [
                'content' => 'Which SQL JOIN type returns all rows from the left table and matching rows from the right table?',
                'answersData' => 'LEFT JOIN',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'intermediate',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL JOIN'],
            ],

            [
                'content' => 'What does the SQL keyword "GROUP BY" do?',
                'answersData' => 'Group rows that have the same values into summary rows',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Orders the result set in ascending order', 'Group rows that have the same values into summary rows', 'Filters rows based on a specified condition', 'Joins two or more tables'],
            ],

            [
                'content' => 'What does the SQL constraint "FOREIGN KEY" do?',
                'answersData' => 'Links a column to a primary key in another table',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['Ensures that all values in a column are unique', 'Specifies a default value for a column', 'Links a column to a primary key in another table', 'Enforces a condition on the values allowed in a column'],
            ],

            [
                'content' => 'Write an SQL query to retrieve the names of employees who belong to the \'Sales\' department and have a salary greater than 60000.',
                'answersData' => 'SELECT name FROM employees WHERE department = \'Sales\' AND salary > 60000;',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['SELECT name FROM employees WHERE department = \'Sales\' AND salary > 60000;', 'SELECT name FROM employees WHERE department = \'Sales\' OR salary > 60000;', 'SELECT name FROM employees WHERE department = \'Sales\' HAVING salary > 60000;', 'SELECT name FROM employees GROUP BY department, salary HAVING department = \'Sales\' AND salary > 60000;'],
            ],

            [
                'content' => 'Which SQL operator is used to test for negation?',
                'answersData' => 'NOT',
                'status' => 'Approved',
                'language' => 'SQL',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['NOT', 'EXISTS', 'IS', '!='],
            ],

            [
                'content' => 'Which SQL operator is used to test for negation?',
                'answersData' => 'NOT',
                'status' => 'Approved',
                'language' => 'JavaScript',
                'level' => 'advanced',
                'questionCategory' => 'competencyTest',
                'questionType' => 'MCQ_Single',
                'options' => ['NOT', 'EXISTS', 'IS', '!='],
            ],

        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}