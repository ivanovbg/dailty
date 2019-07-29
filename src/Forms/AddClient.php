<?php
namespace App\Forms;

use App\Entity\City;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddClient extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options){

        $builder
            ->add('name', TextType::class,[
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('email', EmailType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add("phone", TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add("address", TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add("sendNotifications", CheckboxType::class, [
                'attr' => [
                    'data-toggle' => 'toggle'
                ],
                'label' => 'Изпращай известия',
                'required' => false
            ])
            ->add("city", EntityType::class,[
                'class' => City::class,
                'choice_label' => 'name',
                'attr' =>[
                    'class' => 'form-control selectpicker',
                    'data-show-subtext' => "true",
                    'data-live-search' => "true"
                ],
                'required' => false
            ])
            ->add('note', TextareaType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
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
            'data_class' => Client::class,
        ]);
    }

    public function getName()
    {
        return 'client_add';
    }
}

