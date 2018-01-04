<?php
/* ===========================================================================
 * Copyright (c) 2014-2017 The Opis Project
 *
 * Licensed under the MIT License
 * =========================================================================== */

namespace Opis\Closure\Test;

use stdClass;
use Closure;

class SerializeTest extends \PHPUnit_Framework_TestCase
{
    public function testCustomSerialization()
    {
        $f =  function ($value){
            return $value;
        };

        $a = new Abc($f);
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($u->test(true));
    }

    public function testCustomSerializationSameObjects()
    {
        $f =  function ($value){
            return $value;
        };

        $i = new Abc($f);
        $a = array($i, $i);
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));

        $this->assertTrue($u[0] === $u[1]);
    }

    public function testCustomSerializationThisObject1()
    {
        $a = new A2();
        $a = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertEquals('Hello, World!', $a->getPhrase());
    }

    public function testCustomSerializationThisObject2()
    {
        $a = new A2();
        $a = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($a->getEquality());
    }

    public function testCustomSerializationSameClosures()
    {
        $f =  function ($value){
            return $value;
        };

        $i = new Abc($f);
        $a = array($i, $i);
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($u[0]->getF() === $u[1]->getF());
    }

    public function testCustomSerializationSameClosures2()
    {
        $f =  function ($value){
            return $value;
        };

        $a = array(new Abc($f), new Abc($f));
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertTrue($u[0]->getF() === $u[1]->getF());
    }

    public function testPrivateMethodClone()
    {
        $a = new Clone1();
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($a));
        $this->assertEquals(1, $u->value());
    }

    public function testPrivateMethodClone2()
    {
        $a = new Clone1();
        $f = function () use($a){
            return $a->value();
        };
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
        $this->assertEquals(1, $u());
    }

    public function testNestedObjects()
    {
        $parent = new Entity();
        $child = new Entity();
        $parent->children[] = $child;
        $child->parent = $parent;

        $f = function () use($parent, $child){
            return $parent === $child->parent;
        };

        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($f));
        $this->assertTrue($u());
    }

    public function testNestedObjects2()
    {
        $child = new stdClass();
        $parent = new stdClass();
        $child->parent = $parent;
        $parent->childern = [$child];
        $parent->closure = function () use($child){
            return true;
        };
        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent))->closure;
        $this->assertTrue($u());
    }

    public function testNestedObjects3()
    {
        $obj = new \stdClass;
        $obj->closure = function ($arg) use ($obj) {
            return $arg === $obj;
        };

        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($obj));
        $c = $u->closure;
        $this->assertTrue($c($u));
    }

    public function testNestedObjects4()
    {
        $parent = new \stdClass;
        $child1 = new \stdClass;

        $child1->parent = $parent;

        $parent->closure = function ($p) use ($child1) {
            return $child1->parent === $p;
        };

        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent));
        $c = $u->closure;
        $this->assertTrue($c($u));
    }

    public function testNestedObjects5()
    {
        $parent = new \stdClass;
        $child1 = new \stdClass;
        $child2 = new \stdClass;

        $child1->parent = $parent;
        $child2->parent = $parent;

        $parent->closure = function ($p) use ($child1, $child2) {
            return $child1->parent === $child2->parent && $child1->parent === $p;
        };

        $u = \Opis\Closure\unserialize(\Opis\Closure\serialize($parent));
        $c = $u->closure;
        $this->assertTrue($c($u));
    }
}

class Abc
{
    private $f;

    public function __construct(Closure $f)
    {
        $this->f = $f;
    }

    public function getF()
    {
        return $this->f;
    }

    public function test($value)
    {
        $f = $this->f;
        return $f($value);
    }
}

class Clone1
{
    private $a = 1;

    private function __clone()
    {
    }

    public function value()
    {
        return $this->a;
    }
}

class Entity {
    public $parent;
    public $children = [];
}