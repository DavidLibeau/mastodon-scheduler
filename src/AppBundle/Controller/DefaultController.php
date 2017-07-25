<?php

namespace AppBundle\Controller;

use AppBundle\Entity\status;
use AppBundle\Entity\app;
use AppBundle\Entity\user;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $status = new Status();

        $session = $request->getSession();
        if($session->get('user_name')){
            $status->setAccount($session->get('user_name'));
        }
        $status->setdatetime(new \DateTime('now'));
        $form = $this->createForm('AppBundle\Form\statusType', $status);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($status->getAccount()==$session->get('user_name')){
                $em = $this->getDoctrine()->getManager();
                $em->persist($status);
                $em->flush();

                return $this->redirectToRoute('status_show', array('id' => $status->getId()));
            }else{
                if($session->get('user_name')){
                    $status->setAccount($session->get('user_name'));
                }
                return $this->render('status/new.html.twig', array(
                    'status' => $status,
                    'form' => $form->createView(),
                    'error' => "Invalid account"
                ));
            }
        }

        return $this->render('status/new.html.twig', array(
            'status' => $status,
            'form' => $form->createView(),
            'error' => null
        ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->clear();
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('about.html.twig');
    }

    /**
     * Auth on Mastodon
     *
     * @Route("/auth/{instanceURL}", name="auth")
     */
    public function authAction($instanceURL,Request $request)
    {
        if($instanceURL!=null){
            $appSaved=$this->getDoctrine()->getRepository('AppBundle:app')->findOneBy(
                array('url' => $instanceURL)
            );


            if($appSaved==false || $request->query->get('force')=="true"){ //we don't have this app in database
                if($request->query->get('dev')=="true"){
                    $redirectURL="http://127.0.0.1:8000/auth/".$instanceURL;
                }else{
                    $redirectURL="http://127.0.0.1:8000/auth/".$instanceURL;
                }

                $ch = curl_init("https://".$instanceURL."/api/v1/apps");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST,true);

                curl_setopt($ch, CURLOPT_POSTFIELDS,array('client_name'=>'Mastodon.tools','redirect_uris'=>$redirectURL,'scopes'=>'read write','website'=>'http://scheduler.mastodon.tools'));

                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);

                $ch_res=curl_exec( $ch );
                curl_close($ch);
                $M_app = json_decode($ch_res);

                if(isset($M_app->client_id)) {
                    $app = new app();
                    $app->setUrl($instanceURL);
                    $app->setAppId($M_app->client_id);
                    $app->setAppSecret($M_app->client_secret);
                    $app->setRedirectUrl($redirectURL);

                    $em = $this->getDoctrine()->getManager();
                    if($request->query->get('force')=="true"){
                        $em->remove($appSaved);
                        $em->flush();
                    }
                    $em->persist($app);
                    $em->flush();

                    return $this->render('auth/get.html.twig', array(
                        'response' => "ok",
                        'app_id' => $M_app->client_id,
                        'redirect_url' => $M_app->redirect_uri,
                    ));
                }

            } else { //we have this app in database
                if($request->query->get('code')!=null && $request->query->get('code')!=""){ //we are here after Mastodon auth
                    //Get Mastodon access_token
                    $ch = curl_init("https://".$instanceURL."/oauth/token");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST,true);

                    curl_setopt($ch, CURLOPT_POSTFIELDS,array('client_id'=>$appSaved->getAppId(),'client_secret'=>$appSaved->getAppSecret(),'redirect_uri'=>$appSaved->getRedirectUrl(),'grant_type'=>'authorization_code','code'=>$request->query->get('code')));

                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

                    $ch_res=curl_exec( $ch );
                    curl_close($ch);
                    $M_token=json_decode($ch_res);

                    /*return $this->render('auth/dump.html.twig', array(
                        'dump' => $M_token,
                    ));*/

                    //Get Mastodon user
                    $ch = curl_init("https://".$instanceURL."/api/v1/accounts/verify_credentials");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST,false);

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer '.$M_token->access_token
                    ));

                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

                    $ch_res=curl_exec( $ch );
                    curl_close($ch);
                    $M_user=json_decode($ch_res);
                    $M_username="@".$M_user->username."@".parse_url($M_user->url)["host"];

                    $userSaved=$this->getDoctrine()->getRepository('AppBundle:user')->findOneBy(
                        array('name' => $M_username)
                    );

                    $user = new user();
                    $user->setName($M_username);
                    $user->setAccessToken($M_token->access_token);
                    $user->setMastodonObject($M_user);

                    $em = $this->getDoctrine()->getManager();

                    if($userSaved!=null){
                        $em->remove($userSaved);
                        $em->flush();
                    }

                    $em->persist($user);
                    $em->flush();

                    $session = $request->getSession();
                    $session->set('user_name', $M_username);
                    $session->set('user_token', $M_token->access_token);
                    return $this->redirectToRoute('homepage');

                }else{ //we are here only to have the app_id
                    return $this->render('auth/get.html.twig', array(
                        'response' => "ok",
                        'app_id' => $appSaved->getAppId(),
                        'redirect_url' => $appSaved->getRedirectUrl(),
                    ));
                }
            }
        }

        return $this->render('auth/get.html.twig', array(
            'response' => "error",
            'app_id' => "",
        ));

    }
}
