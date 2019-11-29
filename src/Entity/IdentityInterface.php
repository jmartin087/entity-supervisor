<?php


namespace Grom\EntitySupervisor\Entity;


/**
 * Interface IdentityInterface
 * interface définissant la fonction getIdentity que doit implementer une entité pour être géré de maniere unique par l'EntitySupervisor
 * @package Lequipe\Sports\Domain\Entity
 */
interface IdentityInterface
{

    /**
     * retourne une chaine qui sera uiid de l'object
     * @return string
     */
    public function getIdentity():string;
}