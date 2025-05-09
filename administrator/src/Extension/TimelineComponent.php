<?php
/**
 * @package     Salazarjoelo\Component\Timeline
 * @subpackage  com_timeline
 *
 * @copyright   Copyright (C) 2023-2025 Joel Salazar. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1); // Para PHP 7+ (recomendado en PHP 8+)

namespace Salazarjoelo\Component\Timeline\Administrator\Extension;

// No necesitas `defined('_JEXEC') or die;` aquí si tu .htaccess y entry points lo manejan,
// pero es una capa de seguridad común en Joomla. Lo mantendré por si acaso.
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface; // Importar para el tipado
use Joomla\CMS\Router\Route;
// use Joomla\Database\DatabaseAwareTrait; // No parece usarse directamente en esta clase

/**
 * Component MVCComponent for com_timeline (Administrator)
 *
 * @since  5.0.0
 */
class TimelineComponent extends MVCComponent
{
    /**
     * Constructor.
     *
     * @param   CMSApplication       $application  The CMS Application.
     * @param   MVCFactoryInterface  $factory      The MVC factory. // Tipado añadido
     * @param   ?string              $basePath     The base path for the component.
     *
     * @since   5.0.0
     */
    public function __construct(CMSApplication $application, MVCFactoryInterface $factory, ?string $basePath = null) // Tipado mejorado
    {
        // Establecer el espacio de nombres del controlador para la carga automática de clases
        // El MVCFactory se encargará de esto si los controladores están en el namespace correcto.
        // $this->setControllerNamespace('Salazarjoelo\\Component\\Timeline\\Administrator\\Controller');

        // Si no proporcionas un basePath, MVCComponent intentará deducirlo.
        // Para mayor claridad, puedes especificarlo. JPATH_COMPONENT_ADMINISTRATOR se define tarde,
        // así que podrías necesitar construirlo o confiar en la detección automática.
        // $basePath = $basePath ?? dirname(__DIR__); // Asumiendo que Extension está un nivel dentro de src

        parent::__construct($application, $factory, $basePath);
    }

    /**
     * Get an instance of a an administrator controller.
     *
     * Controllers are created adhering to the PSR-11 container pattern.
     *
     * @param   string  $name    The name of the controller. (e.g. 'Mycontroller')
     * @param   string  $prefix  The class prefix. (e.g. 'Mycomponent')
     * @param   array   $config  An array of optional key/value settings.
     *
     * @return  BaseController  A BaseController object.
     *
     * @since   5.0.0
     *
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     */
    public function getController(string $name, string $prefix = 'Administrator', array $config = []): BaseController // Tipado añadido
    {
        // No necesitas sobrescribir esto si tus controladores siguen el patrón de nombres
        // y están en el namespace esperado: Salazarjoelo\Component\Timeline\Administrator\Controller\NombreController
        // El MVCFactory que se pasa al constructor de MVCComponent se encargará de encontrarlos.
        // Si lo sobrescribes, asegúrate de que la lógica sea robusta.
        // La clase base MVCComponent tiene una implementación de getController.

        // Si tienes una razón específica para personalizar la carga del controlador, aquí es donde lo harías.
        // Por ejemplo, si tus controladores NO están en el sub-namespace 'Controller' directamente.
        // Pero basado en tu estructura de `admin/src/Controller/`, deberían ser autocargables.

        return parent::getController($name, $prefix, $config);
    }

    // Puedes añadir otros métodos si necesitas lógica específica en el punto de entrada,
    // como registrar servicios si no usas un Service Provider externamente,
    // o establecer rutas, aunque esto se maneja mejor en un archivo de enrutamiento dedicado.
}
