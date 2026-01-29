<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\Status;
use App\Entity\User;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Project $project */
        $project = $options['project'];

        $builder
            ->add('title')
            ->add('description')
            ->add('deadline', null, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'label',
            ])
            ->add('assignee', EntityType::class, [
                'class' => User::class,
                'choice_label' => fn (User $u) =>
                    trim($u->getFirstName() . ' ' . $u->getLastName()),
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'project' => null, // ✅ option personnalisée
        ]);

        $resolver->setAllowedTypes('project', ['null', Project::class]);
    }
}
