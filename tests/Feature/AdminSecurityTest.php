<?php

namespace Tests\Feature;

use App\Http\Middleware\SecureDomainMiddleware;
use App\Models\Admin;
use App\Models\AdminSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_redirects_to_admin_login_route_for_guest_admin(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    public function test_same_admin_cannot_login_again_while_active_session_exists(): void
    {
        $admin = Admin::create([
            'name' => 'Panitia OSIS',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        AdminSession::create([
            'admin_id' => $admin->id,
            'session_id' => 'active-session-123',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'logged_in_at' => now(),
            'last_seen_at' => now(),
            'revoked_at' => null,
        ]);

        $response = $this->from('/admin/login')
            ->post('/admin/login', [
                'username' => 'admin',
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_secure_domain_middleware_rejects_non_allowed_host(): void
    {
        $middleware = new SecureDomainMiddleware;
        $request = Request::create('http://evil.test/admin/dashboard', 'GET');

        try {
            $middleware->handle($request, function () {
                return response()->noContent();
            });
            $this->fail('Domain middleware should abort for invalid host.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_admin_login_updates_last_login_at_and_ip(): void
    {
        $admin = Admin::create([
            'name' => 'Panitia OSIS',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $this->assertNull($admin->last_login_at);
        $this->assertNull($admin->last_login_ip);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');

        $admin->refresh();
        $this->assertNotNull($admin->last_login_at);
        $this->assertNotNull($admin->last_login_ip);
    }

    public function test_admin_can_force_login_and_revoke_existing_session(): void
    {
        $admin = Admin::create([
            'name' => 'Panitia OSIS',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $oldSession = AdminSession::create([
            'admin_id' => $admin->id,
            'session_id' => 'stuck-session-456',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'logged_in_at' => now(),
            'last_seen_at' => now(),
            'revoked_at' => null,
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password123',
            'force_login' => '1',
        ]);

        $response->assertRedirect('/admin/dashboard');

        $oldSession->refresh();
        $this->assertNotNull($oldSession->revoked_at);
    }

    public function test_stale_session_auto_expires_on_admin_login(): void
    {
        $admin = Admin::create([
            'name' => 'Panitia OSIS',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $staleSession = AdminSession::create([
            'admin_id' => $admin->id,
            'session_id' => 'stale-session-789',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
            'logged_in_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subMinutes(5),
            'revoked_at' => null,
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');

        $staleSession->refresh();
        $this->assertNotNull($staleSession->revoked_at);
    }

    public function test_beacon_logout_revokes_session_and_logs_out_admin(): void
    {
        $admin = Admin::create([
            'name' => 'Panitia OSIS',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated('admin');

        $adminSession = AdminSession::where('admin_id', $admin->id)->whereNull('revoked_at')->first();
        $this->assertNotNull($adminSession);

        $response = $this->post('/admin/beacon-logout');
        $response->assertNoContent();

        $adminSession->refresh();
        $this->assertNotNull($adminSession->revoked_at);
        $this->assertGuest('admin');
    }
}

