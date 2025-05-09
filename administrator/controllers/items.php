<?php
/**
 * @package     Salazarjoelo\Component\Timeline
 * @subpackage  com_timeline
 *
 * @copyright   Copyright (C) 2023-2025 Joel Salazar. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Salazarjoelo\Component\Timeline\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route; // Aunque no se usa directamente en este archivo, es bueno mantenerlo si se planea usar
use Joomla\CMS\Session\Session; // Igual que Route
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel; // Para el tipado de getModel

/**
 * Items list controller class.
 *
 * @since  5.0.0
 */
class ItemsController extends AdminController
{
    /**
     * The prefix for the models.
     *
     * @var    string
     * @since  5.0.0
     */
    protected $model_prefix = 'Administrator'; // Definido como propiedad para claridad

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     \Joomla\CMS\MVC\Controller\BaseController
     * @since   5.0.0
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        // Puedes registrar tareas aquí si es necesario, aunque AdminController ya tiene muchas.
        // $this->registerTask('nueva_tarea', 'metodoNuevaTarea');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  The prefix for the class name.
     * @param   array   $config  Configuration array for model.
     *
     * @return  BaseDatabaseModel|false  Model object on success; otherwise false.
     *
     * @since   5.0.0
     */
    public function getModel(string $name = 'Item', string $prefix = '', array $config = ['ignore_request' => true]): BaseDatabaseModel|false // Tipado añadido
    {
        // Si $prefix está vacío, usa el $model_prefix de la clase.
        // Esto permite más flexibilidad si se llama externamente.
        if (empty($prefix)) {
            $prefix = $this->model_prefix;
        }
        
        // Asegura que el modelo solicitado sea uno de los modelos principales de la lista
        // o un modelo específico de un ítem. AdminController espera 'nombreplural' para la lista
        // y 'nombresingular' para el ítem.
        // Aquí, estamos forzando 'Item' como el modelo singular por defecto.
        // Si el $name que llega es 'Items' (plural, para la lista),
        // AdminController lo manejará correctamente si existe ItemsModel.
        // Si es para un ítem singular, 'Item' es correcto.

        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    // AdminController ya proporciona métodos como publish, unpublish, delete, etc.
    // Solo necesitas sobrescribirlos o añadir nuevos si requieres lógica personalizada.
    // Por ejemplo, si tuvieras un botón "Archivar" personalizado:
    /*
    public function archive(): void
    {
        $this->checkToken(); // Comprobar CSRF Token
        $cid   = $this->input->get('cid', [], 'array'); // Obtener los IDs seleccionados
        $model = $this->getModel('Item'); // Obtener el modelo singular

        if (empty($cid)) {
            Factory::getApplication()->enqueueMessage(Text::_('JGLOBAL_NO_ITEM_SELECTED'), 'error');
            $this->setRedirect(Route::_('index.php?option=com_timeline&view=items', false));
            return;
        }

        try {
            $model->archive($cid); // Suponiendo que tienes un método archive en tu ItemModel
            $this->setMessage(Text::plural('COM_TIMELINE_N_ITEMS_ARCHIVED', count($cid)));
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_timeline&view=items', false));
    }
    */
}
