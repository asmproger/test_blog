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
use Symfony\Component\DependencyInjection\ContainerInterface;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Home', array('route' => 'homepage'));
        $menu->addChild('Blog', array('route' => 'blog_index'));

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

        // create another menu item
        //$menu->addChild('About Me', array('route' => 'homepage'));
        // you can also add sub level's to your menu's as follows
        //$menu['About Me']->addChild('Edit profile', array('route' => 'default_test'));

        return $menu;
    }
}