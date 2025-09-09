<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rut', null, [
                'label' => 'RUT (sin puntos ni código verificador)',
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true
            ])
            ->add('name', null, [
                'label' => 'Nombres/Names',
                'required' => true
            ])
            ->add('lastName', null, [
                'label' => 'Apellidos/Last Name',
                'required' => true
            ])
            ->add('sex', ChoiceType::class, [
                'label' => 'Sexo',
                'choices' => ['Masculino' => 1, 'Femenino' => 0],
                'expanded' => 'true',
                'required' => true
            ])
            ->add('phone', null, [
                'label' => 'Teléfono/Phone Number',
            ])
            ->add('deactivatedNotifications', null, [
                'label' => 'Desactivar Notificaciones en Febrero',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Contraseña',
                'attr' => [
                    'class' => 'password-field',
                    'id' => 'user_password',
                    'autocomplete' => 'new-password'
                ]
            ])
            ->add('picture', null, [
                'label' => 'Foto (URL)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://intrawww.ing.puc.cl/siding/datos/fotos/publicas/',
                    'value' => 'https://intrawww.ing.puc.cl/siding/datos/fotos/publicas/'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
