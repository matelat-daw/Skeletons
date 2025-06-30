<?php
/**
 * StarsController - Maneja las estrellas
 */
require_once 'BaseController.php';

class StarsController extends BaseController {
    private $starModel;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/Star.php';
        
        // Usar la base de datos nexus_stars para estrellas
        $this->starModel = new Star($this->dbManager->getConnection('nexus_stars'));
    }
    
    /**
     * GET /api/Stars
     * Obtiene todas las estrellas
     */
    public function getAll($params = []) {
        try {
            $stars = $this->starModel->getAll();
            
            // Devolver directamente el array como lo espera Angular
            header('Content-Type: application/json');
            echo json_encode($stars);
            
        } catch (Exception $e) {
            error_log("Error en getAll stars: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET /api/Stars/{id}
     * Obtiene una estrella específica por ID
     */
    public function getById($params = []) {
        try {
            if (!isset($params['id']) || !is_numeric($params['id'])) {
                $this->sendResponse(400, "ID de estrella requerido y debe ser numérico", null, false);
                return;
            }
            
            $star = $this->starModel->getById($params['id']);
            
            if (!$star) {
                $this->sendResponse(404, "Estrella no encontrada", null, false);
                return;
            }
            
            // Devolver directamente el objeto como lo espera Angular
            header('Content-Type: application/json');
            echo json_encode($star);
            
        } catch (Exception $e) {
            error_log("Error en getById star: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
