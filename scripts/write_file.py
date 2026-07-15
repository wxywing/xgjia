#!/usr/bin/env python3
import sys
import os

def write_file(filepath, content, platform='auto'):
    """Write file with platform-appropriate encoding"""
    # Determine encoding based on platform
    if platform == 'auto':
        platform = sys.platform
    
    # For macOS/Linux, use UTF-8 without BOM
    # For Windows, might need BOM for some file types
    encoding = 'utf-8'
    if platform == 'win32' and filepath.endswith(('.csv', '.txt')):
        encoding = 'utf-8-sig'
    
    # Ensure directory exists
    os.makedirs(os.path.dirname(os.path.abspath(filepath)), exist_ok=True)
    
    # Write file
    with open(filepath, 'w', encoding=encoding, newline='' if 'csv' in filepath else None) as f:
        f.write(content)
    
    print(f"File written: {filepath}")

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print("Usage: python3 write_file.py <filepath> <content> [platform]")
        sys.exit(1)
    
    filepath = sys.argv[1]
    content = sys.argv[2]
    platform = sys.argv[3] if len(sys.argv) > 3 else 'auto'
    
    write_file(filepath, content, platform)
