<?php
$producto = "";
$resultados = [];
$mensaje = "";
$sugerencias = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto = $_POST['nombre_producto'];
}

$conn = new mysqli("localhost", "root", "", "farma");

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Función para obtener la descripción del medicamento usando Gemini
function obtenerDescripcion($nombre_medicamento) {
    $ruta_python = 'C:\Python\python.exe'; // Ajusta esta ruta según tu instalación de Python
    $ruta_script = __DIR__ . '/Model/GeminiDescripcion.py';
    
    // Verificar que el script existe
    if (!file_exists($ruta_script)) {
        return "Error: El script GeminiDescripcion.py no se encuentra en la ruta especificada.";
    }
    
    // Asegurarse de que el nombre del medicamento no contenga caracteres problemáticos
    $nombre_medicamento_seguro = escapeshellarg(trim($nombre_medicamento));
    
    // Ejecutar el comando y capturar la salida completa
    $comando = "$ruta_python \"$ruta_script\" $nombre_medicamento_seguro 2>&1";
    $salida_completa = shell_exec($comando);
    
    // Para depuración: registrar la salida en un archivo log
    $log_file = __DIR__ . '/gemini_log.txt';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Medicamento: $nombre_medicamento\n", FILE_APPEND);
    file_put_contents($log_file, "Comando: $comando\n", FILE_APPEND);
    file_put_contents($log_file, "Salida completa:\n$salida_completa\n\n", FILE_APPEND);
    
    // Procesar la salida: eliminar las líneas de inicio y fin
    $lineas = explode("\n", $salida_completa);
    $descripcion = [];
    
    foreach ($lineas as $linea) {
        // Ignorar líneas específicas de inicio o fin
        if (strpos($linea, '>>> GeminiDescripcion.py se ha iniciado <<<') !== false ||
            strpos($linea, 'Argumentos recibidos:') !== false ||
            strpos($linea, '>>> GeminiDescripcion.py finalizado <<<') !== false ||
            strpos($linea, 'Usando modelo:') !== false) {
            continue;
        }
        
        // Capturar líneas que no son mensajes de error
        if (strpos($linea, 'Error:') === false && 
            strpos($linea, 'error:') === false && 
            trim($linea) !== '') {
            $descripcion[] = $linea;
        }
    }
    
    // Si no hay descripción, proporcionar un mensaje genérico
    if (empty($descripcion)) {
        return "No se pudo obtener la descripción del medicamento. Consulte con un profesional de la salud.";
    }
    
    return implode("\n", $descripcion);
}

// Función para buscar productos alternativos basados en ingredientes similares
function buscarAlternativasPorIngredientes($conn, $tablas, $ingrediente_principal, $producto_original) {
    $alternativas = [];
    $ingredientes_palabras = explode(" ", strtolower($ingrediente_principal));
    
    // Filtrar palabras muy cortas (preposiciones, artículos, etc.)
    $palabras_filtradas = [];
    foreach ($ingredientes_palabras as $palabra) {
        if (strlen($palabra) > 3) {
            $palabras_filtradas[] = $palabra;
        }
    }
    
    // Si no hay palabras significativas, usar el ingrediente completo
    if (empty($palabras_filtradas)) {
        $palabras_filtradas = $ingredientes_palabras;
    }
    
    foreach ($tablas as $tabla) {
        $prefijo = substr($tabla, 0, 3);
        
        // Para cada palabra relevante del ingrediente principal, buscar productos similares
        foreach ($palabras_filtradas as $palabra) {
            $sql = "SELECT * FROM $tabla WHERE ing_$prefijo LIKE ? AND nom_$prefijo != ? AND cant_$prefijo > 0 LIMIT 5";
            $stmt = $conn->prepare($sql);
            $param = "%" . $palabra . "%";
            $stmt->bind_param("ss", $param, $producto_original);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Revisar si ya tenemos este producto para evitar duplicados
                $producto_existe = false;
                foreach ($alternativas as $alt) {
                    if ($alt['nombre'] === $row['nom_' . $prefijo]) {
                        $producto_existe = true;
                        break;
                    }
                }
                
                if (!$producto_existe) {
                    // Calcular similitud basada en palabras clave comunes
                    $similitud = calcularSimilitud($ingrediente_principal, $row['ing_' . $prefijo]);
                    
                    $alternativas[] = [
                        'nombre' => $row['nom_' . $prefijo],
                        'stock' => $row['cant_' . $prefijo],
                        'ingredientes' => $row['ing_' . $prefijo],
                        'similitud' => $similitud,
                        'descripcion' => obtenerDescripcion($row['nom_' . $prefijo])
                    ];
                }
            }
        }
    }
    
    // Ordenar por similitud (mayor a menor)
    usort($alternativas, function($a, $b) {
        return $b['similitud'] <=> $a['similitud'];
    });
    
    // Limitar a máximo 5 sugerencias
    return array_slice($alternativas, 0, 5);
}

// Función para calcular la similitud entre dos strings de ingredientes
function calcularSimilitud($ingrediente1, $ingrediente2) {
    $ing1 = explode(" ", strtolower($ingrediente1));
    $ing2 = explode(" ", strtolower($ingrediente2));
    
    // Filtrar palabras cortas
    $ing1 = array_filter($ing1, function($palabra) { return strlen($palabra) > 3; });
    $ing2 = array_filter($ing2, function($palabra) { return strlen($palabra) > 3; });
    
    // Contar palabras comunes
    $comunes = array_intersect($ing1, $ing2);
    $total_palabras = count(array_unique(array_merge($ing1, $ing2)));
    
    // Si no hay palabras significativas, retornar 0
    if ($total_palabras == 0) return 0;
    
    // Calcular coeficiente de similitud (Jaccard)
    return count($comunes) / $total_palabras;
}

$tablas = ['Infecciones', 'Oncologicas', 'Endocrinologicas'];
$encontrado = false;
$producto_encontrado = [];
$ingrediente_activo = "";
$stock_producto = 0;

// Paso 1: Buscar el producto en las tablas
if (!empty($producto)) {
    foreach ($tablas as $tabla) {
        $prefijo = substr($tabla, 0, 3);
        $sql = "SELECT * FROM $tabla WHERE nom_$prefijo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $producto);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Producto encontrado
            $encontrado = true;
            $stock_producto = $row['cant_' . $prefijo];
            
            // Extraer el ingrediente activo para buscar alternativas si es necesario
            $ingrediente_activo = $row['ing_' . $prefijo];
            
            // Obtener descripción del medicamento principal
            $descripcion = obtenerDescripcion($row['nom_' . $prefijo]);
            
            // Inicializar la variable para la sede
            $nombre_sede = 'Sede no asignada';

            // Consultar en cada vista de inventario
            $vistas = ['Inventario_Sede_Central', 'Inventario_Sede_Norte', 'Inventario_Sede_Sur'];
            foreach ($vistas as $vista) {
                $sql_sede = "SELECT s.nom_Sed
                             FROM $vista i
                             JOIN Sedes s ON s.nom_Sed = i.sede
                             WHERE i.id_Medicamento = ? AND i.cantidad > 0";
                $stmt_sede = $conn->prepare($sql_sede);
                $stmt_sede->bind_param("i", $row['id_' . $prefijo]);
                $stmt_sede->execute();
                $result_sede = $stmt_sede->get_result();

                if ($sede = $result_sede->fetch_assoc()) {
                    $nombre_sede = $sede['nom_Sed'];
                    break; // Salir del bucle si se encuentra la sede
                }
            }
            
            // Agregar a resultados siempre, independientemente del stock
            $resultados[] = [
                'nombre' => $row['nom_' . $prefijo],
                'stock' => $stock_producto,
                'ingredientes' => $ingrediente_activo,
                'descripcion' => $descripcion,
                'sede' => $nombre_sede
            ];
            
            // Si no tiene stock, buscar sugerencias por ingredientes similares
            if ($stock_producto <= 0) {
                $sugerencias = buscarAlternativasPorIngredientes($conn, $tablas, $ingrediente_activo, $producto);
                $mensaje = "El producto no tiene stock. Se muestran alternativas con ingredientes similares.";
            } else {
                $mensaje = "Producto encontrado con stock disponible.";
            }
            
            break; // No es necesario buscar más si ya se encontró
        }
    }
    
    // Si no se encontró el producto en ninguna tabla
    if (!$encontrado) {
        $mensaje = "Producto no encontrado en la base de datos.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="View/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="View/css/style.css">
    <title>Medisync Consulta</title>
</head>
<body>

    <div class="main-container">
        <div class="container-bot">
            <header>
                <h1>Medisync</h1>
                <span class="full">💊 Tu Salud Sin Esperas💊</span>
                <span class="medium"> 🤖 Rápido, fácil y siempre disponible 🤖</span>
            </header>

            <main class="main">
                <h2>Producto</h2>
                <form method="POST" action="">
                    <div class="product">
                        <input type="text" name="nombre_producto" placeholder="Ingresa el producto a buscar" required value="<?= htmlspecialchars($producto) ?>">
                        <button type="submit" class="btn">BUSCAR</button>
                    </div>

                    <div class="result">
                        <h2>Resultado</h2>
                        <?php if (!empty($mensaje)) : ?>
                            <div class="alert <?= $encontrado ? ($stock_producto > 0 ? 'alert-success' : 'alert-warning') : 'alert-danger' ?>">
                                <?= htmlspecialchars($mensaje) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($resultados)) : ?>
                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Stock</th>
                                        <th>Ingredientes Activos</th>
                                        <th>Sede</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados as $item) : ?>
                                        <tr class="<?= $item['stock'] <= 0 ? 'table-danger' : '' ?>">
                                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                                            <td><?= htmlspecialchars($item['stock']) ?></td>
                                            <td><?= htmlspecialchars($item['ingredientes']) ?></td>
                                            <td><?= htmlspecialchars($item['sede']) ?></td>
                                            <td>
                                                <div class="descripcion-medicamento">
                                                    <?= nl2br(htmlspecialchars($item['descripcion'])) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        
                        <?php if (!empty($sugerencias)) : ?>
                            <h2 class="mt-4">Sugerencias de Reemplazo</h3>
                            <!--<p>Basadas en ingredientes similares a "<?= htmlspecialchars($ingrediente_activo) ?>"</p>-->
                            
                            <table class="table table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Stock</th>
                                        <th>Ingredientes Activos</th>
                                        <th>Sede</th>
                                        <th>Similitud</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sugerencias as $sugerencia) : ?>
                                        <?php 
                                            // Determinar clase de similitud
                                            $clase_similitud = '';
                                            $texto_similitud = '';
                                            
                                            if ($sugerencia['similitud'] >= 0.65) {
                                                $clase_similitud = 'similitud-alta';
                                                $texto_similitud = 'Alta';
                                            } elseif ($sugerencia['similitud'] >= 0.3) {
                                                $clase_similitud = 'similitud-media';
                                                $texto_similitud = 'Media';
                                            } else {
                                                $clase_similitud = 'similitud-baja';
                                                $texto_similitud = 'Baja';
                                            }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sugerencia['nombre']) ?></td>
                                            <td><?= htmlspecialchars($sugerencia['stock']) ?></td>
                                            <td><?= htmlspecialchars($sugerencia['ingredientes']) ?></td>
                                            <td><?= htmlspecialchars($item['sede']) ?></td>
                                            <td>
                                                <span class="similitud-badge <?= $clase_similitud ?>">
                                                    <?= $texto_similitud ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="descripcion-medicamento">
                                                    <?= nl2br(htmlspecialchars($sugerencia['descripcion'])) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </form>
            </main>
        </div>
        <div class="backdrop"></div>
    </div>

<script src="View/bootstrap/bootstrap.bundle.min.js"></script>
<script src="View/bootstrap/jquery.js"></script>
<script src="View/bootstrap/typed.js"></script>
<script src="Controller/main.js"></script>
</body>
</html>