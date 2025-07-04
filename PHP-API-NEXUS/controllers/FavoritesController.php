<?php
/**
 * FavoritesController - Maneja operaciones de favoritos
 */
require_once 'BaseController.php';

class FavoritesController extends BaseController {
    private $favoritesRepository;
    private $constellation;
    
    public function __construct() {
        parent::__construct();
        require_once 'repositories/FavoritesRepository.php';
        require_once 'models/Constellation.php';
        
        // Usar NexusUsers para favoritos y nexus_stars para constelaciones
        $this->favoritesRepository = new FavoritesRepository($this->dbManager->getConnection('NexusUsers'));
        $this->constellation = new Constellation($this->dbManager->getConnection('nexus_stars'));
    }
    
    /**
     * GET /api/Account/Favorites
     * Obtiene todos los favoritos del usuario
     */
    public function getUserFavorites($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Obtener favoritos
            $userFavorites = $this->favoritesRepository->getUserFavorites($userId);
            $stats = $this->favoritesRepository->getUserFavoritesStats($userId);
            
            $this->sendResponse(200, "Favoritos obtenidos exitosamente", [
                'favorites' => $userFavorites,
                'stats' => $stats
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en getUserFavorites: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET /api/Account/Favorites/{id}
     * Verifica si una constelación específica es favorita
     */
    public function checkFavorite($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Validar ID de constelación
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
            }
            
            $constellationId = $this->validateId($params['id']);
            
            // Verificar si es favorito
            $isFavorite = $this->favoritesRepository->isFavorite($userId, $constellationId);
            
            $this->sendResponse(200, $isFavorite ? "Es favorito" : "No es favorito", [
                'isFavorite' => $isFavorite,
                'constellationId' => $constellationId
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en checkFavorite: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * POST /api/Account/Favorites/{id}
     * Agrega una constelación a favoritos
     */
    public function addFavorite($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Validar ID de constelación
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
            }
            
            $constellationId = $this->validateId($params['id']);
            
            // Verificar que la constelación existe
            $constellationData = $this->constellation->getById($constellationId);
            if (!$constellationData) {
                $this->sendResponse(404, "Constelación no encontrada", null, false);
            }
            
            // Verificar si ya es favorito
            if ($this->favoritesRepository->isFavorite($userId, $constellationId)) {
                $this->sendResponse(409, "Esta constelación ya está en tus favoritos", null, false);
            }
            
            // Agregar a favoritos
            $newFavoriteId = $this->favoritesRepository->addFavorite($userId, $constellationId);
            if ($newFavoriteId) {
                $this->sendResponse(200, "Constelación agregada a favoritos", [
                    'id' => $newFavoriteId,
                    'userId' => $userId,
                    'constellationId' => $constellationId,
                    'constellationName' => $constellationData['english_name'] ?? 'Desconocida'
                ], true);
            } else {
                $this->sendResponse(500, "Error al agregar a favoritos", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en addFavorite: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * DELETE /api/Account/Favorites/{id}
     * Elimina una constelación de favoritos
     */
    public function removeFavorite($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Validar ID de constelación
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
            }
            
            $constellationId = $this->validateId($params['id']);
            
            // Verificar si es favorito antes de eliminar
            if (!$this->favoritesRepository->isFavorite($userId, $constellationId)) {
                $this->sendResponse(404, "Favorito no encontrado", null, false);
            }
            
            // Eliminar de favoritos
            if ($this->favoritesRepository->removeFavorite($userId, $constellationId)) {
                $this->sendResponse(200, "Constelación eliminada de favoritos", null, true);
            } else {
                $this->sendResponse(500, "Error al eliminar favorito", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en removeFavorite: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
