<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Neusta\Pimcore\TestingFramework\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PimcoreKernelBrowserTest extends WebTestCase
{
    use ResetDatabase;

    /**
     * @test
     */
    public function it_allows_logging_in_and_out_from_pimcore_backend(): void
    {
        $client = static::createClient();
        $client->catchExceptions(false);

        // Access should be denied before login
        try {
            $client->request('GET', '/admin/user/get-minimal');
            $this->fail('AccessDeniedException expected.');
        } catch (AccessDeniedException) {
        }

        // Access should be granted after login
        $client->loginToPimcoreBackend();
        $client->request('GET', '/admin/user/get-minimal');
        static::assertResponseIsSuccessful();

        // Access should be denied again after logout
        $client->logoutFromPimcoreBackend();
        try {
            $client->request('GET', '/admin/user/get-minimal');
            $this->fail('AccessDeniedException expected.');
        } catch (AccessDeniedException) {
        }
    }
}
