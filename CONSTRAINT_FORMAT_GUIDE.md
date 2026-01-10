# Constraint Formatting Guide

## Overview
This guide explains how to format constraints for coding questions to ensure they display properly with bold labels and dashes.

## Format Structure

Constraints should be stored as a JSON array where each item can be:

1. **A section with a bold label** - Use `<strong>Label:</strong>` followed by the content
2. **Multiple lines with dashes** - Each sub-item should be on a new line starting with `-`

## Example Format

```json
[
  "<strong>Input parameters:</strong>\n- 'inventory': A JavaScript object where keys are product IDs and values are objects\n- 'productID': A string representing the ID of the product to search for",
  "<strong>Output:</strong>\n- If the 'productID' exists in the inventory, return the product name as a string\n- If the 'productID' does not exist, return 'Product not found'",
  "<strong>Rules:</strong>\n- The function must use object property access methods\n- The function should not modify the original inventory object\n- Handle case sensitivity appropriately",
  "<strong>Edge cases:</strong>\n- Ensure the 'productName' is returned even if the inventory object is large\n- Handle scenarios where the inventory might be empty\n- Consider what happens if productID is null or undefined"
]
```

## Display Result

The above format will display as:

**Input parameters:**
- 'inventory': A JavaScript object where keys are product IDs and values are objects
- 'productID': A string representing the ID of the product to search for

**Output:**
- If the 'productID' exists in the inventory, return the product name as a string
- If the 'productID' does not exist, return 'Product not found'

**Rules:**
- The function must use object property access methods
- The function should not modify the original inventory object
- Handle case sensitivity appropriately

**Edge cases:**
- Ensure the 'productName' is returned even if the inventory object is large
- Handle scenarios where the inventory might be empty
- Consider what happens if productID is null or undefined

## Section Labels

Common section labels to use:
- `<strong>Input parameters:</strong>`
- `<strong>Output:</strong>`
- `<strong>Rules:</strong>`
- `<strong>Edge cases:</strong>`
- `<strong>Constraints:</strong>`
- `<strong>Requirements:</strong>`
- `<strong>Note:</strong>`
- `<strong>Examples:</strong>`

## Tips

1. Always use `<strong>` tags for section labels
2. Use newlines (`\n`) to separate items
3. Start each sub-item with a dash (`-`)
4. Keep the colon (`:`) after the section label
5. Ensure proper JSON encoding when storing in database

## Reformatting Existing Constraints

If you have existing constraints that need reformatting, use the Artisan command:

```bash
# Preview changes without applying them
php artisan constraints:reformat --dry-run

# Apply the changes
php artisan constraints:reformat
```

This command will automatically:
- Detect section headers
- Add `<strong>` tags to labels
- Ensure sub-items start with dashes
- Preserve existing formatting where appropriate
