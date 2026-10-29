import os
import sys
import joblib
import re
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
import warnings
from sklearn.exceptions import InconsistentVersionWarning

# hilangkan warning sklearn versi berbeda
warnings.filterwarnings("ignore", category=InconsistentVersionWarning)

# --- path absolut folder ini (input) ---
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# --- load model dari folder yang sama ---
MODEL_PATH = os.path.join(BASE_DIR, 'model_prioritas.pkl')
VECTORIZER_PATH = os.path.join(BASE_DIR, 'vectorizer_tfidf.pkl')

model = joblib.load(MODEL_PATH)
vectorizer = joblib.load(VECTORIZER_PATH)
stemmer = StemmerFactory().create_stemmer()

def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^a-z\s]', '', text)
    tokens = text.split()
    stemmed = [stemmer.stem(t) for t in tokens]
    return ' '.join(stemmed)

if __name__ == '__main__':
    if len(sys.argv) > 1:
        text = sys.argv[1]
        cleaned = clean_text(text)
        vec = vectorizer.transform([cleaned])
        pred = model.predict(vec)[0]
        print(pred)
    else:
        print("Non Prioritas")  # fallback jika argumen kosong
