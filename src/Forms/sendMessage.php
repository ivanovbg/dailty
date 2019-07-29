<?php

namespace App\Forms;
use App\Entity\Account;
use App\Entity\Message;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class sendMessage extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add("receiver", EntityType::class,[
                'class' => Account::class,
                'choice_label' => 'name',
                'query_builder' => $options['accounts'],
                'attr' =>[
                    'class' => 'form-control selectpicker',
                    'data-show-subtext' => "true",
                    'data-live-search' => "true"
                ]
            ])
            ->add("subject", TextType::class, [
              'attr' => [
                  'class' => 'form-control',
                  'placeholder' => "Тема"
              ]
            ])
            ->add("message", TextareaType::class, [
                'attr' => [
                    'class' => 'form-control message',
                    'id' => 'compose-textarea',
                    'style' => 'height: 300px'
                ]
            ])
            ->add("submit", SubmitType::class, [
                'label' => 'Изпрати',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'accounts' => Account::class
        ]);
    }

    public function getName()
    {
        return 'send_message_form';
    }

}

