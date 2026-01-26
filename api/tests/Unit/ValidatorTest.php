<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utils\Validator;

/**
 * Tests unitaires pour la classe Validator.
 *
 * ATTENTION: Le test testValidateReturnsArray échoue actuellement car
 * Validator::validate() retourne void au lieu d'un array.
 * Ce bug cause l'erreur "array_merge(): Argument #2 must be of type array, null given"
 * dans PromoCodeController::update() ligne 237.
 */
class ValidatorTest extends TestCase
{
    // =========================================================================
    // TESTS DE RETOUR DE validate()
    // =========================================================================

    /**
     * Test que validate() retourne un array vide quand il n'y a pas d'erreurs.
     *
     * CE TEST ÉCHOUE ACTUELLEMENT car validate() retourne void/null.
     * C'est le bug qui cause l'erreur dans PromoCodeController::update().
     */
    public function testValidateReturnsEmptyArrayWhenValid(): void
    {
        $validator = new Validator([
            'name' => 'Test Name',
            'email' => 'test@example.com'
        ]);

        $validator->required('name');
        $validator->email('email');

        // Ce test va échouer car validate() retourne void/null au lieu d'un array
        $result = $validator->validate();

        $this->assertIsArray($result, 'validate() devrait retourner un array');
        $this->assertEmpty($result, 'validate() devrait retourner un array vide quand il n\'y a pas d\'erreurs');
    }

    /**
     * Test que getErrors() retourne un array vide quand il n'y a pas d'erreurs.
     */
    public function testGetErrorsReturnsEmptyArrayWhenValid(): void
    {
        $validator = new Validator([
            'name' => 'Test Name',
            'email' => 'test@example.com'
        ]);

        $validator->required('name');
        $validator->email('email');

        $errors = $validator->getErrors();

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    /**
     * Test que isValid() retourne true quand il n'y a pas d'erreurs.
     */
    public function testIsValidReturnsTrueWhenValid(): void
    {
        $validator = new Validator([
            'name' => 'Test Name',
            'email' => 'test@example.com'
        ]);

        $validator->required('name');
        $validator->email('email');

        $this->assertTrue($validator->isValid());
    }

    /**
     * Test que getErrors() retourne un array avec les erreurs.
     */
    public function testGetErrorsReturnsErrorsWhenInvalid(): void
    {
        $validator = new Validator([
            'name' => '',
            'email' => 'invalid-email'
        ]);

        $validator->required('name');
        $validator->email('email');

        $errors = $validator->getErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    /**
     * Test que isValid() retourne false quand il y a des erreurs.
     */
    public function testIsValidReturnsFalseWhenInvalid(): void
    {
        $validator = new Validator([
            'name' => '',
            'email' => 'invalid'
        ]);

        $validator->required('name');
        $validator->email('email');

        $this->assertFalse($validator->isValid());
    }

    // =========================================================================
    // TESTS DES RÈGLES DE VALIDATION
    // =========================================================================

    public function testRequiredValidation(): void
    {
        // Field present and not empty
        $validator = new Validator(['field' => 'value']);
        $validator->required('field');
        $this->assertTrue($validator->isValid());

        // Field missing
        $validator = new Validator([]);
        $validator->required('field');
        $this->assertFalse($validator->isValid());

        // Field empty string
        $validator = new Validator(['field' => '']);
        $validator->required('field');
        $this->assertFalse($validator->isValid());

        // Field whitespace only
        $validator = new Validator(['field' => '   ']);
        $validator->required('field');
        $this->assertFalse($validator->isValid());
    }

    public function testEmailValidation(): void
    {
        // Valid email
        $validator = new Validator(['email' => 'test@example.com']);
        $validator->email('email');
        $this->assertTrue($validator->isValid());

        // Invalid email
        $validator = new Validator(['email' => 'not-an-email']);
        $validator->email('email');
        $this->assertFalse($validator->isValid());

        // Missing email - should not fail (only validates if present)
        $validator = new Validator([]);
        $validator->email('email');
        $this->assertTrue($validator->isValid());
    }

    public function testMinLengthValidation(): void
    {
        // Meets minimum
        $validator = new Validator(['field' => 'hello']);
        $validator->minLength('field', 3);
        $this->assertTrue($validator->isValid());

        // Below minimum
        $validator = new Validator(['field' => 'hi']);
        $validator->minLength('field', 3);
        $this->assertFalse($validator->isValid());

        // Missing field - should not fail
        $validator = new Validator([]);
        $validator->minLength('field', 3);
        $this->assertTrue($validator->isValid());
    }

    public function testMaxLengthValidation(): void
    {
        // Within maximum
        $validator = new Validator(['field' => 'hello']);
        $validator->maxLength('field', 10);
        $this->assertTrue($validator->isValid());

        // Exceeds maximum
        $validator = new Validator(['field' => 'this is a very long string']);
        $validator->maxLength('field', 10);
        $this->assertFalse($validator->isValid());
    }

    public function testNumericValidation(): void
    {
        // Valid numeric
        $validator = new Validator(['field' => '123']);
        $validator->numeric('field');
        $this->assertTrue($validator->isValid());

        // Valid float
        $validator = new Validator(['field' => '123.45']);
        $validator->numeric('field');
        $this->assertTrue($validator->isValid());

        // Invalid
        $validator = new Validator(['field' => 'abc']);
        $validator->numeric('field');
        $this->assertFalse($validator->isValid());
    }

    public function testInArrayValidation(): void
    {
        $allowed = ['a', 'b', 'c'];

        // Valid value
        $validator = new Validator(['field' => 'a']);
        $validator->inArray('field', $allowed);
        $this->assertTrue($validator->isValid());

        // Invalid value
        $validator = new Validator(['field' => 'd']);
        $validator->inArray('field', $allowed);
        $this->assertFalse($validator->isValid());
    }

    public function testChainedValidations(): void
    {
        $validator = new Validator([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => '25'
        ]);

        $validator
            ->required('name')
            ->minLength('name', 2)
            ->maxLength('name', 50)
            ->email('email')
            ->numeric('age');

        $this->assertTrue($validator->isValid());
    }

    // =========================================================================
    // TESTS PHONE NORMALIZATION
    // =========================================================================

    /**
     * @dataProvider phoneNormalizationProvider
     */
    public function testNormalizePhone(string $input, string $expected, string $countryCode = '+33'): void
    {
        $result = Validator::normalizePhone($input, $countryCode);
        $this->assertEquals($expected, $result);
    }

    public static function phoneNormalizationProvider(): array
    {
        return [
            // French numbers
            'french_with_leading_zero' => ['0612345678', '+33612345678'],
            'french_with_spaces' => ['06 12 34 56 78', '+33612345678'],
            'french_with_dots' => ['06.12.34.56.78', '+33612345678'],
            'french_with_dashes' => ['06-12-34-56-78', '+33612345678'],
            'french_already_international' => ['+33612345678', '+33612345678'],
            'french_with_00' => ['0033612345678', '+33612345678'],

            // Belgian numbers
            'belgian_with_leading_zero' => ['0412345678', '+32412345678', '+32'],
            'belgian_already_international' => ['+32412345678', '+32412345678', '+32'],
        ];
    }

    public function testNormalizePhoneWithNull(): void
    {
        $this->assertNull(Validator::normalizePhone(null));
        $this->assertNull(Validator::normalizePhone(''));
        $this->assertNull(Validator::normalizePhone('   '));
    }

    // =========================================================================
    // TESTS PHONE DISPLAY FORMATTING
    // =========================================================================

    /**
     * @dataProvider phoneDisplayProvider
     */
    public function testFormatPhoneForDisplay(?string $input, ?string $expected): void
    {
        $result = Validator::formatPhoneForDisplay($input);
        $this->assertEquals($expected, $result);
    }

    public static function phoneDisplayProvider(): array
    {
        return [
            'french_mobile' => ['+33612345678', '+33 6 12 34 56 78'],
            'french_landline' => ['+33123456789', '+33 1 23 45 67 89'],
            'null' => [null, null],
            'empty' => ['', null],
        ];
    }
}
