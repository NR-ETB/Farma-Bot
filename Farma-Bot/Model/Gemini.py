import google.generativeai as genai

# Configura tu clave de API
# Asegúrate de reemplazar "TU_CLAVE_DE_API" con tu clave real obtenida de Google AI Studio
genai.configure(api_key="TU_CLAVE_DE_API")

# Selecciona el modelo Gemini Flash Lite
# El nombre exacto del modelo puede variar, verifica la documentación más reciente
model = genai.GenerativeModel('gemini-2.0-flash-lite')

# Envía una consulta
prompt = "Explica brevemente la importancia de la energía solar."
response = model.generate_content(prompt)

# Imprime la respuesta
print(response.text)