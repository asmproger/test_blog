<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/15/18
 * Time: 4:22 PM
 */

namespace AppBundle\Menu;


use AppBundle\Entity\Page;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Menu builder for KNP menu
 * Class Builder
 * @package AppBundle\Menu
 */
class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Home', array('route' => 'homepage'));
        $menu->addChild('Blog', array('route' => 'blog_index'));
        $menu->addChild('Ajax Blog', array('route' => 'rest_blog'));
        $menu->addChild('Angular Blog', array('route' => 'blog_angular'));

        // access services from the container!
        $em = $this->container->get('doctrine')->getManager();
        // findMostRecent and Blog are just imaginary examples
        $items = $em->getRepository(Page::class)->findAll();
        foreach ($items as $item) {
            $menu->addChild($item->getTitle(), array(
                'route' => 'default_page',
                'routeParameters' => array('url' => $item->getUrl())
            ));
        }
        return $menu;
    }
}