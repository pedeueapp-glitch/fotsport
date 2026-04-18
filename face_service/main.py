from fastapi import FastAPI, UploadFile, File, Form, HTTPException
import face_recognition
import numpy as np
import json
import os
import uuid
from PIL import Image

app = FastAPI(title="Foco Radical Face Service")

DB_FILE = "encodings.json"

def load_encodings():
    if os.path.exists(DB_FILE):
        with open(DB_FILE, 'r') as f:
            return json.load(f)
    return {}

def save_encodings(data):
    with open(DB_FILE, 'w') as f:
        json.dump(data, f)

def resize_image(image_path, max_size=1024):
    try:
        with Image.open(image_path) as img:
            img = img.convert("RGB")
            # Calcula proporção mantendo aspecto
            ratio = min(max_size / img.size[0], max_size / img.size[1])
            if ratio < 1:
                new_size = (int(img.size[0] * ratio), int(img.size[1] * ratio))
                img = img.resize(new_size, Image.Resampling.LANCZOS)
                img.save(image_path, "JPEG")
    except Exception as e:
        print(f"Error resizing: {e}")

@app.get("/")
def root():
    return {"message": "Foco Radical Face Service is running!", "status": "ok"}

@app.post("/index_photo/")
def index_photo(photo_id: str = Form(...), event_id: str = Form(None), file: UploadFile = File(...)):
    img_data = file.file.read()
    temp_filename = f"temp_{uuid.uuid4().hex}.jpg"
    with open(temp_filename, "wb") as f:
        f.write(img_data)
        
    resize_image(temp_filename)
        
    try:
        image = face_recognition.load_image_file(temp_filename)
        face_encodings = face_recognition.face_encodings(image)
    finally:
        if os.path.exists(temp_filename):
            os.remove(temp_filename)
    
    if not face_encodings:
        return {"status": "no_faces_found", "photo_id": photo_id}
        
    data = load_encodings()
    
    for i, encoding in enumerate(face_encodings):
        enc_id = f"{photo_id}_{i}"
        data[enc_id] = {
            "photo_id": photo_id,
            "event_id": str(event_id) if event_id else None,
            "encoding": encoding.tolist()
        }
        
    save_encodings(data)
    return {"status": "success", "faces_indexed": len(face_encodings), "photo_id": photo_id}

@app.post("/search_face/")
def search_face(event_id: str = Form(None), file: UploadFile = File(...)):
    img_data = file.file.read()
    temp_filename = f"temp_{uuid.uuid4().hex}.jpg"
    with open(temp_filename, "wb") as f:
        f.write(img_data)
        
    resize_image(temp_filename)
        
    try:
        image = face_recognition.load_image_file(temp_filename)
        search_encodings = face_recognition.face_encodings(image)
    finally:
        if os.path.exists(temp_filename):
            os.remove(temp_filename)
            
    if not search_encodings:
        raise HTTPException(status_code=400, detail="No faces found in selfie")
        
    search_encoding = search_encodings[0]
    
    data = load_encodings()
    matches = []
    
    known_encodings = []
    known_photo_ids = []
    
    for key, val in data.items():
        if event_id and str(val.get("event_id")) != str(event_id):
            continue
        known_encodings.append(np.array(val["encoding"]))
        known_photo_ids.append(val["photo_id"])
        
    if known_encodings:
        face_distances = face_recognition.face_distance(known_encodings, search_encoding)
        matches_with_dist = []
        for i, distance in enumerate(face_distances):
            if distance <= 0.55:
                matches_with_dist.append((known_photo_ids[i], distance))
        
        # Ordena por distância (menor distância = mais parecido)
        matches_with_dist.sort(key=lambda x: x[1])
        matches = [m[0] for m in matches_with_dist]
                
    # Remove duplicatas mantendo a ordem (set remove a ordem, então usamos OrderedDict ou loop)
    seen = set()
    unique_matches = []
    for m in matches:
        if m not in seen:
            unique_matches.append(m)
            seen.add(m)

    return {"matches": unique_matches}
