<?php

namespace OHMedia\TeamBundle\Form;

use OHMedia\FileBundle\Form\Type\FileEntityType;
use OHMedia\TeamBundle\Entity\TeamMember;
use OHMedia\UtilityBundle\Form\PhoneType;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('honorific', TextType::class, [
            'help' => 'Mr., Mrs., Dr., etc.',
            'required' => false,
        ]);

        $builder->add('first_name', TextType::class, [
            'label' => 'First Name',
        ]);

        $builder->add('last_name', TextType::class, [
            'label' => 'Last Name',
        ]);

        $builder->add('designation', TextType::class, [
            'help' => 'MBA, PhD, etc.',
            'required' => false,
        ]);

        $builder->add('title', TextType::class, [
            'help' => 'Manager, Lead Developer, etc.',
            'required' => false,
        ]);

        $builder->add('email', EmailType::class, [
            'required' => false,
        ]);

        $builder->add('phone', PhoneType::class, [
            'required' => false,
        ]);

        $builder->add('image', FileEntityType::class, [
            'required' => false,
            'image' => true,
        ]);

        $builder->add('bio', WysiwygType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamMember::class,
        ]);
    }
}
