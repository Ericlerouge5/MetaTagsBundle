<?php

namespace Copiaincolla\MetaTagsBundle\Service;

use Copiaincolla\MetaTagsBundle\Entity\Metatag;
use Copiaincolla\MetaTagsBundle\Loader\UrlsLoader;

/*
 * Get information about MetaTags objects managed by CopiaincollaMetaTagsBundle
 */
class MetaTagsAdmin
{
    protected $em;
    protected $urlsLoader;

    /**
     * @param $em
     * @param UrlsLoader $urlsLoader
     */
    public function __construct($em, UrlsLoader $urlsLoader)
    {
        $this->em = $em;
        $this->urlsLoader = $urlsLoader;
    }

    /**
     * Get all MetaTag objects managed by CopiaincollaMetaTagsBundle
     *
     * They are a mix of MetatagsObject:
     *  - generated by the routes exposed by user through CopiaincollaMetaTagsBundle configuration
     *  - entities stored in database
     *
     * $options filter options
     *  - "filter_by_route_names" => enter an array of route names to filter; values must be an array
     *
     * @param array $options
     * @return array an associative array, array('url' => [MetaTag object], ...)
     */
    public function getManagedMetaTags($options = array())
    {
        // merge default options
        $options = array_merge(array(
            'filter_by_route_names' => false
        ), $options);

        // fetch entities from database
        $entities = $this->em->getRepository('CopiaincollaMetaTagsBundle:Metatag')->findAll();

        /**
         * fetch database MetaTag entities
         *
         * build an array with urls as keys and database entity as value
         */
        $arrayUrlEntities = array();
        foreach ($entities as $entity) {
            if (is_string($entity->getUrl())) {
                $arrayUrlEntities[$entity->getUrl()] = $entity;
            }
        }

        /**
         * build an array with url as keys and a MetaTag object as value
         */
        $output = array();

        foreach ($this->urlsLoader->getGeneratedUrls() as $routeName => $urls) {
            foreach ($urls as $url) {

                // filter by route name if specified in $options
                if (is_array($options['filter_by_route_names'])) {
                    if (!in_array($routeName, $options['filter_by_route_names'])) {
                        continue;
                    }
                }

                // associate url with a database entity
                if ($url && array_key_exists($url, $arrayUrlEntities)) {
                    $metaTag = $arrayUrlEntities[$url];
                } else {
                    $metaTag = new Metatag();
                    $metaTag->setUrl($url);
                }

                $output[$url] = $metaTag;
            }
        }

        /**
         * add the urls stored in database but not generated by the bundle
         *
         * $arrayRemainingUrlEntities is the array of entities not already associate to a generated url
         */
        if ($options['filter_by_route_names'] == false) {
            $arrayRemainingUrlEntities = array_diff_key($arrayUrlEntities, array_intersect_key($output, $arrayUrlEntities));
            foreach ($arrayRemainingUrlEntities as $url => $entity) {
                $output[$url] = $entity;
            }
        }

        ksort($output);

        return $output;
    }
}