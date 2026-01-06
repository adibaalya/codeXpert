#!/usr/bin/env python3
"""
AI Plagiarism Detection using CodeBERT (Microsoft's Pre-trained Model)
This script compares student code against known AI-generated solutions
using semantic embeddings from CodeBERT for deeper understanding.
"""

import sys
import os
import torch
import numpy as np
from transformers import AutoTokenizer, AutoModel
from sklearn.metrics.pairwise import cosine_similarity

# Global variables for model caching
tokenizer = None
model = None

def load_codebert_model():
    """Load CodeBERT model and tokenizer (cached after first load)"""
    global tokenizer, model
    
    if tokenizer is None or model is None:
        try:
            # Load Microsoft's pre-trained CodeBERT model
            model_name = "microsoft/codebert-base"
            tokenizer = AutoTokenizer.from_pretrained(model_name)
            model = AutoModel.from_pretrained(model_name)
            model.eval()  # Set to evaluation mode
            
            # Use GPU if available (much faster)
            if torch.cuda.is_available():
                model = model.cuda()
        except Exception as e:
            print(f"Error loading CodeBERT model: {e}", file=sys.stderr)
            return False
    
    return True

def get_code_embedding(code_text):
    """
    Convert code to semantic embedding vector using CodeBERT
    Returns a 768-dimensional vector representing the code's meaning
    """
    try:
        # Tokenize code (max length 512 tokens)
        inputs = tokenizer(
            code_text,
            return_tensors="pt",
            max_length=512,
            truncation=True,
            padding=True
        )
        
        # Move to GPU if available
        if torch.cuda.is_available():
            inputs = {k: v.cuda() for k, v in inputs.items()}
        
        # Get embeddings from CodeBERT
        with torch.no_grad():
            outputs = model(**inputs)
            # Use [CLS] token embedding as code representation
            embedding = outputs.last_hidden_state[:, 0, :].cpu().numpy()
        
        return embedding
    
    except Exception as e:
        print(f"Error generating embedding: {e}", file=sys.stderr)
        return None

def main():
    # Check if correct number of arguments provided
    if len(sys.argv) != 3:
        print("0.00")
        sys.exit(1)
    
    # 1. Read file paths from arguments
    student_file = sys.argv[1]
    ai_directory = sys.argv[2]  # Folder containing AI ghost solutions
    
    # 2. Load CodeBERT model
    if not load_codebert_model():
        print("0.00|model_error")
        sys.exit(1)
    
    # 3. Load Student Code
    try:
        with open(student_file, 'r', encoding='utf-8', errors='ignore') as f:
            student_code = f.read()
    except FileNotFoundError:
        print("0.00|file_not_found")
        sys.exit(1)
    
    # Check if student code is empty
    if not student_code.strip():
        print("0.00|empty_code")
        sys.exit(0)
    
    # 4. Get student code embedding
    student_embedding = get_code_embedding(student_code)
    if student_embedding is None:
        print("0.00|embedding_error")
        sys.exit(1)
    
    # 5. Load All AI Ghost Files and compute embeddings
    ai_embeddings = []
    ghost_filenames = []
    
    if not os.path.isdir(ai_directory):
        print("0.00|no_ghost_dir")
        sys.exit(0)
    
    for filename in os.listdir(ai_directory):
        path = os.path.join(ai_directory, filename)
        if os.path.isfile(path) and not filename.startswith('.'):
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    if content.strip():  # Only add non-empty files
                        embedding = get_code_embedding(content)
                        if embedding is not None:
                            ai_embeddings.append(embedding)
                            ghost_filenames.append(filename)
            except Exception:
                continue
    
    # Check if we have any AI codes to compare
    if not ai_embeddings:
        print("0.00|no_ghost_files")
        sys.exit(0)
    
    # 6. Calculate Semantic Similarity using Cosine Similarity
    # CodeBERT captures semantic meaning, not just token patterns
    ai_embeddings_matrix = np.vstack(ai_embeddings)
    
    similarities = cosine_similarity(
        student_embedding,
        ai_embeddings_matrix
    ).flatten()
    
    # 7. Get the Highest Match Score and corresponding file
    max_score = float(np.max(similarities))
    max_index = int(np.argmax(similarities))
    matched_file = ghost_filenames[max_index]
    
    # Output format: score|filename
    # Laravel will parse this output
    print(f"{max_score:.4f}|{matched_file}")

if __name__ == "__main__":
    main()
