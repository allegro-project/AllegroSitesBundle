<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToStringTransformer implements DataTransformerInterface
{

    protected function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * Transforms a user object to a string.
     *
     * @param  User|null $user
     * @return string
     */
    public function transform($user)
    {
        if (null === $user) {
            return null;
        }

        return "$user";
    }

    /**
     * Transforms a user string to a user object.
     *
     * @param  string $user
     *
     * @return User|null
     *
     * @throws TransformationFailedException if cannot transform to User.
     */
    public function reverseTransform($user)
    {
        return null;
    }
}
