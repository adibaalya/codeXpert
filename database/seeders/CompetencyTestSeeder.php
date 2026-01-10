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

            // JavaScript MCQ Questions
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

        // Validate and filter out SQL questions before inserting
        $allowedLanguages = ['Java', 'C', 'C++', 'Python', 'JavaScript', 'PHP', 'C#'];
        
        foreach ($questions as $question) {
            // Skip SQL questions
            if (isset($question['language']) && strtoupper($question['language']) === 'SQL') {
                continue;
            }
            
            // Validate language is in allowed list
            if (!isset($question['language']) || !in_array($question['language'], $allowedLanguages)) {
                \Log::warning("Skipping question with invalid language: " . ($question['language'] ?? 'unknown'));
                continue;
            }
            
            Question::create($question);
        }
    }
}