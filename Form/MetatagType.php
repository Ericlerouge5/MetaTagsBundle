<?php

namespace Copiaincolla\MetaTagsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Copiaincolla\MetaTagsBundle\Loader\UrlsLoader;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetatagType extends AbstractType {

    protected $urlsLoader;

    public function __construct(UrlsLoader $urlsLoader) {
        $this->urlsLoader = $urlsLoader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('url', HiddenType::class);


        // add other fields
        $builder
                ->add('title', null, array('label' => 'Meta tag "title"'))
                ->add('description', null, array('label' => 'Meta tag "description"'))
                ->add('keywords', null, array('label' => 'Meta tag "keywords"'))
                ->add('robots', null, array('label' => 'Meta tag "robots"'))
                ->add('googlebot', null, array('label' => 'Meta tag "googlebot"'))
                ->add('author', null, array('label' => 'Meta tag "author"'))
                ->add('language', null, array('label' => 'Meta tag "language"'))
                ->add('ogTitle', null, array('label' => 'Meta tag "og:title"'))
                ->add('ogDescription', null, array('label' => 'Meta tag "og:description"'))
                ->add('ogImage', null, array('label' => 'Meta tag "og:image"'))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Copiaincolla\MetaTagsBundle\Entity\Metatag'
        ));
    }

}
