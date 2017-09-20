<?php

namespace AppBundle\Controller;

use AppBundle\Entity\status;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Status controller.
 *
 * @Route("status")
 */
class statusController extends Controller
{

    /**
     * Cron
     *
     * @Route("/cron", name="cron_index")
     * @Method("GET")
     */
    public function cronAction(Request $request)
    {
    	$data=array();
        array_push ($data, array("Dump"));
        
        
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:status');

        $query = $repository->createQueryBuilder('s')
            ->where('s.datetime <= :now')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery();

        $statuses = $query->getResult();

		//array_push ($data, $statuses);

        foreach($statuses as $status){

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:user')->findOneByName($status->getAccount());


            $ch = curl_init("https://".parse_url($user->getMastodonObject()["url"])["host"]."/api/v1/statuses");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST,true);

            curl_setopt($ch, CURLOPT_POSTFIELDS,array('status'=>$status->getContent())); /* , 'visibility' => $status->getVisibility() */

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$user->getAccessToken()
            ));

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);

            $ch_res=curl_exec( $ch );
            curl_close($ch);
            

			if(json_decode($ch_res)->error){ //error tooting
				array_push ($data, "error tooting : ");
				array_push ($data, json_decode($ch_res)->error);
			}else{
            	if(json_decode($ch_res)->created_at){
                	$em->remove($status);
                	$em->flush();
            	}
            }
            
        }

        return new Response("ok");
        /*return $this->render('dump.html.twig', array(
            'data' => $data,
        ));*/
    }



    /**
     * Lists all status entities.
     *
     * @Route("/all", name="status_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();

        $statuses = $em->getRepository('AppBundle:status')->findByAccount($session->get('user_name'));

        return $this->render('status/index.html.twig', array(
            'statuses' => $statuses,
        ));
    }

    /**
     * Finds and displays a status entity.
     *
     * @Route("/{id}", name="status_show")
     * @Method("GET")
     */
    public function showAction(Request $request,status $status)
    {
        $session = $request->getSession();
        if($status->getAccount()==$session->get('user_name')) {
            $deleteForm = $this->createDeleteForm($status);
            return $this->render('status/show.html.twig', array(
                'status' => $status,
                'delete_form' => $deleteForm->createView(),
            ));
        }else{
            return $this->redirectToRoute('homepage');
        }


    }

    /**
     * Displays a form to edit an existing status entity.
     *
     * @Route("/{id}/edit", name="status_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, status $status)
    {
        $session = $request->getSession();
        if($status->getAccount()==$session->get('user_name')){
            $deleteForm = $this->createDeleteForm($status);
            $editForm = $this->createForm('AppBundle\Form\statusType', $status);
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('status_edit', array('id' => $status->getId()));
            }

            return $this->render('status/edit.html.twig', array(
                'status' => $status,
                'edit_form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
            ));
        }else{
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * Deletes a status entity.
     *
     * @Route("/{id}", name="status_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, status $status)
    {
        $form = $this->createDeleteForm($status);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();
            if($status->getAccount()==$session->get('user_name')) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($status);
                $em->flush();
            }
        }

        return $this->redirectToRoute('status_index');
    }

    /**
     * Creates a form to delete a status entity.
     *
     * @param status $status The status entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(status $status)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('status_delete', array('id' => $status->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }



}
