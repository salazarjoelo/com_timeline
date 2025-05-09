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
use Joomla\CMS\MVC\Model\ListModel; // Usar ListModel directamente es más específico para listas
use Joomla\CMS\Language\Text;
use Joomla\Database\Query\Query; // Para el tipado de retorno de getListQuery

/**
 * Items Model for Timeline component (Administrator)
 *
 * @since  5.0.0
 */
class ItemsModel extends ListModel // Cambiado de AdminModel a ListModel para mayor especificidad
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   5.0.0
     */
    public function __construct(array $config = [])
    {
        // Definir los campos que se pueden filtrar en esta vista de lista
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'description', 'a.description', // Si quieres buscar en la descripción
                'state', 'a.state',
                'created', 'a.created',
                'created_by', 'a.created_by', 'uc.name', // Asumiendo alias uc para el creador
                'ordering', 'a.ordering',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string|null  $ordering   An optional ordering field.
     * @param   string|null  $direction  An optional direction (asc|desc).
     *
     * @return  void
     * @since   5.0.0
     */
    protected function populateState(string $ordering = null, string $direction = null): void
    {
        $app = Factory::getApplication();

        // List state information
        $ordering  = $ordering ?? 'a.ordering'; // Campo de ordenación por defecto
        $direction = $direction ?? 'ASC';      // Dirección por defecto

        parent::populateState($ordering, $direction);

        // Filtro de búsqueda
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        // Filtro de estado (publicado/despublicado/archivado/etc.)
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);

        // Cargar parámetros del componente si son necesarios para la consulta
        // $params = ComponentHelper::getParams('com_timeline');
        // $this->setState('params', $params);
    }

    /**
     * Method to get a JDatabaseQuery object for retrieving the data set from a database.
     *
     * @return  Query|null  A JDatabaseQuery object to retrieve the data set or null if error.
     * @since   5.0.0
     */
    protected function getListQuery(): ?Query
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Selectar los campos necesarios
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.description'),
                    $db->quoteName('a.date'),
                    $db->quoteName('a.state'),
                    $db->quoteName('a.ordering'),
                    $db->quoteName('a.created'),
                    $db->quoteName('a.created_by'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time')
                ]
            )
        );
        $query->from($db->quoteName('#__timeline_items', 'a')); // Tu tabla principal

        // Join para el nombre del creador
        $query->select($db->quoteName('uc.name', 'author_name'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.created_by'));

        // Join para el nombre del editor (si tienes un campo modified_by)
        // $query->select($db->quoteName('ue.name', 'editor_name'))
        //    ->join('LEFT', $db->quoteName('#__users', 'ue'), $db->quoteName('ue.id') . ' = ' . $db->quoteName('a.modified_by'));
        
        // Filtrar por estado (publicado, despublicado, archivado)
        $state = $this->getState('filter.published');
        if (is_numeric($state)) {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $state);
        } elseif ($state === '') {
            // Por defecto, no mostrar ítems archivados si el filtro está vacío
            $query->where($db->quoteName('a.state') . ' IN (0, 1)');
        }

        // Filtrar por búsqueda en el título (y descripción si se desea)
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(' . $db->quoteName('a.title') . ' LIKE ' . $search .
                ' OR ' . $db->quoteName('a.description') . ' LIKE ' . $search . ')' // Ejemplo si buscas en descripción
            );
        }

        // Añadir la ordenación
        $orderCol = $this->getState('list.ordering', 'a.ordering');
        $orderDirn = $this->getState('list.direction', 'ASC');
        
        // Validar la columna de ordenación para evitar inyección SQL
        // $config['filter_fields'] debe estar definido en el constructor
        if (in_array($orderCol, $this->get('filter_fields'))) {
             $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        }


        return $query;
    }
}
