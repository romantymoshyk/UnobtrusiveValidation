<?php
namespace RomanTymoshyk\UnobtrusiveValidationBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;

class UnobtrusiveValidationExtension extends AbstractTypeExtension
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var null|string
     */
    protected $translationDomain;

    /**
     * @param TranslatorInterface $translator The translator for translating error messages
     * @param null|string $translationDomain The translation domain for translating
     */
    public function __construct(TranslatorInterface $translator = null, $translationDomain = false)
    {
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            array(
                'label_translation_domain' => false,
                'translation_domain' => $this->translationDomain
            )
        )
            ->setAllowedTypes('label_translation_domain', array('null', 'string', 'boolean'))
            ->setAllowedTypes('translation_domain', array('null', 'string', 'boolean'));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (count($options['constraints']) > 0) {
            $view->vars['attr']['data-val'] = 'true';

            $label = $this->trans($options['label'], array(), $options['label_translation_domain']);

            /** @var Constraint $constraint */
            foreach ($options['constraints'] as $constraint) {
                switch (get_class($constraint)) {
                    case 'Symfony\Component\Validator\Constraints\Required':
                        $view->vars['attr']['data-val-required'] = $this->trans(
                            'The \'{{ field_name }}\' field is required.',
                            array('{{ field_name }}' => $label)
                        );
                        break;
                    case 'Symfony\Component\Validator\Constraints\Regex':
                        $view->vars['attr']['data-val-regex'] = $this->trans(
                            'The \'{{ field_name }}\' value is not valid',
                            array('{{ field_name }}' => $label)
                        );
                        $view->vars['attr']['data-val-regex-pattern'] = $constraint->htmlPattern;
                        break;
                    case 'Symfony\Component\Validator\Constraints\Range':
                        if (!empty($constraint->min) && !empty($constraint->max)) {
                            $view->vars['attr']['data-val-range'] = $this->trans(
                                'The field \'{{ field_name }}\' value should be in range {{ min }} to {{ max }}.',
                                array(
                                    '{{ field_name }}' => $label,
                                    '{{ min }}' => $constraint->min,
                                    '{{ max }}' => $constraint->max
                                )
                            );
                        }
                        break;
                    case 'Symfony\Component\Validator\Constraints\LessThanOrEqual':
                        if (!empty($constraint->min)) {
                            $view->vars['attr']['data-val-length-max'] = $constraint->min;
                            $view->vars['attr']['data-val-length'] = $this->trans(
                                'The field \'{{ field_name }}\' should be less than {{ limit }}.',
                                array(
                                    '{{ field_name }}' => $label,
                                    '{{ limit }}' => $constraint->min
                                )
                            );
                        }
                        break;
                    case 'Symfony\Component\Validator\Constraints\GreaterThanOrEqual':
                        if (!empty($constraint->min)) {
                            $view->vars['attr']['data-val-length-min'] = $constraint->min;
                            $view->vars['attr']['data-val-length'] = $this->trans(
                                'The field \'{{ field_name }}\' should be greater than {{ limit }}.',
                                array(
                                    '{{ field_name }}' => $label,
                                    '{{ limit }}' => $constraint->min
                                )
                            );
                        }
                        break;
                    case 'Symfony\Component\Validator\Constraints\Length':
                        if (!empty($constraint->min)) {
                            $view->vars['attr']['data-val-length-min'] = $constraint->min;
                            $view->vars['attr']['data-val-length'] = $this->trans(
                                'The field \'{{ field_name }}\' should have {{ limit }} or more.',
                                array(
                                    '{{ field_name }}' => $label,
                                    '{{ limit }}' => $constraint->min
                                )
                            );
                        }

                        if (!empty($constraint->max)) {
                            $view->vars['attr']['data-val-length-max'] = $constraint->max;
                            $view->vars['attr']['data-val-length'] = $this->trans(
                                'The field \'{{ field_name }}\' should have {{ limit }} or less.',
                                array(
                                    '{{ field_name }}' => $label,
                                    '{{ limit }}' => $constraint->max
                                )
                            );
                        }

                        if (!empty($constraint->min) && !empty($constraint->max)) {
                            $view->vars['attr']['data-val-length'] = $this->trans(
                                'The field \'{{ field_name }}\' should have from {{ min }} to {{ max }} characters.',
                                array(
                                    '{{ field_name }}' => $label,
                                    '{{ min }}' => $constraint->min,
                                    '{{ max }}' => $constraint->max
                                )
                            );
                        }
                        break;
                    case 'Symfony\Component\Validator\Constraints\Type':
                        switch ($constraint->type) {
                            case 'integer':
                            case 'int':
                            case 'long':
                                $view->vars['attr']['data-val-digits'] = $this->trans(
                                    'The \'{{ field_name }}\' field value is not a valid integer.',
                                    array('{{ field_name }}' => $label)
                                );
                                break;
                            case 'float':
                            case 'numeric':
                            case 'real':
                                $view->vars['attr']['data-val-number'] = $this->trans(
                                    'The \'{{ field_name }}\' field value is not a valid number.',
                                    array('{{ field_name }}' => $label)
                                );
                                break;
                        }
                        break;
                    case 'Symfony\Component\Validator\Constraints\Date':
                        $view->vars['attr']['data-val-date'] = $this->trans(
                            'The \'{{ field_name }}\' field value is not a valid date.',
                            array('{{ field_name }}' => $label)
                        );
                        break;
                    case 'Symfony\Component\Validator\Constraints\Email':
                        $view->vars['attr']['data-val-email'] = $this->trans(
                            'The \'{{ field_name }}\' field value is not a valid email address.',
                            array('{{ field_name }}' => $label)
                        );
                        break;
                    case 'Symfony\Component\Validator\Constraints\CardScheme':
                        $view->vars['attr']['data-val-creditcard'] = $this->trans(
                            'The \'{{ field_name }}\' field value is not a valid credit card number.',
                            array('{{ field_name }}' => $label)
                        );
                        break;
                    case 'Symfony\Component\Validator\Constraints\Url':
                        $view->vars['attr']['data-val-url'] = $this->trans(
                            'The \'{{ field_name }}\' field value is not a valid url.',
                            array('{{ field_name }}' => $label)
                        );
                        break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FormType';
    }

    /**
     * Translates the given message.
     *
     * @param string $id The message id (may also be an object that can be cast to string)
     * @param array $parameters An array of parameters for the message
     *
     * @return string The translated string
     */
    public function trans($id, array $parameters = array(), $translationDomain = null)
    {
        $result = $id;

        if ($this->translator !== null) {
            $translationDomain = $translationDomain !== null ? $translationDomain : $this->translationDomain;

            $result = $this->translator->trans(
                $id,
                $parameters,
                $translationDomain
            );
        }

        return $result;
    }
}
