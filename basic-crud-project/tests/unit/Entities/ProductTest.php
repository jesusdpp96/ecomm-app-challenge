<?php

namespace Tests\Unit\Entities;

use App\Entities\Product;
use CodeIgniter\Test\CIUnitTestCase;
use DateTime;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Comprehensive test suite for Product entity covering edge cases
 */
class ProductTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ========== Constructor Tests ==========

    public function testConstructorWithValidData()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->assertEquals(1, $product->id);
        $this->assertEquals('Test Product', $product->title);
        $this->assertEquals(99.99, $product->price);
        $this->assertInstanceOf(DateTime::class, $product->created_at);
    }


    public function testConstructorWithCustomCreatedAt()
    {
        $date = new DateTime('2023-01-01 12:00:00');
        $product = new Product(1, 'Test Product', 99.99, $date);
        
        $this->assertEquals($date, $product->created_at);
    }

    public function testConstructorWithStringCreatedAt()
    {
        $product = new Product(1, 'Test Product', 99.99, '2023-01-01 12:00:00');
        
        $this->assertInstanceOf(DateTime::class, $product->created_at);
        $this->assertEquals('2023-01-01 12:00:00', $product->created_at->format('Y-m-d H:i:s'));
    }

    // ========== Validation Edge Cases ==========

    public function testConstructorThrowsExceptionForNullId()
    {
        $this->expectException(ValidationException::class);
        new Product(null, 'Test Product', 99.99);
    }

    public function testConstructorThrowsExceptionForEmptyTitle()
    {
        $this->expectException(ValidationException::class);
        new Product(1, '', 99.99);
    }

    public function testConstructorThrowsExceptionForNullTitle()
    {
        $this->expectException(ValidationException::class);
        // @phpstan-ignore-next-line - Intentionally passing null to test TypeError
        new Product(1, null, 99.99);
    }

    public function testConstructorThrowsExceptionForTitleTooLong()
    {
        $this->expectException(ValidationException::class);
        $longTitle = str_repeat('a', 256); // 256 characters, exceeds 255 limit
        new Product(1, $longTitle, 99.99);
    }

    public function testConstructorThrowsExceptionForNegativePrice()
    {
        $this->expectException(ValidationException::class);
        new Product(1, 'Test Product', -1.0);
    }

    public function testConstructorThrowsExceptionForZeroPrice()
    {
        $this->expectException(ValidationException::class);
        new Product(1, 'Test Product', 0.0);
    }

    public function testConstructorThrowsExceptionForPriceTooHigh()
    {
        $this->expectException(ValidationException::class);
        new Product(1, 'Test Product', 1000000.0); // Exceeds 999999.99 limit
    }

    public function testConstructorThrowsExceptionForNegativeId()
    {
        $this->expectException(ValidationException::class);
        new Product(-1, 'Test Product', 99.99);
    }

    public function testConstructorThrowsExceptionForZeroId()
    {
        $this->expectException(ValidationException::class);
        new Product(0, 'Test Product', 99.99);
    }

    // ========== Boundary Value Tests ==========

    public function testTitleBoundaryValues()
    {
        // Test minimum valid title (1 character)
        $product = new Product(1, 'A', 99.99);
        $this->assertEquals('A', $product->title);

        // Test maximum valid title (255 characters)
        $maxTitle = str_repeat('a', 255);
        $product = new Product(1, $maxTitle, 99.99);
        $this->assertEquals($maxTitle, $product->title);
    }

    public function testPriceBoundaryValues()
    {
        // Test minimum valid price (just above 0)
        $product = new Product(1, 'Test Product', 0.01);
        $this->assertEquals(0.01, $product->price);

        // Test maximum valid price
        $product = new Product(1, 'Test Product', 999999.99);
        $this->assertEquals(999999.99, $product->price);
    }

    public function testIdBoundaryValues()
    {
        // Test minimum valid id
        $product = new Product(1, 'Test Product', 99.99);
        $this->assertEquals(1, $product->id);

        // Test large valid id
        $product = new Product(PHP_INT_MAX, 'Test Product', 99.99);
        $this->assertEquals(PHP_INT_MAX, $product->id);
    }

    // ========== Sanitization Edge Cases ==========

    public function testTitleSanitization()
    {
        // Test HTML tag removal
        $product = new Product(1, '<script>alert("xss")</script>Clean Title', 99.99);
        $this->assertStringNotContainsString('<script>', $product->title);
        $this->assertStringContainsString('Clean Title', $product->title);

        // Test null byte removal
        $product = new Product(1, "Title\0WithNull", 99.99);
        $this->assertStringNotContainsString("\0", $product->title);

        // Test control character removal
        $product = new Product(1, "Title\x0BWithControl", 99.99);
        $this->assertStringNotContainsString("\x0B", $product->title);

        // Test whitespace trimming
        $product = new Product(1, '  Trimmed Title  ', 99.99);
        $this->assertEquals('Trimmed Title', $product->title);
    }

    public function testPriceSanitization()
    {
        // Test string with currency symbols
        $product = new Product(1, 'Test Product', '$99.99');
        $this->assertEquals(99.99, $product->price);

        // Test string with commas
        $product = new Product(1, 'Test Product', '1,234.56');
        $this->assertEquals(1234.56, $product->price);

        // Test invalid numeric string defaults to 0.0 (which should fail validation)
        $this->expectException(ValidationException::class);
        new Product(1, 'Test Product', 'invalid');
    }

    // ========== Immutable Methods Tests ==========

    public function testWithTitleCreatesNewInstance()
    {
        $original = new Product(1, 'Original Title', 99.99);
        $updated = $original->withTitle('New Title');

        $this->assertNotSame($original, $updated);
        $this->assertEquals('Original Title', $original->title);
        $this->assertEquals('New Title', $updated->title);
        $this->assertEquals($original->id, $updated->id);
        $this->assertEquals($original->price, $updated->price);
    }

    public function testWithPriceCreatesNewInstance()
    {
        $original = new Product(1, 'Test Product', 99.99);
        $updated = $original->withPrice(149.99);

        $this->assertNotSame($original, $updated);
        $this->assertEquals(99.99, $original->price);
        $this->assertEquals(149.99, $updated->price);
        $this->assertEquals($original->id, $updated->id);
        $this->assertEquals($original->title, $updated->title);
    }

    public function testWithIdCreatesNewInstance()
    {
        $original = new Product(1, 'Test Product', 99.99);
        $updated = $original->withId(2);

        $this->assertNotSame($original, $updated);
        $this->assertEquals(1, $original->id);
        $this->assertEquals(2, $updated->id);
        $this->assertEquals($original->title, $updated->title);
        $this->assertEquals($original->price, $updated->price);
    }

    public function testWithCreatedAtCreatesNewInstance()
    {
        $original = new Product(1, 'Test Product', 99.99);
        $newDate = new DateTime('2023-01-01 12:00:00');
        $updated = $original->withCreatedAt($newDate);

        $this->assertNotSame($original, $updated);
        $this->assertNotEquals($original->created_at, $updated->created_at);
        $this->assertEquals($newDate, $updated->created_at);
    }

    public function testWithAttributesCreatesNewInstance()
    {
        $original = new Product(1, 'Original Title', 99.99);
        $updated = $original->withAttributes([
            'id' => 2,
            'title' => 'New Title',
            'price' => 149.99
        ]);

        $this->assertNotSame($original, $updated);
        $this->assertEquals(1, $original->id);
        $this->assertEquals(2, $updated->id);
        $this->assertEquals('Original Title', $original->title);
        $this->assertEquals('New Title', $updated->title);
    }

    // ========== Immutability Tests ==========

    public function testDirectPropertySetterThrowsException()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product entity is immutable');
        $product->title = 'New Title';
    }

    public function testDirectPriceSetterThrowsException()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product entity is immutable');
        $product->price = 149.99;
    }

    // ========== Validation Methods Tests ==========

    public function testIsValidReturnsTrueForValidProduct()
    {
        $product = new Product(1, 'Test Product', 99.99);
        $this->assertTrue($product->isValid());
    }

    public function testGetValidationErrorsReturnsEmptyForValidProduct()
    {
        $product = new Product(1, 'Test Product', 99.99);
        $this->assertEmpty($product->getValidationErrors());
    }

    // ========== Array Conversion Tests ==========

    public function testFromArrayWithValidData()
    {
        $data = [
            'id' => 1,
            'title' => 'Test Product',
            'price' => 99.99,
            'created_at' => '2023-01-01 12:00:00'
        ];

        $product = Product::fromArray($data);

        $this->assertEquals(1, $product->id);
        $this->assertEquals('Test Product', $product->title);
        $this->assertEquals(99.99, $product->price);
        $this->assertInstanceOf(DateTime::class, $product->created_at);
    }

    public function testFromArrayWithMissingData()
    {
        $this->expectException(ValidationException::class);
        Product::fromArray([]); // Missing required fields should cause validation to fail
    }

    public function testToArrayReturnsCorrectFormat()
    {
        $product = new Product(1, 'Test Product', 99.99);
        $array = $product->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('price', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertIsString($array['created_at']); // Should be formatted as string
    }

    public function testFromArrayWithPartialData()
    {
        $data = [
            'title' => 'Test Product',
            'price' => 99.99
        ];

        $this->expectException(ValidationException::class);
        Product::fromArray($data);
    }

    // ========== Formatting Methods Tests ==========

    public function testGetFormattedPriceWithDefaultCurrency()
    {
        $product = new Product(1, 'Test Product', 99.99);
        $this->assertEquals('$99.99', $product->getFormattedPrice());
    }

    public function testGetFormattedPriceWithCustomCurrency()
    {
        $product = new Product(1, 'Test Product', 99.99);
        $this->assertEquals('€99.99', $product->getFormattedPrice('€'));
    }

    public function testGetFormattedPriceWithLargeNumber()
    {
        $product = new Product(1, 'Test Product', 12345.67);
        $this->assertEquals('$12,345.67', $product->getFormattedPrice());
    }

    public function testGetFormattedDateWithDefaultFormat()
    {
        $date = new DateTime('2023-01-01 12:30:45');
        $product = new Product(1, 'Test Product', 99.99, $date);
        $this->assertEquals('2023-01-01 12:30:45', $product->getFormattedDate());
    }

    public function testGetFormattedDateWithCustomFormat()
    {
        $date = new DateTime('2023-01-01 12:30:45');
        $product = new Product(1, 'Test Product', 99.99, $date);
        $this->assertEquals('01/01/2023', $product->getFormattedDate('m/d/Y'));
    }

    // ========== Magic Methods Tests ==========

    public function testMagicGetterReturnsCorrectValues()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->assertEquals(1, $product->id);
        $this->assertEquals('Test Product', $product->title);
        $this->assertEquals(99.99, $product->price);
        $this->assertInstanceOf(DateTime::class, $product->created_at);
    }

    public function testMagicIssetReturnsCorrectValues()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->assertTrue(isset($product->id));
        $this->assertTrue(isset($product->title));
        $this->assertTrue(isset($product->price));
        $this->assertTrue(isset($product->created_at));
        $this->assertFalse(isset($product->nonexistent));
    }

    // ========== Special Characters and Unicode Tests ==========

    public function testTitleWithUnicodeCharacters()
    {
        $product = new Product(1, 'Título con acentos áéíóú', 99.99);
        $this->assertStringContainsString('Título', $product->title);
        $this->assertStringContainsString('acentos', $product->title);
    }

    public function testTitleWithSpecialCharacters()
    {
        $product = new Product(1, 'Product & Co. "Special" \'Quotes\'', 99.99);
        // Should be sanitized but still contain the essence
        $this->assertStringContainsString('Product', $product->title);
    }

    // ========== Memory and Performance Edge Cases ==========

    public function testLargeValidTitle()
    {
        $largeTitle = str_repeat('A', 255);
        $product = new Product(1, $largeTitle, 99.99);
        $this->assertEquals(255, strlen($product->title));
    }

    public function testVerySmallPrice()
    {
        $product = new Product(1, 'Test Product', 0.01);
        $this->assertEquals(0.01, $product->price);
    }

    public function testMaximumValidPrice()
    {
        $product = new Product(1, 'Test Product', 999999.99);
        $this->assertEquals(999999.99, $product->price);
    }

    // ========== Error Handling Edge Cases ==========

    public function testInvalidDateStringThrowsException()
    {
        $this->expectException(\Exception::class);
        new Product(1, 'Test Product', 99.99, 'invalid-date-string');
    }

    public function testWithMethodsValidateNewValues()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->expectException(ValidationException::class);
        $product->withTitle(''); // Empty title should fail validation
    }

    public function testWithPriceValidatesNewValue()
    {
        $product = new Product(1, 'Test Product', 99.99);
        
        $this->expectException(ValidationException::class);
        $product->withPrice(-1.0); // Negative price should fail validation
    }
}