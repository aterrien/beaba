<?php

namespace beaba\tests;

class Unit {
    protected $_tests;
    
    public function __construct( \Testify $tests ) {
        $this->_tests = $tests;
    }
    
    public function assert( $arg ) {
        $this->_tests->assert($arg);
        return $this;
    }
    
    public function assertEqual($arg1, $arg2) {
        $this->_tests->assertEqual($arg1, $arg2);
        return $this;
    }
    
    public function pass() {
        $this->_tests->pass();
        return $this;
    }
    
    public function fail() {
        $this->_tests->fail();
        return $this;
    }
}