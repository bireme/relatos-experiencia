<?php

// This file is part of the ProEthos Software. 
// 
// Copyright 2013, PAHO. All rights reserved. You can redistribute it and/or modify
// ProEthos under the terms of the ProEthos License as published by PAHO, which
// restricts commercial use of the Software. 
// 
// ProEthos is distributed in the hope that it will be useful, but WITHOUT ANY
// WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
// PARTICULAR PURPOSE. See the ProEthos License for more details. 
// 
// You should have received a copy of the ProEthos License along with the ProEthos
// Software. If not, see
// https://github.com/bireme/proethos2/blob/master/LICENSE.txt


namespace Proethos2\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;


/**
 * @Route("/api", name="api")
 * @Template()
 */
class AjaxController extends Controller
{
    /**
     * @Route("/user", name="api_get_user_by_id")
     * @Template()
     */
    public function getUserByIdAction()
    {
        $data = array();
        $request = $this->getRequest();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        // checking if was a post request
        if($request->isMethod('POST')) {

            // getting post data
            $post_data = $request->request->all();

            $em = $this->getDoctrine()->getManager();
            $user_repository = $em->getRepository('Proethos2ModelBundle:User');
            $data = $user_repository->find($post_data['id']);

        }

        $jsonContent = $serializer->serialize($data, 'json');
        $response = new JsonResponse();
        $response->setContent($jsonContent);

        return $response;
    }

    /**
     * @Route("/experience/{protocol_id}", defaults={"protocol_id"=null}, methods={"GET"}, name="api_get_experience")
     * @Template()
     */
    public function getExperienceAction($protocol_id)
    {

        $data = array();
        $request = $this->getRequest();
        $serializer = SerializerBuilder::create()->build();

        // getting post data
        $post_data = $request->query->all();

        $lang = $post_data["lang"] ? $post_data["lang"] : 'en';
        $limit = $post_data["limit"] ? $post_data["limit"] : 10;
        $offset = $post_data["offset"] ? $post_data["offset"] : null;
        $orderBy = array();
        if ($post_data["sort"] and $post_data["order"]) {
            $orderBy = array($post_data["sort"], $post_data["order"]);
        }

        $locale = array('pt_BR', 'es_ES', 'fr_FR', 'en');
        if ( in_array($lang, $locale) ) {
            $this->container->get('gedmo.listener.translatable')->setTranslatableLocale($lang);
        } else {
            $this->container->get('gedmo.listener.translatable')->setTranslatableLocale('en');
        }

        $em = $this->getDoctrine()->getManager();
        $protocol_repository = $em->getRepository('Proethos2ModelBundle:Protocol');

        if ( $protocol_id ) {
            $data = $protocol_repository->findBy(array('id' => $protocol_id, 'status' => 'A'));
        } else {
            $protocols = $protocol_repository->findBy(array('status' => 'A'), $orderBy, $limit, $offset);
            $data['total'] = count($protocols);
            $data['items'] = $protocols;
        }

        $json = $serializer->serialize($data, 'json');
        $response = new JsonResponse();
        $response->setContent($json);

        return $response;
    }

}
