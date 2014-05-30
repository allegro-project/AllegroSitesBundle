<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Allegro\SitesBundle\Entity\Site;
use Allegro\SitesBundle\Entity\Page;

class LoadTestData
    extends AbstractFixture
    implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $manager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    // used for getting access to user manager
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $admin = $this->makeUser('SiteAdmin', 'xxxx', 'siteadmin@example.com', true);
        $suser = $this->makeUser('SiteCollaborator', 'zzzz', 'user@mail.com', false);

        // // // // // // // // // // S I T E  0 // // // // // // // // // //

        $s0 = $this->makeSite($admin,
            'en', array('es'),
            'Base site',
            'base',
            'A site without template');

            $s0_1 = $this->makePage($admin, $s0, null, 'p',
                'Home',
                null,
                'home');
                $s0_1_1 = $this->makePage($suser, $s0, $s0_1, 'p',
                    'A child page',
                    'This is a child page',
                    'child');

        // // // // // // // // // // S I T E  1 // // // // // // // // // //

        $s1 = $this->makeSite($admin,
            'en', array('es'),
            'Corporatex Corp.',
            'corporatex',
            'Corporate Site');

            $s1_1 = $this->makePage($admin, $s1, null, 'p',
                'Home',
                null,
                'home',
                <<<CONT
            <div id="banner-container">
                <div id="banner-text">
                    We build apps to<br>increase your productivity<br>and SALES
                </div>
            </div>

            <div class="center-content">
                <div class="info-block">
                    <div class="center-content">
                        <i class="fa fa-5x fa-cloud"></i>
                    </div>
                    <h3>Lorem ipsum dolor</h3>
                    <p>Pellentesque diam ligula, egestas eget rhoncus vel, hendrerit in sem. Viva mus laoreet erat sit amet ante ullamcorper vestibulum. Viva mus laoreet erat sit amet ante ullamcorper vestibulum. Duis porta, nisl sed cursus volutpat, est nulla placerat.</p>
                    <a class="more" href="#">READ MORE</a>
                </div>

                <div class="info-block">
                    <div class="center-content">
                        <i class="fa fa-5x fa-tachometer"></i>
                    </div>
                    <h3>Maecenas tempus</h3>
                    <p>Pellentesque diam ligula, egestas eget rhoncus vel, hendrerit in sem. Viva mus laoreet erat sit amet ante ullamcorper vestibulum. Viva mus laoreet erat sit amet ante ullamcorper vestibulum.</p>
                    <a class="more" href="#">READ MORE</a>
                </div>

                <div class="info-block">
                    <div class="center-content">
                        <i class="fa fa-5x fa-bookmark"></i>
                    </div>
                    <h3>Vivamus elementum</h3>
                    <p>Pellentesque diam ligula, egestas eget rhoncus vel, hendrerit in sem. Viva mus laoreet erat sit amet ante ullamcorper vestibulum. Viva mus laoreet erat sit amet ante ullamcorper vestibulum. Duis porta, nisl sed cursus volutpat, est nulla placerat.</p>
                    <a class="more" href="#">READ MORE</a>
                </div>
            </div>

            <div id="us">
                <div class="margin-as-padding">
                    <h4>Corporatex</h4>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque diam ligula, egestas eget rhoncus vel, hendrerit in sem. Vivamus laoreet era  sit amet ante ullamcorper vestibulum. Duis porta, nisl sed cursus volutpat, est nulla placerat mauris, id viverra leo ante sed leo. Vivamus fermentum dignissim sem, quis pellentesque dolor posuere vel. In vitae lorem ligula, a congue nibh. Lorem ipsum dolor sit amet.<a href="#">Read More</a></p>
                </div>
            </div>

            <div id="data-container">
            <div class="margin-as-padding">
                <div id="block-why-us">
                    <h4>Why choose us?</h4>
                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. </p>
                </div>

                <div id="block-services">
                    <h4>Our services</h4>
                    <ul>
                        <li><a href="#">Lorem ipsum dolor sit amet</a></li>
                        <li><a href="#"> Aenean commodo ligula eget dolor</a></li>
                        <li><a href="#">Cum sociis natoque penatibus</a></li>
                        <li><a href="#">Nulla consequat massa quis enim</a></li>
                        <li><a href="#">Donec pede justo, fringilla vel</a></li>
                        <li><a href="#">Aliquet nec, vulputate eget, arcu.</a></li>
                    </ul>
                </div>

                <div id="block-news">
                    <h4>Latest news</h4>

                    <span> April 20, 2014</span>
                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.</p>

                    <span> March 29, 2014</span>
                    <p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu.</p>
                </div>
            </div>
            </div>
        </section>
CONT
                );

            $s1_2 = $this->makePage($suser, $s1, null, 'p',
                'About Us',
                'Why do we exist',
                'us');

            $s1_3 = $this->makePage($admin, $s1, null, 'p',
                'Services',
                'Ors services ready to help you',
                'services');

                    $s1_3_1 = $this->makePage($suser, $s1, $s1_3, 'p',
                        'Careers',
                        '',
                        'careers');

                    $s1_3_2 = $this->makePage($suser, $s1, $s1_3, 'p',
                        'Courses',
                        'Some courses',
                        'courses');

            $s1_4 = $this->makePage($admin, $s1, null, 'p',
                'Blog',
                'What are we on?',
                'blog');

            $s1_5 = $this->makePage($admin, $s1, null, 'l',
                'Contact Us',
                'Get in touch',
                'contact',
                '/contact');

        $this->manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    /**
     * 
     * @param string $userName
     * @param string $password
     * @param string $email
     * @param boolean $isAdmin
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    private function makeUser($userName, $password, $email, $isAdmin)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        /* @var $user \Application\Sonata\UserBundle\Entity\User */
        $user = $userManager->createUser();
        $user->setUsername($userName);
        $user->setPlainPassword($password);
        $user->setEmail($email);
        $user->setEnabled(true);
        $user->setLocked(false);
        $user->setSuperAdmin($isAdmin);
        $userManager->updateUser($user);
        return $user;
    }

    /**
     * 
     * @param \Application\Sonata\UserBundle\Entity\User $author
     * @param string $mainLang
     * @param array $translations
     * @param string $title
     * @param string $slug
     * @param string $description
     * @return \Allegro\SitesBundle\Entity\Site
     */
    private function makeSite($author, $mainLang, $translations, $title, $slug, $description)
    {
        $entity = new Site();

        $entity->setCreatedBy($author);
        $entity->setMainLanguage($mainLang);
        $entity->setTranslationLanguages($translations);
        $entity->setTitle($title);
        $entity->setSlug($slug);
        $entity->setDescription($description);

        $this->manager->persist($entity);
        return $entity;
    }

    /**
     * 
     * @param \Application\Sonata\UserBundle\Entity\User $author
     * @param \Allegro\SitesBundle\Entity\Site $site
     * @param Page $parentPage
     * @param string $type
     * @param string $title
     * @param string $description
     * @param string $slug
     * @param string $content
     * @return \Allegro\SitesBundle\Entity\Page
     */
    private function makePage($author, Site $site, $parentPage, $type,
    $title, $description, $slug, $content = null)
    {
        $entity = new Page();

        $entity->setMainLanguage($site->getMainLanguage());
        $entity->setCreatedBy($author);
        $entity->setSite($site);
        $entity->setParent($parentPage);
        $entity->setType($type);
        $entity->setTitle($title);
        $entity->setSlug($slug);
        $entity->setDescription($description);
        $entity->setBody(null !== $content ? $content : $this->getLorem());

        $this->manager->persist($entity);
        return $entity;
    }

    private function getLorem()
    {
        return <<< LOREM
<b>Lorem ipsum dolor sit amet</b>, <i>consectetur adipiscing elit</i>. Integer nec odio. Praesent
libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum
imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper
porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
<br><br>
Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
himenaeos. Curabitur sodales ligula in libero. Sed dignissim lacinia nunc. Curabitur
tortor. Pellentesque nibh. Aenean quam. In scelerisque sem at dolor. Maecenas mattis.
Sed convallis tristique sem. Proin ut ligula vel nunc egestas porttitor. Morbi lectus
risus, iaculis vel, suscipit quis, luctus non, massa.
<br><br>
Fusce ac turpis quis ligula lacinia aliquet. Mauris ipsum. Nulla metus metus,
ullamcorper vel, tincidunt sed, euismod in, nibh. Quisque volutpat condimentum velit.
Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
himenaeos. Nam nec ante. Sed lacinia, urna non tincidunt mattis, tortor neque
adipiscing diam, a cursus ipsum ante quis turpis. Nulla facilisi. Ut fringilla.
Suspendisse potenti. Nunc feugiat mi a tellus consequat imperdiet. Vestibulum sapien.
<br><br>
Proin quam. Etiam ultrices. Suspendisse in justo eu magna luctus suscipit. Sed
lectus. Integer euismod lacus luctus magna. Quisque cursus, metus vitae pharetra
auctor, sem massa mattis sem, at interdum magna augue eget diam. Vestibulum ante
ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Morbi
lacinia molestie dui. Praesent blandit dolor. Sed non quam. In vel mi sit amet
augue congue elementum. Morbi in ipsum sit amet pede facilisis laoreet. Donec lacus
nunc, viverra nec, blandit vel, egestas et, augue.
<br><br>
Vestibulum tincidunt malesuada tellus. Ut ultrices ultrices enim. Curabitur sit
amet mauris. Morbi in dui quis est pulvinar ullamcorper. Nulla facilisi. Integer
lacinia sollicitudin massa. Cras metus. Sed aliquet risus a tortor. Integer id
quam. Morbi mi. Quisque nisl felis, venenatis tristique, dignissim in, ultrices sit
amet, augue. Proin sodales libero eget ante.
LOREM;
    }
}
