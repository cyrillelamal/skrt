<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Intercept and validate API requests done via JS.
 */
class CsrfSubscriber implements EventSubscriberInterface
{
    public const CSRF_METHODS = ['POST', 'PUT', 'DELETE', 'PATCH'];

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (strpos($request->getPathInfo(), '/api/') === 0
            && in_array($request->getMethod(), self::CSRF_METHODS)) {
            if (strpos($request->headers->get('Content-Type'), 'application/json') !== 0) {
                $response = new Response(null, Response::HTTP_UNAUTHORIZED);

                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
