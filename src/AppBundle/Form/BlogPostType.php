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

    public function buidForm(FormBuilderInterface $builder, array $options) {
die('ok');
        $builder
            ->setMethod('POST')
            ->add('_image_id', HiddenType::class, [
                'mapped' => false
            ])
            ->add('_image_token', HiddenType::class, [
                'mapped' => false
            ])
            ->add('Title', TextType::class, [
                'label' => 'Title'
            ])
            ->add('label', TextType::class, [
                'label' => 'Label'
            ])
            ->add('href', TextType::class, [
                'label' => 'Href'
            ])
            ->add('short', TextareaType::class, [
                'label' => 'Short description'
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Body'
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
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => BlogPost::class,
        ));
    }
}