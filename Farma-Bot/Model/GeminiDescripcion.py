import sys
import os
import json
import io

# Intenta importar la biblioteca de Google Generative AI
try:
    import google.generativeai as genai
except ImportError:
    print("No se pudo importar la biblioteca google.generativeai.", file=sys.stderr)
    print("Asegúrate de instalarla con: pip install google-generativeai", file=sys.stderr)
    sys.exit(1)

print(">>> GeminiDescripcion.py se ha iniciado <<<")
print(f"Argumentos recibidos: {sys.argv}")

# Verificación de argumentos
if len(sys.argv) <= 1:
    print("Por favor, proporciona el nombre del medicamento como argumento.", file=sys.stderr)
    sys.exit(1)

medicamento = sys.argv[1]

try:
    # Configurar API
    api_key = "AIzaSyAWV2FQloJS26NB7AmOl4UK2imw6jFmpig"
    genai.configure(api_key=api_key)
    
    # El nombre correcto del modelo es "gemini-pro"
    modelo = "gemini-1.5-flash"
    print(f"Usando modelo: {modelo}")
    
    model = genai.GenerativeModel(modelo)
    prompt = f"Dame una breve descripción médica concisa (máximo 3 frases) sobre para qué se utiliza el medicamento llamado '{medicamento}'. Si no conoces este medicamento específico, proporciona información sobre la categoría de medicamentos a la que probablemente pertenece basado en su nombre."
    
    response = model.generate_content(prompt)
    print(response.text)
    
except Exception as e:
    print(f"Error al generar descripción: {e}", file=sys.stderr)
    
    # Como fallback, generamos una descripción genérica para que el sistema siga funcionando
    descripciones_fallback = {
        "amoxicilina": "Antibiótico de amplio espectro utilizado para tratar infecciones bacterianas. Pertenece a la familia de las penicilinas y combate infecciones del oído, garganta, vías respiratorias, piel y sistema urinario.",
        "paracetamol": "Analgésico y antipirético utilizado para aliviar dolores leves a moderados y reducir la fiebre. No tiene propiedades antiinflamatorias significativas.",
        "ibuprofeno": "Antiinflamatorio no esteroideo (AINE) que alivia dolor, reduce inflamación y baja la fiebre. Comúnmente usado para dolores de cabeza, musculares, artritis y menstruales.",
        "omeprazol": "Inhibidor de la bomba de protones que reduce la producción de ácido en el estómago. Trata la acidez, úlceras y el reflujo gastroesofágico.",
        "aspirina": "Analgésico, antipirético y antiinflamatorio que también tiene propiedades anticoagulantes. Utilizado para aliviar dolor, reducir fiebre e inflamación, y prevenir eventos cardiovasculares."
    }
    
    medicamento_lower = medicamento.lower()
    if medicamento_lower in descripciones_fallback:
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', line_buffering=True)
        print(descripciones_fallback[medicamento_lower])
    else:
        print(f"El medicamento {medicamento} se utiliza para tratar determinadas condiciones médicas. Consulte con un profesional de la salud para información específica sobre este medicamento.")

print(">>> GeminiDescripcion.py finalizado <<<")