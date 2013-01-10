<?php
namespace Copiaincolla\MetaTagsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\HttpFoundation\Request;

use Copiaincolla\MetaTagsBundle\Loader\UrlsLoader;
use Copiaincolla\MetaTagsBundle\Loader\MetaTagsLoader;

class DebugCommand extends ContainerAwareCommand
{
    /**
     * Configure Command
     */
    protected function configure()
    {
        $this
            ->setName('ci_metatags:debug')

            ->setDescription('Displays routes and urls managed by CopiaincollaMetaTagsBundle')

            ->setHelp(<<<EOF
The <info>ci_metatags:debug</info> command displays the routes and the urls managed by CopiaincollaMetaTagsBundle.

  <info>php app/console ci_metatags:debug</info>

Urls are generated by loading the routes you selected, by:
  - adding a bundle under the <comment>'exposed_routes.bundles'</comment> key in config.yml
  - adding a route under the <comment>'dynamic_routes.routes'</comment> key in config.yml
  - adding the parameter <comment>'options={"ci_metatags_expose"=true}'</comment> to @Route annotation in a Controller


You can specify a route name as first argument to get the urls related only to that route:

  <info>php app/console ci_metatags:debug product_show</info>


You can specify, as second argument, the index of the url (the number comparing in the left) to get the real metatags loaded

  <info>php app/console ci_metatags:debug product_show 1</info>

EOF
        )

            ->addArgument('filter_route_name', InputArgument::OPTIONAL, 'Route name to filter')
            ->addArgument('filter_url_index', InputArgument::OPTIONAL, 'Url index to display')

            // filter the urls, excluding the ones already associated
            ->addOption('exclude-already-associated', null, InputOption::VALUE_NONE, 'Excludes already associated urls')
        ;
    }

    /**
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>[CopiaincollaMetaTagsBundle routes]</info>');

        $urlsLoader = $this->getContainer()->get('ci_metatags.url_loader');

        // load urls managed by CopiaincollaMetaTagsBundle
        $routes = $urlsLoader->getUrls(
            ($input->getOption('exclude-already-associated')) ? true : false
        );

        /**
         * print routes and urls
         */
        // if console argument 'route_name' is specified, display only urls generated by that route
        if ($filter_route_name = $input->getArgument('filter_route_name')) {

            // throw exception if filtering route is not present
            if (!array_key_exists($filter_route_name, $routes)) {
                throw new \InvalidArgumentException(sprintf('The route "%s" is not managed by CopiaincollaMetaTagsBundle.', $filter_route_name));
            }

            $routes = array_intersect_key($routes, array($filter_route_name => array()));

            // show the meta tags of an url
            if ($filter_url_index = $input->getArgument('filter_url_index')) {

                if (!isset($routes[$filter_route_name][$filter_url_index-1])) {
                    throw new \InvalidArgumentException(sprintf('The url with index "%s" is not does not exist.', $filter_url_index));
                }

                $url = $routes[$filter_route_name][$filter_url_index-1];
                $this->printUrl($input, $output, $url);
            }

            // show urls loaded for route $filter_route_name
            else {
                $output->write("Meta Tags details for route: ");
                $this->printRoute($input, $output, $filter_route_name, $routes[$filter_route_name]);
            }
        }

        // print all routes/urls
        else {
            $this->printRoutes($input, $output, $routes);
        }
    }

    /**
     * Print a list of routes and urls
     *
     * @param $input
     * @param $output
     * @param $routes
     */
    public function printRoutes($input, $output, $routes)
    {
        // print the list of routes/urls
        foreach ($routes as $route => $urls) {
            if (count($urls) > 0) {
                $this->printRoute($input, $output, $route, $urls);
                $output->writeln("");
            }
        }
    }

    /**
     * Print a single route with relative urls
     *
     * @param $input
     * @param $output
     * @param $route
     * @param $urls
     */
    public function printRoute($input, $output, $route, $urls)
    {
        $output->writeln("<comment>$route</comment>");

        foreach ($urls as $k => $url) {
            $output->writeln("  ".($k+1).".  {$url}");
        }
    }

    /**
     * Print url details, fetching MetaTags from database and combining them with default values
     *
     * @param $input
     * @param $output
     * @param $url
     */
    public function printUrl($input, $output, $url)
    {
        $output->writeln("Meta Tags details for url: <comment>$url</comment>");
        $output->writeln("");

        $request = Request::create($url, 'GET');

        $metatagsLoader = $this->getContainer()->get('ci_metatags.metatags_loader');

        $metaTags = $metatagsLoader->getMetaTagsForRequest($request);

        foreach ($metaTags as $k => $metaTag) {
            $output->writeln("  <comment>{$k}:</comment> {$metaTag}");
        }


    }

}
