<?php

namespace Copiaincolla\MetaTagsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Copiaincolla\MetaTagsBundle\Loader\UrlsLoader;

class MetatagType extends AbstractType
{

    protected $urlsLoader;

    public function __construct(UrlsLoader $urlsLoader)
    {
        $this->urlsLoader = $urlsLoader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metaTag = $builder->getForm()->getData();

        // load urls to populate the select
        $routes = $this->urlsLoader->getUrls();

        /*
         * prepare the multidimensional array of urls to be put in a <select> tag
         * by replacing the urls keys
         */
        array_walk($routes, function($val, $key) use(&$routes){
            if (count($val) > 0) {
                $routes[$key] = array_combine($val, $val);
            }
        });

        // sort the urls array by route names
        ksort($routes);

        /**
         * add 'url' field
         *
         * if $metaTag object is not new, 'url' field is hidden.
         * otherwise it is a choice field to select the url to associate
         */
        if ($metaTag->getId()) {
            $builder->add('url', 'hidden');
        } else {
            $builder->add('url', 'choice', array(
                'choices' => $routes,
                'required' => true,
            ));
        }

        // add meta tags fields
        $builder
            ->add('title')
            ->add('description')
            ->add('keywords')
            ->add('author')
        ;
    }

    public function getName()
    {
        return 'copiaincolla_metatagsbundle_metatagtype';
    }

}
