<?php


use Grom\EntitySupervisor\Entity\IdentityInterface;
use Grom\EntitySupervisor\EntitySupervisor;
use PHPUnit\Framework\TestCase;

class EntitySupervisorTest extends TestCase
{
    public function testObjectWithIdentityCanBeRetrive(){
        $entity=$this->getSimpleEntity();
        $entity->id='123456';
        $entity->name='wazaaaa';

        $entitySupervisor=new EntitySupervisor();
        $ref=$entitySupervisor->getReferenceAndMerge($entity);

        self::assertEquals($entity->id,$ref->id);

    }

    public function testMerge2ObjectWithSameIdentity(){
        $entity=$this->getSimpleEntity();
        $entity->id='123456';
        $entity->name='wazaaaa';

        $entity2=$this->getSimpleEntity();
        $entity2->id='123456';
        $entity2->value='thevalue';

        $entitySupervisor=new EntitySupervisor();
        $ref=$entitySupervisor->getReferenceAndMerge($entity);
        $ref2=$entitySupervisor->getReferenceAndMerge($entity2);

        self::assertEquals($ref->value,$ref2->value);
        self::assertEquals($ref->name,$ref2->name);

    }

    public function testNotMerge2ObjectWithDifferrentIdentity(){
        $entity=$this->getSimpleEntity();
        $entity->id='123456';
        $entity->name='wazaaaa';

        $entity2=$this->getSimpleEntity();
        $entity2->id='123457';
        $entity2->value='thevalue';

        $entitySupervisor=new EntitySupervisor();
        $ref=$entitySupervisor->getReferenceAndMerge($entity);
        $ref2=$entitySupervisor->getReferenceAndMerge($entity2);


        self::assertNotEquals($ref2->value,$ref->value);
        self::assertNotEquals($ref->name,$ref2->name);

    }

    public function testMerge2ObjectWithSetterAndGetter(){
        $entity=$this->getEntityWithGetterAndSetter();
        $entity->setId('123456')->setName('wazaaaa');

        $entity2=$this->getEntityWithGetterAndSetter();
        $entity2->setId('123456')->setValue('thevalue');

        $entitySupervisor=new EntitySupervisor();
        $ref=$entitySupervisor->getReferenceAndMerge($entity);
        $ref2=$entitySupervisor->getReferenceAndMerge($entity2);

        self::assertEquals($ref->getValue(),$ref2->getValue());
        self::assertEquals($ref->getName(),$ref2->getName());

    }

    public function testNotMerge2DifferrentObjects(){
        $entity=$this->getEntityWithObject();
        $entity->id='123456';
        $entity->name='wazaaaa';

        $entity2=$this->getSimpleEntity();
        $entity2->id='123456';
        $entity2->name='thevalue';

        $entitySupervisor=new EntitySupervisor();
        $ref=$entitySupervisor->getReferenceAndMerge($entity);
        $ref2=$entitySupervisor->getReferenceAndMerge($entity2);

        self::assertNotEquals($ref->getIdentity(),$ref2->getIdentity());
        self::assertNotEquals($ref->name,$ref2->name);

    }

    public function testSaveNestedObjectWithIdentity(){
        $entity=$this->getEntityWithObject();
        $entity->id='123456';
        $entity->name='wazaaaa';

        $entity2=$this->getSimpleEntity();
        $entity2->id='123';
        $entity2->name='thevalue';

        $entity3=$this->getSimpleEntity();
        $entity3->id='123';

        $entity->uniqueObject=$entity2;

        $entitySupervisor=new EntitySupervisor();
        $ref=$entitySupervisor->getReferenceAndMerge($entity);
        $ref2=$entitySupervisor->getReferenceAndMerge($entity3);

        self::assertEquals($ref2->name,$ref->uniqueObject->name);

    }

    protected function getSimpleEntity(){
        $entity=new class() implements IdentityInterface {
            public $id;
            public $name;
            public $value;
            public function getIdentity(): string
            {
                return __CLASS__.'-'.$this->id;
            }
        };
        return $entity;
    }

    protected function getEntityWithGetterAndSetter(){
        $entity=new class() implements IdentityInterface {
            protected $id;
            protected $name;
            protected $value;
            public function getIdentity(): string
            {
                return __CLASS__.'-'.$this->id;
            }

            /**
             * @return mixed
             */
            public function getId()
            {
                return $this->id;
            }

            /**
             * @param mixed $id
             * @return
             */
            public function setId($id)
            {
                $this->id = $id;
                return $this;
            }

            /**
             * @return mixed
             */
            public function getName()
            {
                return $this->name;
            }

            /**
             * @param mixed $name
             * @return
             */
            public function setName($name)
            {
                $this->name = $name;
                return $this;
            }

            /**
             * @return mixed
             */
            public function getValue()
            {
                return $this->value;
            }

            /**
             * @param mixed $value
             * @return
             */
            public function setValue($value)
            {
                $this->value = $value;
                return $this;
            }

        };
        return $entity;
    }

    protected function getEntityWithObject(){
        $entity=new class() implements IdentityInterface {
            public $id;
            public $name;
            public $uniqueObject;
            public $listObject;
            public function getIdentity(): string
            {
                return __CLASS__.'-'.$this->id;
            }
        };
        return $entity;
    }
}
