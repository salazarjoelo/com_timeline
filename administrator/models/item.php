<?php
/**
 * @package     Salazarjoelo\Component\Timeline
 * @subpackage  com_timeline
 *
 * @copyright   Copyright (C) 2023-2025 Joel Salazar. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Salazarjoelo\Component\Timeline\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form; // Para tipado
use Joomla\CMS\Table\Table; // Para tipado
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper; // Para convertir objetos a arrays

/**
 * Item Model for Timeline component (Administrator)
 *
 * @since  5.0.0
 */
class ItemModel extends AdminModel
{
    /**
     * The type alias for this content type.
     *
     * @var    string
     * @since  5.0.0
     */
    public string $typeAlias = 'com_timeline.item'; // Asegúrate que esto sea único y relevante

    /**
     * Method to get the table name.
     *
     * @param   string  $type    Name of the JTable class to instantiate.
     * @param   string  $prefix  Prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  Table  A JTable object
     *
     * @since   5.0.0
     */
    public function getTable(string $type = 'Item', string $prefix = 'Administrator', array $config = []): Table
    {
        // Asegúrate que el nombre de la clase de la tabla sea correcto:
        // Salazarjoelo\Component\Timeline\Administrator\Table\ItemTable
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|false  A Form object on success, false on failure.
     *
     * @since   5.0.0
     */
    public function getForm(array $data = [], bool $loadData = true): Form|false
    {
        // Get the form.
        $form = $this->loadForm(
            'com_timeline.item', // Nombre del formulario (ej: admin/forms/item.xml)
            'item',              // Nombre del archivo XML del formulario
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected into the form.
     *
     * @return  array|null  The data for the form.
     *
     * @since   5.0.0
     */
    protected function loadFormData(): ?array
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_timeline.edit.item.data', null);

        if (empty($data)) {
            $data = $this->getItem(); // Carga el ítem desde la BD si no hay datos en sesión
        }
        
        // Convertir el objeto a array si es necesario para el formulario
        if (is_object($data)) {
            $data = ArrayHelper::fromObject($data);
        }

        return $data;
    }

    /**
     * Method to preprocess the form.
     *
     * @param   Form   $form   A Form object.
     * @param   mixed  $data   The data expected for the form.
     * @param   string $group  The name of the plugin group to import.
     *
     * @return  void
     *
     * @see     \Joomla\CMS\MVC\Model\FormModel::preprocessForm
     * @since   5.0.0
     *
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, string $group = 'content'): void // $data puede ser array u objeto
    {
        // Ejemplo: Ocultar campos basados en alguna condición o establecer valores por defecto
        $user = Factory::getUser();
        
        // Si es un nuevo ítem y el campo 'created_by' existe y está vacío
        if (is_array($data) && empty($data['id']) && isset($data['created_by']) && empty($data['created_by'])) {
             // Puedes querer que el formulario no muestre 'created_by' para ítems nuevos
             // o establecerlo aquí, aunque la tabla podría manejarlo.
        }

        // Si el usuario no tiene permiso para cambiar el estado, hacer el campo de estado readonly
        // Esto es un ejemplo, la lógica de ACL para campos es más compleja
        // if (!$user->authorise('core.edit.state', 'com_timeline.item.' . (isset($data['id']) ? $data['id'] : 0))) {
        //     $form->setFieldAttribute('state', 'readonly', 'true');
        //     $form->setFieldAttribute('state', 'filter', 'unset'); // No permitir que se guarde si no tiene permiso
        // }

        parent::preprocessForm($form, $data, $group);
    }
    
    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   5.0.0
     */
    public function save(array $data): bool
    {
        $app   = Factory::getApplication();
        $table = $this->getTable();
        $pk    = (!empty($data['id'])) ? $data['id'] : null; // Asumiendo que 'id' es tu clave primaria

        try {
            // Cargar el registro existente si es una edición
            if ($pk > 0) {
                if (!$table->load($pk)) {
                    $this->setError($table->getError());
                    return false;
                }
            }

            // Validar y enlazar los datos al objeto Table
            // El método bind usualmente no necesita $ignore, ya que los campos no existentes en la tabla se ignoran
            // Si tu $data puede tener campos extra que NO deben ir a la tabla, considera filtrarlos antes.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Realizar la validación de datos (método check() en la clase Table)
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Guardar los datos
            if (!$table->store(true)) { // El true es para reestablecer el ID después de insertar
                $this->setError($table->getError());
                return false;
            }

            // Limpiar la caché si es necesario
            $this->cleanCache();

            // Actualizar el ID en los datos si es un nuevo registro
            if (is_null($pk) && $table->id) {
                 $data['id'] = $table->id;
                 // Actualizar el contexto para la redirección
                 $this->setState($this->getName() . '.id', $table->id);
            }


        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
        
        // Limpiar datos de sesión después de guardar exitosamente
        $app->setUserState('com_timeline.edit.item.data', null);

        return true;
    }
     /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of primary key values.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   5.0.0
     */
    public function delete(array &$pks): bool
    {
        $table = $this->getTable();

        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!$table->delete($pk)) {
                    $this->setError($table->getError());
                    return false;
                }
            } else {
                $this->setError($table->getError()); // Error al cargar el ítem
                return false;
            }
        }
        
        $this->cleanCache();

        return true;
    }
}
