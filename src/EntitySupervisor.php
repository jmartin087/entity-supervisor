<?php


namespace Grom\EntitySupervisor;

use Grom\EntitySupervisor\Entity\IdentityInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class EntitySupervisor
 * Gere l'unicité des object créé par les requetes on ne garde
 * @package Lequipe\Sports\Domain\Service
 */
class EntitySupervisor
{
    /**
     * @var array Tableau contenant la liste des object reference a plat
     */
    protected $objectList;

    /**
     * Ajout d'une reference dans $objectList
     * @param IdentityInterface $entity
     */
    protected function add($entity){
        $ref=&$entity;
        $ref=$this->mergeEntity($ref,$entity);
        $this->objectList[$this->getIdentity($entity)]=$ref;
        return $ref;
    }

    /**
     * ajoute/update une reference et retourne la reference
     * @param IdentityInterface $entity
     */
    public function getReferenceAndMerge($entity){
        if($saveEntity=$this->get($entity)){
            $saveEntity=$this->mergeEntity($saveEntity,$entity);
        }else{
            $saveEntity=$this->add($entity);
        }

        return $saveEntity;
    }

    /**
     * vérifie si un reference existe dans $objectList
     * @param $entity
     * @return bool
     */
    protected function exist($entity){
         $identity=$this->getIdentity($entity);
         return !empty($identity) && isset($this->objectList[$identity]) ;
    }

    /**
     * récupere la reference d'une entité
     * @param $entity
     * @return bool
     */
    public function get($entity){
        return $this->exist($entity)?$this->objectList[$this->getIdentity($entity)]:false;
    }

    /**
     * Recupere la clé unique de la reference a une entité
     * @param $entity
     * @return mixed
     */
    public function getIdentity($entity){
        return $entity->getIdentity();
    }


    /**
     * Merge le contenu d'une entité dans sa réference
     * si le champ reference est vide alors on le rempli
     * si le contenu est une entité alors on merge
     * si le champ de reference est déja rempli on ne fait rien
     * @param $reference
     * @param $entity
     * @return mixed
     * @throws \Exception
     */
    private function mergeEntity($reference, $entity) {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $referenceClass=get_class($reference);
        $entityClass=get_class($entity);

        if($entityClass != $referenceClass){
            throw new \Exception("impossible to merge ".get_class($entity)."and ".get_class($reference));
        }

        $referenceProperties=$this->getAllObjectProperties($reference);

        foreach ($referenceProperties as $prop){
            if($propertyAccessor->isWritable($reference,$prop) && $propertyAccessor->isReadable($entity,$prop)){
                $valEntity=$propertyAccessor->getValue($entity,$prop);
                $valRef=$propertyAccessor->getValue($reference,$prop);

                /*if(is_object($valRef) && $valRef instanceof IdentityInterface){
                    $valRef=$this->getReferenceAndMerge($valRef);
                }*/

                if(is_object($valEntity) && $valEntity instanceof IdentityInterface){
                    $valEntity=$this->getReferenceAndMerge($valEntity);
                    $propertyAccessor->setValue($reference,$prop,$valEntity);
                }elseif(empty($valRef)){
                    $propertyAccessor->setValue($reference,$prop,$valEntity);
                }

            }
        }

        return $reference;
    }

    /**
     * on recupere toutes les propriétés public possible d'une classe
     * @param $object
     * @return array
     */
    private function getAllObjectProperties($object){
        $properties=array_keys(get_object_vars($object));
        $methods=get_class_methods($object);
        foreach( $methods as $method){
            $properties[]=preg_replace('@^(set|is|has|get)@',"",$method);
        }

        return $properties;
    }

}