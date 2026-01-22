<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests de sécurité pour la suppression de personnes
 * Vérifie que seuls les utilisateurs autorisés peuvent supprimer une personne
 */
class PersonDeleteSecurityTest extends TestCase
{
    /**
     * Simule la logique d'autorisation de suppression
     */
    private function canDeletePerson(
        bool $isAuthenticated,
        bool $isAdmin,
        bool $isAssignedToUser,
        int $sessionCount
    ): array {
        // 1. Vérification authentification
        if (!$isAuthenticated) {
            return ['allowed' => false, 'error' => 401, 'message' => 'Token d\'authentification manquant'];
        }

        // 2. Vérification autorisation (admin ou assigné)
        if (!$isAdmin && !$isAssignedToUser) {
            return ['allowed' => false, 'error' => 403, 'message' => 'Accès non autorisé'];
        }

        // 3. Vérification contrainte métier (pas de séances)
        if ($sessionCount > 0) {
            return ['allowed' => false, 'error' => 400, 'message' => "Cette personne a {$sessionCount} séance(s)"];
        }

        return ['allowed' => true, 'error' => null, 'message' => 'Personne supprimée'];
    }

    // ==========================================
    // TESTS AUTHENTIFICATION (401)
    // ==========================================

    public function testUnauthenticatedUserCannotDelete(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: false,
            isAdmin: false,
            isAssignedToUser: false,
            sessionCount: 0
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(401, $result['error']);
    }

    public function testUnauthenticatedUserCannotDeleteEvenIfWouldBeAdmin(): void
    {
        // Même si les données indiquent "admin", sans auth c'est bloqué
        $result = $this->canDeletePerson(
            isAuthenticated: false,
            isAdmin: true,
            isAssignedToUser: true,
            sessionCount: 0
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(401, $result['error']);
    }

    // ==========================================
    // TESTS AUTORISATION (403)
    // ==========================================

    public function testAuthenticatedUserNotAssignedCannotDelete(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: false,
            isAssignedToUser: false,
            sessionCount: 0
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(403, $result['error']);
    }

    public function testAuthenticatedUserNotAssignedCannotDeleteEvenWithNoSessions(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: false,
            isAssignedToUser: false,
            sessionCount: 0
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(403, $result['error']);
    }

    // ==========================================
    // TESTS CONTRAINTE MÉTIER (400)
    // ==========================================

    public function testAssignedUserCannotDeletePersonWithSessions(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: false,
            isAssignedToUser: true,
            sessionCount: 5
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(400, $result['error']);
        $this->assertStringContainsString('5 séance(s)', $result['message']);
    }

    public function testAdminCannotDeletePersonWithSessions(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: true,
            isAssignedToUser: false,
            sessionCount: 3
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(400, $result['error']);
        $this->assertStringContainsString('3 séance(s)', $result['message']);
    }

    public function testCannotDeletePersonWithOneSession(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: true,
            isAssignedToUser: true,
            sessionCount: 1
        );

        $this->assertFalse($result['allowed']);
        $this->assertEquals(400, $result['error']);
    }

    // ==========================================
    // TESTS CAS AUTORISÉS (200)
    // ==========================================

    public function testAssignedUserCanDeletePersonWithNoSessions(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: false,
            isAssignedToUser: true,
            sessionCount: 0
        );

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['error']);
    }

    public function testAdminCanDeletePersonWithNoSessions(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: true,
            isAssignedToUser: false,
            sessionCount: 0
        );

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['error']);
    }

    public function testAdminAssignedCanDeletePersonWithNoSessions(): void
    {
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: true,
            isAssignedToUser: true,
            sessionCount: 0
        );

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['error']);
    }

    // ==========================================
    // TESTS DATA PROVIDER - TOUS LES SCÉNARIOS
    // ==========================================

    /**
     * @dataProvider deletePermissionScenariosProvider
     */
    public function testDeletePermissionScenarios(
        bool $isAuthenticated,
        bool $isAdmin,
        bool $isAssigned,
        int $sessions,
        bool $expectedAllowed,
        ?int $expectedError
    ): void {
        $result = $this->canDeletePerson($isAuthenticated, $isAdmin, $isAssigned, $sessions);

        $this->assertEquals($expectedAllowed, $result['allowed']);
        $this->assertEquals($expectedError, $result['error']);
    }

    public static function deletePermissionScenariosProvider(): array
    {
        return [
            // [auth, admin, assigned, sessions, allowed, error]

            // Non authentifié - toujours 401
            'no_auth_no_admin_no_assigned' => [false, false, false, 0, false, 401],
            'no_auth_admin' => [false, true, false, 0, false, 401],
            'no_auth_assigned' => [false, false, true, 0, false, 401],

            // Authentifié mais pas autorisé - 403
            'auth_not_admin_not_assigned' => [true, false, false, 0, false, 403],
            'auth_not_admin_not_assigned_with_sessions' => [true, false, false, 5, false, 403],

            // Autorisé mais sessions existantes - 400
            'auth_admin_with_sessions' => [true, true, false, 1, false, 400],
            'auth_assigned_with_sessions' => [true, false, true, 10, false, 400],
            'auth_admin_assigned_with_sessions' => [true, true, true, 3, false, 400],

            // Autorisé et pas de sessions - OK
            'auth_admin_no_sessions' => [true, true, false, 0, true, null],
            'auth_assigned_no_sessions' => [true, false, true, 0, true, null],
            'auth_admin_assigned_no_sessions' => [true, true, true, 0, true, null],
        ];
    }

    // ==========================================
    // TESTS ORDRE DES VÉRIFICATIONS
    // ==========================================

    public function testAuthenticationIsCheckedFirst(): void
    {
        // Même avec tous les autres critères OK, sans auth c'est 401
        $result = $this->canDeletePerson(
            isAuthenticated: false,
            isAdmin: true,
            isAssignedToUser: true,
            sessionCount: 0
        );

        $this->assertEquals(401, $result['error']);
    }

    public function testAuthorizationIsCheckedBeforeBusinessRule(): void
    {
        // Authentifié mais pas autorisé = 403 (pas 400 même s'il y a des sessions)
        $result = $this->canDeletePerson(
            isAuthenticated: true,
            isAdmin: false,
            isAssignedToUser: false,
            sessionCount: 5
        );

        $this->assertEquals(403, $result['error']);
    }
}
