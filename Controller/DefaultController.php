<?php

namespace BiberLtd\Bundle\FAQBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdFAQBundle:Default:index.html.twig', array('name' => $name));
    }
}
