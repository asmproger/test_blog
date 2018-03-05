<?php
/**
 * Created by PhpStorm.
 * User: sovkutsan
 * Date: 2/14/18
 * Time: 1:59 PM
 */

namespace AppBundle\DataFixtures;

use AppBundle\Entity\Page;
use AppBundle\Entity\Setting;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Test data for first start after deploy
 * Class AppFixtures
 * @package AppBundle\DataFixtures
 */
class AppFixtures extends Fixture
{
    // we need add admin, and encode his password
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // test admin
        $user = new User();
        $user->setUsername('admin2');
        $user->setUsernameCanonical('admin2');
        $user->setEmail('asmproger@rambler.ru');
        $user->setEmailCanonical('asmproger@rambler.ru');
        $user->setEnabled(1);
        $user->setRoles(['ROLE_SUPER_ADMIN']);

        // very difficul password for admin2
        $pwd = $this->encoder->encodePassword($user, '123456');
        $user->setPassword($pwd);

        $manager->persist($user);

        // some settings for blogposts task
        $settings = [
            'parser_task_period' => '5',
            'parser_task_query' => 'xiaomi, fitness, intel',
            'parser_task_last_update' => '1519030806',
            'parser_task_count' => '5',
            'parse_task_settings' => '{}'
        ];
        foreach ($settings as $k => $v) {
            $setting = new Setting();
            $setting->setSkey($k);
            $setting->setValue($v);
            $manager->persist($setting);
        }

        // some static pages
        $pages = [
            [
                'title' => 'Contacts',
                'body' => 'Contacts page',
                'label' => 'contacts'
            ],
            [
                'title' => 'About us',
                'body' => 'Aobut us page. We are the champions, my friends!',
                'label' => 'about_us'
            ],
            [
                'title' => 'Our mission',
                'body' => 'Our mission us page. Some water.',
                'label' => 'our_mission'
            ]
        ];
        foreach ($pages as $item) {
            $page = new Page();
            $page->setTitle($item['title']);
            $page->setBody($item['body']);
            $page->setShort($item['body']);
            $page->setLabel($item['label']);

            $manager->persist($page);
        }

        $manager->flush();
    }
}