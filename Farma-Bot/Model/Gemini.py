import mysql.connector
import google.generativeai as genai

# Conexión a la base de datos
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="tu_contraseña",
    database="farma"
)

cursor = conn.cursor()

# Configura tu clave de API
genai.configure(api_key="AIzaSyAWV2FQloJS26NB7AmOl4UK2imw6jFmpig")

# Función para obtener información sobre un medicamento
def obtener_informacion_medicamento(nombre_medicamento):
    prompt = f"Proporciona información detallada sobre el medicamento {nombre_medicamento}, incluyendo su composición activa, indicaciones terapéuticas y posibles interacciones."
    response = genai.GenerativeModel("gemini-2.0-flash-lite").generate_content(prompt)
    return response.text

# Consulta para obtener medicamentos con stock bajo
cursor.execute("""
    SELECT nom_Inf, ing_Inf FROM Infecciones WHERE cant_Inf < 10
""")
medicamentos_agotados = cursor.fetchall()

# Procesar cada medicamento agotado
for medicamento in medicamentos_agotados:
    nombre = medicamento[0]
    composicion = medicamento[1]
    print(f"Medicamento agotado: {nombre} - Composición: {composicion}")

    # Buscar alternativas con composiciones similares
    cursor.execute("""
        SELECT nom_Inf FROM Infecciones WHERE ing_Inf LIKE %s
    """, ('%' + composicion.split()[0] + '%',))
    alternativas = cursor.fetchall()

    # Mostrar alternativas encontradas
    if alternativas:
        print("Alternativas encontradas:")
        for alternativa in alternativas:
            print(f"- {alternativa[0]}")
            # Obtener información adicional sobre la alternativa
            info = obtener_informacion_medicamento(alternativa[0])
            print(f"Información: {info}")
    else:
        print("No se encontraron alternativas con composiciones similares.")

    print("-" * 50)

# Cerrar la conexión
cursor.close()
conn.close()
