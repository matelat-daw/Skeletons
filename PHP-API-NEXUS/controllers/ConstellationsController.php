<?php
/**
 * ConstellationsController - Maneja las constelaciones
 */
require_once 'BaseController.php';

class ConstellationsController extends BaseController {
    private $constellationModel;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/Constellation.php';
        
        // Usar la base de datos nexus_stars para constelaciones
        $this->constellationModel = new Constellation($this->dbManager->getConnection('nexus_stars'));
    }
    
    /**
     * GET /api/Constellations
     * Obtiene todas las constelaciones
     */
    public function getAll($params = []) {
        try {
            $constellations = $this->constellationModel->getAll();
            
            // Devolver directamente el array como lo espera Angular
            echo json_encode($constellations);
            
        } catch (Exception $e) {
            error_log("Error en getAll constellations: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET /api/Constellations/{id}
     * Obtiene una constelación específica por ID
     */
    public function getById($params = []) {
        try {
            if (!isset($params['id']) || !is_numeric($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido y debe ser numérico", null, false);
                return;
            }
            
            $constellation = $this->constellationModel->getById($params['id']);
            
            if (!$constellation) {
                $this->sendResponse(404, "Constelación no encontrada", null, false);
                return;
            }
            
            // Devolver directamente el objeto como lo espera Angular
            header('Content-Type: application/json');
            echo json_encode($constellation);
            
        } catch (Exception $e) {
            error_log("Error en getById constellation: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * GET /api/Constellations/GetStars/{id}
     * Obtiene las estrellas de una constelación específica
     */
    public function getStars($params = []) {
        try {
            if (!isset($params['id']) || !is_numeric($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido y debe ser numérico", null, false);
                return;
            }
            
            $stars = $this->constellationModel->getStarsByConstellationId($params['id']);
            
            if ($stars === false) {
                $this->sendResponse(404, "Esa Constelación no Existe.", null, false);
                return;
            }
            
            // Devolver directamente el array como lo espera Angular
            header('Content-Type: application/json');
            echo json_encode($stars);
            
        } catch (Exception $e) {
            error_log("Error en getStars constellation: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * GET /api/Constellations/ConstelationLines
     * Obtiene las líneas de constelaciones desde archivo JSON
     */
    public function getConstellationLines($params = []) {
        try {
            $filePath = __DIR__ . '/../assets/constellationLines.json';
            
            if (!file_exists($filePath)) {
                $this->sendResponse(404, "No se encontró el archivo de líneas de constelaciones.", null, false);
                return;
            }
            
            $json = file_get_contents($filePath);
            $constellationLines = json_decode($json, true);
            
            if ($constellationLines === null) {
                $this->sendResponse(500, "Error al procesar el archivo de líneas de constelaciones.", null, false);
                return;
            }
            
            // Devolver directamente el contenido como lo espera Angular
            header('Content-Type: application/json');
            echo $json;
            
        } catch (Exception $e) {
            error_log("Error en getConstellationLines: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
