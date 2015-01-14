<?php
namespace Wsdl2PhpGenerator\Tests\Unit\Filter;

use Wsdl2PhpGenerator\ComplexType;
use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\ConfigInterface;
use Wsdl2PhpGenerator\Enum;
use Wsdl2PhpGenerator\Filter\ServiceOperationFilter;
use Wsdl2PhpGenerator\Operation;
use Wsdl2PhpGenerator\Service;

/**
 * Use test for the ServiceOperationFilter class.
 */
class ServiceOperationFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ServiceOperationFilter
     */
    private $sut;

    protected function setUp()
    {
        $this->config = new Config(array(
            'inputFile' => 'tst.wsdl',
            'outputDir' => 'test',
            'operationNames' => 'GetBook'

        ));
        $this->sut = new ServiceOperationFilter($this->config);
    }

    /**
     * Test that the service operation filter is able to reduce
     * operations and types based on operations.

     */
    public function testFilterReturnsFilteredServiceWithUsedTypesOnly()
    {
        $sourceService = $this->givenServiceWithOperations();

        $actualService = $this->sut->filter($sourceService);

        // Check that getAuthors and types for this not exists
        $this->assertNull($actualService->getOperation('GetAuthor'));
        $this->assertNull($actualService->getType('Method_Get_Authors_Response'));
        $this->assertNull($actualService->getType('Get_Authors_Response_Author'));
        $this->assertNull($actualService->getType('Method_Get_Authors_Request'));
        // Check that getBook and types exists
        $this->assertEquals($sourceService->getOperation('GetBook'), $actualService->getOperation('GetBook'));
        $this->assertEquals($sourceService->getType('Method_Get_Book_Response_BOOK'), $actualService->getType('Method_Get_Book_Response_BOOK'));
        $this->assertEquals($sourceService->getType('Book_Type_Enumeration'), $actualService->getType('Book_Type_Enumeration'));
        $this->assertEquals($sourceService->getType('Method_Get_Book_Response_BOOK_BOOK_NAME'), $actualService->getType('Method_Get_Book_Response_BOOK_BOOK_NAME'));
        $this->assertEquals($sourceService->getType('Get_Book_Type_Response'), $actualService->getType('Get_Book_Type_Response'));
        $this->assertEquals($sourceService->getType('Method_Get_Book_Request_BOOK'), $actualService->getType('Method_Get_Book_Request_BOOK'));
        $this->assertEquals($sourceService->getType('Get_Book_Type_Request'), $actualService->getType('Get_Book_Type_Request'));

    }

    /**
     * Setup and return a service with operations and types.
     *
     * @return Service
     */
    private function givenServiceWithOperations()
    {
        // Response GetBook types
        $responseBookName = new ComplexType($this->config, 'Method_Get_Book_Response_BOOK_BOOK_NAME');
        $responseBookName->addMember('string', 'bookName', false);
        $responseBook = new ComplexType($this->config, 'Method_Get_Book_Response_BOOK');
        $responseBook->addMember('int', 'bookId', false);
        // Base type example
        $responseBook->setBaseType($responseBookName);
        $returnGetBookType = new ComplexType($this->config, 'Get_Book_Type_Response');
        $returnGetBookType->addMember('Method_Get_Book_Response_BOOK', 'book_response', false);
        // Request GetBook types
        $bookType = new Enum($this->config, 'Book_Type_Enumeration', 'string');
        $bookType->addValue('fiction');
        $bookType->addValue('comedy');
        $requestBook = new ComplexType($this->config, 'Method_Get_Book_Request_BOOK');
        $requestBook->addMember('int', 'bookId', false);
        $requestBook->addMember('Book_Type_Enumeration', 'genre', false);
        $requestGetBook = new ComplexType($this->config, 'Get_Book_Type_Request');
        $requestGetBook->addMember('Method_Get_Book_Request_BOOK', 'book_request', false);
        // Operation GetBook
        $getBookOperation = new Operation('GetBook', 'Get_Book_Type_Request $request', 'Get Book', 'Get_Book_Type_Response');
        // Response GetAuthors type
        $responseAuthor = new ComplexType($this->config, 'Get_Authors_Response_Author');
        $responseAuthor->addMember('int', 'authorId', false);
        $responseAuthor->addMember('string', 'authorName', false);
        $returnGetAuthors = new ComplexType($this->config, 'Method_Get_Authors_Response');
        $returnGetAuthors->addMember('Get_Authors_Response_Author[]', 'Get_Authors_Response_Author', false);
        // Request GetAuthors type
        $requestGetAuthor = new ComplexType($this->config, 'Method_Get_Authors_Request');
        $requestGetAuthor->addMember('Method_Get_Book_Request_BOOK', 'book_request', false);
        // Operation GetAuthors
        $getAuthorsOperator = new Operation('GetAuthor', 'Method_Get_Authors_Request $request', 'Get Authors', 'Method_Get_Authors_Response');
        // Service creation
        $types = array(
            $responseBookName,
            $responseBook,
            $returnGetBookType,
            $requestBook,
            $requestGetBook,
            $responseAuthor,
            $returnGetAuthors,
            $requestGetAuthor,
            $bookType
        );
        $service = new Service($this->config, 'Book_Shell', $types, 'Book shells');
        $service->addOperation($getBookOperation);
        $service->addOperation($getAuthorsOperator);
        return $service;
    }
}
