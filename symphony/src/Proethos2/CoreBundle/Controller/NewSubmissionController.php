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
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Proethos2\CoreBundle\Util\Util;
use Proethos2\CoreBundle\Util\CountryLocale;

use Proethos2\ModelBundle\Entity\Submission;
use Proethos2\ModelBundle\Entity\SubmissionCountry;
use Proethos2\ModelBundle\Entity\SubmissionCost;
use Proethos2\ModelBundle\Entity\SubmissionTask;
use Proethos2\ModelBundle\Entity\SubmissionUpload;
use Proethos2\ModelBundle\Entity\Protocol;
use Proethos2\ModelBundle\Entity\ProtocolHistory;
use Proethos2\ModelBundle\Entity\SubmissionClinicalTrial;
use Proethos2\ModelBundle\Entity\SubmissionClinicalStudy;
use Proethos2\ModelBundle\Entity\SubmissionResponsible;
use Proethos2\ModelBundle\Entity\SubmissionMember;


class NewSubmissionController extends Controller
{
    /**
     * @Route("/submission/new/first", name="submission_new_first_step")
     * @Template()
     */
    public function FirstStepAction(Request $request)
    {
        $output = array();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $locale = $request->getSession()->get('_locale');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting thematic area list
        $thematic_area_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        // $thematic_area = $thematic_area_repository->findByStatus(true);
        $thematic_area = $thematic_area_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['thematic_area'] = $thematic_area;

        // status options
        $status = array(
            "A" => $translator->trans("The practice is in the initial stage of implementation"),
            "B" => $translator->trans("The practice is fully implemented and continues to operate"),
            "C" => $translator->trans("The practice was discontinued before it was fully implemented"),
            "D" => $translator->trans("The practice was discontinued after a period of operation"),
            "E" => $translator->trans("The practice has not been implemented"),
            "F" => $translator->trans("Other")
        );

        $output['status'] = $status;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-first-step', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('title', 'thematic-area', 'status', 'start-date', 'language') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $protocol = new Protocol();
            $protocol->setOwner($user);
            $protocol->setStatus('D');
            $em->persist($protocol);
            $em->flush();

            $submission = new Submission();
            $submission->setTitle($post_data['title']);
            $submission->setStatus($post_data['status']);
            $submission->setOtherstatus($post_data['other_status']);
            $submission->setStartDate(new \DateTime($post_data['start-date']));
            if ( $post_data['end-date'] ) $submission->setEnddate(new \DateTime($post_data['end-date']));
            if ( $post_data['partial-date'] ) $submission->setPartialdate(new \DateTime($post_data['partial-date']));
            $submission->setOtherDate($post_data['other_date']);
            $submission->setNotes($post_data['notes']);
            $submission->setLanguage(($post_data['language']) ? $post_data['language'] : $locale);
            $submission->setProtocol($protocol);
            $submission->setNumber(1);

            $submission->setOwner($user);

            // removing all thematic areas to re-add
            if ($submission->getThematicArea()) {
                foreach($submission->getThematicArea() as $ta) {
                    $submission->removeIntervention($ta);
                }
            }
            // re-add thematic areas
            if(isset($post_data['thematic-area'])) {
                foreach($post_data['thematic-area'] as $ta) {
                    $ta = $thematic_area_repository->find($ta);
                    $submission->addThematicArea($ta);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            $protocol->setMainSubmission($submission);
            $em->persist($protocol);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Submission started with success."));
            return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/first", name="submission_new_first_created_protocol_step")
     * @Template()
     */
    public function FirstStepCreatedProtocolAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $locale = $request->getSession()->get('_locale');

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting thematic area list
        $thematic_area_repository = $em->getRepository('Proethos2ModelBundle:ThematicArea');
        // $thematic_area = $thematic_area_repository->findByStatus(true);
        $thematic_area = $thematic_area_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['thematic_area'] = $thematic_area;

        // status options
        $status = array(
            "A" => $translator->trans("The practice is in the initial stage of implementation"),
            "B" => $translator->trans("The practice is fully implemented and continues to operate"),
            "C" => $translator->trans("The practice was discontinued before it was fully implemented"),
            "D" => $translator->trans("The practice was discontinued after a period of operation"),
            "E" => $translator->trans("The practice has not been implemented"),
            "F" => $translator->trans("Other")
        );

        $output['status'] = $status;

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or !$submission->getCanBeEdited() or ($submission->getCanBeEdited() and !in_array('investigator', $user->getRolesSlug()))) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-first-step-created', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('title', 'thematic-area', 'status', 'start-date', 'language') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $submission->setTitle($post_data['title']);
            $submission->setStatus($post_data['status']);
            $submission->setOtherstatus($post_data['other_status']);
            $submission->setStartDate(new \DateTime($post_data['start-date']));
            if ( $post_data['end-date'] ) $submission->setEnddate(new \DateTime($post_data['end-date']));
            if ( $post_data['partial-date'] ) $submission->setPartialdate(new \DateTime($post_data['partial-date']));
            $submission->setOtherDate($post_data['other_date']);
            $submission->setNotes($post_data['notes']);
            $submission->setLanguage(($post_data['language']) ? $post_data['language'] : $locale);

            // removing all thematic areas to re-add
            if ($submission->getThematicArea()) {
                foreach($submission->getThematicArea() as $ta) {
                    $submission->removeThematicArea($ta);
                }
            }
            // re-add thematic areas
            if(isset($post_data['thematic-area'])) {
                foreach($post_data['thematic-area'] as $ta) {
                    $ta = $thematic_area_repository->find($ta);
                    $submission->addThematicArea($ta);
                }
            }

            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("First step saved with success."));
            return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/translation", name="submission_new_first_translation_protocol_step")
     * @Template()
     */
    public function FirstStepTranslationProtocolAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // status options
        $status = array(
            "A" => $translator->trans("The practice is in the initial stage of implementation"),
            "B" => $translator->trans("The practice is fully implemented and continues to operate"),
            "C" => $translator->trans("The practice was discontinued before it was fully implemented"),
            "D" => $translator->trans("The practice was discontinued after a period of operation"),
            "E" => $translator->trans("The practice has not been implemented"),
            "F" => $translator->trans("Other")
        );

        $output['status'] = $status;

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-first-step-translation', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            foreach(array('scientific_title', 'public_title', 'language') as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            $protocol = $submission->getProtocol();

            $new_submission = new Submission();
            $new_submission->setIsTranslation(true);
            $new_submission->setOriginalSubmission($submission);
            $new_submission->setIsClinicalTrial($submission->getIsClinicalTrial());
            $new_submission->setIsConsultation($submission->getIsConsultation());
            $new_submission->setPublicTitle($post_data['public_title']);
            $new_submission->setScientificTitle($post_data['scientific_title']);
            $new_submission->setTitleAcronym($post_data['title_acronym']);
            $new_submission->setLanguage($post_data['language']);
            $new_submission->setProtocol($protocol);
            $new_submission->setNumber(1);

            $new_submission->setGender($submission->getGender());
            $new_submission->setSampleSize($submission->getSampleSize());
            $new_submission->setMinimumAge($submission->getMinimumAge());
            $new_submission->setMaximumAge($submission->getMaximumAge());
            $new_submission->setRecruitmentInitDate($submission->getRecruitmentInitDate());
            $new_submission->setRecruitmentStatus($submission->getRecruitmentStatus());
            $new_submission->setPriorEthicalApproval($submission->getPriorEthicalApproval());

            $new_submission->setOwner($submission->getOwner());

            $em = $this->getDoctrine()->getManager();
            $em->persist($new_submission);
            $em->flush();

            $submission->addTranlsation($new_submission);
            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("First step saved with success."));
            return $this->redirectToRoute('submission_new_second_step', array('submission_id' => $new_submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/second", name="submission_new_second_step")
     * @Template()
     */
    public function SecondStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        // $submission_country_repository = $em->getRepository('Proethos2ModelBundle:SubmissionCountry');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting population group list
        $population_group_repository = $em->getRepository('Proethos2ModelBundle:PopulationGroup');
        $population_group = $population_group_repository->findByStatus(true);
        // $population_group = $population_group_repository->findBy(array('status' => true), array('name' => 'ASC'));
        $output['population_group'] = $population_group;

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-second-step', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            // echo "<pre>"; print_r($post_data); echo "</pre>"; die();

            // checking required files
            $required_fields = array(
                'description',
                'objectives',
                'context'
            );

            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            // removing all population groups to re-add
            if ($submission->getPopulationGroup()) {
                foreach($submission->getPopulationGroup() as $population_group) {
                    $submission->removePopulationGroup($population_group);
                }
            }
            // re-add population groups
            if(isset($post_data['population_group'])) {
                foreach($post_data['population_group'] as $population_group) {
                    $population_group = $population_group_repository->find($population_group);
                    $submission->addPopulationGroup($population_group);
                }
            }

            // adding fields to model
            $submission->setDescription($post_data['description']);
            $submission->setObjectives($post_data['objectives']);
            $submission->setResources($post_data['resources']);
            $submission->setContext($post_data['context']);
            $submission->setOtherPopulationGroup($post_data['other_population_group']);
            
            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Second step saved with success."));
            return $this->redirectToRoute('submission_new_third_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/third", name="submission_new_third_step")
     * @Template()
     */
    public function ThirdStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-third-step', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            // checking required files
            $required_fields = array('main_results', 'challenges_information', 'other_results', 'lessons_learned');

            foreach($required_fields as $field) {
                if(!isset($post_data[$field]) or empty($post_data[$field])) {
                    $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
                    return $output;
                }
            }

            // adding fields to model
            $submission->setMainResults($post_data['main_results']);
            $submission->setChallengesInformation($post_data['challenges_information']);
            $submission->setOtherResults($post_data['other_results']);
            $submission->setLessonsLearned($post_data['lessons_learned']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Third step saved with success."));
            return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/fourth", name="submission_new_fourth_step")
     * @Template()
     */
    public function FourthStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $submission_responsible_repository = $em->getRepository('Proethos2ModelBundle:SubmissionResponsible');
        $submission_member_repository = $em->getRepository('Proethos2ModelBundle:SubmissionMember');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-fourth-step', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();
            // echo "<pre>"; print_r($post_data); echo "</pre>"; die();

            if(isset($post_data['responsible-name'])) {
                $post_data = array_map('trim', $post_data);

                // check if exists
                $submission_responsible = $submission_responsible_repository->findOneBy(array(
                    'submission' => $submission,
                    'email' => $post_data['responsible-email']
                ));

                // if not exists, create the new submission_responsible
                if(!$submission_responsible) {
                    $submission_responsible = new SubmissionResponsible();
                    $submission_responsible->setSubmission($submission);
                    $submission_responsible->setName($post_data['responsible-name']);
                    $submission_responsible->setFiliation($post_data['responsible-filiation']);
                    $submission_responsible->setEmail($post_data['responsible-email']);
                    $submission_responsible->setPhone($post_data['responsible-phone']);
                    $submission_responsible->setCurriculum($post_data['responsible-curriculum']);
                    $submission_responsible->setOrcid($post_data['responsible-orcid']);

                    $file = $request->files->get('responsible-picture');

                    if ( $file ) {
                        $file_ext = '.'.$file->getClientOriginalExtension();
                        $ext_formats = array('.jpg', '.jpeg', '.png');
                        if ( !in_array($file_ext, $ext_formats) ) {
                            $session->getFlashBag()->add('error', $translator->trans("File extension not allowed"));
                            return $output;
                        }

                        $submission_responsible->setFile($file);
                    }
                }

                $em->persist($submission_responsible);
                $em->flush();

                // add in submission
                $submission->addResponsible($submission_responsible);

                $session->getFlashBag()->add('success', $translator->trans("Responsible added with success."));
                return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
            }

            if(isset($post_data['delete-responsible-id']) and !empty($post_data['delete-responsible-id'])) {
                $submission_responsible = $submission_responsible_repository->find($post_data['delete-responsible-id']);
                if($submission_responsible) {
                    $em->remove($submission_responsible);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("Responsible removed with success."));
                    return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
                }
            }

            if(isset($post_data['member-name'])) {
                $post_data = array_map('trim', $post_data);

                // check if exists
                $submission_member = $submission_member_repository->findOneBy(array(
                    'submission' => $submission,
                    'name' => $post_data['member-name']
                ));

                // if not exists, create the new submission_member
                if(!$submission_member) {
                    $submission_member = new SubmissionMember();
                    $submission_member->setSubmission($submission);
                    $submission_member->setName($post_data['member-name']);
                    $submission_member->setAcademicFormation($post_data['member-academic-formation']);
                    $submission_member->setProfessionalCategory($post_data['member-professional-category']);
                    $submission_member->setInstitution($post_data['member-institution']);
                    $submission_member->setResponsibility($post_data['member-responsibility']);
                }

                $em->persist($submission_member);
                $em->flush();

                // add in submission
                $submission->addMember($submission_member);

                $session->getFlashBag()->add('success', $translator->trans("Member added with success."));
                return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
            }

            if(isset($post_data['delete-member-id']) and !empty($post_data['delete-member-id'])) {
                $submission_member = $submission_member_repository->find($post_data['delete-member-id']);
                if($submission_member) {
                    $em->remove($submission_member);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("Member removed with success."));
                    return $this->redirectToRoute('submission_new_fourth_step', array('submission_id' => $submission->getId()), 301);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Fourth step saved with success."));
            return $this->redirectToRoute('submission_new_fifth_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/fifth", name="submission_new_fifth_step")
     * @Template()
     */
    public function FifthStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        $upload_types = $upload_type_repository->findByStatus(true);
        $output['upload_types'] = $upload_types;

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-fifth-step', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            $file = $request->files->get('new-attachment-file');
            if(!empty($file)) {

                $upload_type = $upload_type_repository->findOneBy(array("slug" => "image"));

                $file_ext = '.'.$file->getClientOriginalExtension();
                $ext_formats = $upload_type->getExtensionsFormat();
                if ( !in_array($file_ext, $ext_formats) ) {
                    $session->getFlashBag()->add('error', $translator->trans("File extension not allowed"));
                    return $output;
                }

                $submission_upload = new SubmissionUpload();
                $submission_upload->setSubmission($submission);
                $submission_upload->setUploadType($upload_type);
                $submission_upload->setUser($user);
                $submission_upload->setFile($file);
                $submission_upload->setSubmissionNumber($submission->getNumber());

                $em = $this->getDoctrine()->getManager();
                $em->persist($submission_upload);
                $em->flush();

                $submission->addAttachment($submission_upload);
                $em = $this->getDoctrine()->getManager();
                $em->persist($submission);
                $em->flush();

                $session->getFlashBag()->add('success', $translator->trans("File uploaded with success."));
                return $this->redirectToRoute('submission_new_fifth_step', array('submission_id' => $submission->getId()), 301);

            }

            if(isset($post_data['delete-attachment-id']) and !empty($post_data['delete-attachment-id'])) {

                $submission_upload = $submission_upload_repository->find($post_data['delete-attachment-id']);
                if($submission_upload) {

                    $em->remove($submission_upload);
                    $em->flush();
                    $session->getFlashBag()->add('success', $translator->trans("File removed with success."));
                    return $this->redirectToRoute('submission_new_fifth_step', array('submission_id' => $submission->getId()), 301);
                }
            }

            // checking required fields
            // $required_fields = array('products_information', 'keywords');
            // foreach($required_fields as $field) {
            //     if(!isset($post_data[$field]) or empty($post_data[$field])) {
            //         $session->getFlashBag()->add('error', $translator->trans("Field '%field%' is required.", array("%field%" => $field)));
            //         return $output;
            //     }
            // }

             // adding fields to model
            $submission->setOtherMedias($post_data['other_medias']);
            $submission->setProductsInformation($post_data['products_information']);
            $submission->setKeywords($post_data['keywords']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($submission);
            $em->flush();

            $session->getFlashBag()->add('success', $translator->trans("Fifth step saved with success."));
            return $this->redirectToRoute('submission_new_sixth_step', array('submission_id' => $submission->getId()), 301);
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/sixth", name="submission_new_sixth_step")
     * @Template()
     */
    public function SixthStepAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $locale = $request->getSession()->get('_locale');

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $util = new Util($this->container, $this->getDoctrine());

        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $upload_type_repository = $em->getRepository('Proethos2ModelBundle:UploadType');
        $submission_upload_repository = $em->getRepository('Proethos2ModelBundle:SubmissionUpload');
        $user_repository = $em->getRepository('Proethos2ModelBundle:User');
        $submission_responsible_repository = $em->getRepository('Proethos2ModelBundle:SubmissionResponsible');
        $submission_member_repository = $em->getRepository('Proethos2ModelBundle:SubmissionMember');

        // getting the current submission
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        $mail_translator = $this->get('translator');
        $mail_translator->setLocale($submission->getLanguage());

        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $help_repository = $em->getRepository('Proethos2ModelBundle:Help');
        // $help = $help_repository->findBy(array("id" => {id}, "type" => "mail"));
        // $translations = $trans_repository->findTranslations($help[0]);

        if (!$submission or $submission->getCanBeEdited() == false) {
            if(!$submission or ($submission->getProtocol()->getIsMigrated() and !in_array('administrator', $user->getRolesSlug()))) {
                throw $this->createNotFoundException($translator->trans('No submission found'));
            }
        }

        // Revisions
        $revisions = array();
        $final_status = true;

        // $text = $translator->trans("%total% member(s)", array("%total%" => $submission->getTotalTeam()));
        // $item = array('text' => $text, 'status' => true);
        // $revisions[] = $item;

        $text = $translator->trans('Title');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getTitle())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Collection');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getThematicArea())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Status');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getStatus())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        if ( 'F' == $submission->getStatus() ) {

            $text = $translator->trans('Other Status');
            $item = array('text' => $text, 'status' => true);
            if(empty($submission->getOtherStatus())) {
                $item = array('text' => $text, 'status' => false);
                $final_status = false;
            }
            $revisions[] = $item;

        }

        $text = $translator->trans('Start Date');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getStartDate())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans("Issue");
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getDescription())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Objectives');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getObjectives())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Context');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getContext())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Main Results');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getMainResults())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Challenges');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getChallengesInformation())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Other Results');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getOtherResults())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $text = $translator->trans('Lessons Learned');
        $item = array('text' => $text, 'status' => true);
        if(empty($submission->getLessonsLearned())) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $submission_responsible = $submission_responsible_repository->findOneBy(array('submission' => $submission));
        $text = $translator->trans('Responsible');
        $item = array('text' => $text, 'status' => true);
        if(!$submission_responsible) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;

        $submission_responsible = $submission_member_repository->findOneBy(array('submission' => $submission));
        $text = $translator->trans('Members');
        $item = array('text' => $text, 'status' => true);
        if(!$submission_responsible) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;
/*
        $text = $translator->trans('Attachments');
        $item = array('text' => $text, 'status' => true);
        $upload_type = $upload_type_repository->findOneBy(array('slug' => 'image'));
        $upload_type_id = $upload_type->getId();
        $submission_data = $submission_upload_repository->findBy(array('submission' => $submission->getId(), 'upload_type' => $upload_type_id));
        if( !$submission_data ) {
            $item = array('text' => $text, 'status' => false);
            $final_status = false;
        }
        $revisions[] = $item;
*/
        $output['revisions'] = $revisions;
        $output['final_status'] = $final_status;

        // checking if was a post request
        if($this->getRequest()->isMethod('POST')) {

            $submittedToken = $request->request->get('token');

            if (!$this->isCsrfTokenValid('submission-seventy-step', $submittedToken)) {
                throw $this->createNotFoundException($translator->trans('CSRF token not valid'));
            }

            // getting post data
            $post_data = $request->request->all();

            if($final_status) {

                if($post_data['accept-terms'] == 'on') {

                    // gerando um novo pdf da submission original
                    try {
                        $html = $this->renderView(
                            'Proethos2CoreBundle:NewSubmission:showPdf.html.twig',
                            $output
                        );

                        $pdf = $this->get('knp_snappy.pdf');

                        if ( version_compare(PHP_VERSION, '7.3.0') < 0 ) {
                            // setting margins
                            $pdf->getInternalGenerator()->setOption('margin-top', '50px');
                            $pdf->getInternalGenerator()->setOption('margin-bottom', '50px');
                            $pdf->getInternalGenerator()->setOption('margin-left', '20px');
                            $pdf->getInternalGenerator()->setOption('margin-right', '20px');
                        }

                        // adding pdf to tmp file
                        $filepath = "/tmp/" . uniqid() . '-' . date("Y-m-d-H\hi\ms\s") . "-submission.pdf";
                        file_put_contents($filepath, $pdf->getOutputFromHtml($html));

                        $submission_number = count($submission->getProtocol()->getSubmission());

                        $upload_type = $upload_type_repository->findOneBy(array("slug" => "protocol"));

                        // send tmp file to upload class and save
                        $pdfFile = new SubmissionUpload();
                        $pdfFile->setSubmission($submission);
                        $pdfFile->setSimpleFile($filepath);
                        $pdfFile->setUploadType($upload_type);
                        $pdfFile->setUser($user);
                        $pdfFile->setSubmissionNumber($submission->getNumber());
                        $em->persist($pdfFile);
                        $em->flush();

                    } catch(\RuntimeException $e) {

                        if($post_data['extra'] != 'no-pdf') {
                            $session->getFlashBag()->add('error', $translator->trans('Problems generating PDF. Please contact the administrator.'));
                            return $output;
                        }
                    }

                    // genrating pdf from translations
                    foreach($submission->getTranslations() as $translation) {

                        $new_output = $output;
                        $new_output['submission'] = $translation;

                        // gerando um novo pdf
                        try {
                            $html = $this->renderView(
                                'Proethos2CoreBundle:NewSubmission:showPdf.html.twig',
                                $new_output
                            );

                            $pdf = $this->get('knp_snappy.pdf');

                            if ( version_compare(PHP_VERSION, '7.3.0') < 0 ) {
                                // setting margins
                                $pdf->getInternalGenerator()->setOption('margin-top', '50px');
                                $pdf->getInternalGenerator()->setOption('margin-bottom', '50px');
                                $pdf->getInternalGenerator()->setOption('margin-left', '20px');
                                $pdf->getInternalGenerator()->setOption('margin-right', '20px');
                            }

                            // adding pdf to tmp file
                            $filepath = "/tmp/" . uniqid() . '-' . date("Y-m-d-H\hi\ms\s") . "-submission-". $translation->getLanguage() .".pdf";
                            file_put_contents($filepath, $pdf->getOutputFromHtml($html));

                            $upload_type = $upload_type_repository->findOneBy(array("slug" => "protocol"));

                            // send tmp file to upload class and save
                            $pdfFile = new SubmissionUpload();
                            $pdfFile->setSubmission($submission);
                            $pdfFile->setSimpleFile($filepath);
                            $pdfFile->setUploadType($upload_type);
                            $pdfFile->setUser($user);
                            $pdfFile->setSubmissionNumber($submission->getNumber());
                            $em->persist($pdfFile);
                            $em->flush();

                        } catch(\RuntimeException $e) {

                            if($post_data['extra'] != 'no-pdf') {
                                $session->getFlashBag()->add('error', $translator->trans('Problems generating PDF. Please contact the administrator.'));
                                return $output;
                            }
                        }
                    }

                    // in case of editing migrated posts
                    if ($submission->getProtocol()->getIsMigrated() and !$submission->getCanBeEdited()) {
                        $em->persist($submission);
                        $em->flush();

                        $session->getFlashBag()->add('success', $translator->trans("Best practice submitted with success!"));
                        return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $submission->getProtocol()->getId()), 301);
                    }

                    // updating protocol and setting status
                    $protocol = $submission->getProtocol();
                    $protocol->setStatus("S");
                    $protocol->setDateInformed(new \DateTime());
                    $protocol->setUpdatedIn(new \DateTime());
                    $em->persist($protocol);
                    $em->flush();

                    $submission->setIsSended(true);
                    $em->persist($submission);
                    $em->flush();

                    $protocol_history = new ProtocolHistory();
                    $protocol_history->setProtocol($protocol);
                    $protocol_history->setUser($user);
                    $protocol_history->setMessage($translator->trans("Submission of best practice."));
                    $em->persist($protocol_history);
                    $em->flush();

                    if($protocol->getMonitoringAction()) {
                        // sending email
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                        $help = $help_repository->find(201);
                        $translations = $trans_repository->findTranslations($help);
                        $text = $translations[$submission->getLanguage()];
                        $body = $text['message'];
                        $body = str_replace("%protocol_url%", $url, $body);
                        $body = str_replace("%protocol_code%", $protocol->getCode(), $body);
                        $body = str_replace("\r\n", "<br />", $body);
                        $body .= "<br /><br />";

                        $secretaries_emails = array();
                        foreach($user_repository->findAll() as $secretary) {
                            if(in_array("secretary", $secretary->getRolesSlug())) {
                                $secretaries_emails[] = $secretary->getEmail();
                            }
                        }

                        $message = \Swift_Message::newInstance()
                        ->setSubject("[BP] " . $mail_translator->trans("A new monitoring action has been submitted."))
                        ->setFrom($util->getConfiguration('committee.email'))
                        ->setTo($secretaries_emails)
                        ->setBody(
                            $body
                            ,
                            'text/html'
                        );

                        $send = $this->get('mailer')->send($message);

                        $session->getFlashBag()->add('success', $translator->trans("Amendment submitted with success!"));
                    } else {
                        // sending email
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        $home_url = $baseurl . $this->generateUrl('home');
                        $url = $baseurl . $this->generateUrl('protocol_show_protocol', array("protocol_id" => $protocol->getId()));

                        // sending message to investigator
                        $total_submissions = count($protocol->getSubmission());
                        if ( $total_submissions == 1 ) {
                            $help = $help_repository->find(202);
                            $translations = $trans_repository->findTranslations($help);
                            $text = $translations[$submission->getLanguage()];
                            $body = $text['message'];
                            $body = str_replace("%protocol_url%", $url, $body);
                            $body = str_replace("\r\n", "<br />", $body);
                            $body .= "<br /><br />";

                            $recipient = $protocol->getMainSubmission()->getOwner();

                            $message = \Swift_Message::newInstance()
                            ->setSubject("[BP] " . $mail_translator->trans("Your best practice was sent to review."))
                            ->setFrom($util->getConfiguration('committee.email'))
                            ->setTo($recipient->getEmail())
                            ->setBody(
                                $body
                                ,
                                'text/html'
                            );

                            $send = $this->get('mailer')->send($message);
                        }

                        $help = $help_repository->find(217);
                        $translations = $trans_repository->findTranslations($help);
                        $text = $translations[$submission->getLanguage()];
                        $body = $text['message'];
                        $body = str_replace("%home_url%", $home_url, $body);
                        $body = str_replace("%protocol_url%", $url, $body);
                        $body = str_replace("\r\n", "<br />", $body);
                        $body .= "<br /><br />";

                        $secretaries_emails = array();
                        foreach($user_repository->findAll() as $secretary) {
                            if(in_array("secretary", $secretary->getRolesSlug())) {
                                $secretaries_emails[] = $secretary->getEmail();
                            }
                        }

                        $message = \Swift_Message::newInstance()
                        ->setSubject("[BP] " . $mail_translator->trans("A new best practice has been submitted."))
                        ->setFrom($util->getConfiguration('committee.email'))
                        ->setTo($secretaries_emails)
                        ->setBody(
                            $body
                            ,
                            'text/html'
                        );

                        $send = $this->get('mailer')->send($message);

                        $session->getFlashBag()->add('success', $translator->trans("Best practice submitted with success!"));
                    }

                    return $this->redirectToRoute('protocol_show_protocol', array('protocol_id' => $protocol->getId()), 301);

                } else {
                    $session->getFlashBag()->add('error', $translator->trans("You must accept the terms and conditions."));
                }
            } else {
                $session->getFlashBag()->add('error', $translator->trans('You have pending reviews.'));
            }
        }

        return $output;
    }

    /**
     * @Route("/submission/new/{submission_id}/pdf", name="submission_generate_pdf")
     * @Template()
     */
    public function showPdfAction($submission_id)
    {
        $output = array();
        $request = $this->getRequest();
        $session = $request->getSession();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $util = new Util($this->container, $this->getDoctrine());

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $url = $baseurl . $this->generateUrl('home');
        $output['home_url'] = rtrim($url, '/');

        $configuration_repository = $em->getRepository('Proethos2ModelBundle:Configuration');

        // getting the current submission
        $submission_repository = $em->getRepository('Proethos2ModelBundle:Submission');
        $submission = $submission_repository->find($submission_id);
        $output['submission'] = $submission;

        $protocol = $submission->getProtocol();
        $committee_prefix = $util->getConfiguration('committee.prefix');
        $total_submissions = count($protocol->getSubmission());
        $protocol_code = sprintf('%s.%04d.%02d', $committee_prefix, $protocol->getId(), $total_submissions);
        $output['protocol_code'] = $protocol_code;

        // status options
        $status = array(
            "A" => $translator->trans("The practice is in the initial stage of implementation"),
            "B" => $translator->trans("The practice is fully implemented and continues to operate"),
            "C" => $translator->trans("The practice was discontinued before it was fully implemented"),
            "D" => $translator->trans("The practice was discontinued after a period of operation"),
            "E" => $translator->trans("The practice has not been implemented"),
            "F" => $translator->trans("Other")
        );

        $output['status'] = $status;

        if (!$submission or ($submission->getCanBeEdited() and !in_array('investigator', $user->getRolesSlug()))) {
            throw $this->createNotFoundException($translator->trans('No submission found'));
        }

        $html = $this->renderView(
            'Proethos2CoreBundle:NewSubmission:showPdf.html.twig',
            $output
        );

        $pdf = $this->get('knp_snappy.pdf');

        if ( version_compare(PHP_VERSION, '7.3.0') < 0 ) {
            // setting margins
            $pdf->getInternalGenerator()->setOption('margin-top', '50px');
            $pdf->getInternalGenerator()->setOption('margin-bottom', '50px');
            $pdf->getInternalGenerator()->setOption('margin-left', '20px');
            $pdf->getInternalGenerator()->setOption('margin-right', '20px');
        }

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf'
            )
        );
    }
}
