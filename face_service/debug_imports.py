import face_recognition_models
print("face_recognition_models imported from:", face_recognition_models.__file__)
try:
    import face_recognition
    print("face_recognition imported successfully")
except Exception as e:
    print("Error importing face_recognition:", e)
