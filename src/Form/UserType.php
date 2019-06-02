<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserShow;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'locale',
                'choices' => [
                    'English' => 'en',
                    'Lietuvių' => 'lt',
                    'Pусский' => 'ru',
                ],
                'choice_translation_domain' => false,
            ])
            ->add('theme', ChoiceType::class, [
                'label' => 'theme',
                'choices' => [
                    '' => '',
                    'darkster' => 'darkster',
                    'fresca' => 'fresca',
                    'greyson' => 'greyson',
                    'herbie' => 'herbie',
                    'monotony' => 'monotony',
                    'tequila' => 'tequila',
                ],
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('calendarShow', ChoiceType::class, [
                'label' => 'show.Calendar include',
                'choices' => [
                    'Archived' => UserShow::STATUS_ARCHIVED,
                    'Watchlater' => UserShow::STATUS_WATCH_LATER,
                ],
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'size' => 2
                ],

            ])
            ->add('defaultOffset', NumberType::class, [
                'label' => 'show.offset',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ])
        ;
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
