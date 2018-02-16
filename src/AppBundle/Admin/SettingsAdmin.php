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

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('skey', 'text')
            ->add('value', 'text')
        ;
    }

    public function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('skey')
            ->add('value')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('skey')
        ;
    }

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