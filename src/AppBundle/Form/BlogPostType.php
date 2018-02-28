<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/28/18
 * Time: 9:38 AM
 */

namespace AppBundle\Form;

use AppBundle\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogPostType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod($options['method'])
            ->add('_image_id', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('_image_token', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
                'required' => false
            ])
            /*->add('label', TextType::class, [
                'label' => 'Label',
                'disabled' => true
            ])*/
            ->add('href', TextType::class, [
                'label' => 'Href',
                'required' => false
            ])
            ->add('short', TextareaType::class, [
                'label' => 'Short description',
                'required' => false
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Body',
                'required' => false
            ])
            ->add('created_date', DateTimeType::class, [
                'label' => 'Creation date:',
                'required' => false,
                'placeholder' => array(
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                )
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enabled',
                'required' => false
            ])
            ->add('pic', FileType::class, [
                'label' => 'Image:',
                'required' => false,
                'data_class' => null
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['label']//$post->getId() ? 'Edit' : 'Add'
            ])
            ->add('id', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => BlogPost::class,
        ));
    }
}