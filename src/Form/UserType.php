<?php

namespace App\Form;

use App\Entity\User;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timezone', ChoiceType::class, [
                'choices' => $this->getTimezones(),
                'choice_translation_domain' => false,
            ]);
    }

    /**
     * @return array
     */
    private function getTimezones()
    {
        $timezones = [];
        $zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        try {
            foreach ($zones as $timezone) {
                $time = new DateTime(null, new DateTimeZone($timezone));
                $timezones[$timezone . ' (' . $time->format('H:i') . ')'] = $timezone;
            }
        } catch (Exception $e) {
        }

        return $timezones;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}