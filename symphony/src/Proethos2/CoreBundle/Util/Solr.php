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


namespace Proethos2\CoreBundle\Util;


class Solr {

    protected $solr_service = 'http://plugins-idx.bvsalud.org:8983/solr/best-practices/update';

    public function __construct() {}

    public function update($protocol)
    {

        global $kernel;
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $trans_repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $submission = $protocol->getMainSubmission();

        $data = array();
        $data['id'] = $protocol->getId();
        $data['title'] = $submission->getTitle();
        $data['status'] = $submission->getStatus();
        if ( 'F' == $submission->getStatus() ) $data['other_status'] = $submission->getOtherStatus();
        $data['notes'] = $submission->getNotes();
        $data['issue'] = $submission->getDescription();
        $data['objectives'] = $submission->getObjectives();
        $data['resources'] = $submission->getResources();
        $data['context'] = $submission->getContext();
        $data['main_results'] = $submission->getMainResults();
        $data['challenges_information'] = $submission->getChallengesInformation();
        $data['other_results'] = $submission->getOtherResults();
        $data['lessons_learned'] = $submission->getLessonsLearned();
        $data['other_medias'] = $submission->getOtherMediasLinks();
        $data['products_information'] = $submission->getProductsInformation();
        $data['keywords'] = $submission->getKeywordsList();
        $data['start_date'] = $submission->getStartDate()->format('Y-m-d H:i:s');
        if ( $submission->getEndDate() ) $data['end_date'] = $submission->getEndDate()->format('Y-m-d H:i:s');
        if ( $submission->getPartialDate() ) $data['partial_date'] = $submission->getPartialDate()->format('Y-m-d H:i:s');
        if ( $submission->getOtherDate() ) $data['other_date'] = $submission->getOtherDate();

        // thematic area field
        $thematic_area = $submission->getThematicArea();
        $data['thematic_area'] = array();

        foreach ($thematic_area as $ta) {
            $ta->setTranslatableLocale('en');
            $em->refresh($ta);

            // thematic area translations
            $translations = $trans_repository->findTranslations($ta);
            $texts = array();
            $texts['en'] = 'en^'.$ta->getName();
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $_locale) {
                if ( array_key_exists($_locale, $translations) ) {
                    $text = $translations[$_locale];
                    $_locale = substr($_locale, 0, 2);
                    $texts[$_locale] = $_locale.'^'.$text['name'];
                }
            }
            $data['thematic_area'][] = implode('|', $texts);
        }

        // population group field
        $population_group = $submission->getPopulationGroup();
        $data['population_group'] = array();

        foreach ($population_group as $pg) {
            $pg->setTranslatableLocale('en');
            $em->refresh($pg);

            // population group translations
            $translations = $trans_repository->findTranslations($pg);
            $texts = array();
            $texts['en'] = 'en^'.$pg->getName();
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $_locale) {
                if ( array_key_exists($_locale, $translations) ) {
                    $text = $translations[$_locale];
                    $_locale = substr($_locale, 0, 2);
                    $texts[$_locale] = $_locale.'^'.$text['name'];
                }
            }
            $data['population_group'][] = implode('|', $texts);
        }

        // tags field
        $tags = $submission->getTags();
        $data['tags'] = array();

        foreach ($tags as $tag) {
            $tag->setTranslatableLocale('en');
            $em->refresh($tag);

            // tags translations
            $translations = $trans_repository->findTranslations($tag);
            $texts = array();
            $texts['en'] = 'en^'.$tag->getName();
            foreach(array('pt_BR', 'es_ES', 'fr_FR') as $_locale) {
                if ( array_key_exists($_locale, $translations) ) {
                    $text = $translations[$_locale];
                    $_locale = substr($_locale, 0, 2);
                    $texts[$_locale] = $_locale.'^'.$text['name'];
                }
            }
            $data['tags'][] = implode('|', $texts);
        }

        $json = json_encode($data);

        $ch = curl_init($this->solr_service.'/json/docs?commit=true');

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = json_decode(curl_exec($ch));
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return array($response, $responseCode);

    }

    public function delete($protocol)
    {
        $query = ( $protocol ) ? 'id:'.$protocol->getId() : '*:*';
        $json = "{'delete': {'query': '".$query."'}}";
        $ch = curl_init($this->solr_service.'?commit=true');

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        $response = json_decode(curl_exec($ch));
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return array($response, $responseCode);

    }

}
