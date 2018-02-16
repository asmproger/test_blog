<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/15/18
 * Time: 1:58 PM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\BlogPost;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class BlogPostsAdmin extends AbstractAdmin
{

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('title', 'text')
            ->add('href', 'text')
            ->add('body', 'textarea', [
                'attr' => [
                    'class' => 'textarea'
                ]
            ])
            ->add('file', 'file', [
                'required' => false
            ]);
    }

    public function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('title');
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('title')
            ->add('label')
        ;
    }

    public function toString($object)
    {
        /**
         * @var BlogPost $object
         */
        if (null !== $object) {
            return $object->getTitle();
        }
        return 'Static page';
    }

    public function preUpdate($obj)
    {
        /**
         * @var BlogPost $obj
         */
        $obj->setUpdated();
    }
}