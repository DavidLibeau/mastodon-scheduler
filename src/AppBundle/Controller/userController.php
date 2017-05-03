<?php

namespace AppBundle\Controller;

use AppBundle\Entity\user;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * User controller.
 *
 * @Route("user")
 */
class userController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:user')->findAll();

        return $this->render('user/index.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{username}", name="user_show")
     * @Method("GET")
     */
    public function showAction(Request $request, $username)
    {
        $em = $this->getDoctrine()->getManager();
        if($username=="me"){
            $session = $request->getSession();
            $user = $em->getRepository('AppBundle:user')->findOneByName($session->get('user_name'));
        }else{
            $user = $em->getRepository('AppBundle:user')->findOneByName($username);
        }

        return $this->render('user/show.html.twig', array(
            'user' => $user,
        ));
    }


}
