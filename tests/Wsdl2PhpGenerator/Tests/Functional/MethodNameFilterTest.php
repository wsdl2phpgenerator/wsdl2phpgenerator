<?php

namespace Wsdl2PhpGenerator\Tests\Functional;


class MethodNameFilterTest extends FunctionalTestCase {

    public function testFilterByMethodName() {

    }

    protected function configureOptions()
    {
        // TODO: Remove namespace and createAccessors options.
        // Testing these belong in a separate class.
        $this->config->set('methodNames', array('Get_Book'));
    }
    /**
     * @return string The path to the WSDL to generate code from.
     */
    protected function getWsdlPath() {
        return $this->fixtureDir . '/abstract/book_shell.wsdl';
    }
}