<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Browser;

use Pimcore\Model\User;
use Pimcore\Security\User\User as SecurityUser;
use Pimcore\Tool\Session;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

class PimcoreKernelBrowser extends KernelBrowser
{
    public function loginToPimcoreBackend(string $username = 'admin'): void
    {
        if (!$user = User::getByName($username)) {
            throw new \InvalidArgumentException(\sprintf('User "%s" does not exist.', $username));
        }

        $this->setServerParameter('HTTP_X_PIMCORE_CSRF_TOKEN', 'test-csrf-token');
        $this->loginUser(new SecurityUser($user), 'pimcore_admin');
        $this->request('GET', '/admin/login');

        Session::useBag(
            $this->getRequest()->getSession(),
            function (AttributeBagInterface $adminSession) use ($user) {
                $adminSession->set('user', $user);
                // Sign your POST requests with this CSRF token to avoid 403 responses
                $adminSession->set('csrfToken', 'test-csrf-token');
            },
        );
    }

    public function logoutFromPimcoreBackend(): void
    {
        Session::useBag(
            $this->getRequest()->getSession(),
            function (AttributeBagInterface $adminSession) {
                $adminSession->set('user', null);
                $adminSession->set('csrfToken', null);
            },
        );

        $this->getCookieJar()->clear();
    }
}
