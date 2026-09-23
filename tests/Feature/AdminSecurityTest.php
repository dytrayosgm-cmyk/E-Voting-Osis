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
        $middleware = new SecureDomainMiddleware();
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
}
