#!/usr/bin/env python3
"""
AI Plagiarism Detection using TF-IDF Vector Similarity
This script compares student code against known AI-generated solutions
using cosine similarity of TF-IDF vectors.
"""

import sys
import os
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

def main():
    # Check if correct number of arguments provided
    if len(sys.argv) != 3:
        print("0.00")
        sys.exit(1)
    
    # 1. Read file paths from arguments
    student_file = sys.argv[1]
    ai_directory = sys.argv[2]  # Folder containing AI ghost solutions
    
    # 2. Load Student Code
    try:
        with open(student_file, 'r', encoding='utf-8', errors='ignore') as f:
            student_code = f.read()
    except FileNotFoundError:
        print("0.00")
        sys.exit(1)
    
    # Check if student code is empty
    if not student_code.strip():
        print("0.00")
        sys.exit(0)
    
    # 3. Load All AI Ghost Files
    ai_codes = []
    ghost_filenames = []
    
    if not os.path.isdir(ai_directory):
        print("0.00")
        sys.exit(0)
    
    for filename in os.listdir(ai_directory):
        path = os.path.join(ai_directory, filename)
        if os.path.isfile(path) and not filename.startswith('.'):
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    if content.strip():  # Only add non-empty files
                        ai_codes.append(content)
                        ghost_filenames.append(filename)
            except Exception:
                continue
    
    # Check if we have any AI codes to compare
    if not ai_codes:
        print("0.00")
        sys.exit(0)
    
    # 4. Create Vectors (The Magic Part)
    # We add the student code to the list of documents
    documents = [student_code] + ai_codes
    
    
    try:
        # TF-IDF converts code to numbers based on unique tokens
        # This captures the importance of rare keywords and patterns
        tfidf_vectorizer = TfidfVectorizer(
            lowercase=True,
            token_pattern=r'\b\w+\b',  # Match word tokens
            min_df=1,
            max_df=1.0
        )
        tfidf_matrix = tfidf_vectorizer.fit_transform(documents)
        
        # 5. Calculate Cosine Similarity
        # Compare Student (index 0) against all AI files (index 1 to end)
        cosine_similarities = cosine_similarity(
            tfidf_matrix[0:1], 
            tfidf_matrix[1:]
        ).flatten()
        
        # 6. Get the Highest Match Score and corresponding file
        max_score = max(cosine_similarities)
        max_index = cosine_similarities.argmax()
        matched_file = ghost_filenames[max_index]
        
        # Output format: score|filename
        # Laravel will parse this output
        print(f"{max_score:.4f}|{matched_file}")
        
    except Exception as e:
        # If any error occurs during vectorization, return 0
        print("0.00")
        sys.exit(1)

if __name__ == "__main__":
    main()
