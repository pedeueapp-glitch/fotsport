from fastapi import FastAPI, UploadFile, File, Form, HTTPException
import face_recognition
import numpy as np
import json
import os
import uuid
from PIL import Image
import threading
import logging

# Configuração de logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = FastAPI(title="Foco Radical Face Service")

DB_FILE = "encodings.json"
db_lock = threading.Lock()

def load_encodings():
    with db_lock:
        if os.path.exists(DB_FILE):
            try:
                with open(DB_FILE, 'r') as f:
                    return json.load(f)
            except Exception as e:
                logger.error(f"Error loading encodings: {e}")
                return {}
        return {}

def save_encodings(data):
    with db_lock:
        try:
            with open(DB_FILE, 'w') as f:
                json.dump(data, f)
        except Exception as e:
            logger.error(f"Error saving encodings: {e}")

def resize_image(image_path, max_size=1200):
    try:
        with Image.open(image_path) as img:
            img = img.convert("RGB")
            # Calcula proporção mantendo aspecto
            ratio = min(max_size / img.size[0], max_size / img.size[1])
            if ratio < 1:
                new_size = (int(img.size[0] * ratio), int(img.size[1] * ratio))
                img = img.resize(new_size, Image.Resampling.LANCZOS)
                img.save(image_path, "JPEG", quality=85)
            else:
                # Mesmo que não precise redimensionar, garantimos que seja um JPEG RGB
                img.save(image_path, "JPEG", quality=90)
    except Exception as e:
        logger.error(f"Error resizing: {e}")

@app.get("/")
def root():
    return {"message": "Fotsport Face Service is running!", "status": "ok"}

@app.post("/index_photo/")
def index_photo(photo_id: str = Form(...), event_id: str = Form(None), file: UploadFile = File(...)):
    temp_filename = f"temp_idx_{uuid.uuid4().hex}.jpg"
    try:
        logger.info(f"Indexing photo {photo_id} for event {event_id}")
        
        img_data = file.file.read()
        with open(temp_filename, "wb") as f:
            f.write(img_data)
            
        resize_image(temp_filename)
            
        image = face_recognition.load_image_file(temp_filename)
        face_encodings = face_recognition.face_encodings(image)
            
        if not face_encodings:
            logger.info(f"No faces found in photo {photo_id}")
            return {"status": "no_faces_found", "photo_id": photo_id}
            
        data = load_encodings()
        
        # Limpa event_id de possíveis strings "null" ou "undefined"
        clean_event_id = None
        if event_id and str(event_id).lower() not in ["none", "null", "undefined", ""]:
            clean_event_id = str(event_id)
            
        for i, encoding in enumerate(face_encodings):
            enc_id = f"{photo_id}_{i}"
            data[enc_id] = {
                "photo_id": photo_id,
                "event_id": clean_event_id,
                "encoding": encoding.tolist()
            }
            
        save_encodings(data)
        logger.info(f"Indexed {len(face_encodings)} faces for photo {photo_id}")
        
        # Converte encodings para lista para retorno
        encodings_list = [enc.tolist() for enc in face_encodings]
        
        return {
            "status": "success", 
            "faces_indexed": len(face_encodings), 
            "photo_id": photo_id,
            "encodings": encodings_list
        }
    except Exception as e:
        logger.error(f"Exception in index_photo: {e}")
        return {"status": "error", "message": str(e)}
    finally:
        if os.path.exists(temp_filename):
            os.remove(temp_filename)

@app.post("/get_face_encodings/")
def get_face_encodings(file: UploadFile = File(...)):
    """Apenas extrai as encodings de uma imagem sem salvar no banco local."""
    temp_filename = f"temp_ext_{uuid.uuid4().hex}.jpg"
    try:
        img_data = file.file.read()
        with open(temp_filename, "wb") as f:
            f.write(img_data)
            
        resize_image(temp_filename)
        image = face_recognition.load_image_file(temp_filename)
        face_encodings = face_recognition.face_encodings(image)
        
        return {
            "status": "success",
            "encodings": [enc.tolist() for enc in face_encodings]
        }
    except Exception as e:
        logger.error(f"Error in get_face_encodings: {e}")
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        if os.path.exists(temp_filename):
            os.remove(temp_filename)

@app.post("/compare_faces/")
def compare_faces(
    source_encoding: str = Form(...), # JSON string of a list
    candidates: str = Form(...)       # JSON string of a dict { "photo_id": [enc1, enc2] }
):
    """Compara uma encoding de busca contra uma lista de candidatos do banco."""
    try:
        source_enc = np.array(json.loads(source_encoding))
        candidates_data = json.loads(candidates)
        
        matches = []
        tolerance = 0.62 # Valor ajustado para esportes
        
        for photo_id, encodings in candidates_data.items():
            if not encodings:
                continue
                
            # Converter cada encoding do candidato para numpy
            candidate_encs = [np.array(enc) for enc in encodings]
            
            # Calcular distâncias
            distances = face_recognition.face_distance(candidate_encs, source_enc)
            
            min_dist = min(distances) if len(distances) > 0 else 1.0
            
            if min_dist <= tolerance:
                matches.append({
                    "photo_id": photo_id,
                    "distance": float(min_dist)
                })
        
        # Ordenar por menor distância
        matches.sort(key=lambda x: x["distance"])
        
        return {
            "status": "success",
            "matches": [m["photo_id"] for m in matches]
        }
    except Exception as e:
        logger.error(f"Error in compare_faces: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/search_face/")
def search_face(event_id: str = Form(None), file: UploadFile = File(...)):
    temp_filename = f"temp_search_{uuid.uuid4().hex}.jpg"
    try:
        logger.info(f"Searching face in event {event_id}")
        
        img_data = file.file.read()
        with open(temp_filename, "wb") as f:
            f.write(img_data)
            
        resize_image(temp_filename)
            
        image = face_recognition.load_image_file(temp_filename)
        search_encodings = face_recognition.face_encodings(image)
            
        if not search_encodings:
            logger.warning("No faces detected in the provided selfie.")
            raise HTTPException(status_code=400, detail="Nenhuma face detectada na selfie enviada.")
            
        search_encoding = search_encodings[0]
        data = load_encodings()
        
        known_encodings = []
        known_photo_ids = []
        
        target_event = None
        if event_id and str(event_id).lower() not in ["none", "null", "undefined", ""]:
            target_event = str(event_id)

        for key, val in data.items():
            # Filtro por evento
            if target_event:
                photo_event = str(val.get("event_id")) if val.get("event_id") else None
                if photo_event != target_event:
                    continue
            
            known_encodings.append(np.array(val["encoding"]))
            known_photo_ids.append(val["photo_id"])
            
        # Tenta buscar com filtro de evento primeiro
        matches_found = []
        if known_encodings:
            face_distances = face_recognition.face_distance(known_encodings, search_encoding)
            
            # Tolerância: 0.6 é o padrão, mas em esportes 0.62 costuma ser melhor devido a suor/esforço
            tolerance = 0.62
            
            for i, distance in enumerate(face_distances):
                if distance <= tolerance:
                    matches_found.append({
                        "photo_id": known_photo_ids[i],
                        "distance": float(distance)
                    })
        
        # Se não encontrou nada com o filtro de evento, pode ser que as fotos antigas estejam sem event_id
        # Vamos tentar buscar em TODAS as fotos que NÃO têm event_id como fallback
        if not matches_found and target_event:
            logger.info("No matches with event filter, trying fallback (encodings without event_id)")
            fallback_encodings = []
            fallback_photo_ids = []
            for key, val in data.items():
                if not val.get("event_id"): # Apenas fotos sem evento definido
                    fallback_encodings.append(np.array(val["encoding"]))
                    fallback_photo_ids.append(val["photo_id"])
            
            if fallback_encodings:
                f_distances = face_recognition.face_distance(fallback_encodings, search_encoding)
                for i, distance in enumerate(f_distances):
                    if distance <= tolerance:
                        matches_found.append({
                            "photo_id": fallback_photo_ids[i],
                            "distance": float(distance)
                        })
        
        # Ordena por menor distância (mais parecidos primeiro)
        matches_found.sort(key=lambda x: x["distance"])
        
        # Remove duplicatas mantendo a melhor distância
        unique_matches = []
        seen_ids = set()
        for m in matches_found:
            pid = m["photo_id"]
            if pid not in seen_ids:
                unique_matches.append(pid)
                seen_ids.add(pid)
                    
        logger.info(f"Search completed. Found {len(unique_matches)} matches.")
        return {"matches": unique_matches}
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Exception in search_face: {e}")
        raise HTTPException(status_code=500, detail=str(e))
    finally:
        if os.path.exists(temp_filename):
            os.remove(temp_filename)
