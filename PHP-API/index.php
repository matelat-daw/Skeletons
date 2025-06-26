<?php
// Headers para CORS y tipo de contenido
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluir archivos necesarios
include_once 'config/database.php';
include_once 'models/Client.php';

// Instanciar la base de datos y el modelo
$database = new Database();
$db = $database->getConnection();
$client = new Client($db);

// Obtener el método HTTP y la URI
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

// Función para enviar respuesta JSON
function sendResponse($status_code, $data = null, $message = null) {
    http_response_code($status_code);
    $response = array();
    
    if ($message) {
        $response['message'] = $message;
    }
    
    if ($data) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// Routing simple
switch($method) {
    case 'GET':
        if ($path == 'clients' || $path == '') {
            // GET /clients - Obtener todos los clientes
            if (isset($_GET['search'])) {
                // Búsqueda
                $stmt = $client->search($_GET['search']);
            } else {
                // Todos los clientes
                $stmt = $client->read();
            }
            
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $clients_arr = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $client_item = array(
                        "id" => $id,
                        "name" => $name,
                        "email" => $email,
                        "phone" => $phone,
                        "address" => $address,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($clients_arr, $client_item);
                }
                
                sendResponse(200, $clients_arr, "Clientes encontrados.");
            } else {
                sendResponse(404, null, "No se encontraron clientes.");
            }
        } 
        elseif (preg_match('/^clients\/(\d+)$/', $path, $matches)) {
            // GET /clients/{id} - Obtener un cliente específico
            $client->id = $matches[1];
            
            if($client->readOne()) {
                $client_arr = array(
                    "id" => $client->id,
                    "name" => $client->name,
                    "email" => $client->email,
                    "phone" => $client->phone,
                    "address" => $client->address,
                    "created_at" => $client->created_at,
                    "updated_at" => $client->updated_at
                );
                
                sendResponse(200, $client_arr, "Cliente encontrado.");
            } else {
                sendResponse(404, null, "Cliente no encontrado.");
            }
        }
        else {
            sendResponse(404, null, "Endpoint no encontrado.");
        }
        break;
        
    case 'POST':
        if ($path == 'clients' || $path == '') {
            // POST /clients - Crear nuevo cliente
            $data = json_decode(file_get_contents("php://input"));
            
            if(!empty($data->name) && !empty($data->email)) {
                $client->name = $data->name;
                $client->email = $data->email;
                $client->phone = isset($data->phone) ? $data->phone : '';
                $client->address = isset($data->address) ? $data->address : '';
                
                if($client->create()) {
                    $client_arr = array(
                        "id" => $client->id,
                        "name" => $client->name,
                        "email" => $client->email,
                        "phone" => $client->phone,
                        "address" => $client->address
                    );
                    
                    sendResponse(201, $client_arr, "Cliente creado exitosamente.");
                } else {
                    sendResponse(503, null, "No se pudo crear el cliente.");
                }
            } else {
                sendResponse(400, null, "Datos incompletos. Nombre y email son requeridos.");
            }
        } else {
            sendResponse(404, null, "Endpoint no encontrado.");
        }
        break;
        
    case 'PUT':
        if (preg_match('/^clients\/(\d+)$/', $path, $matches)) {
            // PUT /clients/{id} - Actualizar cliente
            $client->id = $matches[1];
            $data = json_decode(file_get_contents("php://input"));
            
            if(!empty($data->name) && !empty($data->email)) {
                $client->name = $data->name;
                $client->email = $data->email;
                $client->phone = isset($data->phone) ? $data->phone : '';
                $client->address = isset($data->address) ? $data->address : '';
                
                if($client->update()) {
                    sendResponse(200, null, "Cliente actualizado exitosamente.");
                } else {
                    sendResponse(503, null, "No se pudo actualizar el cliente.");
                }
            } else {
                sendResponse(400, null, "Datos incompletos. Nombre y email son requeridos.");
            }
        } else {
            sendResponse(404, null, "Endpoint no encontrado.");
        }
        break;
        
    case 'DELETE':
        if (preg_match('/^clients\/(\d+)$/', $path, $matches)) {
            // DELETE /clients/{id} - Eliminar cliente
            $client->id = $matches[1];
            
            if($client->delete()) {
                sendResponse(200, null, "Cliente eliminado exitosamente.");
            } else {
                sendResponse(503, null, "No se pudo eliminar el cliente.");
            }
        } else {
            sendResponse(404, null, "Endpoint no encontrado.");
        }
        break;
        
    default:
        sendResponse(405, null, "Método no permitido.");
        break;
}
?>
