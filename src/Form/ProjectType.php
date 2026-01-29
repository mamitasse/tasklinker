<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Titre du projet',
                'attr' => ['id' => 'projet_nom'],
            ])
            ->add('users', EntityType::class, [
                'label' => 'Inviter des membres',
                'class' => User::class,
                'choice_label' => fn(User $u) => $u->getFullName(),
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'id' => 'projet_employes', // IMPORTANT (Select2)
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
