<?php

namespace Oro\Bundle\FrontendBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\LayoutBundle\Annotation\Layout;

class FrontendController extends Controller
{
    /**
     * @Layout
     * @Route("/", name="oro_frontend_root")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/exception/{code}/{text}", name="oro_frontend_exception", requirements={"code"="\d+"})
     * @param string $code
     * @param string $text
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function exceptionAction($code, $text)
    {
        $code = (int)$code;

        $params = ['data' => ['status_code' => $code, 'status_text' => $text]];
        $content = $this->get('layout')->render($params);

        return new Response($content, $code);
    }
}
