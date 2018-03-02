<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/15/18
 * Time: 1:58 PM
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Page;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PageAdmin extends AbstractAdmin
{

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('title', 'text')
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
         * @var Page $object
         */
        if (null !== $object) {
            return $object->getTitle();
        }
        return 'Static page';
    }

    public function prePersist($object)
    {
        $this->uploadPic($object);
    }

    public function preUpdate($object)
    {
        $this->uploadPic($object);
        $object->setUpdated();
    }

    private function uploadPic(Page $post)
    {
        if (null === $post->getFile()) {
            return;
        }

        $newName = md5(time()) . '.' . $post->getFile()->guessExtension();
        $path = $this->getConfigurationPool()->getContainer()->getParameter('images_directory');

        $post->getFile()->move(
            $path, $newName
        );
        $post->setPic($newName);
    }
}