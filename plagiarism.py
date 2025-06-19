import sys
import json
import os
import requests

text = sys.argv[1]
sentences = [s.strip() for s in text.split('.') if s.strip()]

GOOGLE_API_KEY = "AIzaSyBZe2nxpQa57HmUMEQ-83ml7bAs09a6NBs"
CX = "c1ad8a992919b49ad"

def check_plagiarism(sentence):
    url = 'https://www.googleapis.com/customsearch/v1'
    params = {
        'key': GOOGLE_API_KEY,
        'cx': CX,
        'q': sentence
    }
    response = requests.get(url, params=params)
    data = response.json()

    # Nếu có kết quả trả về → nghi đạo văn
    if 'items' in data and len(data['items']) > 0:
        return 'Có thể đạo văn (80%-100%)'
    else:
        return 'Khó phát hiện (dưới 30%)'

results = []

for sentence in sentences:
    score = check_plagiarism(sentence)
    results.append({
        'sentence': sentence,
        'plagiarism': score
    })

print(json.dumps({'results': results}, ensure_ascii=False))
