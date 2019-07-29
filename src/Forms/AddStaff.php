<?php
/**
 * Created by PhpStorm.
 * User: Krasimir
 * Date: 10/28/2018
 * Time: 20:24
 */

namespace App\Forms;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddStaff extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('email', EmailType::class, [
                'required' =>true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add("submit", SubmitType::class, [
                'label' => isset($options['data']['edit']) && $options['data']['edit']  ? "Редактирай" : "Добави",
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data'
        ]);
    }

    public function getName()
    {
        return 'add_staff_form';
    }


}