<?php
namespace App\Forms;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddService extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options){

        $builder
            ->add("name", TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class,[
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('cost', MoneyType::class, [
                'currency' => false,
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('time', NumberType::class, [
                'attr' =>[
                    'min' => 0,
                    'class' => 'form-control'
                ]
            ])
            ->add('is_private', ChoiceType::class, [
                'choices' => [
                    'Да' => 1,
                    'Не' => 0
                 ],
                'attr' => [
                    'class' => 'form-control selectpicker'
                ]
            ])
            ->add("submit", SubmitType::class, [
                'label' => 'Запази',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }

    public function getName()
    {
        return 'service_add';
    }
}

