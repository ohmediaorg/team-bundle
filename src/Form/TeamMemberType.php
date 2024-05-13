<?php

namespace OHMedia\TeamBundle\Form;

use OHMedia\FileBundle\Form\Type\FileEntityType;
use OHMedia\TeamBundle\Entity\TeamMember;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $teamMember = $options['data'];

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

        $builder->add('phone', TelType::class, [
            'required' => false,
        ]);

        $builder->add('facebook', UrlType::class, [
            'required' => false,
        ]);

        $builder->add('twitter', UrlType::class, [
            'required' => false,
        ]);

        $builder->add('instagram', UrlType::class, [
            'required' => false,
        ]);

        $builder->add('linked_in', UrlType::class, [
            'required' => false,
            'label' => 'LinkedIn',
        ]);

        $builder->add('image', FileEntityType::class, [
            'required' => false,
            'image' => true,
            'data' => $teamMember->getImage(),
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
