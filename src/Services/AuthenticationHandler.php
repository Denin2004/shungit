<?php
namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

use App\Services\SiteConfig;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**     * Constructor
     *  @author     Joe Sexton <joe@webtipblog.com>
     *
     *  @param     RouterInterface $router
     *  @param     Session $session
     */
    /**     * onAuthenticationSuccess
     *  @author     Joe Sexton <joe@webtipblog.com>
     *
     *  @param     Request $request
     *  @param     TokenInterface $token
     *
     *  @return     Response
     */

    protected $siteConfig;

    public function __construct(SiteConfig $siteConfig)
    {
        $this->siteConfig = $siteConfig;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $firstRole = $token->getRoleNames()[0];
        $redirect = $this->siteConfig->get('redirect');
        return new JsonResponse([
            'success' => true,
            'redirect' => isset($redirect[$firstRole]) ? $redirect[$firstRole] : $redirect['default']
        ]);
    }
    /**
     *  onAuthenticationFailure.
     *
     *  @author     Joe Sexton <joe@webtipblog.com>
     *
     *  @param     Request $request
     *  @param     AuthenticationException $exception
     *  @return     Response      */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        dump($exception);
        return new JsonResponse([
            'success' => false,
            'error' => $exception->getMessage()
        ]); // data to return via JSON
    }
}
