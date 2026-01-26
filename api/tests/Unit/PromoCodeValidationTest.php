<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utils\Validator;

/**
 * Tests unitaires pour la validation des codes promo.
 *
 * ATTENTION: Ces tests documentent le bug dans PromoCodeController::update()
 * où $validator->validate() retourne void/null au lieu d'un array,
 * causant l'erreur "array_merge(): Argument #2 must be of type array, null given"
 *
 * Le pattern utilisé dans update() est :
 *   $validationErrors = $validator->validate();
 *   $errors = array_merge($errors, $validationErrors);
 *
 * Mais validate() retourne void, pas un array.
 */
class PromoCodeValidationTest extends TestCase
{
    // Constantes miroir de PromoCode model
    private const DISCOUNT_TYPES = ['percentage', 'fixed_amount', 'free_session'];
    private const APPLICATION_MODES = ['manual', 'automatic'];

    // =========================================================================
    // TESTS DU PATTERN DE VALIDATION UPDATE
    // =========================================================================

    /**
     * Simule le pattern de validation utilisé dans PromoCodeController::update()
     * Ce test ÉCHOUE car validate() retourne void au lieu d'un array.
     *
     * Code actuel bugué (ligne 236-237) :
     *   $validationErrors = $validator->validate();
     *   $errors = array_merge($errors, $validationErrors);
     */
    public function testUpdateValidationPatternWithValidData(): void
    {
        $existingPromo = [
            'id' => 'promo-123',
            'name' => 'Test Promo',
            'code' => 'TESTCODE',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'application_mode' => 'manual',
            'is_active' => true
        ];

        // Données de mise à jour valides
        $updateData = [
            'name' => 'Updated Promo Name',
            'discount_value' => 15
        ];

        $errors = [];
        $validator = new Validator($updateData);

        if (isset($updateData['name'])) {
            $validator->minLength('name', 2)->maxLength('name', 255);
        }

        if (isset($updateData['discount_type'])) {
            $validator->inArray('discount_type', self::DISCOUNT_TYPES);
        }

        if (isset($updateData['discount_value'])) {
            $validator->numeric('discount_value');
            $discountValue = (float)$updateData['discount_value'];
            if ($discountValue < 0) {
                $errors['discount_value'] = 'La valeur de remise doit être positive';
            }
            $discountType = $updateData['discount_type'] ?? $existingPromo['discount_type'];
            if ($discountType === 'percentage' && $discountValue > 100) {
                $errors['discount_value'] = 'Le pourcentage ne peut pas dépasser 100%';
            }
        }

        if (isset($updateData['application_mode'])) {
            $validator->inArray('application_mode', self::APPLICATION_MODES);
        }

        // C'EST ICI QUE LE BUG SE PRODUIT
        // validate() retourne void, pas un array
        // On teste que le résultat est utilisable avec array_merge
        $validationErrors = $validator->validate();

        // Ce test échoue car $validationErrors est null
        $this->assertIsArray($validationErrors, 'validate() devrait retourner un array pour être utilisable avec array_merge()');

        // Si le test précédent passait, ceci fonctionnerait
        $errors = array_merge($errors, $validationErrors);

        $this->assertEmpty($errors, 'Il ne devrait pas y avoir d\'erreurs avec des données valides');
    }

    /**
     * Test du pattern corrigé utilisant getErrors() au lieu de validate()
     * Ce test PASSE et montre comment le code devrait être écrit.
     */
    public function testUpdateValidationPatternWithGetErrors(): void
    {
        $existingPromo = [
            'id' => 'promo-123',
            'name' => 'Test Promo',
            'code' => 'TESTCODE',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'application_mode' => 'manual',
            'is_active' => true
        ];

        // Données de mise à jour valides
        $updateData = [
            'name' => 'Updated Promo Name',
            'discount_value' => 15
        ];

        $errors = [];
        $validator = new Validator($updateData);

        if (isset($updateData['name'])) {
            $validator->minLength('name', 2)->maxLength('name', 255);
        }

        if (isset($updateData['discount_value'])) {
            $validator->numeric('discount_value');
            $discountValue = (float)$updateData['discount_value'];
            if ($discountValue < 0) {
                $errors['discount_value'] = 'La valeur de remise doit être positive';
            }
        }

        // SOLUTION CORRECTE: utiliser getErrors() au lieu de validate()
        $validationErrors = $validator->getErrors();

        $this->assertIsArray($validationErrors);

        $errors = array_merge($errors, $validationErrors);

        $this->assertEmpty($errors, 'Il ne devrait pas y avoir d\'erreurs avec des données valides');
    }

    /**
     * Test de validation avec des données invalides utilisant getErrors()
     */
    public function testUpdateValidationWithInvalidData(): void
    {
        $updateData = [
            'name' => 'A', // Trop court (min 2)
            'discount_value' => 'not-a-number',
            'discount_type' => 'invalid_type'
        ];

        $errors = [];
        $validator = new Validator($updateData);

        $validator->minLength('name', 2)->maxLength('name', 255);
        $validator->inArray('discount_type', self::DISCOUNT_TYPES);
        $validator->numeric('discount_value');

        $validationErrors = $validator->getErrors();

        $this->assertIsArray($validationErrors);
        $this->assertNotEmpty($validationErrors);
        $this->assertArrayHasKey('name', $validationErrors);
        $this->assertArrayHasKey('discount_type', $validationErrors);
        $this->assertArrayHasKey('discount_value', $validationErrors);
    }

    // =========================================================================
    // TESTS DE VALIDATION PROMO CODE
    // =========================================================================

    /**
     * Test validation nom avec minLength
     */
    public function testPromoNameMinLength(): void
    {
        $validator = new Validator(['name' => 'A']);
        $validator->minLength('name', 2);

        $this->assertFalse($validator->isValid());
        $this->assertArrayHasKey('name', $validator->getErrors());
    }

    /**
     * Test validation nom avec maxLength
     */
    public function testPromoNameMaxLength(): void
    {
        $validator = new Validator(['name' => str_repeat('A', 300)]);
        $validator->maxLength('name', 255);

        $this->assertFalse($validator->isValid());
        $this->assertArrayHasKey('name', $validator->getErrors());
    }

    /**
     * Test validation discount_type
     */
    public function testPromoDiscountType(): void
    {
        // Type valide
        $validator = new Validator(['discount_type' => 'percentage']);
        $validator->inArray('discount_type', self::DISCOUNT_TYPES);
        $this->assertTrue($validator->isValid());

        // Type invalide
        $validator = new Validator(['discount_type' => 'invalid']);
        $validator->inArray('discount_type', self::DISCOUNT_TYPES);
        $this->assertFalse($validator->isValid());
    }

    /**
     * Test validation discount_value
     */
    public function testPromoDiscountValue(): void
    {
        // Valeur numérique valide
        $validator = new Validator(['discount_value' => '10.5']);
        $validator->numeric('discount_value');
        $this->assertTrue($validator->isValid());

        // Valeur non numérique
        $validator = new Validator(['discount_value' => 'abc']);
        $validator->numeric('discount_value');
        $this->assertFalse($validator->isValid());
    }

    /**
     * Test validation application_mode
     */
    public function testPromoApplicationMode(): void
    {
        // Mode valide
        $validator = new Validator(['application_mode' => 'manual']);
        $validator->inArray('application_mode', self::APPLICATION_MODES);
        $this->assertTrue($validator->isValid());

        $validator = new Validator(['application_mode' => 'automatic']);
        $validator->inArray('application_mode', self::APPLICATION_MODES);
        $this->assertTrue($validator->isValid());

        // Mode invalide
        $validator = new Validator(['application_mode' => 'invalid']);
        $validator->inArray('application_mode', self::APPLICATION_MODES);
        $this->assertFalse($validator->isValid());
    }

    /**
     * Test validation complète création promo (simule store())
     */
    public function testCreatePromoValidation(): void
    {
        $data = [
            'name' => 'Summer Sale',
            'discount_type' => 'percentage',
            'discount_value' => '20',
            'application_mode' => 'manual',
            'code' => 'SUMMER20'
        ];

        $validator = new Validator($data);
        $validator
            ->required('name')
            ->minLength('name', 2)
            ->maxLength('name', 255)
            ->required('discount_type')
            ->inArray('discount_type', self::DISCOUNT_TYPES)
            ->required('discount_value')
            ->numeric('discount_value')
            ->inArray('application_mode', self::APPLICATION_MODES);

        $this->assertTrue($validator->isValid(), 'La création avec données valides devrait passer');
        $this->assertEmpty($validator->getErrors());
    }

    /**
     * Test validation création promo avec données manquantes
     */
    public function testCreatePromoValidationMissingRequired(): void
    {
        $data = [
            'discount_value' => '20'
            // name et discount_type manquants
        ];

        $validator = new Validator($data);
        $validator
            ->required('name')
            ->required('discount_type')
            ->required('discount_value');

        $this->assertFalse($validator->isValid());
        $errors = $validator->getErrors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('discount_type', $errors);
        $this->assertArrayNotHasKey('discount_value', $errors); // présent
    }

    // =========================================================================
    // TESTS VALIDATION BUSINESS RULES
    // =========================================================================

    /**
     * Test que le pourcentage ne peut pas dépasser 100%
     */
    public function testPercentageCannotExceed100(): void
    {
        $data = [
            'discount_type' => 'percentage',
            'discount_value' => 150
        ];

        $errors = [];

        // Validation numérique
        $validator = new Validator($data);
        $validator->numeric('discount_value');

        if ($validator->isValid()) {
            $discountValue = (float)$data['discount_value'];
            if ($data['discount_type'] === 'percentage' && $discountValue > 100) {
                $errors['discount_value'] = 'Le pourcentage ne peut pas dépasser 100%';
            }
        }

        $errors = array_merge($errors, $validator->getErrors());

        $this->assertArrayHasKey('discount_value', $errors);
        $this->assertStringContainsString('100%', $errors['discount_value']);
    }

    /**
     * Test que la valeur de remise doit être positive
     */
    public function testDiscountValueMustBePositive(): void
    {
        $data = [
            'discount_value' => -10
        ];

        $errors = [];

        $validator = new Validator($data);
        $validator->numeric('discount_value');

        if ($validator->isValid()) {
            $discountValue = (float)$data['discount_value'];
            if ($discountValue < 0) {
                $errors['discount_value'] = 'La valeur de remise doit être positive';
            }
        }

        $errors = array_merge($errors, $validator->getErrors());

        $this->assertArrayHasKey('discount_value', $errors);
        $this->assertStringContainsString('positive', $errors['discount_value']);
    }

    /**
     * Test que le code est requis pour les promos manuelles
     */
    public function testCodeRequiredForManualMode(): void
    {
        $data = [
            'application_mode' => 'manual',
            'code' => '' // vide
        ];

        $errors = [];

        if (($data['application_mode'] ?? 'manual') === 'manual') {
            if (empty($data['code'])) {
                $errors['code'] = 'Le code est requis pour les promotions manuelles';
            }
        }

        $this->assertArrayHasKey('code', $errors);
    }

    /**
     * Test que le code n'est pas requis pour les promos automatiques
     */
    public function testCodeNotRequiredForAutomaticMode(): void
    {
        $data = [
            'application_mode' => 'automatic',
            'code' => '' // vide mais ok pour automatic
        ];

        $errors = [];

        if (($data['application_mode'] ?? 'manual') === 'manual') {
            if (empty($data['code'])) {
                $errors['code'] = 'Le code est requis pour les promotions manuelles';
            }
        }

        $this->assertArrayNotHasKey('code', $errors);
    }
}
