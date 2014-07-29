<?php
/**
 * Created by PhpStorm.
 * User: ross
 * Date: 7/19/14
 * Time: 8:11 AM
 */

namespace Tetranz\Select2EntityBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

class EntitiesToPropertyTransformer implements DataTransformerInterface
{
    protected $em;
    protected $className;
    protected $textProperty;

    public function __construct(EntityManager $em, $class, $textProperty)
    {
        $this->em = $em;
        $this->className = $class;
        $this->textProperty = $textProperty;
    }

    public function transform($entities)
    {
        if (count($entities) == 0) {
            return '';
        }

        // return an array of initial values as html encoded json
        $data = array();

        foreach($entities as $entity) {

            $text = is_null($this->textProperty)
                    ? (string) $entity
                    : $entity->{'get' . $this->textProperty}();

            $data[] = array(
                'id' => $entity->getId(),
                'text' => $text
            );
        }

        return htmlspecialchars(json_encode($data));
    }

    public function reverseTransform($values)
    {
        // remove the 'magic' non-blank value added in fields.html.twig
        $values = ltrim($values, 'x,');

        if (null === $values || '' === $values) {
            return new ArrayCollection();
        }

        $ids = explode(',', $values);

        // get multiple entities with one query
        $entities = $this->em->createQueryBuilder()
                ->select('entity')
                ->from($this->className, 'entity')
                ->where('entity.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();

        return $entities;
    }
}
