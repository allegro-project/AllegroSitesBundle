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

class DateTimeToStringTransformer implements DataTransformerInterface
{

    private $format;
    
    public function __construct($format)
    {
        $this->format = $format;
    }
    
    /**
     * Transforms a datetime object to a string.
     *
     * @param  DateTime|null $date
     * @return string
     */
    public function transform($dateTime)
    {
        return null === $dateTime
            ? ''
            : $dateTime->format($this->format);
    }

    /**
     * Transforms a string date to a DateTime object.
     *
     * @param  string $dateTime
     *
     * @return DateTime|null
     *
     * @throws TransformationFailedException if cannot transform to DateTime.
     */
    public function reverseTransform($dateTime)
    {
        if (empty($dateTime)) {
            return null;
        }

        $dt = DateTime::createFromFormat($this->format, $dateTime);
        if (null === $dt) {
            throw new TransformationFailedException(sprintf(
                "Unable to transform '%s' to a DateTime object",
                $dateTime
            ));
        }

        return $dt;
    }
}
