<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/15/18
 * Time: 1:58 PM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Setting;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * CRUD for key-value settings pairs
 * Class SettingsAdmin
 * @package AppBundle\Admin
 */
class SettingsAdmin extends AbstractAdmin
{
    /**
     * Create-edit form fields
     * @param FormMapper $form
     */
    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('skey', 'text')
            ->add('value', 'text');
    }

    /**
     * Search fields in items list
     * @param DatagridMapper $filter
     */
    public function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('skey')
            ->add('value');
    }

    /**
     * Clickable field in items list (to open edit form) and rest items list feilds displayed
     * @param ListMapper $list
     */
    public function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('skey');
    }

    /**
     * Return string represent of $object
     * @param $object
     * @return string
     */
    public function toString($object)
    {
        /**
         * @var Setting $object
         */
        if (null !== $object) {
            return $object->getSkey();
        }
        return 'Setting';
    }
}